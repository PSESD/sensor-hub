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
        return [];
    }

    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        $collections = $this->collectParentModels();
        $c['servers'] = $collections['server']->getPackage($itemLimit);
        $c['sites'] = $collections['site']->getPackage($itemLimit);
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
}