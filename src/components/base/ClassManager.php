<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\components\base;

/**
 * ClassManager Class name helper for the application.
 *
 * @author Jacob Morrison <jmorrison@psesd.org>
 */
class ClassManager extends \canis\base\ClassManager
{
    /**
     * @inheritdoc
     */
    public function baseClasses()
    {
        return [
            'Registry' => 'canis\registry\models\Registry',
            'Relation' => 'canis\registry\models\Relation',
            'RelationDependency' => 'canis\registry\models\RelationDependency',

            'Aca' => 'canis\acl\models\Aca',
            'Acl' => 'canis\acl\models\Acl',
            'AclRole' => 'canis\acl\models\AclRole',
            'Role' => 'canis\acl\models\Role',

            'User' => 'psesd\sensorHub\models\User',

            'UserDevice' => 'canis\auth\models\UserDevice',
            'Group' => 'canis\auth\models\Group',
            'IdentityProvider' => 'canis\auth\models\IdentityProvider',
            'Identity' => 'canis\auth\models\Identity',

            'Storage' => 'canis\storage\models\Storage',
            'StorageEngine' => 'canis\storage\models\StorageEngine',

            'Audit' => 'canis\auditable\models\Audit',
            'Meta' => 'canis\metable\models\Meta',

            'SearchTermResult' => 'psesd\sensorHub\components\db\behaviors\SearchTermResult'
        ];
    }
}
