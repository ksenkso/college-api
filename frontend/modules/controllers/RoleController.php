<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 24.04.2017
 * Time: 6:19
 */

namespace frontend\modules\controllers ;

use frontend\modules\models\Role;

class RoleController extends ApiController
{

	public $modelClass = 'frontend\modules\models\Role';

	public function actions() {
		$actions = parent::actions();

		unset($actions['index']);

		return $actions;
	}

	public function actionIndex() {

		return Role::find()
			->where(['type' => 1])
			->asArray()
			->all();
	}

}