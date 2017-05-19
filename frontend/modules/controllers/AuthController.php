<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 30.01.2017
 * Time: 21:46
 */

namespace frontend\modules\controllers;


use frontend\modules\models\User;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class AuthController extends ApiController
{
	protected function verbs()
	{
		$verbs = parent::verbs();
		//$verbs['check'] = ['GET'];
		return $verbs;
	}

	public function behaviors() {
		$b = parent::behaviors();
		unset($b['authenticator']);
		return $b;
	}

	public $modelClass = 'frontend\models\User';



	public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['create'], $actions['view'], $actions['update'], $actions['delete'], $actions['check']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionIndex()
    {
    	$request = Yii::$app->request;
    	if ($request->method === 'OPTIONS') {
    		return true;
	    }

    	list($username, $password) = $this->parseBasicAuthToken();


    	Yii::trace("username: $username, password: $password");

        $user = User::findByUsername($username);
        if ($user && $user->validatePassword($password)) {

        	if (!$user->access_token) {
		        $user->access_token = Yii::$app->security->generateRandomString();
	        }

        	$user->save();
        	return $user;
        } else {
        	throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
        }


    }

	public function actionCheck($route = 'dashboard') {

		$request = Yii::$app->request;

		/*if ($request->method === 'OPTIONS') {
			return true;
		}*/

		$token = $request->headers->get('X-Token');

		Yii::trace($token);

		$user = User::findIdentityByAccessToken($token);
		if ($user) {
			Yii::trace('test');
			$auth = Yii::$app->authManager;

			$perms = $auth->getPermissionsByUser($user->id);
			if ($perms) {
				foreach ( $perms as $perm ) {
					if ($perm->name === 'nav' . ucfirst($route)) return true;
				}
			}
		}
		throw new NotFoundHttpException('User with this token not found');
    }

    public function actionDelete()
    {
    	$token = $this->parseBearerAuthToken();

		$user = User::findIdentityByAccessToken($token);
		if ($user) {
			$user->access_token = '';
			$user->save();
			return true;
		}
	    throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
    }

}