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
use canis\sensorHub\models\Provider;
use canis\sensorHub\components\sensors\ProviderInstance;

class ProviderController extends Controller
{

    public function actionIndex()
    {
        Yii::$app->response->view = 'index';
    }
    public function actionCreate()
    {
        $this->params['model'] = $model = new Provider;
        $this->params['scenario'] = $scenario = 'create';
        $this->params['instance'] = $this->params['model']->initializeData = new ProviderInstance;
        if (!empty($_POST)) {
            $data = false;
            if (isset($_POST['Provider']['data'])) {
                $data = $_POST['Provider']['data'];
                unset($_POST['Provider']['data']);
            }
            $this->params['model']->load($_POST);
            $this->params['instance']->attributes = $data;
            $this->params['model']->active = 1;
            $valid = $this->params['instance']->validateSetup($scenario);
            if (!$valid) {
                $this->params['model']->validate();
            }
            $this->params['model']->last_check = date("Y-m-d G:i:s");
            if ($valid && $this->params['model']->save() && !$this->params['model']->initializationFailed) {
                Yii::$app->response->success = 'Sensor provider \'' . $model->name .'\' created!';
                Yii::$app->response->task = 'trigger';
                if (!empty($_GET['redirect'])) {
                    if ($_GET['redirect'] === 'providers') {
                        Yii::$app->response->redirect = ['/provider/index'];
                    }
                }
                Yii::$app->response->trigger = [['refresh', '.provider-manager']];
                return;
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Create';
        Yii::$app->response->taskOptions = ['title' => 'Add Sensor Provider', 'width' => '800px'];
    }
}
?>