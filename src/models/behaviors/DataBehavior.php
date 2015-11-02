<?php

namespace canis\sensorHub\models\behaviors;

use Yii;

class DataBehavior extends \yii\base\Behavior
{
	public $buildModelsAfter = false;
    protected $_dataObject;

	public function events()
    {
        return [
            \canis\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'serializeData',
            \canis\db\ActiveRecord::EVENT_AFTER_INSERT => 'cleanObject',
            \canis\db\ActiveRecord::EVENT_AFTER_UPDATE => 'cleanObject',
        ];
    }

     /**
     * [[@doctodo method_description:serializeAction]].
     */
    public function serializeData()
    {
        if (isset($this->_dataObject)) {
            try {
                $this->owner->data = serialize($this->_dataObject);
            } catch (\Exception $e) {
                \d($this->_dataObject);
                exit;
            }
        }
    }


    public function getDataObject($clean = false)
    {
    	$loaded = false;
        if (!isset($this->_dataObject) && !empty($this->owner->data)) {
            $this->_dataObject = unserialize($this->owner->data);
            $this->_dataObject->model = $this->owner;
            if ($this->buildModelsAfter && $this->_dataObject && $this->owner->primaryKey !== null) {
            	$loaded = true;
            	$this->_dataObject->loadModels();
            }
        }
        if ($this->buildModelsAfter && $this->_dataObject && $this->owner->primaryKey !== null && $clean) {
            if (!$loaded) {
            	$this->_dataObject->loadModels();
        	}
        	$this->_dataObject->cleanModels();
        }
        return $this->_dataObject;
    }

    /**
     * Set action object.
     *
     * @param [[@doctodo param_type:ao]] $ao [[@doctodo param_description:ao]]
     */
    public function setDataObject($do)
    {
        $do->model = $this->owner;
        $this->_dataObject = $do;
    }

    public function cleanObject($event = null)
    {
    	if (!$this->buildModelsAfter) { return true; }
        if ($event === null) {
            $event = new \yii\base\Event;
        }
        $dataObject = $this->getDataObject(true);
        //\d($dataObject);
        //exit;
        return true;
    }


}