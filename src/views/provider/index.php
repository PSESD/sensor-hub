<?php
/**
 * @var yii\base\View
 */
use canis\helpers\ArrayHelper;
use canis\helpers\Html;

ArrayHelper::multisort($tasks, 'title');
$this->title = 'Sensor Providers';
$this->params['breadcrumbs'][] = ['label' => $this->title];

echo Html::beginTag('div', ['class' => 'panel panel-default']);
echo Html::beginTag('div', ['class' => 'panel-heading']);
echo Html::beginTag('h3', ['class' => 'panel-title']);
echo Html::beginTag('div', ['class' => 'btn-group btn-group-sm  pull-right']);
echo Html::a('<span class="fa fa-plus"></span> Add Provider', ['/provider/create'], ['class' => 'btn btn-primary', 'data-handler' => 'background']);
echo Html::endTag('div');
echo 'Sensor Providers';
echo Html::endTag('h3');
echo Html::endTag('div');
echo Html::beginTag('div', ['class' => 'panel-body']);


echo Html::endTag('div');
echo Html::endTag('div');