<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "source".
 *
 * @property string $id
 * @property string $system_id
 * @property string $name
 * @property resource $data
 * @property integer $active
 * @property string $last_check
 * @property string $next_check
 * @property string $created
 * @property string $modified
 *
 * @property Asset[] $assets
 * @property Site[] $sites
 * @property Registry $id0
 */
class Source extends \canis\db\ActiveRecordRegistry
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
        return 'source';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'DataBehavior' => [
                'class' => behaviors\DataBehavior::className(),
                'buildModelsAfter' => true
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['system_id', 'name'], 'required'],
            [['data'], 'string'],
            [['active'], 'integer'],
            [['last_check', 'next_check', 'created', 'modified'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['system_id', 'name'], 'string', 'max' => 255],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Registry::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'system_id' => 'System ID',
            'name' => 'Name',
            'data' => 'Data',
            'active' => 'Active',
            'last_check' => 'Last Check',
            'next_check' => 'Next Check',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssets()
    {
        return $this->hasMany(Asset::className(), ['source_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSites()
    {
        return $this->hasMany(Site::className(), ['source_id' => 'id']);
    }
}
