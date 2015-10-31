<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\web\assetBundles;

use yii\web\AssetBundle;

class SensorMonitorAsset extends AssetBundle
{
    public $sourcePath = '@canis/sensorHub/assets/sensor_monitor';
    public $css = [
        'css/canis.sensorMonitor.css',
    ];
    public $js = [
        'js/canis.sensorMonitor.js',
    ];
    public $depends = [
        'canis\sensorHub\components\web\assetBundles\AppAsset'
    ];
}
