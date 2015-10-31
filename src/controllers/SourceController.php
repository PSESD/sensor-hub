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
use canis\sensorHub\models\Source;
use canis\sensorHub\components\sensors\SourceInstance;

class SourceController extends Controller
{

    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }
    public function actionCreate()
    {
        $this->params['model'] = new Source;
        $this->params['scenario'] = $scenario = 'create';
        $this->params['instance'] = $this->params['model']->dataObject = new SourceInstance;
        if (!empty($_POST)) {
            $data = false;
            if (isset($_POST['Source']['data'])) {
                $data = $_POST['Source']['data'];
                unset($_POST['Source']['data']);
            }
            $this->params['model']->load($_POST);
            $this->params['instance']->attributes = $data;
            $this->params['model']->active = 1;
            $valid = $this->params['instance']->validateSetup($scenario);
            if (!$valid) {
                $this->params['model']->validate();
            }
            if ($valid && $this->params['model']->save()) {
                Yii::$app->response->success = 'Sensor source \'' . $this->name .'\' created!';
                Yii::$app->response->task = 'trigger';
                if (!empty($_GET['redirect'])) {
                    if ($_GET['redirect'] === 'sources') {
                        Yii::$app->response->redirect = ['/source/index'];
                    }
                }
                Yii::$app->response->trigger = [['refresh', '.source-manager']];
                return;
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Create';
        Yii::$app->response->taskOptions = ['title' => 'Add Sensor Source', 'width' => '800px'];
    }
}
?>