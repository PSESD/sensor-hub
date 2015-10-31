<?php
/**
 * @var yii\base\View
 */
use canis\helpers\ArrayHelper;
use canis\helpers\Html;
use canis\daemon\widgets\ExtendedStatus as DaemonStatus;

\canis\web\assetBundles\CanisLogViewerAsset::register($this);

ArrayHelper::multisort($tasks, 'title');
$this->title = 'Administration';
// $this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

echo Html::beginTag('div', ['class' => 'panel panel-default']);
echo Html::beginTag('div', ['class' => 'panel-heading']);
echo Html::beginTag('h3', ['class' => 'panel-title']);
echo Html::beginTag('div', ['class' => 'btn-group btn-group-sm  pull-right']);
echo Html::endTag('div');
echo 'Administration Tasks';
echo Html::endTag('h3');
echo Html::endTag('div');
echo Html::beginTag('div', ['class' => 'panel-body']);

echo Html::beginTag('div', ['class' => 'list-group']);
foreach ($tasks as $taskId => $task) {
    echo Html::a(
        Html::tag('h4', $task['title'], ['class' => 'list-group-item-heading']) .
        Html::tag('div', $task['description'], ['class' => 'list-group-item-text']),
        ['/admin/index', 'task' => $taskId], ['class' => 'list-group-item', 'data-handler' => 'background']);
}

echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endTag('div');

echo DaemonStatus::widget();
