<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\components\web\assetBundles;

use yii\web\AssetBundle;

class ViewerAsset extends AssetBundle
{
    public $sourcePath = '@psesd/sensorHub/assets/viewer';
    public $css = [
        'css/psesd.viewer.css',
    ];
    public $js = [
        'js/psesd.viewer.js',
    ];
    public $depends = [
        'psesd\sensorHub\components\web\assetBundles\AppAsset',
        'canis\web\assetBundles\HighchartsAsset',
        'canis\web\assetBundles\TimeAgoAsset'
    ];
}
