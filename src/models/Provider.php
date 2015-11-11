<?php

namespace canis\sensorHub\models;

use Yii;

/**
 * This is the model class for table "provider".
 *
 * @property string $id
 * @property string $system_id
 * @property resource $data
 * @property integer $active
 * @property string $last_check
 * @property string $created
 * @property string $modified
 *
 * @property Registry $id0
 */
class Provider extends \canis\db\ActiveRecordRegistry
{
    protected $_initializeData;


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
            [['last_check', 'created', 'modified'], 'safe'],
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
            'last_check' => 'Last Check',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    public function setInitializeData($data)
    {
        $this->_initializeData = $data;
        $this->_initializeData->model = $this;
        return $this;
    }

    public function getIntitializeData()
    {
        // $idata = null;
        // if (isset($this->_initializeData)) {
        //     $this->_initializeData->model = $this;
        //     $idata = clone $this->_initializeData;
        // }
        return $this->_initializeData;
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
        if (!isset($this->_initializeData)) {
            $this->getIntitializeData()->statusLog->addError('No initialization data');
            return false;
        }
        //\d($this->getIntitializeData()->object); $this->delete();exit;
        $this->dataObject = clone $this->getIntitializeData();
        $this->dataObject->object = null;
        //\d($this->getIntitializeData()->object->getSites());exit;
        if (!$this->getIntitializeData()->initialize($this->getIntitializeData()->statusLog)) {
            if (!empty($this->_initializeData)) {
                $this->getIntitializeData()->statusLog->addError('Unable to initialize provider!');
            }
            if ($isFirstInitialize) {
                $this->delete();
            }
            return false;
        } else {
            $this->dataObject = $this->getIntitializeData();
            return true;
        }
    }
}
