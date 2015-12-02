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


    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['sensor']->getAll(2), 
            $collections['resource']->getAll(1), 
            $collections['site']->getAll(5),
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