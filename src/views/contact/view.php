<?php
use yii\helpers\Html;
$this->title = $model->descriptor;
echo Html::beginTag('div', ['class' => 'panel panel-default']);
echo Html::beginTag('div', ['class' => 'panel-heading']);
echo Html::tag('h3', $this->title, ['class' => 'panel-title']);
echo Html::endTag('div');
echo Html::beginTag('div', ['class' => 'panel-body']);
echo Html::beginTag('dl', ['class' => 'dl-horizontal']);
echo Html::tag('dt', 'Email');
if (empty($model->email)) {
	echo Html::tag('dd', '(None Provided)');
} else {
	echo Html::tag('dd', Html::a($model->email, 'mailto:'. $model->email));
}
echo Html::tag('dt', 'Phone');
if (empty($model->phone)) {
	echo Html::tag('dd', '(None Provided)');
} else {
	echo Html::tag('dd', $model->phone);
}
echo Html::endTag('dl');
if (!empty($model->notes)) {
	echo Html::beginTag('div', ['class' => 'well']);
	echo $model->notes;
	echo Html::endTag('div');
}
echo Html::endTag('div');
echo Html::endTag('div');
