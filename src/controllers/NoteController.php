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
use canis\sensorHub\models\Note;
use canis\registry\models\Registry;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use canis\sensorHub\components\instances\ProviderInstance;

class NoteController extends Controller
{

    public function actionDelete()
    {
        if (empty($_GET['id'])) {
            $this->params['model'] = $model = Note::find()->where(['id' => $_GET['id']])->one();
        }
        if (empty($model)) {
            throw new NotFoundHttpException("Note not found");
        }
        if ($model->delete()) {
            Yii::$app->response->success = 'Note \'' . $model->descriptor .'\' deleted!';
        } else {
            Yii::$app->response->error = 'Note \'' . $model->descriptor .'\' could not be deleted.';
        }
        Yii::$app->response->trigger = [['refresh', '.sensor-viewer']];
    }

    public function actionUpdate()
    {
        if (empty($_GET['id'])) {
            $this->params['model'] = $model = Note::find()->where(['id' => $_GET['id']])->one();
        }
        if (empty($model)) {
            throw new NotFoundHttpException("Note not found");
        }
        // $this->params['scenario'] = $scenario = 'update';

        if (!empty($_POST)) {
            $this->params['model']->load($_POST);
            if ($this->params['model']->save()) {
                Yii::$app->response->success = 'Note \'' . $model->descriptor .'\' updated!';
                Yii::$app->response->trigger = [['refresh', '.sensor-viewer']];
                return;
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Update';
        Yii::$app->response->taskOptions = ['title' => 'Update Note', 'width' => '800px'];
    }

    public function actionCreate()
    {
        if (!empty($_GET['objectId'])) {
            $this->params['objectModel'] = $objectModel = Registry::getObject($_GET['objectId']);
        }
        if (empty($objectModel)) {
            throw new NotFoundHttpException("Object not found");
        }
        $this->params['model'] = $model = new Note;
        $model->object_id = $objectModel->id;
        if (!empty($_POST)) {
            
            $this->params['model']->load($_POST);
            if ($this->params['model']->save()) {
                Yii::$app->response->success = 'Note \'' . $model->descriptor .'\' created!';
                Yii::$app->response->refresh = true;
            }
        }
        Yii::$app->response->view = 'create';
        Yii::$app->response->task = 'dialog';
        Yii::$app->response->labels['submit'] = 'Create';
        Yii::$app->response->taskOptions = ['title' => 'Add Note', 'width' => '800px'];
    }
}
?>