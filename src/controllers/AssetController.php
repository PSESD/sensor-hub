<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\controllers;

use Yii;
use yii\web\NotFoundHttpException;

class AssetController extends Controller
{

    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }
}
?>