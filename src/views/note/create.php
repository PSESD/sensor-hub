<?php
use yii\helpers\Html;
$model->attributes = $model->convertToHumanDate();
$this->title = 'Create Note';
echo Html::beginForm('', 'post', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'form']);
$fieldExtra = '';
if (!empty($model->errors['title'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'title');
echo Html::activeTextInput($model, 'title', ['class' => 'form-control']);
echo Html::error($model, 'title', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');

$fieldExtra = '';
if (!empty($model->errors['content'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'content');
echo Html::activeTextArea($model, 'content', ['class' => 'form-control']);
echo Html::error($model, 'content', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endForm();