<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "sensor".
 *
 * @property string $id
 * @property string $instance_id
 * @property string $system_id
 * @property string $state
 * @property resource $data
 * @property integer $resolution_attempts
 * @property string $last_resolution_attempt
 * @property bool $active
 * @property string $checked
 * @property string $created
 *
 * @property Instance $instance
 * @property Registry $id0
 * @property SensorEvent[] $sensorEvents
 */
class Sensor extends \canis\db\ActiveRecordRegistry
{
    protected $_dataObject;
    
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'serializeData']);
    }
    
    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
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
        return 'sensor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['instance_id', 'system_id', 'name'], 'required'],
            [['state', 'data', 'name'], 'string'],
            [['resolution_attempts'], 'integer'],
            [['active'], 'integer'],
            [['last_resolution_attempt', 'checked', 'created'], 'safe'],
            [['id', 'instance_id'], 'string', 'max' => 36],
            [['system_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'system_id' => 'System ID',
            'state' => 'State',
            'data' => 'Data',
            'resolution_attempts' => 'Resolution Attempts',
            'last_resolution_attempt' => 'Last Resolution Attempt',
            'checked' => 'Checked',
            'created' => 'Created',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSensorEvents()
    {
        return $this->hasMany(SensorEvent::className(), ['sensor_id' => 'id']);
    }

    public function getRecentSensorEventQuery($limit = 10)
    {
        return SensorEvent::find()->where(['sensor_id' => $this->id])->orderBy(['created' => SORT_DESC])->limit($limit);
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
        $do->model = $this;
        $this->_dataObject = $do;
    }
}
