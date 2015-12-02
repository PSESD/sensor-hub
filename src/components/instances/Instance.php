<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */
namespace canis\sensorHub\components\instances;

use Yii;
use yii\helpers\Url;
use canis\caching\Cacher;
use canis\broadcaster\eventTypes\EventType;
use canis\actions\Status;
use linslin\yii2\curl;

use canis\registry\models\Registry;

use canis\sensors\resources\ResourceInterface;
use canis\sensors\resourceReferences\ResourceReferenceInterface;
use canis\sensors\sites\SiteInterface;
use canis\sensors\base\SensorInterface;
use canis\sensors\services\ServiceInterface;
use canis\sensors\serviceReferences\ServiceReferenceInterface;
use canis\sensors\servers\ServerInterface;
// use canis\sensors\base\SensorInterface;
use canis\sensors\providers\ProviderInterface;

use canis\sensorHub\components\base\Engine;
use canis\sensorHub\models\Contact as ContactModel;
use canis\sensorHub\models\Note as NoteModel;
use canis\sensorHub\models\Site as SiteModel;
use canis\sensorHub\models\Server as ServerModel;
use canis\sensorHub\models\Sensor as SensorModel;
use canis\sensorHub\models\Service as ServiceModel;
use canis\sensorHub\models\ServiceReference as ServiceReferenceModel;
use canis\sensorHub\models\Resource as ResourceModel;
use canis\sensorHub\models\ResourceReference as ResourceReferenceModel;
use canis\sensorHub\models\Provider as ProviderModel;

abstract class Instance 
	extends \canis\base\Component
    implements \canis\broadcaster\BroadcastableInterface
{
    const EVENT_COLLECT_FROM_OBJECTS = 'collectFromObjects';
    const EVENT_COLLECT_FROM_MODELS = 'collectFromModels';

    public $collectObjectCacheTime = 3600;
	public $model;
	public $object;
    public $setupErrors = [];
	protected $_attributes = [];
	protected $_statusLog;
	protected $_cache = [];

	public function __sleep()
    {
        $keys = array_keys((array) $this);
        $bad = ["\0*\0_cache", "model"];
        foreach ($keys as $k => $key) {
            if (in_array($key, $bad)) {
                unset($keys[$k]);
            }
        }

        return $keys;
    }

    public function __clone()
    {
        if (isset($this->object)) {
            $this->object = clone $this->object;
            $this->object->model = $this->model;
        }
    }

    abstract public function getObjectType();

    public function init()
    {
        parent::init();
        $this->on(static::EVENT_COLLECT_FROM_OBJECTS, [$this, 'internalCollectFromObjects']);
        $this->on(static::EVENT_COLLECT_FROM_MODELS, [$this, 'internalCollectFromModels']);
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(),
            [
                'Broadcastable' => 'canis\broadcaster\Broadcastable'
            ]
        );
    }

    protected function getCollectEvent($type, $maxDepth = false)
    {
        $event = new CollectEvent;
        $event->type = $type;
        $event->maxDepth = $maxDepth;
        $event->addCollection('sensor', new SensorCollection(['model' => $this->model]));
        $event->addCollection('server', new ServerCollection(['model' => $this->model]));
        $event->addCollection('site', new SiteCollection(['model' => $this->model]));
        $event->addCollection(['resource', 'resourceReference'], new ResourceCollection(['model' => $this->model]));
        $event->addCollection(['service', 'serviceReference'], new ServiceCollection(['model' => $this->model]));
        return $event;
    }

    public function collectParentModels()
    {
        if (isset($this->_cache['parent_models'])) {
            return $this->_cache['parent_models'];
        }
        $maxDepth = static::COLLECT_DEPTH;
        $event = $this->getCollectEvent('parents', $maxDepth);
        $this->trigger(static::EVENT_COLLECT_FROM_MODELS, $event);
        return $this->_cache['parent_models'] = $event->collections;
    }


    public function collectChildModels()
    {
        if (isset($this->_cache['child_models'])) {
            return $this->_cache['child_models'];
        }
        $maxDepth = static::COLLECT_DEPTH;
        $event = $this->getCollectEvent('children', $maxDepth);
        $this->trigger(static::EVENT_COLLECT_FROM_MODELS, $event);
        return $this->_cache['child_models']= $event->collections;
    }

    public function collectParentModelsFromObjects()
    {
        $maxDepth = static::COLLECT_DEPTH;
        $event = $this->getCollectEvent('parents', $maxDepth);
        $this->trigger(static::EVENT_COLLECT_FROM_OBJECTS, $event);
        return $event->collections;
    }


    public function collectChildModelsFromObjects()
    {
        $maxDepth = static::COLLECT_DEPTH;
        $event = $this->getCollectEvent('children', $maxDepth);
        $this->trigger(static::EVENT_COLLECT_FROM_OBJECTS, $event);
        return $event->collections;
    }

    public function internalCollectFromModels($event)
    {
        if ($event->type === 'children') {
            $models = $this->model->getChildIds();
        } else {
            $models = $this->model->getParentIds();
        }
        foreach ($models as $modelId) {
            $model = Registry::getObject($modelId);
            if (!isset($model->dataObject) || !isset($model->dataObject->object) || !($model->dataObject->object instanceof \canis\sensors\base\BaseInterface)) {
                continue;
            }
            if ($model->hasAttribute('active') && empty($model->active)) {
                continue;
            }
            $event->pass($model);
            $recurse[] = $model;
        }
    }

    public function internalCollectFromObjects($event)
    {
        if ($event->type === 'children') {
            $modelTypes = $this->model->childModels();
        } else {
            $modelTypes = $this->model->parentModels();
        }
        $recurse = [];
        foreach ($modelTypes as $type => $models) {
            foreach ($models as $model) {
                if (!isset($model->dataObject) || !isset($model->dataObject->object) || !($model->dataObject->object instanceof \canis\sensors\base\BaseInterface)) {
                    continue;
                }
                $event->pass($model);
                $recurse[] = $model;
            }
        }
        $event->depth++;
        foreach ($recurse as $model) {
            if ($event->maxDepth === false || $event->maxDepth > $event->depth) {
                $model->dataObject->trigger(static::EVENT_COLLECT_FROM_OBJECTS, $event);
            }
        }
        $event->depth--;
    }

    protected function internalFindModel(\canis\sensors\base\BaseInterface $object, $parentObject, $modelClass)
    {
        $dummyModel = new $modelClass;
        $model = false;
        $where = [];
        $parentId = null;
        if (isset($parentObject->model->primaryKey)) {
            $parentId = $parentObject->model->primaryKey;
        }
        $self = $this;
        // $uniqueAttributes = [];
        $uniqueAttributes = ['provider_id' => $parentId];
        $findAttributes = [
            'system_id' => $object->id,
            //  'service_id' => function($object, $parentObject) use ($self) { return $object->discoverServiceId($self); },
            //  'resource_id' => function($object, $parentObject) use ($self) { return $object->discoverResourceId($self); },
            //  'provider_id' => isset($parentObject) ? $parentObject->model->primaryKey : null,
            //  'object_id' => isset($parentObject) ? $parentObject->model->primaryKey : null
        ];
        if ($object instanceof \canis\sensors\base\Sensor
            ||  $object instanceof \canis\sensors\services\Base
            ) {
            $findAttributes['object_id'] = isset($parentObject) ? $parentObject->model->primaryKey : null;
        }
        if ($object instanceof \canis\sensors\serviceReferences\Base
            ) {
            $findAttributes['object_id'] = isset($parentObject) ? $parentObject->model->primaryKey : null;
            $findAttributes['service_id'] = function($object, $parentObject) use ($self) { return $object->discoverServiceId($self); };
        }
        if ($object instanceof \canis\sensors\resourceReferences\Base
            ) {
            $findAttributes['object_id'] = isset($parentObject) ? $parentObject->model->primaryKey : null;
            $findAttributes['resource_id'] = function($object, $parentObject) use ($self) { return $object->discoverResourceId($self); };
        }

        foreach ($findAttributes as $attribute => $value) {
            if ($dummyModel->hasAttribute($attribute)) {
                if (is_callable($value)) {
                    $value = $value($object, $parentObject);
                }
                if (empty($value)) {
                    return false;
                }
                $where[$attribute] = $value;
            }
        }
        if (!empty($where)) {
            $model = $modelClass::find()->where($where)->one();
        }
        if (!empty($model)) {
            if ($model->hasAttribute('active') && !empty($model->active)) {
                foreach ($uniqueAttributes as $attribute => $value) {
                    if ($model->hasAttribute($attribute) && $model->{$attribute} !== $value) {
                        return false;
                    }
                }
            }
            return $model;
        } else {
            return null;
        }
    }

    public function setLog($log)
    {
        $this->_cache['log'] = $log;
        return $this;
    }

    public function getLog()
    {
        if (!isset($this->_cache['log'])) {
            $this->_cache['log'] = Engine::getProviderLog();
        } elseif (!isset($this->_cache['log']->connectedMessageStore)) {
            $this->_cache['log']->setConnectedMessageStore(Engine::getProviderLog());
        }
        return $this->_cache['log'];
    }

    public function discoverObject($objectType, $id, $where = [])
    {
        $className = static::getSensorObjectModelClassByName($objectType);
        if (empty($className)) {
            return false;
        }
        $where['system_id'] = $id;
        return $className::find()->where($where)->one();
    }

    protected function internalConfigModel(\canis\sensors\base\BaseInterface $object, $parentObject, $model)
    {
        $self = $this;
        $parentKey = null;
        if (isset($parentObject)) {
            $parentKey = $parentObject->model->primaryKey;
        }
        $nextCheckFunction = function($object, $parentObject, $model) use ($self) {
            $timeString = $object->discoverInitialCheck($self);
            if ($model->isNewRecord) {
                $timeString = 'now';
            }
            return date("Y-m-d G:i:s", strtotime($timeString)); 
        };
        $possibleAttributes = [
            'system_id' => $object->id,
            'name' => $object->getName(),
            'type' => function($object, $parentObject) { return $object->getType(); },
            'service_id' => function($object, $parentObject) use ($self) { return $object->discoverServiceId($self); },
            'resource_id' => function($object, $parentObject) use ($self) { return $object->discoverResourceId($self); },
            'next_check' => $nextCheckFunction,
            'provider_id' => $parentKey,
            'object_id' => $parentKey
        ];
        foreach ($possibleAttributes as $attribute => $value) {
            if ($model->hasAttribute($attribute)) {
                if (is_callable($value)) {
                    $value = $value($object, $parentObject, $model);
                }
                if (empty($value)) {
                    return false;
                }
                $model->{$attribute} = $value;
            }
        }
        return $model;
    }

    protected function internalInitialize(\canis\sensors\base\BaseInterface $object, \canis\sensors\base\BaseInterface $parentObject = null, $initialInitialize = true)
    {
        $object->model = null;
        $modelClass = static::getSensorObjectModelClass($object);
        //\d($object->getSites());exit;
        if (empty($modelClass)) {
            $this->log->addWarning("Could not determine class for {$object->name}");
            return false;
        }
        $configured = false;
        if (!isset($object->model)) {
            $object->model = $this->internalFindModel($object, $parentObject, $modelClass);
            if ($object->model === false) {
                // $this->log->addWarning("{$object->name} was already in the system by another provider", ['class' => get_class($object->model), 'id' => $object->id]);
                return false;
            } elseif ($object->model === null) {
                $modelConfig = [];
                $modelConfig['class'] = $modelClass;
                $this->log->addInfo("Creating new " . $modelClass ." with ID " . $object->id);
                $modelConfig['dataObject'] = Yii::createObject([
                    'class' => static::getSensorObjectInstanceClass($object)
                ]);
                $object->model = Yii::createObject($modelConfig);
                // $object->model->detachKnownBehaviorsExcept(['Registry', 'Relatable', 'Blame', 'Data']);
                $configured = true;
                if (!$this->internalConfigModel($object, $parentObject, $object->model)) {
                    $this->log->addWarning("{$object->name} could not be set up");
                    return false;
                }
                if (!$object->model->save()) {
                    $this->log->addError('Unable to save object model', ['class' => get_class($object->model), 'attributes' => $object->model->attributes, 'errors' => $object->model->errors]);
                    \d($object->model->errors);
                    return false;
                }
            }
        } else {
            $this->log->addInfo("Object " . get_class($object->model) ." with ID " . $object->id ." already created... updating!");
        }
        if ($object->model->hasAttribute('active')) {
            if ($object->model->isNewRecord) {
                $object->model->active = 1;
            } else {
                $object->model->activate();
            }
        }
        if (!$configured) {
            if (!$this->internalConfigModel($object, $parentObject, $object->model)) {
                $this->log->addWarning("{$object->name} could not be updated");
            }
        }
        $object->model->dataObject->object = $baseObject = $object->simpleClone();
        $extra = '';
        if (!empty($parentObject)) {
            $extra = ' called by ' . get_class($parentObject) .':'. $parentObject->id;
        }
        $objectTypes = ['server', 'resource',  'resourceReference', 'service', 'serviceReference', 'site', 'sensor'];
        if ($initialInitialize) {
            $objectTypes = ['sensor'];
        }
        foreach ($objectTypes as $objectType) {
            $getMethod = 'get'.ucfirst($objectType).'s';
            $behaviorName = 'Has'.ucfirst($objectType) .'s';
            if ($object->getBehavior($behaviorName) === null) {
                continue;
            }
            $subobjects = $object->getBehavior($behaviorName)->{$getMethod}();
            $currentModels = $this->internalCurrentModels($objectType, $object->model->id);
            foreach ($subobjects as $subobject) {
                if (!$this->internalInitialize($subobject, $object, false)) {
                    // $this->log->addError("Unable to initialize {$objectType}: {$subobject->id}");
                }
                if (!empty($subobject->model->id)) {
                    unset($currentModels[$subobject->model->id]);
                }
            }
            foreach ($currentModels as $model) {
                $this->log->addInfo("Deactivating old model {$model->descriptor} ($model->id)");
                $model->deactivate();
            }
        }
        // $object->model->detachKnownBehaviorsExcept(['Registry', 'Relatable', 'Blame', 'Data']);
        if (!$object->model->save()) {
            $this->log->addError('Unable to save (final) object model', ['class' => get_class($object->model), 'attributes' => $object->model->attributes, 'errors' => $object->model->errors]);
            return false;
        }

        if (!$object->onInstantiation($object->model->dataObject)) {
            $this->log->addError('Unable to save (final) object model', ['class' => get_class($object->model), 'attributes' => $object->model->attributes, 'errors' => $object->model->errors]);
            return false;
        }
        return true;
    }

    public function internalCurrentModels($objectType, $objectId) 
    {
        $modelClass = static::getSensorObjectModelClassByName($objectType);
        $search = [];
        if (in_array($objectType, ['site', 'server'])) {
            $search['provider_id'] = $objectId;
        } else {
            $search['object_id'] = $objectId;
        }
        $models = [];
        $all = $modelClass::find()->where($search)->all();
        foreach ($all as $model) {
            $models[$model->id] = $model;
        }
        return $models;
    }

    abstract public function childModelsFromObjects();

    abstract public function getComponentPackage($viewLimit = null);

    public function getViewPackage($package)
    {
        $view = [];
        if (!empty($package['info'])) {
            $view['info'] = ['handler' => 'info', 'columns' => 4];
        }
        if ($this->hasNotes) {
            $view['notes'] = ['handler' => 'notes', 'items' => []];
            foreach (NoteModel::find()->where(['object_id' => $this->model->id])->all() as $model) {
                $view['notes']['items'][$model->id] = $model->attributes;
                $view['notes']['items'][$model->id]['date'] = date("F d, Y", strtotime($model->modified));
            }
            $view['notes']['urls'] = [];
            $view['notes']['urls']['create'] = Url::to(['/note/create', 'objectId' => '__objectId__']);
            $view['notes']['urls']['update'] = Url::to(['/note/update', 'id' => '__id__']);
            $view['notes']['urls']['delete'] = Url::to(['/note/delete', 'id' => '__id__']);
            $view['notes']['urls']['view'] = Url::to(['/note/view', 'id' => '__id__']);
        }

        if ($this->hasContacts) {
            $view['contacts'] = ['handler' => 'contacts', 'items' => []];
            foreach (ContactModel::find()->where(['object_id' => $this->model->id])->all() as $model) {
                $view['contacts']['items'][$model->id] = $model->attributes;
                $view['contacts']['items'][$model->id]['descriptor'] = $model->descriptor;
                $view['contacts']['items'][$model->id]['date'] = date("F d, Y", strtotime($model->modified));
            }
            $view['contacts']['urls'] = [];
            $view['contacts']['urls']['create'] = Url::to(['/contact/create', 'objectId' => '__objectId__']);
            $view['contacts']['urls']['update'] = Url::to(['/contact/update', 'id' => '__id__']);
            $view['contacts']['urls']['delete'] = Url::to(['/contact/delete', 'id' => '__id__']);
            $view['contacts']['urls']['view'] = Url::to(['/contact/view', 'id' => '__id__']);
        }
        $listableComponents = [];
        $listableComponents['services'] = [
            'item' => 'service-button',
            'subitem' => 'services',
            'label' => 'Services',
            'viewUrl' => Url::to(['/service/view', 'id' => '__id__'])
        ];
        $listableComponents['resources'] = [
            'item' => 'resource-button',
            'subitem' => 'resources',
            'label' => 'Provided Resources',
            'viewUrl' => Url::to(['/resource/view', 'id' => '__id__'])
        ];
        $listableComponents['sites'] = [
            'item' => 'site-button',
            'label' => 'Sites',
            'viewUrl' => Url::to(['/site/view', 'id' => '__id__'])
        ];
        $listableComponents['sensors'] = [
            'item' => 'sensor-button',
            'label' => 'Sensors',
            'viewUrl' => Url::to(['/sensor/view', 'id' => '__id__'])
        ];
        $n = 0;
        foreach ($listableComponents as $key => $component) {
            if (!empty($package['components'][$key]['items'][$component['item']]['subitems'])) {
                $items = $package['components'][$key]['items'][$component['item']]['subitems'];
                if (isset($component['subitem'])) {
                    if (!isset($items[$component['subitem']]) || !($items = $items[$component['subitem']]['subitems']) || empty($items)) {
                        continue;
                    }
                }
                $view[$key] = [
                    'handler' => 'list', 
                    'header' => $component['label'], 
                    'priority' => 99999999-$n, 
                    'items' => $items,
                    'minColumns' => 6,
                    'urls' => ['view' => $component['viewUrl']]
                ];
            }
            $n++;
        }
        return $view;
    }

    public function getPackage($viewPackage = false)
    {
        $package = [];
        $package['id'] = $this->model->id;
        $package['type'] = $this->getObjectType();
        $package['url'] = Url::to([$this->getObjectType() . '/view', 'id' => $this->model->id]);
        $package['descriptor'] = $this->model->descriptor;
        $package['subdescriptor'] = $this->model->subdescriptor;
        $itemLimit = null;
        if ($viewPackage) {
            $itemLimit = false;
        }
        $package['components'] = $this->getComponentPackage($itemLimit);
        $package['info'] = $this->getInfo();
        if ($viewPackage) {
            $package['view'] = $this->getViewPackage($package);
            unset($package['components']);
        }
        // $package['object'] = $this->object->getPackage();
        $package['attributes'] = $this->attributes;
        return $package;
    }

    public function getHasContacts()
    {
        return false;
    }


    public function getHasNotes()
    {
        return true;
    }

    public function getInfo()
    {
        $info = $this->object->getInfo();
        return $info;
    }

    public static function getSensorObjectModelClassByName($objectName)
    {
        if ($objectName === 'sensor') {
            return SensorModel::className();
        }
        if ($objectName === 'service') {
            return ServiceModel::className();
        }
        if ($objectName === 'serviceReference') {
            return ServiceReferenceModel::className();
        }
        if ($objectName === 'resource') {
            return ResourceModel::className();
        }
        if ($objectName === 'resourceReference') {
            return ResourceReferenceModel::className();
        }
        if ($objectName === 'site') {
            return SiteModel::className();
        }
        if ($objectName === 'server') {
            return ServerModel::className();
        }
        if ($objectName === 'provider') {
            return ProviderModel::className();
        }
        return false;
    }

    public static function getSensorObjectModelClass(\canis\sensors\base\BaseInterface $object)
    {
        if ($object instanceof SensorInterface) {
            return SensorModel::className();
        }
        if ($object instanceof ServiceInterface) {
            return ServiceModel::className();
        }
        if ($object instanceof ServiceReferenceInterface) {
            return ServiceReferenceModel::className();
        }
        if ($object instanceof ResourceReferenceInterface) {
            return ResourceReferenceModel::className();
        }
        if ($object instanceof ResourceInterface) {
            return ResourceModel::className();
        }
        if ($object instanceof SiteInterface) {
            return SiteModel::className();
        }
        if ($object instanceof ServerInterface) {
            return ServerModel::className();
        }
        if ($object instanceof ProviderInterface) {
            return ProviderModel::className();
        }
        return false;
    }


    public static function getSensorObjectInstanceClass(\canis\sensors\base\BaseInterface $object)
    {
        if ($object instanceof SensorInterface) {
            return SensorInstance::className();
        }
        if ($object instanceof ServiceInterface) {
            return ServiceInstance::className();
        }
        if ($object instanceof ServiceReferenceInterface) {
            return ServiceReferenceInstance::className();
        }
        if ($object instanceof ResourceInterface) {
            return ResourceInstance::className();
        }
        if ($object instanceof ResourceReferenceInterface) {
            return ResourceReferenceInstance::className();
        }
        if ($object instanceof ServerInterface) {
            return ServerInstance::className();
        }
        if ($object instanceof SiteInterface) {
            return SiteInstance::className();
        }
        if ($object instanceof ProviderInterface) {
            return ProviderInstance::className();
        }
        return false;
    }

    public function clearAttribute($k)
    {
    	unset($this->_attributes[$k]);
    }
    
    public function validateSetup($scenario = 'create')
    {
        $this->setupErrors = [];
        foreach ($this->setupFields() as $id => $field) {          
            if (empty($field['on'])) {
                $field['on'] = ['update', 'restore', 'create'];
            }
            if (!in_array($scenario, $field['on'])) { continue; }
            if (!empty($field['required']) && (!isset($this->attributes[$id]) || $this->attributes[$id] === '')) {
                $this->setupErrors[$id] = $field['label'] . ' is required';
                continue;
            }
            if (!empty($field['validator']) && !$field['validator']($this->attributes[$id])) {
                if (isset($field['errorMessage'])) {
                    $this->setupErrors[$id] = $field['errorMessage'];
                } else {
                    $this->setupErrors[$id] = $field['label'] . ' is invalid';
                }
                continue;
            }
        }
        return empty($this->setupErrors);
    }

    public function getAttributes()
    {
    	return $this->_attributes;
    }

    public function setAttributes($attributes)
    {
    	$this->_attributes = $attributes;
    }

    public function getStatusLog()
    {
        if (!isset($this->_statusLog)) {
        	$this->_statusLog = new Status;
            $this->_statusLog->saveDatabaseOnMessage = true;
        	$this->_statusLog->lastUpdate = microtime(true);
        	$this->saveCache()->save();
        } else {
        	$checkLog = Cacher::get(['Instance__StatusLog', $this->model->primaryKey, $this->model->created]);
        	if (!$checkLog && !isset($this->_statusLog)) {	
		    	$checkLog = $this->_statusLog = new Status;
                $this->_statusLog->saveDatabaseOnMessage = true;
		    	$checkLog->lastUpdate = microtime(true);
		    	$this->saveCache()->save();
        	}
        	if ($checkLog && $checkLog->lastUpdate && $this->_statusLog->lastUpdate && $checkLog->lastUpdate > $this->_statusLog->lastUpdate) {
        		$this->_statusLog = $checkLog;
        	}
        }
        $this->_statusLog->persistentLog = true;
        $this->_statusLog->log = $this;

        return $this->_statusLog;
    }


    public static function fetchData($url, $apiKey = null, $timeout = 30)
    {
        $curl = new curl\Curl();
        $headers = [];
        if ($apiKey !== null) {
        	$headers[] = 'X-Api-Key: ' . addslashes($apiKey) .'';
        }
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
        $curl->setOption(CURLOPT_TIMEOUT, $timeout);
        $curl->setOption(CURLOPT_HTTPHEADER, $headers); 
        try {
            $response = $curl->get($url, false);
        } catch (\Exception $e) {
            $response = null;
        }
        if (empty($response) || !is_array($response)) {
        	return ['data' => false, 'error' => true, 'responseCode' => (int) $curl->responseCode];
        }
        return ['data' => $response, 'error' => false, 'responseCode' => (int) $curl->responseCode];
    }

    public function initialize($log)
    {
        return false;
    }

    protected function loadObject($config, $interfaceName)
    {
    	if (!isset($config['class']) || !class_exists($config['class'])) {
    		return false;
    	}
    	$reflection = new \ReflectionClass($config['class']);
    	if (!$reflection->implementsInterface($interfaceName)) {
    		return false;
    	}
        try {
            $object = Yii::createObject($config);
        } catch (\Exception $e) {
            throw $e;
            $object = false;
        }
        if (!$object) {
            return false;
        }
        if (!empty($object->invalidEntries)) {
            \d($object->invalidEntries);exit;
        }
        $this->object = $object;
        $this->object->model = $this->model;
    	$this->model->system_id = $this->object->id;
    	return $this->object;
    }

    public function getSensor($id)
    {
        if (empty($this->model) || empty($this->model->primaryKey)) {
            return false;
        }

        $sensor = SensorModel::find()->where(['object_id' => $this->model->primaryKey, 'system_id' => $id])->one();
        if (!$sensor) {
            return false;
        }
        return $sensor->dataObject;
    }


    /**
     * [[@doctodo method_description:saveCache]].
     */
    public function saveCache()
    {
    	if (!isset($this->_statusLog)) {
    		return $this;
    	}
        $this->_statusLog->lastUpdate = microtime(true);
        Cacher::set(['Instance__StatusLog', $this->model->primaryKey, $this->model->created], $this->_statusLog, 3600);
        return $this;
    }

    public function save()
    {
        Yii::$app->db->ensureConnection();
    	return $this->model->save();
    }

    public function getSimpleState()
    {
        if ($this->model->hasMethod('getSimpleState')) {
            return $this->model->getSimpleState();
        }
        return 'default';
    }
}
?>