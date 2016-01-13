<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = 'Create Sensor Provider';
echo Html::beginForm('', 'post', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'clearfix']);
echo Html::beginTag('div', ['class' => 'form']);
//echo Html::activeHiddenInput($model, 'application_id');

$baseDataId = Html::getInputId($model, 'data');
$baseDataName = Html::getInputName($model, 'data');
echo Html::beginTag('div', ['class' => 'row']);
// \d($instance);
foreach ($model->dataObject->setupFields() as $id => $field) {
	if (empty($field['on'])) {
		$field['on'] = ['update', 'restore', 'create'];
	}
	if (!in_array($scenario, $field['on'])) { continue; }
	echo $this->render('@psesd/sensorHub/views/base/_field', ['model' => $model, 'field' => $field, 'id' => $id, 'instance' => $instance]);
}
echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endTag('div');
echo Html::endForm();