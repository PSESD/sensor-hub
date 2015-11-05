<?php

namespace canis\sensorHub\models\behaviors;

use Yii;

class DataBehavior extends \yii\base\Behavior
{
    protected $_dataObject;

	public function events()
    {
        return [
            \canis\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'serializeData'
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


}