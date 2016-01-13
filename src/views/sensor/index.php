<?php
/**
 * @var yii\base\View
 */
psesd\sensorHub\components\web\assetBundles\MonitorAsset::register($this);

ArrayHelper::multisort($tasks, 'title');
$this->title = $config['title'];
$this->params['breadcrumbs'][] = ['label' => $this->title];


