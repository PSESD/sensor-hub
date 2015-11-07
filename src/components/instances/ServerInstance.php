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

class ServerInstance extends Instance
{
    public function getObjectType()
    {
        return 'server';
    }

    public function getComponentPackage()
    {
        $c = [];
        $collections = $this->collectObjects(4);
        $c['sensors'] = $collections['sensor']->getPackage(5);
        $c['resources'] = $collections['resource']->getPackage(1, ['resource']);
        $c['sites'] = $collections['site']->getPackage(4);
        $c['services'] = $collections['service']->getPackage(3);
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
}