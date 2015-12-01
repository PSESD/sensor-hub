<?php

namespace canis\sensorHub\models;

use Yii;
use canis\registry\models\Registry;

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
    use SensorObjectTrait;
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
            [['active'], 'integer'],
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

    public function getDescriptor()
    {
        return $this->service->descriptor;
    }

    public function getContextualDescriptor($parent = false)
    {
        $extra = '';
        $provider = $this->service->object;
        if ($parent === $provider) {
            $object = $this->object;
            $extra = ' ('. $object->descriptor .')';
        } else {
            $extra = ' ('. $provider->descriptor .')';
        }
        return $this->descriptor . $extra;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return Service::get($this->service_id);
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
        $models['Resource'] = Resource::find()->where(['object_id' => $this->id])->all();
        $models['ResourceReference'] = ResourceReference::find()->where(['object_id' => $this->id])->all();
        $models['Sensor'] = Sensor::find()->where(['object_id' => $this->id])->all();
        return $models;
    }

    public function parentModels()
    {
        $models = [];
        $models['Server'] = Server::find()->where(['id' => $this->object_id])->all();
        $models['Site'] = Site::find()->where(['id' => $this->object_id])->all();
        $models['Service'] = Service::find()->where(['id' => $this->service_id])->all();
        return $models;
    }

    public function childModels($careAboutActive = true)
    {
        if ($careAboutActive) {
            $active = 1;
        } else {
            $active = [0, 1];
        }
        $models = [];
        $models['Resource'] = Resource::find()->where(['object_id' => $this->id, 'active' => $active])->all();
        $models['Sensor'] = Sensor::find()->where(['object_id' => $this->id, 'active' => $active])->all();
        $models['Service'] = Service::find()->where(['id' => $this->service_id, 'active' => $active])->all();
        $models['ResourceReference'] = ResourceReference::find()->where(['object_id' => $this->id, 'active' => $active])->all();
        if (($serviceProvider = Registry::getObject($this->object_id))) {
            if (!$careAboutActive || !empty($serviceProvider->active)) {
                $models['ServiceProvider'] = [$serviceProvider];
            }
        }
        return $models;
    }
}

