<?php
use yii\helpers\Html;

$baseDataId = Html::getInputId($model, 'data');
$baseDataName = Html::getInputName($model, 'data');

$value = isset($instance->attributes[$id]) ? $instance->attributes[$id] : null;
$fieldId = $baseDataId . '_' . $id;
$fieldName = $baseDataName . '[' . $id .']';
if (empty($field['full'])) {
	$size = 6;
} else { 
	$size = 12;
}
$fieldExtra = '';
if (!empty($instance->setupErrors[$id])) {
	$fieldExtra = 'has-feedback has-error';
}
echo Html::beginTag('div', ['class' => 'form-group col-md-'.$size. ' '. $fieldExtra]);
echo Html::label($field['label'], $fieldId);

if (isset($field['default']) && $value === null) {
	$value = $field['default'];
}
switch ($field['type']) {
	case 'password':
		echo Html::passwordInput($fieldName, $value, ['class' => 'form-control', 'id' => $fieldId]);
	break;
	case 'select':
		$options = ['class' => 'form-control', 'id' => $fieldId];
		if (isset($field['prompt'])) {
			$options['prompt'] = $field['prompt'];
		}
		echo Html::dropDownList($fieldName, $value, $field['options'], $options);
	break;
	default:
		echo Html::textInput($fieldName, $value, ['class' => 'form-control', 'id' => $fieldId]);
	break;
}
if (!empty($instance->setupErrors[$id])) {
	echo Html::tag('div', $instance->setupErrors[$id], ['class' => 'help-inline text-danger']);
}else if (isset($field['help'])) {
	echo Html::tag('p', $field['help'], ['class' => 'help-block']);
}
echo Html::endTag('div');
?>