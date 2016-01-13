<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use psesd\sensorHub\models\Instance;

class DefaultController extends Controller
{
	/**
     * The landing page for the application.
     */
    public function actionIndex()
    {
        Yii::$app->response->redirect = '/server/index';
    }
}
?>