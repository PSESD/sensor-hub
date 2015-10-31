<?php
/**
 * @var yii\base\View
 */
use canis\helpers\ArrayHelper;
use canis\helpers\Html;

ArrayHelper::multisort($tasks, 'title');
$this->title = 'Sensors';
$this->params['breadcrumbs'][] = ['label' => $this->title];

echo Html::beginTag('div', ['class' => 'panel panel-default']);
echo Html::beginTag('div', ['class' => 'panel-heading']);
echo Html::beginTag('h3', ['class' => 'panel-title']);
echo 'Sensors';
echo Html::endTag('h3');
echo Html::endTag('div');
echo Html::beginTag('div', ['class' => 'panel-body']);


echo Html::endTag('div');
echo Html::endTag('div');