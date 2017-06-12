<?php
/**
 * Created by PhpStorm.
 * User: yazun
 * Date: 24.04.2017
 * Time: 6:19
 */

namespace frontend\modules\controllers ;

use frontend\modules\models\Group;

class GroupController extends ApiController
{

	public function actions() {
		$actions = parent::actions();

		unset($actions['index'], $actions['view']);

		return $actions;
	}

	public $modelClass = 'frontend\modules\models\Group';

	public function actionIndex(  ) {
		return Group::find()
			->with('spec')
			->asArray()
			->all();
	}

	public function actionView( $id ) {
		return Group::find()
			->where(['id' => $id])
			->with('spec')
			->asArray()
			->one();
	}

}