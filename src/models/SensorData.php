<?php

namespace canis\sensorHub\models;

use Yii;
use canis\registry\models\Registry;

/**
 * This is the model class for table "sensor_data".
 *
 * @property string $id
 * @property string $sensor_id
 * @property string $value
 * @property string $created
 *
 * @property Sensor $sensor
 */
class SensorData extends \canis\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sensor_data';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sensor_id', 'value'], 'required'],
            [['value'], 'number'],
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
            'value' => 'Value',
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
