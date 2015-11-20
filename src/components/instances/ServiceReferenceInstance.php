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

    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['sensor']->getAll(3), 
            $collections['resource']->getAll(1), 
            $collections['service']->getAll(1, ['service']),
            // provided for
            $collections['site']->getAll(1),
            $collections['server']->getAll(1)
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

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
}