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

class ServerInstance extends Instance
{
    const COLLECT_DEPTH = 5;
    
    public function getObjectType()
    {
        return 'server';
    }


    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['sensor']->getAll(2), 
            $collections['resource']->getAll(1), 
            $collections['site']->getAll(3, false, ['service', 'serviceReference']),
            $collections['service']->getAll(1)
        );
    }

    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        $collections = $this->collectChildModels();
        $c['sensors'] = $collections['sensor']->getPackage($itemLimit);
        $c['resources'] = $collections['resource']->getPackage($itemLimit, false, ['resource']);
        $c['sites'] = $collections['site']->getPackage($itemLimit);
        $c['services'] = $collections['service']->getPackage($itemLimit, false, ['service']);
        return $c;
    }

    public function getHasContacts()
    {
        return true;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
}