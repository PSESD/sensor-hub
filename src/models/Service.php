<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "service".
 *
 * @property string $id
 * @property string $object_id
 * @property string $system_id
 * @property string $name
 * @property resource $data
 * @property string $created
 * @property string $modified
 *
 * @property Registry $object
 * @property Registry $id0
 * @property ServiceReference[] $serviceReferences
 */
class Service extends \canis\db\ActiveRecordRegistry
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service';
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
            [['object_id', 'system_id', 'name'], 'required'],
            [['data'], 'string'],
            [['created', 'modified'], 'safe'],
            [['id', 'object_id'], 'string', 'max' => 36],
            [['system_id', 'name'], 'string', 'max' => 255],
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
            'system_id' => 'System ID',
            'name' => 'Name',
            'data' => 'Data',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObject()
    {
        return $this->hasOne(Registry::className(), ['id' => 'object_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceReferences()
    {
        return $this->hasMany(ServiceReference::className(), ['service_id' => 'id']);
    }
}
