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
use frontend\modules\models\User;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\base\InvalidParamException;

class DocumentController extends ApiController
{

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



	public $modelClass = 'frontend\modules\models\Role';

	public function actions() {
		$actions = parent::actions();

		unset($actions['index'], $actions['view']);

		return $actions;
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

	public function actionIndex() {

	}

	public function actionView($type_id) {

		$token = $this->parseBearerAuthToken();
		$teacher = User::findIdentityByAccessToken($token);

		$filename = NULL;

		switch ($type_id) {
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