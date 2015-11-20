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

class ResourceReferenceInstance extends Instance
{
    const COLLECT_DEPTH = 3;
    
    public function getObjectType()
    {
        return 'resourceReference';
    }

    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['resource']->getAll(1, ['resource'])
        );
    }

    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }
    
}