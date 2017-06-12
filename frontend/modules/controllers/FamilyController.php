<?php

namespace frontend\modules\controllers ;

use frontend\modules\models\Family;
use frontend\modules\models\User;
use yii\db\ActiveQuery;
use yii\web\UnauthorizedHttpException;

class FamilyController extends ApiController
{
    public $modelClass = 'frontend\modules\models\Family';

    public function actions() {

    	$actions = parent::actions();

    	unset($actions['create'], $actions['index']);

    	return $actions;
    }

	public function actionIndex() {

		$token = $this->parseBearerAuthToken();
		$user = User::findIdentityByAccessToken($token);



		return User::find()
			->select(['first_name', 'last_name', 'patronymic'])
			->where(['group_id' => $user->group_id])
			->with('family')
			->asArray()
			->all();
    }

	public function actionCreate() {

    	$token = $this->parseBearerAuthToken();
		$user = User::findIdentityByAccessToken($token);

		if ($user) {
			$model = new Family();
			$request = \Yii::$app->request->post();



			if ($model->load(['Family' => $request]) && $model->save()) {
				return $model;
			} else {
				return $model->getErrors();
			}
		}

		throw new UnauthorizedHttpException();
    }

	public function actionByUser( $user_id ) {
		return User::find()
			->where(['id' => $user_id])
			->with('family')
			->asArray()
			->one();
    }

	public function actionByType( $type ) {
		return User::find()
		           ->with([
			           'family' => function(ActiveQuery $query) use ($type) {
		           	        $query->where(['type' => $type]);
			           }
		           ])
		           ->asArray()
		           ->all();
    }

}