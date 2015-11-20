<?php

namespace canis\sensorHub\models;

use Yii;
use canis\registry\models\Registry;

/**
 * This is the model class for table "resource_reference".
 *
 * @property string $id
 * @property string $object_id
 * @property string $resource_id
 * @property string $type
 * @property resource $data
 * @property string $created
 * @property string $modified
 *
 * @property Resource $resource
 * @property Registry $object
 * @property Registry $id0
 */
class ResourceReference extends \canis\db\ActiveRecordRegistry
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'resource_reference';
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
            [['object_id', 'resource_id'], 'required'],
            [['type', 'data'], 'string'],
            [['active'], 'integer'],
            [['created', 'modified'], 'safe'],
            [['id', 'object_id', 'resource_id'], 'string', 'max' => 36],
            [['resource_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resource::className(), 'targetAttribute' => ['resource_id' => 'id']],
            [['object_id'], 'exist', 'skipOnError' => true, 'targetClass' => Registry::className(), 'targetAttribute' => ['object_id' => 'id']],
        ];
    }

    public function getDescriptor()
    {
        return $this->resource->descriptor;
    }

    public function getContextualDescriptor($parent = false)
    {
        return $this->descriptor;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'resource_id' => 'Resource ID',
            'type' => 'Type',
            'data' => 'Data',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return Resource::get($this->resource_id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObject()
    {
        return Registry::getObject($this->object_id);
    }
    
    public function dependentModels()
    {
        $models = [];
        return $models;
    }

    public function parentModels()
    {
        $models = [];
        $models['Sensor'] = Sensor::find()->where(['id' => $this->object_id])->all();
        $models['Service'] = Service::find()->where(['id' => $this->object_id])->all();
        $models['ServiceReference'] = ServiceReference::find()->where(['id' => $this->object_id])->all();
        $models['Server'] = Server::find()->where(['id' => $this->object_id])->all();
        $models['Site'] = Site::find()->where(['id' => $this->object_id])->all();
        return $models;
    }

    public function childModels()
    {
        $models = [];
        $models['Resource'] = Resource::find()->where(['id' => $this->resource_id, 'active' => 1])->all();
        if (($resourceProvider = Registry::getObject($this->object_id))) {
            if (!empty($resourceProvider->active)) {
                $models['ResourceProvider'] = [$resourceProvider];
            }
        }
        return $models;
    }


}

