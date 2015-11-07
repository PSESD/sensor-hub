<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "site".
 *
 * @property string $id
 * @property string $provider_id
 * @property string $system_id
 * @property string $name
 * @property resource $data
 * @property integer $active
 * @property string $created
 * @property string $modified
 *
 * @property Provider $source
 * @property Registry $id0
 */
class Site extends \canis\db\ActiveRecordRegistry
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site';
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
            [['provider_id', 'system_id', 'name'], 'required'],
            [['data'], 'string'],
            [['active'], 'integer'],
            [['created', 'modified'], 'safe'],
            [['id', 'provider_id'], 'string', 'max' => 36],
            [['system_id', 'name'], 'string', 'max' => 255],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::className(), 'targetAttribute' => ['provider_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider_id' => 'Provider ID',
            'system_id' => 'System ID',
            'name' => 'Name',
            'data' => 'Data',
            'active' => 'Active',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    public function dependentModels()
    {
        $models = [];
        $models['Sensor'] = Sensor::find()->where(['object_id' => $this->id])->all();
        $models['Service'] = Service::find()->where(['object_id' => $this->id])->all();
        $models['ServiceReference'] = ServiceReference::find()->where(['object_id' => $this->id])->all();
        $models['Resource'] = Resource::find()->where(['object_id' => $this->id])->all();
        $models['ResourceReference'] = ResourceReference::find()->where(['object_id' => $this->id])->all();
        return $models;
    }

    public function connectedModels()
    {
        $models = $this->dependentModels();
        return $models;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id']);
    }

}
