<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */
namespace canis\sensorHub\components\sensors;

use Yii;
use canis\broadcaster\eventTypes\EventType;
use canis\action\Status;
use linslin\yii2\curl;

use canis\sensors\assets\AssetInterface;
use canis\sensors\sites\SiteInterface;
use canis\sensors\base\SensorInterface;
use canis\sensors\providers\ProviderInterface;

use canis\sensorHub\models\Site as SiteModel;
use canis\sensorHub\models\Sensor as SensorModel;
use canis\sensorHub\models\Asset as AssetModel;
use canis\sensorHub\models\Source as SourceModel;

abstract class Instance 
	extends \canis\base\Component
    implements \canis\broadcaster\BroadcastableInterface
{
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

    public function behaviors()
    {
        return array_merge(parent::behaviors(),
            [
                'Broadcastable' => 'canis\broadcaster\Broadcastable'
            ]
        );
    }

    public function getPackage()
    {
        $package = [];
        $package['model'] = false;
        if (isset($this->model)) {
            $package['model'] = $this->model->attributes;
            $package['model']['descriptor'] = $this->model->descriptor;
        }
        $package['object'] = $this->object->getPackage();
        $package['attributes'] = $this->attributes;
        return $package;
    }

    public function getSensorObjectModelClass(\canis\sensors\base\BaseInterface $object)
    {
        if ($object instanceof SensorInterface) {
            return SensorModel::className();
        }
        if ($object instanceof AssetInterface) {
            return AssetModel::className();
        }
        if ($object instanceof SiteInterface) {
            return SiteModel::className();
        }
        if ($object instanceof ProviderInterface) {
            return SourceModel::className();
        }
        return false;
    }


    public function getSensorObjectInstanceClass(\canis\sensors\base\BaseInterface $object)
    {
        if ($object instanceof SensorInterface) {
            return SensorInstance::className();
        }
        if ($object instanceof AssetInterface) {
            return AssetInstance::className();
        }
        if ($object instanceof SiteInterface) {
            return SiteInstance::className();
        }
        if ($object instanceof ProviderInterface) {
            return SourceInstance::className();
        }
        return false;
    }

    public function buildSensorObjectModel(\canis\sensors\base\BaseInterface $object)
    {
        $modelClass = $this->getSensorObjectModelClass($object);
        $instanceClass = $this->getSensorObjectInstanceClass($object);
        if (!$modelClass) {
            return false;
        }
        if (!isset($object->parentObject->model->primaryKey)) { 
            return false;
        }

        $objectInstance = new $instanceClass;
        $objectClone = clone $object;
        $objectClone->parentObject = null;
        $objectInstance->object = $objectClone;

        $baseAttributes = ['system_id' => $object->getId()];
        $additionalAttributes = [];
        if ($object instanceof SensorInterface) {
            $baseAttributes['object_id'] = $object->parentObject->model->primaryKey;
            $additionalAttributes['last_check'] = date("Y-m-d G:i:s");
            $additionalAttributes['next_check'] = date("Y-m-d G:i:s", strtotime($object->getCheckInterval($objectInstance)));
        }
        if ($object instanceof AssetInterface) {
            $baseAttributes['source_id'] = $object->parentObject->model->primaryKey;
            $baseAttributes['type'] = $object->getType();
        }
        if ($object instanceof SiteInterface) {
            $baseAttributes['source_id'] = $object->parentObject->model->primaryKey;
        }
        $additionalAttributes['name'] = $object->getName();
        
        $model = $modelClass::find()->where($baseAttributes)->one();

        if (!$model) {
            $model = new $modelClass;
            $model->active = 1;
        }
        $model->attributes = array_merge($baseAttributes, $additionalAttributes);

        if (empty($model->dataObject)) {
            $model->dataObject = $objectInstance;   
        }

        if (!$model->save()) {
            return false;
        }
        return $model;
    }

    public function cleanSensorObjectModel(\canis\sensors\base\BaseInterface $object)
    {
        $modelClass = $this->getSensorObjectModelClass($object);
        if (!$modelClass) {
            return false;
        }
    }

    public function loadModels()
    {
        if (empty($this->object)) {
            return true;
        }
        return $this->object->loadModels([$this, 'buildSensorObjectModel']);
    }

    public function cleanModels()
    {
        if (empty($this->object)) {
            return true;
        }
        return $this->object->cleanModels([$this, 'cleanSensorObjectModel']);
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

    public function initialize()
    {
        return false;
    }

    public function cleanObject()
    {

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
}
?>