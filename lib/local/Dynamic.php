<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensors\local;

use Yii;
use canis\sensors\base\CheckEvent;
use canis\sensors\base\SensorDataInterface;

class Dynamic 
	extends Sensor
{
	public function name()
	{
		if (!isset($this->_name)) {
			return 'Local Sensor';
		}
		return $this->_name;
	}

	public function getDataValue()
	{
		return null;
	}

	protected function doCheck(CheckEvent $event)
	{
		if ($this->payload === null) {
			$event->addError('Local sensor did not provide any information');
			$event->state = static::STATE_UNCHECKED;
		} elseif ($this->payload === false) {
			$event->addError('Local sensor encountered an unknown error when determining its value');
			$event->state = static::STATE_CHECK_FAIL;
		} else {
			$payload = $this->payload;
			if (!empty($payload['error'])) {
				$this->addError($payload['error']);
				$event->state = static::STATE_CHECK_FAIL;
			} elseif (isset($payload['state']) 
				&& in_array($payload['state'], [static::STATE_ERROR, static::STATE_LOW, static::STATE_NORMAL, static::STATE_HIGH, static::STATE_UNCHECKED])) {
				$event->state = $payload['state'];
				$event->dataValue = $this->getDataValue();
			} else {
				$event->addError('Local sensor was expected to provide state information but did not.');
				$event->state = static::STATE_ERROR;
			}
		}
	}
}