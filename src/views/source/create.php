<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = 'Create Sensor Source';
echo Html::beginForm('', 'post', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'clearfix']);
echo Html::beginTag('div', ['class' => 'form']);
//echo Html::activeHiddenInput($model, 'application_id');

$fieldExtra = '';
if (!empty($model->errors['name'])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'form-group ' . $fieldExtra]);
echo Html::activeLabel($model, 'name');
echo Html::activeTextInput($model, 'name', ['class' => 'form-control']);
echo Html::error($model, 'name', ['class' => 'help-inline text-danger']);
echo Html::endTag('div');

$baseDataId = Html::getInputId($model, 'data');
$baseDataName = Html::getInputName($model, 'data');
echo Html::beginTag('div', ['class' => 'row']);
foreach ($model->dataObject->setupFields() as $id => $field) {
	if (empty($field['on'])) {
		$field['on'] = ['update', 'restore', 'create'];
	}
	if (!in_array($scenario, $field['on'])) { continue; }
	echo $this->render('@canis/sensorHub/views/base/_field', ['model' => $model, 'field' => $field, 'id' => $id, 'instance' => $instance]);
}
echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endForm();