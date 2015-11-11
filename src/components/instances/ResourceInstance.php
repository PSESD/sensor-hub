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

class ResourceInstance extends Instance
{
    const COLLECT_DEPTH = 4;
    
    public function getObjectType()
    {
        return 'resource';
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
        $collections = $this->getParentObjects();
        $c['servers'] = $collections['server']->getPackage(1);
        $c['sites'] = $collections['site']->getPackage(4);
        return $c;
    }

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
}