<?php
use yii\helpers\Html;
use yii\helpers\Url;

psesd\sensorHub\components\web\assetBundles\BrowserAsset::register($this);
psesd\sensorHub\components\web\assetBundles\ViewerAsset::register($this);

$config = [];
$config['objects'] = $objects;
$config['url'] = Url::to(['', 'refresh' => 1, 'object' => $_GET['object'], 'type' => $_GET['type']]);
echo Html::tag('div', '', ['data-object-browser' => json_encode($config)]);