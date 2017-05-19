<?php

namespace frontend\modules\controllers ;

use frontend\modules\models\Events;
use frontend\modules\models\User;
use Yii;
use yii\web\HttpException;

class EventController extends ApiController
{
    public $modelClass = 'frontend\modules\models\Events';

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['update'], $actions['delete'], $actions['create']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

	/**
	 * @param $year
	 * @param $month
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 * @throws HttpException
	 */
    public function actionIndex($year, $month)
    {
        $startDate = mktime(0, 0, 0, $month+1, 1, $year);
        $endDate = mktime(0, 0, 0, $month+2, 0, $year);

        Yii::trace(json_encode([$startDate, $endDate]));

        $token = $this->parseBearerAuthToken();

	    /**
	     * @var User $user
	     */
        $user = User::findIdentityByAccessToken($token);

        if ($user) {
	        return Events::findByPeriod($startDate, $endDate, $user->id);
        } else throw new HttpException(404, 'Пользователя не существует');


    }

    public function actionCreate()
    {
        $model = new Events();
        //$res = Yii::$app->request->post();

	    $token = $this->parseBearerAuthToken();

	    /**
	     * @var User $user
	     */
	    $user = User::findIdentityByAccessToken($token);

	    $model->user_id = $user->id;

	    $post = Yii::$app->request->post();
	    Yii::trace(json_encode($_POST));

        if ($model->load(['Events' => $post]) && $model->save()) {
            return $model;
        } else {
        	Yii::trace(json_encode($model->getErrors()));
	        throw new HttpException(500, json_encode($model->getErrors()));
        }
    }

    public function actionUpdate($id)
    {
        $model = Events::findOne($id);


        if ($model->load(['Events' => Yii::$app->request->post()]) && $model->save()) {
            return $model;
        } else {
            return false;
        }
    }

    public function actionView($id)
    {
        return Events::findOne($id);
    }

    public function actionDelete($id)
    {
	    Events::findOne($id)->delete();

        return true;
    }

}