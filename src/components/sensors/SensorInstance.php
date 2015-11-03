<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */
namespace canis\sensorHub\components\sensors;

use Yii;
use canis\broadcaster\eventTypes\EventType;
use canis\sensors\providers\ProviderInterface;
use canis\sensors\base\CheckEvent;
use canis\sensorHub\models\SensorEvent;
use canis\sensorHub\models\Registry;
use canis\sensors\base\Sensor as BaseSensor;

class SensorInstance extends Instance
{
	public $payload = false;

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }

    public function getPackage()
    {
    	$package = parent::getPackage();

    	return $package;
    }

    public function getObjectModel()
    {
    	if (!isset($this->_cache['object_model'])) {
    		$this->_cache['object_model'] = false;
    		if (!empty($this->model->object_id)) {
    			$this->_cache['object_model'] = Registry::getObject($this->model->object_id);
    		}
    	}
    	return $this->_cache['object_model'];
    }

    public function check($loop = null, $retriesLeft = null)
    {
    	$this->pauseSensor();
    	$event = $this->object->check($this);
    	if ($retriesLeft === null) {
    		$retriesLeft = $this->object->getCheckRetries();
    	}
    	if ($event->state === BaseSensor::STATE_CHECK_FAIL || $event->verifyState) {
    		if ($retriesLeft > 0 && $loop !== null) {
    			$self = $this;
                // echo microtime(true) .": Setting timer for " . $this->object->getCheckRetryInterval() . "s ({$self->model->id}; {$retriesLeft} left)".PHP_EOL;flush();
    			$loop->addTimer($this->object->getCheckRetryInterval(), function() use ($self, &$loop, $retriesLeft) {
    				$retriesLeft--;
    				$self->check($loop, $retriesLeft);
    			});
    			return true;
    		}
    	}
		$timeString = $this->object->getCheckInterval($this);
		if ($event->pause) {
			$timeString = $event->pause;
		}
    	if ($event->state !== $this->model->state) {
    		$this->triggerStateChange($event);
    	}
    	return $this->scheduleCheck(true, $timeString);
    }

    protected function triggerStateChange(CheckEvent $event)
    {
    	$sensorEvent = new SensorEvent;
    	$sensorEvent->sensor_id = $this->model->primaryKey;
    	$sensorEvent->old_state = $this->model->state;
    	$sensorEvent->new_state = $event->state;
    	$sensorEvent->data = serialize($event);
    	$sensorEvent->save();
    	$this->model->state = $event->state;
    	return true;
    }
    
    public function pauseSensor($save = true)
    {
    	$this->model->next_check = null;
        $this->model->last_check = date("Y-m-d G:i:s");
    	if (!$save) {
    		return true;
    	}
    	return $this->model->save();
    }
    
    public function scheduleCheck($save = true, $timeString = false)
    {
        if ($timeString === false) {
        	$this->model->next_check = null;
        } else {
        	$this->model->next_check = date("Y-m-d G:i:s", strtotime($timeString));
    	}
        if ($save) {
            return $this->model->save();
        }
        return true;
    }
}