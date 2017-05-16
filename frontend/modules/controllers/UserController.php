<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 30.01.2017
 * Time: 21:46
 */

namespace frontend\modules\controllers;


use frontend\modules\models\User;
use frontend\modules\models\UserSearch;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class UserController extends ActiveController
{
    public $modelClass = 'frontend\models\User';

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::className(),
		];
		return $behaviors;
	}

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['create'], $actions['view'], $actions['update']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionIndex()
    {
        return User::find()
            ->select([
                'username',
                'first_name',
                'last_name',
                'patronymic',
                'status',
                'email',
                'group_id',
                'created_at',
                'updated_at',
                'id'])
            ->asArray()
            ->all();


    }

    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->saveUser()) {
            return $model;
        } else {
            return $model->getErrors();
        }
    }

	/**
	 * @param $id
	 *
	 * @return array|null|\yii\db\ActiveRecord
	 */
	public function actionUpdate($id)
    {

	    /**
	     * @var $model User
	     */

        $model = User::find()->where(['id' => $id])->one();

        if (!$model) {
	        throw new NotFoundHttpException('User with such id not found');
        }

        $arr = User::find()->where(['id' => $id])->asArray()->one();
        $ph = $model->password_hash;

        $post = Yii::$app->request->post();
        Yii::trace(json_encode($post));
        Yii::trace(json_encode($arr));

        if ($model->load($post)) {
            Yii::trace('loaded');

	        return $model->updateUser( $ph ) ? $model : $model->getErrors();

        } else {
            Yii::trace('failed to load');
            return $model->getErrors();
        }
    }

    public function actionView($id)
    {
        return User::find()
            ->select('username,first_name,last_name,patronymic,status,email,group_id,created_at,updated_at,id')
            ->where(['id' => $id])
            ->asArray()
            ->one();
    }

    public function actionDelete($id)
    {
        UserSearch::findOne($id)->delete();

        return true;
    }

}