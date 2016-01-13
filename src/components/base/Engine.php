<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */
namespace psesd\sensorHub\components\base;

use Yii;

class Engine extends \canis\base\Component
{
	static $registeredShutdownFunction = false;
    public $verbose = false;

	static public function shutdownLogs()
	{
		foreach (['cron', 'daemon'] as $logId) {
			$log = new \psesd\sensorHub\models\LogModel;
			$log->key = $logId;
			$log->statusLog->save(true);
		}
	}

	static public function registerShutdownFunction()
	{
		if (!static::$registeredShutdownFunction) {
			register_shutdown_function([get_called_class(), 'shutdownLogs']);
		}
		static::$registeredShutdownFunction = true;
	}
	static public function getCronLog()
	{
		static::registerShutdownFunction();
		$cronLog = new \psesd\sensorHub\models\LogModel;
		$cronLog->key = 'cron';
		return $cronLog->statusLog;
	}

	static public function getDaemonLog()
	{
		static::registerShutdownFunction();
		$cronLog = new \psesd\sensorHub\models\LogModel;
		$cronLog->key = 'daemon';
		return $cronLog->statusLog;
	}

	static public function getProviderLog()
	{
		static::registerShutdownFunction();
		$cronLog = new \psesd\sensorHub\models\LogModel;
		$cronLog->key = 'provider';
		return $cronLog->statusLog;
	}
}
?>