<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 24.04.2017
 * Time: 6:19
 */

namespace frontend\modules\controllers ;


use frontend\modules\models\Group;
use frontend\modules\models\GroupSearch;
use Yii;
use yii\rest\ActiveController;

class GroupController extends ApiController
{
    public $modelClass = 'frontend\models\Group';

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['view']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionIndex()
    {
        $students = Group::find()->asArray()->all();

        return $students;
    }

    public function actionCreate()
    {
        $model = new Group();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        } else {
            return false;
        }
    }

    public function actionUpdate($id)
    {
        $model = GroupSearch::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        } else {
            return false;
        }
    }

    public function actionView($id)
    {
        return GroupSearch::findOne($id);
    }

    public function actionDelete($id)
    {
        GroupSearch::findOne($id)->delete();

        return json_encode(true);
    }

}