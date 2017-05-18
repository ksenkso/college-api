<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 30.01.2017
 * Time: 21:46
 */

namespace frontend\modules\controllers ;


use frontend\modules\models\User;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;

class MenuController extends ApiController
{
    public $modelClass = 'frontend\models\User';

	/*
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::className(),
		];
		return $behaviors;
	}
	*/

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
            'dashboard' => ['title' => 'Главная', 'path' => ''],
            'calendar' => ['title' => 'Календарь', 'path' => 'calendar'],
            'students' => ['title' => 'Студенты', 'path' => 'students'],
            'groups' => ['title' => 'Группы', 'path' => 'groups'],
            'users' => ['title' => 'Пользователи', 'path' => 'users'],
            'documents' => ['title' => 'Документы', 'path' => 'documents'],
            'hours' => ['title' => 'Посещаемость', 'path' => 'hours'],
        ];

        $token = $this->parseBearerAuthToken();
        if ($token) {
	        $user = User::findIdentityByAccessToken($token);
	        if ($user) {
		        if ($user->can('admin')) {
			        return [
				        $menu['dashboard'],
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
				        $menu['dashboard'],
				        $menu['calendar'],
				        $menu['students'],
				        $menu['documents'],
				        $menu['hours']
			        ];
		        }

		        return [$menu['dashboard']];
	        }
        }
		throw new UnauthorizedHttpException('Authorization token is invalid');
    }

}