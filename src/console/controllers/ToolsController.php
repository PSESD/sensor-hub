<?php
namespace psesd\sensorHub\console\controllers;

use Yii;
use psesd\sensorHub\models\Instance;
use psesd\sensorHub\models\Provider;
use psesd\sensorHub\components\engine\Engine;
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
