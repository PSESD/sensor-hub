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