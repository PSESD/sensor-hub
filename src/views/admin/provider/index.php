<?php
/**
 * @var yii\base\View
 */
use canis\helpers\ArrayHelper;
use canis\helpers\Html;
use yii\grid\GridView;

ArrayHelper::multisort($tasks, 'title');
$this->title = 'Sensor Providers';
$this->params['breadcrumbs'][] = ['label' => 'Administration', 'url' => ['/admin']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

echo Html::beginTag('div', ['class' => 'panel panel-default']);
echo Html::beginTag('div', ['class' => 'panel-heading']);
echo Html::beginTag('h3', ['class' => 'panel-title']);
echo Html::beginTag('div', ['class' => 'btn-group btn-group-sm  pull-right']);
echo Html::a('Add Provider <span class="caret"></span>', '#', ['class' => 'btn btn-primary dropdown-toggle', 'data-toggle' => 'dropdown']);

echo Html::beginTag('ul', ['class' => 'dropdown-menu']);
echo Html::tag('li', Html::a('Pull Provider', ['/admin/provider/create', 'type' => 'pull'], ['class' => '', 'data-handler' => 'background']));
echo Html::tag('li', Html::a('Pushing Provider', ['/admin/provider/create', 'type' => 'push'], ['class' => '', 'data-handler' => 'background']));
//echo Html::a('Static Provider', ['/admin/provider/create', 'type' => 'static'], ['class' => '', 'data-handler' => 'background']);
echo Html::endTag('ul');

echo Html::endTag('div');
echo 'Sensor Providers';
echo Html::endTag('h3');
echo Html::endTag('div');
echo Html::beginTag('div', ['class' => 'panel-body']);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
    	[
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
            'buttonOptions' => [
                'data-handler' => 'background'
            ],
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    $options = array_merge([
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ], []);
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                }
            ]
        ],
        'name',
        'type',
        'active',
        'last_refresh:datetime',
        'created:datetime'
    ],
]);
echo Html::endTag('div');
echo Html::endTag('div');