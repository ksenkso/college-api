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
use frontend\modules\models\Portfolio;
use frontend\modules\models\User;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Reader\Word2007;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\base\InvalidParamException;
use yii\web\UploadedFile;

class PortfolioController extends ApiController
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

		unset($actions['index'], $actions['view'], $actions['create'], $actions['view']);

		return $actions;
	}

	public function createAttachment( $user_id, $type_id ) {

	}

	public function actionIndex() {

		$res = [];

		/**
		 * @var Word2007 $reader
		 */
		$reader = IOFactory::createReader();
		$phpWord = $reader->load(\Yii::getAlias('@app') . '/templates/tpl_test.docx');
		$sections = $phpWord->getSections();

		foreach ( $sections as $section ) {

			$elements = $section->getElements();

			foreach ( $elements as $element ) {
				if ($element instanceof Table) {
					/**
					 * @var Table $element
					 */
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
		}

		return $res;
	}

	public function actionUpdate() {

		$updatedRecords = \Yii::$app->request->post();

		foreach ( $updatedRecords as $updated_record ) {

			/**
			 * @var Portfolio $model
			 */
			$model = Portfolio::findOne($updated_record['id']);
			$model->load(['Portfolio' => $updated_record]);
			if (!$model->save()) {
				throw new InvalidParamException($model->getErrors());
			}
		}
		return true;
	}

	public function actionCreate() {
		$request = \Yii::$app->request->post();

		$create = $request['create'];
		$update = $request['update'];
		$delete = $request['delete'];

		if (count($create)) {
			foreach ( $create as $item ) {

				$model = new Portfolio();
				$model->load(['Portfolio' => $item]);
				if (!$model->save()) {
					return $model->getErrors();
				}
			}
		}

		if (count($update)) {
			foreach ( $update as $item ) {

				$model = Portfolio::findOne($item['id']);
				unset($item['id']);
				$model->load(['Portfolio' => $item]);
				if (!$model->save()) {
					return $model->getErrors();
				}
			}
		}

		if (count($delete)) {
			foreach ( $delete as $id ) {

				Portfolio::findOne($id)->delete();
			}
		}

		return true;

	}

	public function actionView( $user_id,  $type_id ) {

		return Portfolio::find()
			->where([
				'user_id' => $user_id,
				'record_type' => $type_id
			])
			->all();
	}

	public function actionUpload( $user_id ) {

		$file = UploadedFile::getInstanceByName('file');

		$record = new Portfolio();
		$record->user_id = $user_id;
		$record->content = $file->name;
		$record->record_type = Portfolio::TYPE_ATTACHEMENT;

		if ($record->save()) {
			$file->saveAs(\Yii::getAlias('@app') . '/attachments/' . $file->name);
			return true;
		}

		return false;
	}

	public function actionUploadPhoto( $user_id ) {

		$file = UploadedFile::getInstanceByName('file');

		$record = new Portfolio();
		$record->user_id = $user_id;
		$record->content = $file->name;
		$record->record_type = Portfolio::TYPE_ATTACHEMENT;

		if ($record->save()) {
			$file->saveAs(\Yii::getAlias('@app') . '/attachments/' . $file->name);
			return true;
		}

		return false;
	}



}