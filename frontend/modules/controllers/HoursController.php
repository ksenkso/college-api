<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 30.01.2017
 * Time: 22:52
 */
namespace frontend\modules\controllers ;

use common\models\User;
use frontend\modules\modelsEvent;
use frontend\modules\modelsEventSearch;
use frontend\modules\modelsHours;
use frontend\modules\modelsStudents;
use frontend\modules\modelsToday;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Command;
use yii\db\Query;
use yii\rest\ActiveController;

class HoursController extends ActiveController
{
    public $modelClass = 'frontend\models\Hours';

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['create'], $actions['delete']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionDelete()
    {
        $group_id = User::find()->select(['group_id'])->where(['id' => Yii::$app->getUser()->getId()])->scalar();

        $command = Yii::$app->getDb()->createCommand("delete from `today` where group_id = :group_id");
        $command->bindValue(':group_id', $group_id);
        $command->execute();
        echo json_encode(['done' => true]);

    }

    public function actionIndex($year, $month)
    {


        $group_id = User::find()->select(['group_id'])->where(['id' => Yii::$app->getUser()->getId()])->scalar();
        $time = mktime(0, 0, 0, intval($month) + 1, 1, intval($year));
        $users = User::find()
            ->with([
                'hours' => function(ActiveQuery $query)use ($time) {
                    $query->andWhere([
                        'and',
                        ['>=', 'date', date('Y-m-01', $time)],
                        ['<=', 'date', date('Y-m-31', $time)]
                    ])->orderBy('date');
                }
            ])
            ->where(['group_id' => $group_id])
            ->orderBy('concat(`last_name`, `first_name`)')
            ->asArray()
            ->all();
        echo json_encode(['students' => $users]);
        return;



        /*$q = new Query();
        $q
            ->select(['students.first_name', 'students.last_name', 'tmp_hours.student_id', 'tmp_hours.hours', 'tmp_hours.date'])
            ->from('tmp_hours')
            ->leftJoin([Students::tableName(), 'tmp_hours.student_id = students.student_id'])
            ->where([
                'and',
                [
                    'and',
                    ['>=', 'date', date('Y-m-01', $time)],
                    ['<=', 'date', date('Y-m-31', $time)]
                ],
                ['group_id' => $group_id]
            ])
            ->orderBy('concat(`students`.`last_name`, `students`.`first_name`)');*/




        $command = Yii::$app->getDb()->createCommand("SELECT `students`.`first_name`,`students`.`last_name`,`tmp_hours`.`student_id`,`tmp_hours`.`hours`,`tmp_hours`.`date`FROM`tmp_hours`LEFT JOIN`students` ON `students`.`student_id` = `tmp_hours`.`student_id`WHERE(`date` >= :start_date)AND (`date` <= :end_date)AND (`students`.`group_id` = :group_id)ORDER BY CONCAT(`students`.`last_name`,students.first_name), `tmp_hours`.`date`");
        $command->bindValue(':start_date', date('Y-m-01', $time))
                ->bindValue(':end_date', date('Y-m-31', $time))
                ->bindValue(':group_id', $group_id);
        $res = $command->queryAll();
        $grouped = [];
        foreach ($res as $item) {
            $grouped[$item['student_id']] = [];
        }

        foreach ($res as $item) {
            $grouped[$item['student_id']][] = $item;
        }

        return $grouped;

        $result = [
            'byId' => [],
            'ids' => []
        ];

        foreach ($hours as $record) {

            $result['byId'][$record['hours_id']] = $record;
            $result['ids'][] = (int)$record['hours_id'];
        }

        return $hours;
    }

    public function actionCreate()
    {
        $students = Yii::$app->request->post('students', []);
        print_r($students);
        foreach ($students as $student)
        {
            $model = Today::findOne(['student_id' => $student['student_id']]);
            $model->student_id = $student['student_id'];
            $model->hours = $student['hours'];
            if ($model->validate())
            {
                $model->save();
                return "done";
            }

        }
        return false;
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


}
