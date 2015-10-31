<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use canis\sensorHub\models\Instance;

class DefaultController extends Controller
{

	/**
     * The landing page for the application.
     */
    public function actionIndex()
    {
        Yii::$app->response->redirect = 'sensor/index';
    }
}
?>