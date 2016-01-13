<?php
use yii\helpers\Html;
use yii\helpers\Url;

psesd\sensorHub\components\web\assetBundles\ViewerAsset::register($this);

//$this->params['breadcrumbs'][] = ['label' => 'V'];
$config = [];
$config['initial'] = $initial;
$config['packageUrl'] = Url::to(['view', 'id' => $_GET['id'], 'parent' => $parentId, 'package' => 1]);
$config['hideTitle'] = true;
if (!empty($_GET['bare']) || !Yii::$app->request->isAjax) {
	$config['hideTitle'] = false;
}
$config['url'] = Url::to(['', 'refresh' => 1, 'id' => $_GET['id'], 'parent' => $parentId]);
echo Html::tag('div', '', ['data-object-viewer' => json_encode($config)]);