<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */
namespace psesd\sensorHub\components\instances;

use Yii;
use canis\broadcaster\eventTypes\EventType;
use psesd\sensors\providers\ProviderInterface;

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