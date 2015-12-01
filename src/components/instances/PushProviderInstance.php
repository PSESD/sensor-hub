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

	public function take($data)
	{
		if (!isset($data['provider']) || !isset($data['provider']['class'])) {
    		return false;
    	}
		if (!class_exists($data['provider']['class'])) {
    		return false;
		}
		if (!$this->loadObject($data['provider'], PushProviderInterface::class)) {
    		return false;
		}
        if (!$this->initialize(null, false)) {
            return false;
        }
        $this->model->last_refresh = gmdate("Y-m-d G:i:s");
        $this->model->save();

        $this->internalUpdateRelations();
        return true;
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