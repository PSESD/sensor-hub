<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\web\assetBundles;

use yii\web\AssetBundle;

class ViewerAsset extends AssetBundle
{
    public $sourcePath = '@canis/sensorHub/assets/viewer';
    public $css = [
        'css/canis.viewer.css',
    ];
    public $js = [
        'js/canis.viewer.js',
    ];
    public $depends = [
        'canis\sensorHub\components\web\assetBundles\AppAsset',
        'canis\web\assetBundles\HighchartsAsset',
        'canis\web\assetBundles\TimeAgoAsset'
    ];
}
