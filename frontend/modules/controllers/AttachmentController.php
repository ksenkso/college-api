<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 24.04.2017
 * Time: 6:19
 */

namespace frontend\modules\controllers ;

use frontend\modules\models\Attachment;
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
use yii\web\MultipartFormDataParser;
use yii\web\Response;
use yii\web\UploadedFile;

class AttachmentController extends ApiController
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


	public $modelClass = 'frontend\modules\models\Attachment';

	public function actions() {
		$actions = parent::actions();

		unset($actions['index'], $actions['view'], $actions['create'], $actions['view'], $actions['delete']);

		return $actions;
	}

	public function createAttachment( $user_id, $type ) {

	}

	public function actionDelete( $id ) {

		Attachment::findOne($id)->delete();

		return $id;
	}

	public function actionIndex() {


	}

	public function actionUpdate() {


	}

	public function actionView( $user_id, $type ) {
		return Attachment::find()
			->where(['user_id' => $user_id, 'type' => $type])
			->asArray()
			->all();

	}

	public function actionCreate($user_id, $type) {


		if ($type == Attachment::TYPE_PHOTO) {
			$model = Attachment::find()->where(['user_id' => $user_id, 'type' => Attachment::TYPE_PHOTO])->one();
		} else {
			$model = new Attachment();
			$model->user_id = $user_id;
			$model->type = $type;
		}




		if ($model->load(['Attachment' => \Yii::$app->request->post()]) && $model->save()) {
			return $model;
		} else {
			\Yii::$app->response->setStatusCode(400);
			return $model->getErrors();
		}

	}

	public function actionLoad( $user_id ) {

		$request = \Yii::$app->request->post();

		$result = [];

		\Yii::trace(json_encode($request));

		$files = UploadedFile::getInstancesByName('attachments');


		foreach ( $files as $i => $attachment ) {
			$model = new Attachment();
			preg_match("~([a-zA-z0-9_-]+)\.(\w+)$~", $request['titles'][$i], $matches);

			$model->title = $matches[1];

			$model->user_id = $user_id;
			$model->type = Attachment::TYPE_DOC;
			if ($model->save(true, null,  $attachment)) {
				$result[] = $model;
			} else {
				return $model->getErrors();
			}
		}

		return $result;
	}


}