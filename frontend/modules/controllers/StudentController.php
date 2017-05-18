<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 30.01.2017
 * Time: 21:35
 */

namespace frontend\modules\controllers ;


use frontend\modules\models\Students;
use frontend\modules\models\StudentsSearch;
use Yii;

class StudentController extends ApiController
{
    public $modelClass = 'frontend\modules\models\Students';



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
        $students = Students::find()->asArray()->all();

        return $students;
    }

    public function actionCreate()
    {
        $model = new Students();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        } else {
            return false;
        }
    }

    public function actionUpdate($id)
    {
        $model = StudentsSearch::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        } else {
            return false;
        }
    }

    public function actionView($id)
    {
        return StudentsSearch::findOne($id);
    }

    public function actionDelete($id)
    {
        StudentsSearch::findOne($id)->delete();

        return true;
    }

}