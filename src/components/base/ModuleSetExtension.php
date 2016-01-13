<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace psesd\sensorHub\components\base;

use Yii;

/**
 * ModuleSetExtension base class for a module set.
 *
 * @author Jacob Morrison <jmorrison@psesd.org>
 */
abstract class ModuleSetExtension implements \yii\base\BootstrapInterface
{
    /**
     * Bootstrap the module set on load.
     *
     * @param Application $app the application parameter
     */
    public function bootstrap($app)
    {
        Yii::beginProfile(get_called_class());
        Yii::$app->modules = static::getModules();
        Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        Yii::endProfile(get_called_class());
        Yii::trace("Registered " . count(static::getModules()) . " modules in " . get_called_class());
    }

    /**
     * Actions to run before request starts.
     *
     * @param Event $event the event parameter
     */
    public function beforeRequest($event)
    {
    }

    /**
     * Get modules.
     *
     * @return array of the modules in the set
     */
    public static function getModules()
    {
        return [];
    }
}
