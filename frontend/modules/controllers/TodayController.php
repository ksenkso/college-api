<?php

namespace frontend\modules\controllers ;

use common\models\User;
use frontend\modules\modelsEvent;
use frontend\modules\modelsEventSearch;
use frontend\modules\modelsToday;
use Yii;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class TodayController extends ActiveController
{
    public $modelClass = 'frontend\models\Today';

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }



    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /*public function verbs()
    {
        return [
          'class' => VerbFilter::className(),
            'actions' => [
                'create' => ['post'],
                'index' => ['get', 'head']
            ]
        ];
    }*/

    private function getModels()
    {
        $events = Event::find()->asArray()->all();
        $result = [
            'byId' => [],
            'ids' => []
        ];

        foreach ($events as $event) {
            $event['start_date'] = strtotime($event['start_date']) * 1000;
            $event['end_date'] = strtotime($event['end_date']) * 1000;


            $result['byId'][$event['event_id']] = $event;
            $result['ids'][] = (int)$event['event_id'];
        }

        return $result;
    }

    public function actionIndex()
    {
        $group_id = User::find()->select(['group_id'])->where(['id' => Yii::$app->getUser()->getId()])->scalar();
        $today = Today::find()->select('*')->where(['group_id' => $group_id])->all();
        $res = [];
        foreach ($today as $item) {
            $res[] = [
                'id' => $item->id,
                'hours' => $item->hours,
                'first_name' => $item->student->first_name,
                'last_name' => $item->student->last_name,
                'group_id' => $item->group_id
            ];
        }
        return $res;


        return $today;
    }

    public function actionCreate()
    {
        $model = new Event();
        $res = Yii::$app->request->post();

        $res['Event']['start_time'] .= ":00";
        $res['Event']['end_time'] .= ":00";
        $res['Event']['user_id'] = Yii::$app->user->id;

        if ($model->load($res) && $model->save()) {
            return $this->getModels();
        } else {
            return $res;
        }
    }

    public function actionUpdate($id)
    {
        $model = EventSearch::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        } else {
            return false;
        }
    }

    public function actionView($id)
    {
        return EventSearch::findOne($id);
    }

    public function actionDelete($id)
    {
        EventSearch::findOne($id)->delete();

        return true;
    }

}