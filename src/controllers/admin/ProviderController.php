<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use psesd\sensorHub\models\Provider;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use psesd\sensorHub\components\instances\PushProviderInstance;
use psesd\sensorHub\components\instances\PullProviderInstance;

class ProviderController extends \psesd\sensorHub\controllers\Controller
{

    public function actionIndex()
    {
        $providers = [];
        foreach (Provider::find()->all() as $provider) {
            if (empty($provider->descriptor)) {
                $providerName = 'Unknown Provider';
            } else {
                $providerName = $provider->descriptor;
            }
            $providers[] = [
                'id' => $provider->id,
                'name' => $providerName,
                'type' => $provider->dataObject->providerType,
                'active' => $provider->active === 1 ? 'Yes' : 'No',
                'created' => $provider->created,
                'last_refresh' => $provider->last_refresh
            ];
        }
        $this->params['dataProvider'] = $dataProvider = new ArrayDataProvider([
            'allModels' => $providers,
            'key' => 'id',
            'sort' => [
                'attributes' => ['id', 'name', 'active', 'type', 'created', 'last_refresh'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        Yii::$app->response->view = 'index';
    }


    public function actionDelete()
    {
        $this->params['model'] = $model = Provider::find()->where(['id' => $_GET['id']])->one();
        if (empty($model)) {
            throw new NotFoundHttpException("Provider not found");
        }
        if ($model->delete()) {
            Yii::$app->response->success = 'Sensor provider \'' . $model->descriptor .'\' deleted!';
        } else {
            Yii::$app->response->error = 'Sensor provider \'' . $model->descriptor .'\' could not be deleted.';
        }
        Yii::$app->response->redirect = ['/admin/provider/index'];
    }

    public function actionUpdate()
    {
        $this->params['model'] = $model = Provider::find()->where(['id' => $_GET['id']])->one();
        if (empty($model)) {
            throw new NotFoundHttpException("Provider not found");
        }
        $this->params['scenario'] = $scenario = 'update';
        $this->params['instance'] = $this->params['model']->dataObject;
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
            // $this->params['model']->last_refresh = gmdate("Y-m-d G:i:s");
            if ($valid && $this->params['model']->save()) {
                if (!$model->initializeData(true)) {
                    $model->delete();
                    Yii::$app->response->error = 'Sensor provider could not be created!';
                    Yii::$app->response->refresh = true;
                    return;
                } else {
                    Yii::$app->response->success = 'Sensor provider \'' . $model->dataObject->object->name .'\' created!';
                    Yii::$app->response->refresh = true;
                    return;
                }
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Update';
        Yii::$app->response->taskOptions = ['title' => 'Update Sensor Provider', 'width' => '800px'];
    }

    public function actionCreate()
    {
        $providerMap = [];
        $providerMap['pull'] = PullProviderInstance::class;
        $providerMap['push'] = PushProviderInstance::class;
        if (empty($_GET['type']) || !isset($providerMap[$_GET['type']])) {
            throw new NotFoundHttpException("Provider type not found");
        } 
        $providerClass = $providerMap[$_GET['type']];
        $this->params['model'] = $model = new Provider;
        $this->params['scenario'] = $scenario = 'create';
        $this->params['instance'] = $this->params['model']->dataObject = new $providerClass;
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
            // $this->params['model']->last_refresh = gmdate("Y-m-d G:i:s");
            if ($valid && $this->params['model']->save()) {
                if (!$model->initializeData(true)) {
                    $model->delete();
                    Yii::$app->response->error = 'Sensor provider could not be created!';
                    Yii::$app->response->refresh = true;
                    return;
                } else {
                    Yii::$app->response->success = 'Sensor provider \'' . $model->dataObject->object->name .'\' created!';
                    Yii::$app->response->refresh = true;
                    return;
                }
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Create';
        Yii::$app->response->taskOptions = ['title' => 'Add Sensor Provider', 'width' => '800px'];
    }
}
?>