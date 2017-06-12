<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 24.04.2017
 * Time: 6:19
 */

namespace frontend\modules\controllers ;

use frontend\modules\models\Events;
use frontend\modules\models\Group;
use frontend\modules\models\Hours;
use frontend\modules\models\Portfolio;
use frontend\modules\models\Protocol;
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
		                 ->where(['report_type' => Events::DOCUMENT_ANALYSIS])
		                 ->all();

		$months = [];
		foreach ( $records as $event ) {
			$month = date('n', $event->timestamp);
			$months[DocumentController::MONTH_MAPPING[+$month-1]][] = $event;
		}

		$phpWord = new PhpWord();
		$section = $phpWord->addSection();
		$text = $section->addText('Анализ деятельности классного руководителя');
		$fontStyle = new Font();
		$fontStyle->setBold(true);
		$fontStyle->setAllCaps(true);
		$fontStyle->setName('Times New Roman');
		$fontStyle->setSize(16);
		$text->setFontStyle($fontStyle);

		$section = $phpWord->addSection();
		$tableStyle = array(
			'borderColor' => '000000',
			'borderSize'  => .5,
			'cellMargin'  => .19
		);
		$phpWord->addTableStyle('dataTable', $tableStyle);
		$table = $section->addTable('dataTable');

		$table->addRow();
		$cell = $table->addCell();
		$cell->addText('Дата');

		$cell = $table->addCell();
		$cell->addText('Содержание работы');

		foreach ( $months as $monthName => $month ) {
			$table->addRow();
			$table->addCell();
			$cell = $table->addCell();
			$cell->addText($monthName);

			foreach ( $month as $event) {
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
			return $user->can('student');
		});

		$tp = new TemplateProcessor(self::locateTemplate('guarded_family'));
		$tp->cloneRow('i', count($users));

		foreach ( $users as $i => $user ) {
			$tp->setValue("i", $i + 1);
			$tp->setValue('s_name', $user->fullname());
			$tp->setValue('p_name', $user->family->p_name);
			$tp->setValue('purposes', $user->family->purposes);
			$tp->setValue('address_phone', $user->family->p_address . '\\n' . $user->family->p_phone);
		}

		$filename = 'Опекунские семьи.docx';

		$tp->saveAs($filename);

		return $filename;
	}

	private function processHours( $group_id ) {

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