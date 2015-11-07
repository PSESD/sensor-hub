<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\web\assetBundles;

use yii\web\AssetBundle;

class MonitorAsset extends AssetBundle
{
    public $sourcePath = '@canis/sensorHub/assets/monitor';
    public $css = [
        'css/canis.monitor.css',
    ];
    public $js = [
        'js/canis.monitor.js',
    ];
    public $depends = [
        'canis\sensorHub\components\web\assetBundles\AppAsset'
    ];
}
