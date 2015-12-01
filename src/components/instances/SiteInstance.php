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
use canis\sensors\serviceReferences\ServiceBinding;
use canis\registry\models\Registry;
use canis\sensorHub\models\Service;
use canis\sensorHub\models\Contact as ContactModel;

class SiteInstance extends Instance
{
    const COLLECT_DEPTH = 4;
    public function getObjectType()
    {
        return 'site';
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
            $collections['resource']->getAll(['resource' => 4, 'resourceReference' => 1]), 
            $collections['service']->getAll(1)
        );
    }

    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        $parentCollections = $this->collectParentModels();
        $c['server'] = $parentCollections['server']->getPackage($itemLimit);

        $childCollections = $this->collectChildModels();
        $c['sensors'] = $childCollections['sensor']->getPackage($itemLimit);
        $c['resources'] = $childCollections['resource']->getPackage($itemLimit);
        $c['services'] = $childCollections['service']->getPackage($itemLimit);
        return $c;
    }

    public function getHasContacts()
    {
        return true;
    }

    public function getInfo()
    {

        $info = $this->object->getInfo();
        $collections = $this->collectChildModels();
        $provides = [];
        $references = [];
        $boundIps = [];
        foreach ($collections['service']->getAll() as $serviceObj) {
            if ($serviceObj instanceof Service) {
                $service = $serviceObj;
                $provides[] = $service->descriptor;
            } else {
                $service = Service::get($serviceObj->service_id);
                $provider = Registry::getObject($service->object_id);
                $serviceDescription = $service->descriptor;
                //\d(get_class($serviceObj->dataObject->object));exit;
                if ($serviceObj->dataObject->object instanceof ServiceBinding) {
                    if(!empty($serviceObj->dataObject->object->binding['ip'])) {
                        $boundIps[] = $serviceObj->dataObject->object->binding['ip'];
                    }
                    if(!empty($serviceObj->dataObject->object->binding['hostname'])) {
                        $serviceDescription .= ', '. $serviceObj->dataObject->object->binding['hostname'];
                    }
                }
                $references[] = $provider->descriptor . ' ('.$serviceDescription.')';
            }
        }
        if (!empty($references)) {
            $info['Connected Services'] = implode('; ', array_unique($references));
        }
        if (!empty($provides)) {
            $info['Provides Services'] = implode('; ', array_unique($provides));
        }
        if (!empty($boundIps)) {
            $info['IP(s)'] = implode('; ', array_unique($boundIps));
        }
        return $info;
    }
}