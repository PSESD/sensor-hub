<?php
namespace canis\sensorHub\console\controllers;

use Yii;
use canis\sensorHub\models\Instance;
use canis\sensorHub\models\Provider;
use canis\sensorHub\components\engine\Engine;
use yii\helpers\FileHelper;

ini_set('memory_limit', -1);

class ToolsController extends \canis\console\Controller
{
    public $verbose = false;

    public function actionResetRelations()
    {
        foreach (Provider::find()->where(['active' => 1])->all() as $provider) {
            $this->stdout($provider->dataObject->object->name . "...");
            $provider->dataObject->internalUpdateRelations();
            $this->stdout("done!\n");
        }
    }
}
