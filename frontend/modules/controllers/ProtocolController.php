<?php

namespace frontend\modules\controllers ;

use frontend\modules\models\Portfolio;
use frontend\modules\models\Protocol;
use frontend\modules\models\User;
use yii\base\InvalidParamException;
use yii\web\UnauthorizedHttpException;

class ProtocolController extends ApiController
{

	public function actions() {
		$actions = parent::actions();

		unset($actions['index'], $actions['view'], $actions['create']);

		return $actions;

	}

	public $modelClass = 'frontend\modules\models\Protocol';

	public function actionCreate() {
		$token = $this->parseBearerAuthToken();

		$user = User::findIdentityByAccessToken($token);

		if ($user) {

			$model = new Protocol();
			$model->user_id = $user->id;
			$request = \Yii::$app->request->post();

			if ($request['type'] == Protocol::PROTOCOL_PARENTS) {
				$count = Protocol::find()
				                 ->select('COUNT(id) as c')
				                 ->where(['type' => Protocol::PROTOCOL_PARENTS, 'user_id' => $user->id])
								->scalar();

				if ($count !== null) {
					$model->number = intval($count) + 1;
				}
			}

			if ($model->load(['Protocol' => $request]) && $model->save()) {
				return $model;
			} else return $model->getErrors();

		}

		throw new UnauthorizedHttpException();
	}

	public function actionView( $id ) {
		return Protocol::find()
			->with('protocolType')
			->where(['id' => $id])
			->asArray()
			->one();
	}

	public function actionIndex() {
		return Protocol::find()
			->with('protocolType')
			->asArray()
			->all();
    }

	/**
	 * @param $type
	 *
	 * @return array|Protocol[]
	 */
	public function actionByType( $type ) {
		return Protocol::findByType($type)->all();
    }

}