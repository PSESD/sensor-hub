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

class SiteInstance extends Instance
{
    const COLLECT_DEPTH = 3;
    public function getObjectType()
    {
        return 'site';
    }

	static public function collectEventTypes()
    {
    	$events = [];
    	return $events;
    }

    public function getParentObjects()
    {
        return $this->collectParentObjects(static::COLLECT_DEPTH);
    }

    public function getChildObjects()
    {
        return $this->collectChildObjects(static::COLLECT_DEPTH);
    }

    public function getComponentPackage()
    {
        $c = [];
        $collections = $this->getChildObjects();
        $c['sensors'] = $collections['sensor']->getPackage(3);
        $c['resources'] = $collections['resource']->getPackage(3);
        $c['services'] = $collections['service']->getPackage(1);
        return $c;
    }
    

    public function getInfo()
    {

        $info = $this->object->getInfo();
        $collections = $this->collectChildObjects(static::COLLECT_DEPTH);
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