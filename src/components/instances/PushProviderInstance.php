<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */
namespace canis\sensorHub\components\instances;

use Yii;
use yii\helpers\ArrayHelper;
use canis\broadcaster\eventTypes\EventType;
use canis\sensors\providers\ProviderInterface;
use canis\sensors\providers\PushProviderInterface;
use canis\sensors\providers\PushProvider;
use canis\sensors\base\Sensor as BaseSensor;

class PushProviderInstance extends ProviderInstance
{
	public function getProviderType()
	{
		return 'Pushing';
	}

	public function listen($event)
	{
		if (!isset($data['data']['provider']) || !isset($data['data']['provider']['class'])) {
    		$event->addError('Sensor provider did not provide a valid response.');
    		$event->state = BaseSensor::STATE_ERROR;
    		return;
    	}

		if (!class_exists($data['data']['provider']['class'])) {
    		$event->addError('Sensor provider is not recognized by this sensor monitor instance.');
			$event->notify = true;
			$event->pause = '+1 day';
    		$event->state = BaseSensor::STATE_ERROR;
    		return;
		}

		if (!$this->loadObject($data['data']['provider'], ProviderInterface::class)) {
    		$event->addError('Sensor provider could not be validated.');
			$event->notify = true;
			$event->pause = '+1 day';
    		$event->state = BaseSensor::STATE_ERROR;
    		return;
		}
        if (!$this->initialize(null, false)) {
            $event->addError('Sensor provider could not be initialized.');
            $event->notify = true;
            $event->pause = '+1 day';
            $event->state = BaseSensor::STATE_ERROR;
            return;
        }
        $this->model->last_refresh = date("Y-m-d G:i:s");
        $this->model->save();
	}


    public function validateSetup($scenario = 'create')
    {
    	if (!parent::validateSetup($scenario)) {
    		return false;
    	}
    	$providerConfig = [];
    	$providerConfig['class'] = PushProvider::class;
    	$providerConfig['id'] = $this->attributes['id'];
		if (!$this->loadObject($providerConfig, PushProviderInterface::class)) {
    		$this->setupErrors['url'] = 'Sensor provider could not be validated.';
    		return false;
		}
        $modelClass = get_class($this->model);
        if ($modelClass::find()->where(['and', ['system_id' => $this->model->system_id], ['not', ['id' => $this->model->id]]])->count() > 0) {
            $this->setupErrors['url'] = 'Sensor provider with same ID ('.$this->model->system_id.') already exists in the system.';
            return false;
        }
		return true;	
    }


    public function setupFields()
	{
		$fields = [];
		$fields['id'] = [
			'label' => 'Provider ID',
			'type' => 'text',
			'required' => true,
			'full' => true,
			'on' => ['create', 'update']
		];
		$fields['apiKey'] = [
			'label' => 'API Key',
			'type' => 'text',
			'required' => true,
			'full' => true,
			'disabled' => true,
			'default' => md5(microtime(true) . Yii::$app->params['salt']),
			'on' => ['create', 'update']
		];
		return $fields;
	}
}