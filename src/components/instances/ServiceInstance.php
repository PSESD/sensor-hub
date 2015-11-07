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

class ServiceInstance extends Instance
{
    public function getObjectType()
    {
        return 'service';
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }

    public function getComponentPackage()
    {
        $c = [];
        $collections = $this->collectObjects(3);
        $c['sensors'] = $collections['sensor']->getPackage(3);
        $c['resources'] = $collections['resource']->getPackage(1);
        return $c;
    }
}