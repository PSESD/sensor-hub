<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\web\assetBundles;

use yii\web\AssetBundle;

class BrowserAsset extends AssetBundle
{
    public $sourcePath = '@canis/sensorHub/assets/browser';
    public $css = [
        'css/canis.browser.css',
    ];
    public $js = [
        'js/canis.browser.js',
    ];
    public $depends = [
        'canis\sensorHub\components\web\assetBundles\AppAsset'
    ];
}
