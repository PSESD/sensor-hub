<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "resource".
 *
 * @property string $id
 * @property string $object_id
 * @property string $system_id
 * @property string $type
 * @property string $name
 * @property resource $data
 * @property integer $active
 * @property string $created
 * @property string $modified
 *
 * @property Registry $id0
 */
class Resource extends \canis\db\ActiveRecordRegistry
{
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

    public function connectedModels()
    {
        $models = $this->dependentModels();
        return $models;
    }
}
