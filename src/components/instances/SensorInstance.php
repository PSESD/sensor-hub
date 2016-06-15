<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */
namespace psesd\sensorHub\components\instances;

use Yii;
use canis\broadcaster\eventTypes\EventType;
use psesd\sensors\providers\ProviderInterface;
use psesd\sensors\base\SensorDataInterface;
use psesd\sensors\base\CheckEvent;
use psesd\sensorHub\models\SensorEvent;
use psesd\sensorHub\models\SensorData;
use canis\registry\models\Registry;
use canis\helpers\Date as DateHelper;
use psesd\sensors\base\Sensor as BaseSensor;

class SensorInstance 
    extends Instance
    implements \canis\broadcaster\BroadcastableInterface
{
    const COLLECT_DEPTH = 1;

    public function __wakeup()
    {
        foreach ($this->behaviors() as $id => $behavior) {
            $this->attachBehavior($id, $behavior);
        }
    }
    public function behaviors()
    {
        return array_merge(parent::behaviors(),
            [
                'Broadcastable' => 'canis\broadcaster\Broadcastable'
            ]
        );
    }

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
        $sensorDurationExtra = '{% if duration %} The previous state (\'{{ oldState }}\') lasted for {{ duration }}.{% endif %}';
        $events['sensor_state_changed'] = [
            'name' => 'Sensor State Changed',
            'descriptorString' => 'Sensor \'{{ sensor.name }}\' in \'{{ object.name }}\' changed from \'{{ oldState }}\' to \'{{ newState }}\'.'.$sensorDurationExtra
        ];
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
        if ($this->hasDataPoint()) {
            $this->recordSensorData($event);
        }
    	if ($event->state !== $this->model->state) {
    		$this->triggerStateChange($event);
    	}
    	return $this->scheduleCheck();
    }


    protected function recordSensorData(CheckEvent $event)
    {
        if ($event->dataValue === null) {
            return true;
        }
        $sensorData = new SensorData;
        $sensorData->sensor_id = $this->model->primaryKey;
        $sensorData->value = $event->dataValue;
        $sensorData->save();
        return true;
    }

    protected function triggerStateChange(CheckEvent $event)
    {
        $payload = [];
        $duration = false;
        $previousSensorEvent = SensorEvent::find()->where(['sensor_id' => $this->model->primaryKey])->orderBy(['created' => SORT_DESC])->one();
        if ($previousSensorEvent) {
            $durationSeconds = time() - strtotime($previousSensorEvent->created . ' UTC');
            $duration = DateHelper::niceDuration($durationSeconds, 2);
        }
        $sensorEvent = new SensorEvent;
        $sensorEvent->sensor_id = $this->model->primaryKey;
        $sensorEvent->old_state = $this->model->state;
        if (empty($sensorEvent->old_state)) {
            $sensorEvent->old_state = BaseSensor::STATE_UNCHECKED;
        }
        $payload['oldState'] = $sensorEvent->old_state;
        $sensorEvent->new_state = $payload['newState'] = $event->state;
        $sensorEvent->data = serialize($event);
        $sensorEvent->save();
        $this->model->state = $event->state;

        $payload['duration'] = $duration;
        $payload['sensor'] = [];
        $payload['sensor']['id'] = $this->model->primaryKey;
        $payload['sensor']['system_id'] = $this->model->system_id;
        $payload['sensor']['name'] = $this->object->name;
        $payload['object']['id'] = null;
        $payload['object']['name'] = null;
        $objectId = $this->model->primaryKey;
        if (!empty($this->objectModel)) {
            $payload['object']['id'] = $this->objectModel->primaryKey;
            $payload['object']['name'] = $this->objectModel->descriptor;
        }
        $priority = EventType::PRIORITY_MEDIUM;
        if ($this->object->isCritical && $payload['oldState'] !== BaseSensor::STATE_UNCHECKED) {
            $priority = EventType::PRIORITY_CRITICAL;
        }
        $this->triggerBroadcastEvent('sensor_state_changed', $payload, $objectId, $priority);
        return true;
    }
    
    public function pauseSensor($save = true)
    {
        $this->model->last_check = gmdate("Y-m-d G:i:s");
    	if (!$save) {
    		return true;
    	}
    	return $this->scheduleCheck(true);
    }
    
    public function scheduleCheck($failback = false, $save = true)
    {
        $timeString = $this->object->getCheckInterval($this);
        $failbackTimeString = $this->object->getFailbackTime($this);
        if ($timeString === false) {
            $this->model->next_check = null;
        } else {
            if ($failback) {
                $this->model->next_check = gmdate("Y-m-d G:i:s", max(strtotime($failbackTimeString), strtotime($timeString)));
            } else {
                $this->model->next_check = gmdate("Y-m-d G:i:s", strtotime($timeString));
            }
        }
        if ($save) {
            return $this->model->save();
        }
        return true;
    }


    public function getDataPoint($formatted = false)
    {
        if ($this->hasDataPoint()) {
            if ($formatted) {
                return $this->object->getDataValueFormatted();
            } else {
                return $this->object->getDataValue();
            }
        }
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

    public function getSensorDataPackage()
    {
        $package = [];
        $items = SensorData::find()->where(['sensor_id' => $this->model->id])->orderBy(['created' => SORT_ASC])->all();
        foreach ($items as $item) {
            $package[] = [
                date("c", strtotime($item->created . ' UTC')),
                (float)$item->value,
                $this->object->formatDataPoint($item->value)
            ];
        }
        return $package;
    }

    public function getSensorEventPackage()
    {
        $package = [];
        $items = $this->model->getRecentSensorEventQuery(10)->all();
        foreach ($items as $item) {
            $packageItem = [
                'datetime' => date("c", strtotime($item->created . ' UTC')),
                'datetimeHuman' => date("F j, Y g:i:sa T", strtotime($item->created . ' UTC')),
                'event' => $this->object->describeEventFormatted($this, $item),
                'messages' => []
            ];
            foreach ($item->dataObject->getMessages() as $message) {
                if ($message['level'] === '_e') {
                    $state = 'danger';
                } elseif ($message['level'] === '_w') {
                    $state = 'warning';
                } else {
                    $state = 'info';
                }
                $packageItem['messages'][] = ['datetime' => date("F j, Y g:i:sa T", $message['time']), 'message' => $message['message'], 'state' => $state];
            }
            if ($item->new_state === BaseSensor::STATE_NORMAL) {
                $packageItem['state'] = 'success';
            } elseif ($item->new_state === BaseSensor::STATE_CHECK_FAIL || $item->new_state === BaseSensor::STATE_UNCHECKED) {
                $packageItem['state'] = 'warning';
            } else {
                $packageItem['state'] = 'danger';
            }
            $package[$item->id] = $packageItem;
        }
        return $package;
    }

    public function getViewPackage($package)
    {
        $view = parent::getViewPackage($package);
        $view['state'] = ['handler' => 'sensorState', 'stateDescription' => $this->object->describe($this), 'state' => $this->model->simpleState, 'lastUpdate' => date("c", strtotime($this->model->last_check . ' UTC'))];
        $view['events'] = ['handler' => 'sensorEvents', 'items' => $this->getSensorEventPackage()];

        if ($this->hasDataPoint()) {
            $view['data'] = ['handler' => 'sensorData', 'columns' => 12, 'items' => $this->getSensorDataPackage()];
        }
        return $view;
    }
}