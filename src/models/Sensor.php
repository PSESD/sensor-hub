<?php

namespace canis\sensorHub\models;

use Yii;

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

}
