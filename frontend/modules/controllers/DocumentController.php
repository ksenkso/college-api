<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 24.04.2017
 * Time: 6:19
 */

namespace frontend\modules\controllers ;

use frontend\modules\models\Attachment;
use frontend\modules\models\AuthAssignment;
use frontend\modules\models\Events;
use frontend\modules\models\Family;
use frontend\modules\models\Group;
use frontend\modules\models\Hours;
use frontend\modules\models\Portfolio;
use frontend\modules\models\Protocol;
use frontend\modules\models\Role;
use frontend\modules\models\Spec;
use frontend\modules\models\User;
use frontend\modules\models\UserMeta;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Reader\Word2007;
use PhpOffice\PhpWord\Shared\ZipArchive;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\web\UnauthorizedHttpException;

class DocumentController extends ApiController
{

	const DOCUMENT_ANALYSIS = 1;
	const DOCUMENT_DIARY = 2;
	const DOCUMENT_PLAN = 3;
	const DOCUMENT_PARENTS = 4;

	const MONTH_MAPPING = [
		"Январь",
		"Февраль",
		"Март",
		"Апрель",
		"Май",
		"Июнь",
		"Июль",
		"Август",
		"Сентябрь",
		"Октябрь",
		"Ноябрь",
		"Декабрь"
	];
	const DOCUMENT_OUTCLASS_EVENT = 5;
	const DOCUMENT_PARENTS_MEETING = 6;
	const DOCUMENT_HEALTH_LIST = 7;
	const DOCUMENT_ACTIVITY = 8;
	const DOCUMENT_PORTFOLIO = 9;
	const DOCUMENT_HOURS = 10;

	private static function locateTemplate($name) {
		return \Yii::getAlias('@app') . '/templates/tpl_' . $name . '.docx';
	}

	public $modelClass = 'frontend\modules\models\Role';

	public function actions() {
		$actions = parent::actions();

		unset($actions['index'], $actions['view']);

		return $actions;
	}

	public function actionGroup() {

		$token = $this->parseBearerAuthToken();
		$user = User::findIdentityByAccessToken($token);

		if (!$user) {
			throw new UnauthorizedHttpException();
		}

		$result = [];

		$found = User::find()
			->where(['group_id' => $user->id])
			->asArray()
			->all();
		foreach ( $found as $i => $item ) {
			if ($item['id'] != $user->id) $result[] = $item;
		}

		$group = Group::findOne($user->group_id);
	}

	private function getWorkingDays($startDate,$endDate,$holidays){
		// do strtotime calculations just once
		$endDate = strtotime($endDate);
		$startDate = strtotime($startDate);


		//The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
		//We add one to inlude both dates in the interval.
		$days = ($endDate - $startDate) / 86400 + 1;

		$no_full_weeks = floor($days / 7);
		$no_remaining_days = fmod($days, 7);

		//It will return 1 if it's Monday,.. ,7 for Sunday
		$the_first_day_of_week = date("N", $startDate);
		$the_last_day_of_week = date("N", $endDate);

		//---->The two can be equal in leap years when february has 29 days, the equal sign is added here
		//In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
		if ($the_first_day_of_week <= $the_last_day_of_week) {
			if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
			if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
		}
		else {
			// (edit by Tokes to fix an edge case where the start day was a Sunday
			// and the end day was NOT a Saturday)

			// the day of the week for start is later than the day of the week for end
			if ($the_first_day_of_week == 7) {
				// if the start date is a Sunday, then we definitely subtract 1 day
				$no_remaining_days--;

				if ($the_last_day_of_week == 6) {
					// if the end date is a Saturday, then we subtract another day
					$no_remaining_days--;
				}
			}
			else {
				// the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
				// so we skip an entire weekend and subtract 2 days
				$no_remaining_days -= 2;
			}
		}

		//The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
		$workingDays = $no_full_weeks * 5;
		if ($no_remaining_days > 0 )
		{
			$workingDays += $no_remaining_days;
		}

		//We subtract the holidays
		foreach($holidays as $holiday){
			$time_stamp=strtotime($holiday);
			//If the holiday doesn't fall in weekend
			if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
				$workingDays--;
		}

		return $workingDays;
	}

	private function processAnalysis($teacher) {
		$group = Group::findOne($teacher->group_id);
		$records = Events::find()
		                 ->where(['report_type' => Events::DOCUMENT_ANALYSIS])
		                 ->all();

		$templateProcessor = new TemplateProcessor(\Yii::getAlias('@app') . '/templates/tpl_analysis.docx');

		$templateProcessor->setValue(
			'teacher_name',
			$teacher->last_name . ' ' . $teacher->first_name . ' ' . $teacher->patronymic
		);

		$templateProcessor->setValue('group_name', $group->abbreviation);

		$templateProcessor->cloneRow('date', count($records));

		foreach ( $records as $i => $record ) {
			$templateProcessor->setValue("date#" . ($i+1), date('d.m.Y', $record->timestamp) );
			$templateProcessor->setValue("form#" . ($i+1), $record->form);
			$templateProcessor->setValue("title#" . ($i+1), $record->title);
			$templateProcessor->setValue("tasks#" . ($i+1), $record->description);
			$templateProcessor->setValue("responsible#" . ($i+1), $record->responsible);
			$templateProcessor->setValue("result#" . ($i+1), $record->results);
		}

		$filename = "Анализ деятельности {$teacher->last_name} {$teacher->first_name[0]} {$teacher->patronymic[0]}.docx";
		$templateProcessor->saveAs($filename);

		return $filename;
	}

	private function processActivity( $user_id ) {

		$start = time();
		$end = time() + 365 * 24 * 3600;

		/**
		 * @var Events[] $events
		 */
		$events = Events::find()
			->where(
				[
					'and',
					[
						'and',
						['>=', 'timestamp', $start],
						['<=', 'timestamp', $end]
					],
					['user_id' => $user_id]
				]
			)
			->andWhere([
				'type_id' => Events::EVENT_ACTIVITY
			])
			->all();

		$tp = new TemplateProcessor(self::locateTemplate('activity'));

		$tp->setValue('start_year', date('Y', $start));
		$tp->setValue('end_year', date('Y', $end));

		$tp->cloneRow('date', count($events));

		foreach ( $events as $i => $event ) {

			list($result, $names) = explode('&', $event->description);

			$tp->setValue("date#$i", date('d.m.Y', $event->timestamp));
			$tp->setValue("event_name#$i", $event->title);
			$tp->setValue("result#$i", $result);
			$tp->setValue("description#$i", $names);
		}

		$filename = 'Мероприятия ' . date('Y', $start) . '-' . date('Y', $end);

		$tp->saveAs($filename);

		return $filename;
	}

	private function processDiary($teacher) {
		$records = Events::find()
		                 ->where(['report_type' => Events::DOCUMENT_DIARY])
		                 ->all();

		$months = [
			['name' => "Январь", 'events' => []],
			['name' => "Февраль", 'events' => []],
			['name' => "Март", 'events' => []],
			['name' => "Апрель", 'events' => []],
			['name' => "Май", 'events' => []],
			['name' => "Июнь", 'events' => []],
			['name' => "Июль", 'events' => []],
			['name' => "Август", 'events' => []],
			['name' => "Сентябрь", 'events' => []],
			['name' => "Октябрь", 'events' => []],
			['name' => "Ноябрь", 'events' => []],
			['name' => "Декабрь", 'events' => []],
		];
		foreach ( $records as $event ) {
			$month = date('n', $event->timestamp);
			$months[+$month-1]['events'][] = $event;
		}



		$phpWord = new PhpWord();
		$section = $phpWord->addSection();
		$text = $section->addText('Дневник классного руководителя');
		$fontStyle = new Font();
		$fontStyle->setBold(true);
		$fontStyle->setAllCaps(true);
		$fontStyle->setName('Times New Roman');
		$fontStyle->setSize(16);
		$text->setFontStyle($fontStyle, ['align' => 'center']);

		$section = $phpWord->addSection();
		$tableStyle = array(
			'borderColor' => '000000',
			'borderSize'  => 1.5,
			'cellMargin'  => .19
		);
		$phpWord->addTableStyle('dataTable', $tableStyle, ['bgColor' => '66BBFF']);
		$table = $section->addTable('dataTable');

		$table->addRow(100);
		$cell = $table->addCell(1.5 * 1000);
		$cell->addText('Дата');

		$cell = $table->addCell(8.5 * 1000);
		$cell->addText('Содержание работы');

		\Yii::trace(json_encode($months));
foreach ( $months as $month ) {

			$row = $table->addRow();
			$row->addCell();
			$cell = $row->addCell();
			$cell->addText($month['name']);

			foreach ( $month['events'] as $event) {
				$table->addRow();

				$cell = $table->addCell();
				$cell->addText(date('d.m.Y', $event->timestamp));

				$cell = $table->addCell();
				$cell->addText($event->title);
			}

		}

		$filename = "Дневник классного руководителя {$teacher->last_name} {$teacher->first_name[0]} {$teacher->patronymic[0]}.docx";


		$saver = IOFactory::createWriter($phpWord);
		$saver->save($filename);

		return $filename;

	}

	private function processBreeding( $user, $group_id ) {

		$group = Group::findOne($group_id);

		/**
		 * @var User[] $users
		 */
		$users = User::find()
		             ->where(['group_id' => $group_id])
		             ->with('metaBreeding')
		             ->all();


		$users = array_filter($users, function(User $user) {
			return $user->can('student');
		});

		$tp = new TemplateProcessor(self::locateTemplate('breeding'));
		$tp->cloneRow('i', count($users));

		foreach ( $users as $i => $user ) {

			$meta = UserMeta::unpackMeta($user->healthsMeta);

			$avg = array_sum([$meta['intelligence'],
				$meta['mercy'],
				$meta['responsibility'],
				$meta['justice'],
				$meta['character'],]) / 5;

			if (!$avg) $avg = 0;

			$tp->setValue("i", $i + 1);
			$tp->setValue("full_name#$i", $user->fullname());
			$tp->setValue("int#$i", $meta['intelligence']);
			$tp->setValue("mercy#$i", $meta['mercy']);
			$tp->setValue("resp#$i", $meta['responsibility']);
			$tp->setValue("jus#$i", $meta['justice']);
			$tp->setValue("char#$i", $meta['character']);
			$tp->setValue("avg#$i", $avg);
		}

		$tp->setValue('teacher_name', $user->fullName());

		$filename = 'Мониторинг воспитанности ' . $group->abbreviation . ' .docx';

		$tp->saveAs($filename);

		return $filename;
	}

	/**
	 * @param User $teacher
	 *
	 * @return string
	 */
	private function processHours( $teacher, $month, $year ) {

		/**
		 * @var Group $group
		 * @var Spec $spec
		 * @var User[] $students
		 */
		$group = Group::find()->with('spec')->where(['id' => $teacher->group_id])->one();
		$spec = $group->spec;
		$stewards = AuthAssignment::find()->select('user_id')->where(['item_name' => 'steward'])->asArray()->all();


		$tp = new TemplateProcessor(\Yii::getAlias('@app') . '/templates/tpl_hours.docx');
		$tp->setValue('code', $spec->code);
		$tp->setValue('spec_name', $spec->name);
		$tp->setValue('abbr', $group->abbreviation);
		$tp->setValue('month', self::MONTH_MAPPING[$month-1]);
		$tp->setValue('year', $year);


		$students = User::find()
			->where(['group_id' => $teacher->group_id])
			->andWhere(['<>', 'id', $teacher->id])
			->all();

		$tp->cloneRow('i', count($students));

		$start = new \DateTime($year . '-' . $month . '-' . '01');
		$ts = clone $start;
		$end = $ts->add(new \DateInterval('P1M'));
		$days = $start->diff($end, true)->days;
		$sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
		$daysMonth = $days - $sundays;

		$tp->setValue('days', $daysMonth);
		$tp->setValue('teacher', $teacher->last_name . ' ' . $teacher->first_name[0] . '. ' . $teacher->patronymic[0] . '.');
		$tp->setValue('steward', '');

		foreach ( $students as $i => $student ) {
			$tp->setValue("i#" . ($i+1), $i+1);
			$tp->setValue("fio#" . ($i+1), $student->fullname());


			$days = [
				'd1',
				'd2',
				'd3',
				'd4',
				'd5',
				'd6',
				'd7',
				'd8',
				'd9',
				'd10',
				'd11',
				'd12',
				'd13',
				'd14',
				'd15',
				'd16',
				'd17',
				'd18',
				'd19',
				'd20',
				'd21',
				'd22',
				'd23',
				'd24',
				'd25',
				'd26',
				'd27',
				'd28',
				'd29',
				'd30',
				'd31',
			];
			/**
			 * @var Hours[] $hours
			 */
			$hours = Hours::find()->where(['student_id' => $student->id])->all();
			$sum = 0;
			$sumGood = 0;

			foreach ( $hours as $hour ) {
				$index = explode('-', $hour->date)[2];
				$value = array_reduce(str_split($hour->hours), function($acc, $next) {
					$acc += intval($next);
					return $acc;
				}, 0);
				$tp->setValue('d' . $index . '#' . ($i+1), $value);
				$sum += intval($value);
				if ($hour->hours_good) {
					$value = array_reduce(str_split($hour->hours_good), function($acc, $next) {
						$acc += intval($next);
						return $acc;
					}, 0);
					$sumGood += intval($value);
				}
				unset($days[intval($index)-1]);
			}

			foreach ( $days as $day ) {
				$tp->setValue($day . '#' . ($i+1), '');
			}

			$dn  = $sum - $sumGood;
			$dc = round($daysMonth - round($dn / 6, PHP_ROUND_HALF_DOWN));

			$tp->setValue('dc#' . ($i+1), $dc);
			$tp->setValue('dg#' . ($i+1), $sumGood);
			$tp->setValue('dsum#' . ($i+1), $sum);
			$tp->setValue('dn#' . ($i+1), $dn);
		}

		$filename = 'Ведомость пропусков занятий.docx';

		$tp->saveAs($filename);

		return $filename;

	}

	/**
	 * @param User $user
	 *
	 * @return string
	 */
	private function processGuardedFamilies( $user ) {

		$users = User::find()
		             ->where(['group_id' => $user->group_id])
		             ->with('family')
		             ->all();

		/**
		 * @var User[] $users
		 */
		$users = array_filter($users, function(User $user) {
			return $user->can('student') && $user->family->type == Family::FAMILY_GUARDED;
		});

		$tp = new TemplateProcessor(self::locateTemplate('guarded_family'));
		$tp->cloneRow('i', count($users));

		foreach ( $users as $i => $user ) {
			$tp->setValue("i#$i", $i + 1);
			$tp->setValue("s_name#$i", $user->fullname());
			$tp->setValue("p_name#$i", $user->family->p_name);
			$tp->setValue("purposes#$i", $user->family->purposes);
			$tp->setValue("phone_address#$i", $user->family->p_address . '\\n' . $user->family->p_phone);
		}

		$filename = 'Опекунские семьи.docx';

		$tp->saveAs($filename);

		return $filename;
	}

	private function processProblemFamily( $user ) {
		$users = User::find()
		             ->where(['group_id' => $user->group_id])
		             ->with('family')
		             ->all();

		/**
		 * @var User[] $users
		 */
		$users = array_filter($users, function(User $user) {
			return $user->can('student') && $user->family->type == Family::FAMILY_PROBLEM;
		});

		$tp = new TemplateProcessor(self::locateTemplate('problem_family'));
		$tp->cloneRow('i', count($users));

		foreach ( $users as $i => $user ) {
			$tp->setValue("i#$i", $i + 1);
			$tp->setValue("s_name#$i", $user->fullname());
			$tp->setValue("p_name#$i", $user->family->p_name);
			$tp->setValue("edu_type#$i", $user->family->edu_type);
			$tp->setValue("trouble#$i", $user->family->trouble);
			$tp->setValue("phone_address#$i", $user->family->p_address . '\\n' . $user->family->p_phone);
		}

		$filename = 'Малообеспеченные семьи.docx';

		$tp->saveAs($filename);

		return $filename;
	}

	private function processPoorFamily( $user ) {
		$users = User::find()
		             ->where(['group_id' => $user->group_id])
		             ->with('family')
		             ->all();

		/**
		 * @var User[] $users
		 */
		$users = array_filter($users, function(User $user) {
			return $user->can('student') && $user->family->type == Family::FAMILY_POOR;
		});

		$tp = new TemplateProcessor(self::locateTemplate('poor_family'));
		$tp->cloneRow('i', count($users));

		foreach ( $users as $i => $user ) {
			$tp->setValue("i#$i", $i + 1);
			$tp->setValue("s_name#$i", $user->fullname());
			$tp->setValue("p_name#$i", $user->family->p_name);
			$tp->setValue("employment#$i", $user->family->p_employment);
			$tp->setValue("address_phone#$i", $user->family->p_address . '\\n' . $user->family->p_phone);
		}

		$filename = 'Малообеспеченные семьи.docx';

		$tp->saveAs($filename);

		return $filename;
	}

	private function processRichFamily( $user ) {
		$users = User::find()
		             ->where(['group_id' => $user->group_id])
		             ->with('family')
		             ->all();

		/**
		 * @var User[] $users
		 */
		$users = array_filter($users, function(User $user) {
			return $user->can('student') && $user->family->type == Family::FAMILY_RICH;
		});

		$tp = new TemplateProcessor(self::locateTemplate('poor_family'));
		$tp->cloneRow('i', count($users));

		foreach ( $users as $i => $user ) {
			$tp->setValue("i#$i", $i + 1);
			$tp->setValue("s_name#$i", $user->fullname());
			$tp->setValue("consist#$i", $user->family->consist);
			$tp->setValue("p_name#$i", $user->family->p_name);
			$tp->setValue("employment#$i", $user->family->p_employment);
			$tp->setValue("address_phone#\$i", $user->family->p_address . '\\n' . $user->family->p_phone);
		}

		$filename = 'Многодетные семьи.docx';

		$tp->saveAs($filename);

		return $filename;
	}

	private function processAllFamily( $user ) {
		$users = User::find()
		             ->where(['group_id' => $user->group_id])
		             ->with('family')
		             ->all();

		/**
		 * @var User[] $users
		 */
		$users = array_filter($users, function(User $user) {
			return $user->can('student');
		});

		$tp = new TemplateProcessor(self::locateTemplate('normal_family'));
		$tp->cloneRow('i', count($users));

		foreach ( $users as $i => $user ) {
			$tp->setValue("i", $i + 1);
			$tp->setValue('s_name', $user->fullname());
			$tp->setValue('p_name', $user->family->p_name);
			$tp->setValue('employment', $user->family->p_employment);
			$tp->setValue('address_phone', $user->family->p_address . '\\n' . $user->family->p_phone);
		}

		$filename = 'Малообеспеченные семьи.docx';

		$tp->saveAs($filename);

		return $filename;
	}

	private function proocessHours( $group_id ) {

		$start = date('Y-m-d', mktime(0, 0, 0, null, 1));
		$end = date('Y-m-d', mktime(0, 0, 0, (intval(date('n')) + 1), 1));

		$hours = Hours::find()
			->where(['group_id' => $group_id])
			->andWhere([
				'and',
				['>=', 'date', $start],
				['<=', 'date', $end]
			])
			->all();
	}

	/**
	 * @param $protocol_id
	 *
	 * @return string
	 */
	private function processOutclassEvent( $protocol_id ) {

		\Yii::trace($protocol_id);

		/**
		 * @var Protocol $protocol
		 */
		$protocol = Protocol::findOne($protocol_id);

		if (!$protocol) {
			throw new InvalidParamException('No such protocol');
		}

		$tp = new TemplateProcessor(self::locateTemplate('outclass_event'));
		$tp->setValue('theme', $protocol->theme);
		$tp->setValue('purposes', $protocol->purposes);
		$tp->setValue('form', $protocol->form);

		$date = date('d.m.Y', $protocol->date);

		$tp->setValue('date', $date);
		$tp->setValue('plan', $protocol->plan);
		$tp->setValue('organization', $protocol->organization);
		$tp->setValue('analysis', $protocol->analysis);
		$tp->setValue('conclusions', $protocol->conclusions);

		$filename = 'Внеклассное мероприятия ' . $date . '.docx';

		$tp->saveAs($filename);

		return $filename;

	}

	/**
	 * @param $group_id
	 *
	 * @return string
	 */
	private function processHealthList( $group_id ) {

		/**
		 * @var User[] $users
		 */
		$users = User::find()
		             ->where(['group_id' => $group_id])
		             ->with('metaHealths')
		             ->all();


		$users = array_filter($users, function(User $user) {
			return $user->can('student');
		});

		$tp = new TemplateProcessor(self::locateTemplate('health'));
		$tp->cloneRow('i', count($users));
		foreach ( $users as $i => $user ) {

			$meta = UserMeta::unpackMeta($user->healthsMeta);

			$tp->setValue("i", $i + 1);
			$tp->setValue("full_name#$i", $user->fullname());
			$tp->setValue("health_group#$i", $meta['health_group']);
			$tp->setValue("policy#$i", $meta['insurance_policy']);
			$tp->setValue("recs#$i", $meta['health_recs']);
		}

		$filename = 'Листок здоровья.docx';

		$tp->saveAs($filename);

		return $filename;
	}

	/**
	 * @param User $user
	 * @param integer $protocol_id
	 *
	 * @return string
	 */
	private function processParentsMeeting( $user, $protocol_id ) {
		\Yii::trace($protocol_id);

		/**
		 * @var Protocol $protocol
		 */
		$protocol = Protocol::findOne($protocol_id);

		if (!$protocol) {
			throw new InvalidParamException('No such protocol');
		}

		$tp = new TemplateProcessor(self::locateTemplate('parents_meeting'));
		$tp->setValue('theme', $protocol->theme);
		$tp->setValue('purposes', $protocol->purposes);
		$tp->setValue('number', $protocol->number);

		$date = date('d.m.Y', $protocol->date);

		$tp->setValue('date', $date);
		$tp->setValue('content', $protocol->plan);
		$tp->setValue('conclusions', $protocol->conclusions);

		$filename = 'Родительское собрание ' . $date . '.docx';
		$tp->setValue('teacher_name', $user->fullName());

		$tp->saveAs($filename);

		return $filename;
	}

	private function readDoc() {

		/**
		 * @var Word2007 $reader
		 */
		$res = [];
		$reader = IOFactory::createReader();
		$phpWord = $reader->load(\Yii::getAlias('@app') . '/templates/tpl_analysis.docx');
		$sections = $phpWord->getSections();
		foreach ( $sections as $section ) {

			$elements = $section->getElements();
			$res[$section->getSectionId()] = $elements;
		}

		return $res;


	}

	public function actionIndex() {

		/*$res = [];


		$reader = IOFactory::createReader();
		$phpWord = $reader->load(\Yii::getAlias('@app') . '/templates/tpl_test.docx');
		$sections = $phpWord->getSections();

		foreach ( $sections as $section ) {

			$elements = $section->getElements();

			foreach ( $elements as $element ) {
				if ($element instanceof Table) {

					$rows = $element->getRows();
					foreach ( $rows as $rowIndex => $row ) {
						$cells = $row->getCells();
						foreach ( $cells as $cell ) {
							$innerElements = $cell->getElements();
							foreach ( $innerElements as $item ) {
								if ($item instanceof \PhpOffice\PhpWord\Element\Text) {
									$res[$rowIndex][] = $item->getText();
								}
							}
						}
					}
				}
			}
			//$res[$section->getSectionId()] = $elements;
		}*/

		// return $res;

		return Portfolio::processPortfolio(3);
	}

	public function actionView($type_id, $meta_id = null) {

		$token = $this->parseBearerAuthToken();
		$teacher = User::findIdentityByAccessToken($token);

		if ($teacher) {

			switch ($type_id) {
				case static::DOCUMENT_OUTCLASS_EVENT: {
					$filename = $this->processOutclassEvent($meta_id);

					break;
				}
				case static::DOCUMENT_ACTIVITY: {
					$filename = $this->processActivity($teacher->id);

					break;
				}
				case static::DOCUMENT_PARENTS_MEETING: {
					$filename = $this->processParentsMeeting($teacher, $meta_id);

					break;
				}
				case static::DOCUMENT_HEALTH_LIST: {
					$filename = $this->processHealthList($teacher->group_id);

					break;
				}
				case Events::DOCUMENT_ANALYSIS: {
					$filename = $this->processAnalysis($teacher);

					\Yii::trace($filename);
					break;
				}
				case Events::DOCUMENT_DIARY: {

					$filename = $this->processDiary($teacher);

					break;
				}
				case self::DOCUMENT_PORTFOLIO: {

					$filename = Portfolio::processPortfolio($meta_id);

					/**
					 * @var Attachment[] $attachments
					 */
					$attachments = Attachment::find()
						->where(['user_id' => $meta_id, 'type' => Attachment::TYPE_DOC])
						->all();

					$title = Portfolio::processTitleList($meta_id);

					$zip = new \ZipArchive();

					if ($zip->open($filename . '.zip', ZipArchive::CREATE)) {

						$zip->addFile($filename);
						$zip->addFile($title);

						foreach ( $attachments as $attachment ) {
							$zip->addEmptyDir('uploads');
							$zip->addFile(\Yii::getAlias('@app') . '/web/' . $attachment->source, $attachment->source);
						}

						$zip->close();
						$filename = $filename . '.zip';
					}


					break;
				}
				case self::DOCUMENT_HOURS: {
					$filename = $this->processHours($teacher, $meta_id, 2017);

					break;
				}

				default: {
					throw new InvalidParamException('Invalid type of document');
				}
			}

			\Yii::$app->response->sendFile($filename, $filename);
			unlink($filename);

			return;
		}




	}

}