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
    const COLLECT_DEPTH = 5;
    
    public function getObjectType()
    {
        return 'server';
    }

    public function getParentObjects()
    {
        return $this->collectParentObjects(static::COLLECT_DEPTH);
    }

    public function getChildObjects()
    {
        return $this->collectChildObjects(static::COLLECT_DEPTH);
    }

    public function getComponentPackage()
    {
        $c = [];
        $collections = $this->childObjects;
        $c['sensors'] = $collections['sensor']->getPackage(5);
        $c['resources'] = $collections['resource']->getPackage(1, ['resource']);
        $c['sites'] = $collections['site']->getPackage(4);
        $c['services'] = $collections['service']->getPackage(1, ['service']);
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
}