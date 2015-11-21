<?php
use yii\helpers\Html;
$model->attributes = $model->convertToHumanDate();
$this->title = 'Create Note';
echo Html::beginForm('', 'post', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'form']);

echo Html::beginTag('div', ['class' => 'row']);

$fieldExtra = '';
if (!empty($model->errors['first_name'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'col-sm-6 form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'first_name');
echo Html::activeTextInput($model, 'first_name', ['class' => 'form-control']);
echo Html::error($model, 'first_name', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');

$fieldExtra = '';
if (!empty($model->errors['last_name'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'col-sm-6 form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'last_name');
echo Html::activeTextInput($model, 'last_name', ['class' => 'form-control']);
echo Html::error($model, 'last_name', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');

echo Html::endTag('div');


echo Html::beginTag('div', ['class' => 'row']);

$fieldExtra = '';
if (!empty($model->errors['email'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'col-sm-6 form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'email');
echo Html::activeTextInput($model, 'email', ['class' => 'form-control']);
echo Html::error($model, 'email', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');

$fieldExtra = '';
if (!empty($model->errors['phone'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'col-sm-6 form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'phone');
echo Html::activeTextInput($model, 'phone', ['class' => 'form-control']);
echo Html::error($model, 'phone', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');

echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'row']);
$fieldExtra = '';
if (!empty($model->errors['note'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'col-sm-6 form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'note');
echo Html::activeTextArea($model, 'note', ['class' => 'form-control']);
echo Html::error($model, 'note', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'col-sm-6']);
echo Html::beginTag('div', ['class' => 'form-group ']);
echo Html::activeCheckbox($model, 'is_technical', []);
echo Html::endTag('div');
echo Html::beginTag('div', ['class' => 'form-group ']);
echo Html::activeCheckbox($model, 'is_billing', []);
echo Html::endTag('div');
echo Html::endTag('div');

echo Html::endTag('div');

echo Html::endTag('div');
echo Html::endForm();