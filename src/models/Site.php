<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "site".
 *
 * @property string $id
 * @property string $source_id
 * @property string $system_id
 * @property string $name
 * @property resource $data
 * @property integer $active
 * @property string $created
 * @property string $modified
 *
 * @property Source $source
 * @property Registry $id0
 */
class Site extends \canis\db\ActiveRecordRegistry
{
    
    /**
     * @inheritdoc
     */
    public static function isAccessControlled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'DataBehavior' => [
                'class' => behaviors\DataBehavior::className()
            ]
        ]);
    }
    
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source_id', 'system_id', 'name'], 'required'],
            [['data'], 'string'],
            [['active'], 'integer'],
            [['created', 'modified'], 'safe'],
            [['id', 'source_id'], 'string', 'max' => 36],
            [['system_id', 'name'], 'string', 'max' => 255],
            // [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => Source::className(), 'targetAttribute' => ['source_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source_id' => 'Source ID',
            'system_id' => 'System ID',
            'name' => 'Name',
            'data' => 'Data',
            'active' => 'Active',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Source::className(), ['id' => 'source_id']);
    }
}
