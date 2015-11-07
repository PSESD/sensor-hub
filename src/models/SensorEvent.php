<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "sensor_event".
 *
 * @property string $id
 * @property string $sensor_id
 * @property string $old_state
 * @property string $new_state
 * @property resource $data
 * @property string $created
 *
 * @property Sensor $sensor
 */
class SensorEvent extends \canis\db\ActiveRecord
{
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
    public function rules()
    {
        return [
            [['sensor_id'], 'required'],
            [['old_state', 'new_state', 'data'], 'string'],
            [['created'], 'safe'],
            [['sensor_id'], 'string', 'max' => 36],
            [['sensor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sensor::className(), 'targetAttribute' => ['sensor_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sensor_id' => 'Sensor ID',
            'old_state' => 'Old State',
            'new_state' => 'New State',
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
}
