<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */
namespace canis\sensorHub\components\instances;

use Yii;
use canis\broadcaster\eventTypes\EventType;
use canis\sensors\providers\ProviderInterface;
use canis\sensors\base\SensorDataInterface;
use canis\sensors\base\CheckEvent;
use canis\sensorHub\models\SensorEvent;
use canis\sensorHub\models\SensorData;
use canis\registry\models\Registry;
use canis\sensors\base\Sensor as BaseSensor;

class SensorInstance extends Instance
{
    const COLLECT_DEPTH = 1;

    public function getObjectType()
    {
        return 'sensor';
    }

    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['sensor']->getAll(1)
        );
    }

    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        $collections = $this->collectChildModels();
        $c['sensors'] = $collections['sensor']->getPackage($itemLimit);
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
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
		if ($event->pause) {
			$timeString = $event->pause;
		}
    	if ($event->state !== $this->model->state) {
    		$this->triggerStateChange($event);
    	}
    	return $this->scheduleCheck();
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
        $this->model->last_check = date("Y-m-d G:i:s");
    	if (!$save) {
    		return true;
    	}
    	return $this->scheduleCheck(true);
    }
    
    public function scheduleCheck($failback = false, $save = true)
    {
        $timeString = $this->object->getCheckInterval($this);
        $failbackTimeString = $this->object->getFailbackTime($this);
        if ($failback) {
            $this->model->next_check = date("Y-m-d G:i:s", max(strtotime($failbackTimeString), strtotime($timeString)));
        } else {
            $this->model->next_check = date("Y-m-d G:i:s", strtotime($timeString));
        }
        if ($save) {
            return $this->model->save();
        }
        return true;
    }

    public function getPackage($viewPackage = false)
    {
        $package = parent::getPackage($viewPackage);
        if ($viewPackage) {
            $package['view']['events'] = ['handler' => 'events', 'items' => []];
            foreach (SensorEvent::find()->where(['sensor_id' => $this->model->id])->all() as $event) {
                $package['events']['items'][$event->id] = $event->attributes;
            }
            if ($this->hasDataPoint()) {
                $package['view']['data'] = ['handler' => 'data', 'items' => []];
                foreach (SensorData::find()->where(['sensor_id' => $this->model->id])->all() as $data) {
                    $package['data']['items'][$data->id] = $data->value;
                }
            }
        }
        return $package;
    }

    public function getDataPoint()
    {
        return false;
    }

    public function getHasContacts()
    {
        return false;
    }


    public function getHasNotes()
    {
        return false;
    }

    public function hasDataPoint()
    {
        return $this->object instanceof SensorDataInterface;
    }

    public function getViewPackage($package)
    {
        $view = parent::getViewPackage($package);
        $view['state'] = ['handler' => 'sensorState', 'state' => $this->model->state];
        $view['data'] = ['handler' => 'sensorData', 'items' => []];
        $view['events'] = ['handler' => 'sensorEvents', 'items' => []];
        return $view;
    }
}