<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\base;

/**
 * ClassManager Class name helper for the application.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ClassManager extends \canis\base\ClassManager
{
    /**
     * @inheritdoc
     */
    public function baseClasses()
    {
        return [
            'Registry' => 'canis\sensorHub\models\Registry',
            'Relation' => 'canis\sensorHub\models\Relation',

            'Aca' => 'canis\sensorHub\models\Aca',
            'Acl' => 'canis\sensorHub\models\Acl',
            'AclRole' => 'canis\sensorHub\models\AclRole',
            'Role' => 'canis\sensorHub\models\Role',

            'User' => 'canis\sensorHub\models\User',
            'UserDevice' => 'canis\sensorHub\models\UserDevice',
            'Group' => 'canis\sensorHub\models\Group',
            'IdentityProvider' => 'canis\sensorHub\models\IdentityProvider',
            'Identity' => 'canis\sensorHub\models\Identity',

            'Storage' => 'canis\sensorHub\models\Storage',
            'StorageEngine' => 'canis\sensorHub\models\StorageEngine',

            'Audit' => 'canis\sensorHub\models\Audit',
            'Meta' => 'canis\sensorHub\models\Meta',

            'SearchTermResult' => 'canis\sensorHub\components\db\behaviors\SearchTermResult',
        ];
    }
}
