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

class UserController extends ApiController
{
    public $modelClass = 'frontend\models\User';


	public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['create'], $actions['view'], $actions['update'], $actions['delete']);

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
	        ->with('roles')
            ->asArray()
            ->all();


    }

    public function actionCreate()
    {
	    $model = new User();
	    $post = Yii::$app->request->post();
	    $token = $this->parseBearerAuthToken();
	    $creator = User::findIdentityByAccessToken($token);

	    $username = Yii::$app->security->generateRandomString(8);
	    $password = Yii::$app->security->generateRandomString(8);

	    $model->setPassword($password);
	    $model->username = $username;

	    if (isset($post['group_id'])) {
		    $model->group_id = $post['group_id'];
	    } else {
		    $model->group_id = $creator->group_id;
	    }

	    if ($model->load(['User' => $post]) && $model->save()) {

		    $auth = Yii::$app->authManager;
		    $roles = $post['roles'];

		    if (count($roles)) {
			    foreach ( $roles as $role ) {
				    $auth->assign($auth->getRole($role), $model->id);
			    }
		    } else {
			    $role = $auth->getRole('student');
			    $auth->assign($role, $model->id);
		    }

		    return $model;
	    } else {
		    return $model->getErrors();
	    }
    }

	public function actionUpdate($id)
    {

	    /**
	     * @var $model User
	     */

        $model = User::find()->where(['id' => $id])->one();

	    $post = Yii::$app->request->post();

	    if ($model->load(['User' => $post]) && $model->save()) {

		    $auth = Yii::$app->authManager;
		    $roles = $post['roles'];

		    if (count($roles)) {

		    	$auth->revokeAll($model->id);

			    foreach ( $roles as $role ) {

				    $auth->assign($auth->getRole($role), $model->id);
			    }
		    } else {

			    $auth->revokeAll($model->id);
		    }

		    return $model;
	    } else {
		    return $model->getErrors();
	    }
    }

    public function actionView($id)
    {
        return User::find()
            ->select('username,first_name,last_name,patronymic,status,email,group_id,created_at,updated_at,id,address,phone')
            ->where(['id' => $id])
	        ->with('roles')
            ->asArray()
            ->one();
    }

    public function actionDelete($id)
    {
        User::findOne($id)->delete();

        return true;
    }



}