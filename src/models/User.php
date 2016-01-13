<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\models;

use Yii;
use canis\registry\models\Registry;
use canis\auth\models\Group;
use canis\broadcaster\eventTypes\DynamicUserNotification;
use canis\broadcaster\eventTypes\EventType;

/**
 * User is the model class for table "user".
 */
class User 
    extends \canis\auth\models\User
    implements \canis\broadcaster\BroadcastableInterface
{
    const SYSTEM_EMAIL = 'system@system.local';

    /**
     * @inheritdoc
     */
    public $descriptorField = ['first_name', 'last_name'];

    public function init()
    {
        parent::init();
        $this->on(static::EVENT_PASSWORD_CHANGED, [$this, 'triggerPasswordChange']);
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(),
            [
                'Broadcastable' => 'canis\broadcaster\Broadcastable'
            ]
        );
    }

    public function triggerPasswordChange($event)
    {
        $this->triggerBroadcastEvent('password_changed', $this->getBaseEventPayload(), $this->id);
    }

    public function triggerTwoFactorEnabled($event)
    {
        $this->triggerBroadcastEvent('two_factor_enabled', $this->getBaseEventPayload(), $this->id);
    }

    public function triggerTwoFactorDisabled($event)
    {
        $this->triggerBroadcastEvent('two_factor_disabled', $this->getBaseEventPayload(), $this->id);
    }


    public function getBaseEventPayload()
    {
        return [
            '_user' => $this->id
        ];
    }

    /**
     * __method_systemUser_description__
     * @return __return_systemUser_type__ __return_systemUser_description__
     * @throws Exception                  __exception_Exception_description__
     */
    public static function systemUser()
    {
        $user = self::findOne([self::tableName().'.'.'email' => self::SYSTEM_EMAIL], false);
        if (empty($user)) {
            $superGroup = Group::find()->disableAccessCheck()->where(['system' => 'super_administrators'])->one();
            if (!$superGroup) {
                return false;
            }
            $userClass = self::className();
            $user = new $userClass();
            $user->scenario = 'creation';
            $user->first_name = 'System';
            $user->last_name = 'User';
            $user->email = self::SYSTEM_EMAIL;
            $user->status = static::STATUS_INACTIVE;
            $user->password =  Yii::$app->security->generateRandomKey();
            $user->relationModels = [['parent_object_id' => $superGroup->primaryKey]];
            if (!$user->save()) {
                \d($user->email);
                \d($user->errors);
                throw new Exception("Unable to save system user!");
            }
        }

        return $user;
    }


    static public function collectEventTypes()
    {
        $events = [];
        $events['password_changed'] = [
            'class' => DynamicUserNotification::className(),
            'name' => 'Password Changed',
            'subject' => 'Notification from {{ _application }}: Password changed for user \'{{ _user.email }}\'',
            'descriptorString' => 'The password for \'{{ _user.email }}\' was changed in {{ _application }}. If you did not request this, contact an administrator immediately.',
            'priority' => EventType::PRIORITY_CRITICAL
        ];
        $events['two_factor_enabled'] = [
            'class' => DynamicUserNotification::className(),
            'name' => 'Two-factor Enabled',
            'subject' => 'Notification from {{ _application }}: Two-factor authentication enabled for user \'{{ _user.email }}\'',
            'descriptorString' => 'Two-factor authentication was enabled for \'{{ _user.email }}\' in {{ _application }}. If you did not request this, contact an administrator immediately.',
            'priority' => EventType::PRIORITY_CRITICAL
        ];
        $events['two_factor_disabled'] = [
            'class' => DynamicUserNotification::className(),
            'name' => 'Two-factor Disabled',
            'subject' => 'Notification from {{ _application }}: Two-factor authentication disabled for user \'{{ _user.email }}\'',
            'descriptorString' => 'Two-factor authentication was disabled for \'{{ _user.email }}\' in {{ _application }}. If you did not request this, contact an administrator immediately.',
            'priority' => EventType::PRIORITY_CRITICAL
        ];
        return $events;
    }
}
