<?php

namespace canis\sensorHub\models;

use Yii;
use canis\sensors\base\Sensor as BaseSensor;
use canis\registry\models\Registry;

/**
 * This is the model class for table "sensor".
 *
 * @property string $id
 * @property string $object_id
 * @property string $system_id
 * @property string $state
 * @property resource $data
 * @property integer $resolution_attempts
 * @property string $last_resolution_attempt
 * @property bool $active
 * @property string $next_check
 * @property string $last_check
 * @property string $created
 *
 * @property Instance $instance
 * @property Registry $id0
 * @property SensorEvent[] $sensorEvents
 */
class Sensor extends \canis\db\ActiveRecordRegistry
{    
    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }


    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Data' => [
                'class' => behaviors\DataBehavior::class
            ]
        ]);
    }

    public function getContextualDescriptor($parent = false)
    {
        $extra = '';
        $object = $this->object;
        if ($parent->id !== $object->id) {
            $extra = ' ('. $object->descriptor .')';
        }
        return $this->descriptor . $extra;
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
            [['object_id', 'system_id', 'name'], 'required'],
            [['state', 'data', 'name'], 'string'],
            [['resolution_attempts'], 'integer'],
            [['active'], 'integer'],
            [['last_resolution_attempt', 'last_check', 'next_check', 'created'], 'safe'],
            [['id', 'object_id'], 'string', 'max' => 36],
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
            'last_check' => 'Checked',
            'created' => 'Created',
        ];
    }

    public function dependentModels()
    {
        $models = [];
        $models['Sensor'] = Sensor::find()->where(['object_id' => $this->id])->all();
        $models['SensorData'] = SensorData::find()->where(['sensor_id' => $this->id])->all();
        $models['SensorEvent'] = SensorEvent::find()->where(['sensor_id' => $this->id])->all();
        return $models;
    }

    public function parentModels()
    {
        $models = [];
        $models['Sensor'] = Sensor::find()->where(['id' => $this->object_id])->all();
        return $models;
    }

    public function childModels()
    {
        $models = [];
        $models['Sensor'] = Sensor::find()->where(['object_id' => $this->id])->all();
        return $models;
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

    public function getObject()
    {
        return Registry::getObject($this->object_id);
    }

    public function getSimpleState()
    {
        switch ($this->state) {
            case BaseSensor::STATE_CHECK_FAIL:
            case BaseSensor::STATE_ERROR:
            case BaseSensor::STATE_LOW:
            case BaseSensor::STATE_HIGH:
                return 'danger';
            break;
            case BaseSensor::STATE_NORMAL:
                return 'success';
            break;
        }
        return 'warning';
    }

}
