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
    protected $_isFirstInitialize = true;
    public $initializationFailed = false;

    public function init()
    {
        parent::init();
        $this->on(\canis\db\ActiveRecord::EVENT_BEFORE_VALIDATE, [$this, 'prepInitializeData']);
        $this->on(\canis\db\ActiveRecord::EVENT_AFTER_INSERT, [$this, 'initializeData']);
        $this->on(\canis\db\ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'initializeData']);
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
        return $this->_initializeData;
    }

    public function prepInitializeData()
    {
        $this->_isFirstInitialize = $this->isNewRecord;
    }

    public function initializeData($event)
    {
        if (!isset($this->_initializeData)) {
            return;
        }
        if (!$this->getIntitializeData()->initialize()) {
            $this->initializationFailed = true;
            if (!empty($this->_initializeData)) {
                $this->getIntitializeData()->setupErrors['url'] = 'Unable to initialize provider!';
            }
            if ($this->_isFirstInitialize) {
                $this->delete();
            }
        } else {
            $this->dataObject = $this->getIntitializeData();
            $this->dataObject->cleanObject();
        }
    }
}
