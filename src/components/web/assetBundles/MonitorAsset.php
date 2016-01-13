<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\components\web\assetBundles;

use yii\web\AssetBundle;

class MonitorAsset extends AssetBundle
{
    public $sourcePath = '@psesd/sensorHub/assets/monitor';
    public $css = [
        'css/psesd.monitor.css',
    ];
    public $js = [
        'js/psesd.monitor.js',
    ];
    public $depends = [
        'psesd\sensorHub\components\web\assetBundles\AppAsset'
    ];
}
