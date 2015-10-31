<?php
namespace canis\sensorHub\console\controllers;

use Yii;
use canis\sensorHub\models\Instance;
use canis\sensorHub\models\Sensor;
use canis\sensorHub\components\engine\Engine;
use yii\helpers\FileHelper;

ini_set('memory_limit', -1);

class EventController extends \canis\console\Controller
{
    public $verbose = false;
    protected $_instance;

    public function actionSimulate($eventType = null)
    {
        $broadcaster = Yii::$app->getModule('broadcaster');
        $eventType = trim($eventType);
        while (empty($eventType)) {
            $eventType = $this->prompt("What is the event type?", ['required' => true]);
        }
        $instance = $this->instance->dataObject;
        $payload = $instance->getBaseEventPayload();
        if (!$instance->triggerBroadcastEvent($eventType, $payload, $instance->model->id)) {
            echo "Failed!".PHP_EOL;
        }
    }

    public function getInstance()
    {
        if (!$this->started) {
            return $this->_instance;
        }
        if ($this->_instance !== null) {
            return $this->_instance;
        }
        $this->instance = $this->prompt("What is the instance name?", ['required' => true]);
        return $this->_instance;
    }

    public function setInstance($instance)
    {
        if (($instanceModel = Instance::find()->where(['id' => $instance, 'active' => 1])->one())) {
            $this->_instance = $instanceModel;
        } else if (($instanceModel = Instance::find()->where(['name' => $instance, 'active' => 1])->one())) {
            $this->_instance = $instanceModel;
        } else if (($instanceModel = Instance::find()->where(['id' => $instance])->one())) {
            $this->_instance = $instanceModel;
        } else if (($instanceModel = Instance::find()->where(['name' => $instance])->one())) {
            $this->_instance = $instanceModel;
        } else {
            throw new \Exception("Invalid instance!");
        }
    }

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        return array_merge(parent::options($id), ['instance', 'verbose']);
    }
}
