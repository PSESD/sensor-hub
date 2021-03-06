<?php

namespace psesd\sensorHub\models;

use Yii;
use canis\registry\models\Registry;

/**
 * This is the model class for table "provider".
 *
 * @property string $id
 * @property string $system_id
 * @property resource $data
 * @property integer $active
 * @property string $last_refresh
 * @property string $created
 * @property string $modified
 *
 * @property Registry $id0
 */
class Provider extends \canis\db\ActiveRecordRegistry
{
    use SensorObjectTrait;
    public function getContextualDescriptor($parent = false)
    {
        return $this->descriptor;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'provider';
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
            [['system_id'], 'required'],
            [['data'], 'string'],
            [['active'], 'integer'],
            [['last_refresh', 'created', 'modified'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['system_id'], 'string', 'max' => 255],
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
            'data' => 'Data',
            'active' => 'Active',
            'last_refresh' => 'Last Refresh',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    public function dependentModels()
    {
        $models = [];
        $models['Sensor'] = Sensor::find()->where(['object_id' => $this->id])->all();
        $models['Server'] = Server::find()->where(['provider_id' => $this->id])->all();
        $models['Resource'] = Resource::find()->where(['object_id' => $this->id])->all();
        $models['Site'] = Site::find()->where(['provider_id' => $this->id])->all();
        return $models;
    }

    public function connectedModels()
    {
        $models = $this->dependentModels();
        return $models;
    }

    public function initializeData($isFirstInitialize)
    {
        if (!isset($this->dataObject)) {
            $this->dataObject->statusLog->addError('No initialization data');
            return false;
        }
        if (!$this->dataObject->initialize(null)) {
            if ($isFirstInitialize) {
                $this->delete();
            }
            return false;
        }
        return true;
    }

    public function getDescriptor()
    {
        if (!isset($this->dataObject->object->name)) {
            return 'Unknown Provider';
        }
        return $this->dataObject->object->name;
    }

    public function childModels($active = true)
    {
        if ($active) {
            $active = 1;
        } else {
            $active = [0, 1];
        }
        $models = [];
        $models['Site'] = Site::find()->where(['provider_id' => $this->id, 'active' => $active])->all();
        $models['Server'] = Server::find()->where(['provider_id' => $this->id, 'active' => $active])->all();
        $models['Sensor'] = Sensor::find()->where(['object_id' => $this->id, 'active' => $active])->all();
        return $models;
    }

}
