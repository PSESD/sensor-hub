<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\components\web\assetBundles;

use yii\web\AssetBundle;

class BrowserAsset extends AssetBundle
{
    public $sourcePath = '@psesd/sensorHub/assets/browser';
    public $css = [
        'css/psesd.browser.css',
    ];
    public $js = [
        'js/psesd.browser.js',
    ];
    public $depends = [
        'psesd\sensorHub\components\web\assetBundles\AppAsset'
    ];
}
