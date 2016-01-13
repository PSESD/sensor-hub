<?php
/**
 * @var yii\base\View
 */

use yii\helpers\Html;
psesd\sensorHub\components\web\assetBundles\MonitorAsset::register($this);
psesd\sensorHub\components\web\assetBundles\ViewerAsset::register($this);
psesd\sensorHub\components\web\assetBundles\BrowserAsset::register($this);

$this->title = $config['title'];
$this->params['breadcrumbs'][] = ['label' => $this->title];


echo Html::tag('div', '', ['data-monitor' => $config]);