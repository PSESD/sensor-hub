<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LogModel extends Model
{
    protected $_log;
    protected $_key;

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function getKey()
    {
        return __CLASS__ . '-' . $this->_key;
    }

    public function save()
    {
        if ($this->_log === null) {
            return;
        }
        Yii::$app->fileCache->set($this->getKey(), $this->_log);
    }

    public function saveCache()
    {
        return $this->save();
    }

    public function getStatusLog($refresh = false)
    {
        if (!$refresh && $this->_log !== null) {
            return $this->_log;
        }
        $save = false;
        $log = Yii::$app->fileCache->get($this->getKey());
        if (!$log) {
            $log = new \canis\actions\Status;
            $save = true;
        }
        $log->log = $this;
        $log->persistentLog = true;
        $log->saveDatabaseOnMessage = true;
        $this->_log = $log;
        if ($save) {
            $this->save();
        }
        return $this->_log;
    }
}
