<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use psesd\sensorHub\models\Contact;
use canis\registry\models\Registry;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use psesd\sensorHub\components\instances\ProviderInstance;

class ContactController extends Controller
{

    public function actionDelete()
    {
        if (!empty($_GET['id'])) {
            $this->params['model'] = $model = Contact::find()->where(['id' => $_GET['id']])->one();
        }
        if (empty($model)) {
            throw new NotFoundHttpException("Contact not found");
        }
        if (!empty($_GET['confirm'])) {
            if ($model->delete()) {
                Yii::$app->response->task = 'message';
                Yii::$app->response->success = 'Contact was deleted!';
            } else {
                Yii::$app->response->task = 'message';
                Yii::$app->response->error = 'An error occurred while deleting the contact';
            }
            Yii::$app->response->trigger = [['refresh', '.sensor-viewer']];
            return;
        }

        Yii::$app->response->labels['submit'] = 'Delete';
        Yii::$app->response->taskOptions = ['title' => 'Delete Contact', 'isConfirmDeletion' => true];
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->view = 'delete';
    }

    public function actionUpdate()
    {
        if (!empty($_GET['id'])) {
            $this->params['model'] = $model = Contact::find()->where(['id' => $_GET['id']])->one();
        }
        if (empty($model)) {
            throw new NotFoundHttpException("Contact not found");
        }
        // $this->params['scenario'] = $scenario = 'update';

        if (!empty($_POST)) {
            $this->params['model']->load($_POST);
            if ($this->params['model']->save()) {
                Yii::$app->response->success = 'Contact \'' . $model->descriptor .'\' updated!';
                Yii::$app->response->trigger = [['refresh', '.sensor-viewer']];
                return;
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Update';
        Yii::$app->response->taskOptions = ['title' => 'Update Contact', 'width' => '800px'];
    }

    public function actionCreate()
    {
        if (!empty($_GET['objectId'])) {
            $this->params['objectModel'] = $objectModel = Registry::getObject($_GET['objectId']);
        }
        if (empty($objectModel)) {
            throw new NotFoundHttpException("Object not found");
        }
        $this->params['model'] = $model = new Contact;
        $model->object_id = $objectModel->id;
        if (!empty($_POST)) {
            
            $this->params['model']->load($_POST);
            if ($this->params['model']->save()) {
                Yii::$app->response->success = 'Contact \'' . $model->descriptor .'\' created!';
                Yii::$app->response->trigger = [['refresh', '.sensor-viewer']];
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Create';
        Yii::$app->response->taskOptions = ['title' => 'Add Contact', 'width' => '800px'];
    }


    public function actionView()
    {
        if (!empty($_GET['id'])) {
            $this->params['model'] = $model = Contact::find()->where(['id' => $_GET['id']])->one();
        }
        if (empty($model)) {
            throw new NotFoundHttpException("Contact not found");
        }
        Yii::$app->response->view = 'view';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->taskOptions = ['title' => 'Contact', 'width' => '400px', 'isForm' => false];
    }
}
?>