<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace psesd\sensorHub\setup\tasks;

/**
 *
 * @author Jacob Morrison <jmorrison@psesd.org>
 */
class Collectors extends \canis\setup\tasks\BaseTask
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Collector Item Setup';
    }

    /**
     * @inheritdoc
     */
    public function test()
    {
        return $this->setup->app()->collectors->areReady();
    }
    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->setup->app()->collectors->initialize();
    }
    /**
     * @inheritdoc
     */
    public function getFields()
    {
        return false;
    }
}
