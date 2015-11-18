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
echo Html::a('<span class="fa fa-plus"></span> Add Provider', ['/admin/provider/create'], ['class' => 'btn btn-primary', 'data-handler' => 'background']);
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
        'url',
        'active',
        'last_check:datetime',
        'created:datetime'
    ],
]);
echo Html::endTag('div');
echo Html::endTag('div');