<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "service_reference".
 *
 * @property string $id
 * @property string $object_id
 * @property string $service_id
 * @property string $type
 * @property resource $data
 * @property string $created
 * @property string $modified
 *
 * @property Service $service
 * @property Registry $object
 * @property Registry $id0
 */
class ServiceReference extends \canis\db\ActiveRecordRegistry
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_reference';
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
            [['object_id', 'service_id'], 'required'],
            [['type', 'data'], 'string'],
            [['created', 'modified'], 'safe'],
            [['id', 'object_id', 'service_id'], 'string', 'max' => 36],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['object_id'], 'exist', 'skipOnError' => true, 'targetClass' => Registry::className(), 'targetAttribute' => ['object_id' => 'id']],
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
            'service_id' => 'Service ID',
            'type' => 'Type',
            'data' => 'Data',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObject()
    {
        return $this->hasOne(Registry::className(), ['id' => 'object_id']);
    }
}
