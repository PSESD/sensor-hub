<?php
use yii\helpers\Html;
use yii\helpers\Url;

canis\sensorHub\components\web\assetBundles\BrowserAsset::register($this);

$config = [];
$config['objects'] = $objects;
$config['url'] = Url::to(['', 'refresh' => 1, 'object' => $_GET['object'], 'type' => $_GET['type']]);
echo Html::tag('div', '', ['data-object-browser' => json_encode($config)]);