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
use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'frontend\models\User';

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

    public function actionUpdate($id)
    {

        $model = User::find()->where(['id' => $id])->one();
        $arr = User::find()->where(['id' => $id])->asArray()->one();
        $ph = $model->password_hash;

        $post = Yii::$app->request->post();
        Yii::trace(json_encode($post));
        Yii::trace(json_encode($arr));

        if ($model->load($post)) {
            Yii::trace('loaded');

            if ($model->updateUser($ph)) {
                return $model;
            } else {
                return $model->getErrors();
            }

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