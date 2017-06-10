<?php
/**
 * Created by PhpStorm.
 * User: ksenkso
 * Date: 13.05.17
 * Time: 13:36
 */

namespace frontend\modules\controllers;


use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\UnauthorizedHttpException;


class ApiController extends ActiveController {



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

	public function behaviors() {
		$b = parent::behaviors();
		$b['authenticator'] = [
			'class' => HttpBearerAuth::className(),
		];

		$auth = $b['authenticator'];
		$b['corsFilter'] = [

			'class' => Cors::className(),
			'cors'  => [
				// restrict access to
				'Origin'      => [ 'http://journal.ru' ],
				// Allow only POST and PUT methods
				'Access-Control-Request-Headers'   => [ '*' ],
				// Allow only headers 'X-Wsse'
				'Access-Control-Allow-Credentials' => true,
				// Allow OPTIONS caching
				'Access-Control-Max-Age'           => 3600,
				// Allow the X-Pagination-Current-Page header to be exposed to the browser.
				'Access-Control-Expose-Headers'    => [ 'X-Pagination-Current-Page', 'Content-Disposition', 'X-Limit' ],
			]
		];

		if ($auth) {
			$auth['except'] = ['options'];
			$b['authenticator'] = $auth;
		}

		return $b;
	}

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 */
	public function beforeAction($action)
	{
		$request = Yii::$app->request;
		if ($request->method === 'OPTIONS') {
			$this->actionOptions();
			return false;
		}

		if (!parent::beforeAction($action)) {
			return false;
		}

		// other custom code here

		return true; // or false to not run the action
	}

	protected function parseBearerAuthToken() {
		$request = Yii::$app->request;

		$auth = $request->headers->get('Authorization');
		$token = substr($auth, 7);

		if ($token) return $token;

		throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
	}

	protected function parseBasicAuthToken() {
		$request = Yii::$app->request;

		$auth = $request->headers->get('Authorization');
		$auth = base64_decode(substr($auth, 6));

		@list($username, $password) = explode(':', $auth);
		if (isset($username) && !empty($username) && isset($password) && !empty($password)) {
			return [$username, $password];
		}
		throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
	}

	public function actionOptions ()
	{
		$request = Yii::$app->getRequest();
		if ($request->getMethod() !== 'OPTIONS') {
			Yii::$app->getResponse()->setStatusCode(405);
		}

		$options = ['GET', 'HEAD', 'POST','OPTIONS', 'PUT', 'DELETE'];
		Yii::$app->getResponse()->getHeaders()->set('Allow', implode(',', $options));
		Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Methods', implode(',', $options));

		if ($request->headers->get('Origin')) {
			Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));
			Yii::$app->getResponse()->getHeaders()->set(
				'Access-Control-Allow-Headers',
				join(',', ['X-Token', 'Authorization', 'Content-Type', 'Content-Length', 'X-Limit'])
			);
		}


	}
}