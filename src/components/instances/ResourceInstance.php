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
    public function getObjectType()
    {
        return 'resource';
    }

    public function getComponentPackage()
    {
        $c = [];
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