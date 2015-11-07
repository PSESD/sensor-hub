<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\controllers\admin;

use Yii;
use yii\web\NotFoundHttpException;
use canis\sensorHub\models\Provider;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use canis\sensorHub\components\instances\ProviderInstance;

class ProviderController extends \canis\sensorHub\controllers\Controller
{

    public function actionIndex()
    {
        $providers = [];
        foreach (Provider::find()->all() as $provider) {
            $providers[] = [
                'id' => $provider->id,
                'name' => $provider->dataObject->object->name,
                'url' => $provider->dataObject->attributes['url'],
                'active' => $provider->active === 1 ? 'Yes' : 'No',
                'created' => $provider->created,
                'last_check' => $provider->last_check
            ];
        }
        $this->params['dataProvider'] = $dataProvider = new ArrayDataProvider([
            'allModels' => $providers,
            'key' => 'id',
            'sort' => [
                'attributes' => ['id', 'name', 'active', 'url', 'created', 'last_check'],
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
            Yii::$app->response->success = 'Sensor provider \'' . $model->dataObject->object->name .'\' deleted!';
        } else {
            Yii::$app->response->error = 'Sensor provider \'' . $model->dataObject->object->name .'\' could not be deleted.';
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

        $this->params['instance'] = $this->params['model']->initializeData = $this->params['model']->dataObject;
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
            if ($valid && $this->params['model']->save() && $model->initializeData(false)) {
                Yii::$app->response->success = 'Sensor provider \'' . $model->dataObject->object->name .'\' updated!';
                Yii::$app->response->refresh = true;
                return;
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Update';
        Yii::$app->response->taskOptions = ['title' => 'Update Sensor Provider', 'width' => '800px'];
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
            if ($valid && $this->params['model']->save() && $model->initializeData(true)) {
                Yii::$app->response->success = 'Sensor provider \'' . $model->dataObject->object->name .'\' created!';
                Yii::$app->response->refresh = true;
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