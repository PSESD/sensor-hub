<?php
use yii\helpers\Html;
$this->title = 'Delete Note';
echo Html::beginForm('', 'get', ['class' => 'ajax']);
echo Html::beginTag('div', ['class' => 'form']);
echo Html::hiddenInput('model', $model->id);
echo Html::hiddenInput('confirm', 1);
echo Html::tag('div', 'Are you sure you want to delete the note <i>'. $model->title .'</i>?', ['class' => 'alert alert-warning']);
echo Html::endTag('div');
echo Html::endForm();