<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\controllers;

use Yii;
use canis\sensorHub\models\Provider;
use yii\web\NotFoundHttpException;
use yii\web\NotAcceptableHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ProvideController extends \canis\sensorHub\components\web\Controller
{
    public function beforeAction($action)
    {
        Yii::$app->controller->enableCsrfValidation = false;
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }
    
	/**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'verbs' => ['POST']
                    ],
                ],
            ]
        ];
    }

    public function actionIndex()
    {
        if (empty(Yii::$app->request->getHeaders()->get('x-api-key')) || empty($_POST['provider']['id'])) {
            throw new NotAcceptableHttpException("Invalid package");
        }
        $provider = Provider::find()->where(['system_id' => $_POST['provider']['id']])->one();
        if (empty($provider)) {
            throw new NotAcceptableHttpException("Provider '". $_POST['provider']['id'] ."' is not known by this hub");
        }
        if (!isset($provider->dataObject->attributes['apiKey']) || $provider->dataObject->attributes['apiKey'] !== Yii::$app->request->getHeaders()->get('x-api-key')) {
            throw new UnauthorizedHttpException("API key is invalid!");
        }
        if ($provider->dataObject->take($_POST)) {
            Yii::$app->response->data = ['time' => time(), 'status' => 'accepted'];
        } else {
            throw new UnprocessableEntityHttpException("Data was not accepted");
        }
    }
}
?>