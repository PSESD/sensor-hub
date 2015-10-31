<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */
namespace canis\sensorHub\components\base;

use Yii;

class Engine extends \canis\base\Component
{
	static $registeredShutdownFunction = false;
    public $verbose = false;

	static public function shutdownLogs()
	{
		foreach (['cron', 'daemon'] as $logId) {
			$log = new \canis\sensorHub\models\LogModel;
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
		$cronLog = new \canis\sensorHub\models\LogModel;
		$cronLog->key = 'cron';
		return $cronLog->statusLog;
	}

	static public function getDaemonLog()
	{
		static::registerShutdownFunction();
		$cronLog = new \canis\sensorHub\models\LogModel;
		$cronLog->key = 'daemon';
		return $cronLog->statusLog;
	}
}
?>