<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 30.01.2017
 * Time: 21:35
 */

namespace frontend\modules\controllers ;


use frontend\modules\models\User;
use Yii;
use yii\web\UnauthorizedHttpException;

class StudentController extends ApiController
{
    public $modelClass = 'frontend\modules\models\Students';



    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['view'], $actions['create']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionIndex()
    {
    	$auth = Yii::$app->authManager;
    	$ids = $auth->getUserIdsByRole('student');

    	if (!count($ids)) {
    		return [];
	    }

	    $token = $this->parseBearerAuthToken();
    	$teacher = User::findIdentityByAccessToken($token);


	    return User::find()
		    ->where([
		    	'id' => $ids,
			    'group_id' => $teacher->group_id
		    ])
		    ->all();


        //$students = Students::find()->asArray()->all();

        //return $students;
    }

    public function actionCreate()
    {
        $model = new User();
        $post = Yii::$app->request->post();
        $token = $this->parseBearerAuthToken();
        $creator = User::findIdentityByAccessToken($token);

	    $username = isset($post['username']) ? $post['username'] : Yii::$app->security->generateRandomString(8);
	    $password = isset($post['password']) ? $post['password'] : Yii::$app->security->generateRandomString(8);

        if (isset($post['password'])) {
        	$model->setPassword($password);
        }

	    if (isset($post['username'])) {
		    $model->username = $username;
	    }

        if ($creator->can('admin') && isset($post['group_id'])) {
	        $model->group_id = $post['group_id'];
        } else {
	        $model->group_id = $creator->group_id;
        }

        if ($model->load(['User' => $post]) && $model->save()) {

        	$auth = Yii::$app->authManager;
        	$studentRole = $auth->getRole('student');
        	$auth->assign($studentRole, $model->id);
            return $model;
        } else {
            return $model->getErrors();
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