<?php

namespace canis\sensorHub\models;

use Yii;

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
            [['created', 'modified'], 'safe'],
            [['id', 'object_id', 'resource_id'], 'string', 'max' => 36],
            [['resource_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resource::className(), 'targetAttribute' => ['resource_id' => 'id']],
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
        return $this->hasOne(Resource::className(), ['id' => 'resource_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObject()
    {
        return $this->hasOne(Registry::className(), ['id' => 'object_id']);
    }
    
    public function dependentModels()
    {
        $models = [];
        $models['Resource'] = Resource::find()->where(['object_id' => $this->id])->all();
        $models['ResourceReference'] = ResourceReference::find()->where(['object_id' => $this->id])->all();
        return $models;
    }

    public function connectedModels()
    {
        $models = $this->dependentModels();
        if (($resourceProvider = Registry::getObject($this->object_id))) {
            $models['ResourceProvider'] = [$resourceProvider];
        }
        return $models;
    }
}

