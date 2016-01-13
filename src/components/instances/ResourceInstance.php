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

class ResourceInstance extends Instance
{
    const COLLECT_DEPTH = 4;
    
    public function getObjectType()
    {
        return 'resource';
    }

    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['sensor']->getAll(1)
        );
    }

    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        $childCollections = $this->collectChildModels();
        $parentCollections = $this->collectParentModels();
        $c['servers'] = $parentCollections['server']->getPackage($itemLimit);
        $c['sensors'] = $childCollections['sensor']->getPackage($itemLimit);
        $c['sites'] = $parentCollections['site']->getPackage($itemLimit);
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
}