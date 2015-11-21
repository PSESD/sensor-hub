<?php

namespace canis\sensorHub\models;

use Yii;
use canis\registry\models\Registry;
use canis\auth\models\User;

/**
 * This is the model class for table "contact".
 *
 * @property string $id
 * @property string $object_id
 * @property integer $is_billing
 * @property integer $is_technical
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $note
 * @property string $created
 * @property string $created_user_id
 * @property string $modified
 * @property string $modified_user_id
 *
 * @property User $modifiedUser
 * @property User $createdUser
 * @property Registry $object
 * @property Registry $id0
 */
class Contact extends \canis\db\ActiveRecordRegistry
{
    public $descriptorField = ['first_name', 'last_name'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'first_name', 'last_name'], 'required'],
            [['is_billing', 'is_technical'], 'integer'],
            [['created', 'modified'], 'safe'],
            [['id', 'object_id', 'created_user_id', 'modified_user_id'], 'string', 'max' => 36],
            [['first_name', 'last_name', 'email', 'phone', 'note'], 'string', 'max' => 255],
            [['modified_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['modified_user_id' => 'id']],
            [['created_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_user_id' => 'id']],
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
            'is_billing' => 'Billing Contact?',
            'is_technical' => 'Technical Contact?',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'note' => 'Note',
            'created' => 'Created',
            'created_user_id' => 'Created User ID',
            'modified' => 'Modified',
            'modified_user_id' => 'Modified User ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifiedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'modified_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObject()
    {
        return $this->hasOne(Registry::className(), ['id' => 'object_id']);
    }
}
