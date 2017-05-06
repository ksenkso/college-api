<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 30.01.2017
 * Time: 21:46
 */

namespace frontend\modules\controllers ;


use frontend\modules\modelsUserSearch;
use frontend\modules\modelsUser;
use Yii;
use yii\rest\ActiveController;

class MenuController extends ActiveController
{
    public $modelClass = 'frontend\models\User';

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionIndex()
    {
        $menu = [
            'index' => ['title' => 'Главная', 'path' => 'index'],
            'calendar' => ['title' => 'Календарь', 'path' => 'calendar'],
            'students' => ['title' => 'Студенты', 'path' => 'students'],
            'groups' => ['title' => 'Группы', 'path' => 'groups'],
            'users' => ['title' => 'Пользователи', 'path' => 'users'],
            'documents' => ['title' => 'Документы', 'path' => 'documents'],
            'hours' => ['title' => 'Посещаемость', 'path' => 'hours'],
        ];

        $user = Yii::$app->user;
        if ($user->can('admin')) {
            return [
                $menu['index'],
                $menu['calendar'],
                $menu['students'],
                $menu['documents'],
                $menu['groups'],
                $menu['users'],
                $menu['hours']
            ];
        }

        if ($user->can('teacher')) {
            return [
                $menu['index'],
                $menu['calendar'],
                $menu['students'],
                $menu['documents'],
                $menu['hours']
            ];
        }

        return [$menu['index']];
    }

}