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

class ServiceReferenceInstance extends Instance
{
    const COLLECT_DEPTH = 3;
    
    public function getObjectType()
    {
        return 'serviceReference';
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
        $collections = $this->getChildObjects();
        $c['sensors'] = $collections['sensor']->getPackage(3);
        $c['resources'] = $collections['resource']->getPackage(1);
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
}