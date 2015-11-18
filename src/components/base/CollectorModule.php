<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace canis\sensorHub\components\base;

use canis\collector\CollectedObjectTrait;
use canis\base\exceptions\Exception;
use Yii;

/**
 * CollectorModule is the base class for all collected modules in Cascade.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class CollectorModule extends \canis\base\Module implements \canis\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;


    /**
     * Get collector name.
     */
    abstract public function getCollectorName();

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent, $config = null)
    {
        if (isset(Yii::$app->params['modules'][$id])) {
            if (is_array($config)) {
                $config = array_merge_recursive($config, Yii::$app->params['modules'][$id]);
            } else {
                $config = Yii::$app->params['modules'][$id];
            }
        }
        if ($this->collectorName) {
            if (!isset(Yii::$app->collectors[$this->collectorName])) {
                throw new Exception('Cannot find the collector ' . $this->collectorName . '!');
            }
            if (!(Yii::$app->collectors[$this->collectorName]->register(null, $this))) {
                throw new Exception('Could not register ' . $this->shortName . ' in ' . $this->collectorName . '!');
            }
        }
        $this->loadSubmodules();

        Yii::$app->collectors->onAfterInit([$this, 'onAfterInit']);

        parent::__construct($id, $parent, $config);
    }

    /**
     * Load the submodule sof this collected module.
     *
     * @return bool load was successful
     */
    public function loadSubmodules()
    {
        $this->modules = $this->submodules;

        foreach ($this->submodules as $module => $settings) {
            $mod = $this->getModule($module);
            $mod->init();
        }

        return true;
    }

    /**
     * Get submodules.
     *
     * @return array submodules of this modules
     */
    public function getSubmodules()
    {
        return [];
    }

    /**
     * Action after module init.
     *
     * @param Event $event the event parameter
     *
     * @return bool ran successfully
     */
    public function onAfterInit($event)
    {
        return true;
    }
}
