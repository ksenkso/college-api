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
            'cabinet' => ['title' => 'Кабинет', 'path' => 'cabinet'],
            'calendar' => ['title' => 'Календарь', 'path' => 'calendar'],
            'students' => ['title' => 'Студенты', 'path' => 'students'],
            'groups' => ['title' => 'Группы', 'path' => 'groups'],
            'users' => ['title' => 'Пользователи', 'path' => 'users'],
            'documents' => ['title' => 'Документы', 'path' => 'documents'],
	        'journal' => ['title' => 'Журнал', 'external' => true, 'path' => 'http://' . (getenv('JOURNAL_DOAMIN') ? getenv('JOURNAL_DOAMIN') : 'journal.' . str_replace('api.','',getenv('VIRTUAL_HOST')))],
	        'portfolio' => ['title' => 'Портфолио', 'path' => 'portfolio'],
        ];

        $token = $this->parseBearerAuthToken();
        if ($token) {
	        $user = User::findIdentityByAccessToken($token);
	        if ($user) {
		        if ($user->can('admin')) {
			        return [
				        $menu['dashboard'],
				        $menu['calendar'],
				        $menu['cabinet'],
				        $menu['students'],
				        $menu['documents'],
				        $menu['groups'],
				        $menu['users'],
				        $menu['portfolio'],
				        $menu['journal'],
			        ];
		        }

		        if ($user->can('teacher')) {
			        return [
				        $menu['dashboard'],
				        $menu['cabinet'],
				        $menu['calendar'],
				        $menu['students'],
				        $menu['documents'],
				        $menu['portfolio'],
				        $menu['journal'],

			        ];
		        }

		        return [$menu['dashboard']];
	        }
        }
		throw new UnauthorizedHttpException('Authorization token is invalid');
    }

}