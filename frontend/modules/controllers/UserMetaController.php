<?php
/**
 * Created by PhpStorm.
 * User: ksenkso
 * Date: 06.06.17
 * Time: 15:38
 */

namespace frontend\modules\controllers;


use frontend\modules\models\User;
use frontend\modules\models\UserMeta;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;

class UserMetaController extends ApiController {

	public $modelClass = 'frontend\modules\models\UserMeta';

	public function actions() {
		$actions = parent::actions();

		unset($actions['view'], $actions['create']);

		return $actions;
	}

	public function actionByType( $type, $user_id = null) {

		$token = $this->parseBearerAuthToken();
		$user = User::findIdentityByAccessToken($token);


		if ( $type < 10) {

			$metaType = UserMeta::META_TYPES[$type];
		} else {

			if ( $type < 13 ) {

				$metaType = UserMeta::TEACHER_META_TYPES[$type];
			} else throw new InvalidParamException('Meta type is not valid. Valid values are: 0, 1, 2. Given: ' . $type);
		}

		$users = User::find()->select(['id', 'first_name', 'last_name', 'patronymic', 'birth_date', 'address']);

		if ($user_id) {
			$users = $users->where(['id' => $user_id]);
		} else {
			$users = $users->where(['group_id' => $user->group_id]);
		}

		$found = $users
			->with($metaType)
			->asArray()
			->all();
		$result = [];

		if (!$user_id) {
			foreach ( $found as $i => $item ) {
				if ($item['id'] != $user->id) $result[] = $item;
			}
		} else return $found;


		return $result;
	}

	public function actionView( $user_id ) {
		return UserMeta::find()->where(['user_id' => $user_id])->all();
	}

	public function actionBatch() {
		$request = \Yii::$app->request->post();

		$create = $request['create'];
		$update = $request['update'];
		$delete = $request['delete'];

		if (count($create)) {
			foreach ( $create as $item ) {

				$model = new UserMeta();
				$model->load(['UserMeta' => $item]);
				if (!$model->save()) {
					return $model->getErrors();
				}
			}
		}

		if (count($update)) {
			foreach ( $update as $item ) {

				$model = UserMeta::findOne($item['id']);
				unset($item['id']);
				$model->load(['UserMeta' => $item]);
				if (!$model->save()) {
					return $model->getErrors();
				}
			}
		}

		if (count($delete)) {
			foreach ( $delete as $id ) {

				UserMeta::findOne($id)->delete();
			}
		}

		return true;
	}

	public function actionCreate() {
		$request = \Yii::$app->request->post();

		foreach ( $request as $item ) {

			if ($item['id']) {
				$userMeta = UserMeta::findOne($item['id']);
			} else {
				$userMeta = new UserMeta();
			}

			unset($item['id']);
			$userMeta->load(['UserMeta' => $item]);
			if (!$userMeta->save()) return $userMeta->getErrors();

		}

		return true;

	}
}