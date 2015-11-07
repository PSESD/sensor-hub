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

class SiteInstance extends Instance
{
    public function getObjectType()
    {
        return 'site';
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
        $c['resources'] = $collections['resource']->getPackage(3);
        $c['services'] = $collections['service']->getPackage(1);
        return $c;
    }
    
    public function getPackage()
    {
    	$package = parent::getPackage();

    	return $package;
    }
}