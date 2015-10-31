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

abstract class Instance 
	extends \canis\base\Component
    implements \canis\broadcaster\BroadcastableInterface
{
	public $model;
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

    abstract public function getPackage();

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
        	$headers[] = 'X-Api-Key: \"' . addslashes($apiKey) .'\"';
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