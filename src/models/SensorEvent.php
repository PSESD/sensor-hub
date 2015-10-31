<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "sensor_event".
 *
 * @property string $id
 * @property string $old_state
 * @property string $new_state
 * @property string $sensor_id
 * @property resource $data
 * @property string $created
 *
 * @property Sensor $sensor
 */
class SensorEvent extends \canis\db\ActiveRecord
{
    protected $_dataObject;

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'serializeData']);
    }


     /**
     * [[@doctodo method_description:serializeAction]].
     */
    public function serializeData()
    {
        if (isset($this->_dataObject)) {
            try {
                $this->data = serialize($this->_dataObject);
            } catch (\Exception $e) {
                \d($this->_dataObject);
                exit;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sensor_event';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_state', 'new_state', 'data'], 'string'],
            [['sensor_id'], 'required'],
            [['created'], 'safe'],
            [['sensor_id'], 'string', 'max' => 36],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'old_state' => 'Old State',
            'new_state' => 'New State',
            'sensor_id' => 'Sensor ID',
            'data' => 'Data',
            'created' => 'Created',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSensor()
    {
        return $this->hasOne(Sensor::className(), ['id' => 'sensor_id']);
    }

    public function getDataObject()
    {
        if (!isset($this->_dataObject) && !empty($this->data)) {
            $this->_dataObject = unserialize($this->data);
            $this->_dataObject->model = $this;
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
        if ($do === null) {
            throw new \Exception("what");
            return;
        }
        $do->model = $this;
        $this->_dataObject = $do;
    }
}
