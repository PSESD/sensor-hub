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
    const COLLECT_DEPTH = 3;
    public function getObjectType()
    {
        return 'service';
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }


    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['sensor']->getAll(3),
            $collections['resource']->getAll(1)
        );
    }


    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        $collections = $this->collectChildModels();
        $c['sensors'] = $collections['sensor']->getPackage($itemLimit);
        $c['resources'] = $collections['resource']->getPackage($itemLimit);
        return $c;
    }
}