<?php

namespace canis\sensorHub\models;

use Yii;
use canis\registry\models\Registry;

/**
 * This is the model class for table "resource".
 *
 * @property string $id
 * @property string $object_id
 * @property string $system_id
 * @property string $type
 * @property string $name
 * @property bool $active
 * @property resource $data
 * @property string $created
 * @property string $modified
 *
 * @property Registry $id0
 */
class Resource extends \canis\db\ActiveRecordRegistry
{
    public $descriptorField = 'name';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'resource';
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
            [['object_id', 'system_id', 'type', 'name'], 'required'],
            [['data'], 'string'],
            [['active'], 'integer'],
            [['created', 'modified'], 'safe'],
            [['id', 'object_id'], 'string', 'max' => 36],
            [['system_id', 'type', 'name'], 'string', 'max' => 255],
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
            'type' => 'Type',
            'name' => 'Name',
            'data' => 'Data',
            'active' => 'Active',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    public function parentModels()
    {
        $models = [];
        $models['Sensor'] = Sensor::find()->where(['id' => $this->object_id])->all();
        $models['Service'] = Service::find()->where(['id' => $this->object_id])->all();
        $models['ServiceReference'] = ServiceReference::find()->where(['id' => $this->object_id])->all();
        $models['ResourceReference'] = ResourceReference::find()->where(['resource_id' => $this->id])->all();
        $models['Server'] = Server::find()->where(['id' => $this->object_id])->all();
        $models['Site'] = Site::find()->where(['id' => $this->object_id])->all();
        return $models;
    }

    public function childModels()
    {
        $models = $this->dependentModels();
        return $models;
    }

    public function getContextualDescriptor($parent = false)
    {
        return $this->descriptor;
    }
}
