<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;


canis\sensorHub\components\web\assetBundles\BrowserAsset::register($this);

$config = [];
$categories = [];
$categories['service'] = ['service' => 'Provided Services', 'serviceReference' => 'Bound Services'];
$categories['resource'] = ['resource' => 'Provided Resources', 'resourceReference' => 'Used Resource'];
$categories['site'] = ['site' => 'Sites'];
$categories['server'] = ['server' => 'Servers'];
$categories['sensor'] = ['sensor' => 'Sensors'];

$config['objects'] = [];
foreach ($categories[$objectType] as $categoryType => $categoryLabel) {
	$category = ['label' => $categoryLabel, 'items' => []];
	$depth = false;
	if (in_array($categoryType, ['resourceReference', 'serviceReference'])) {
		$depth = 1;
	}
	foreach ($objects->getAll($depth, $categoryType) as $object) {
		$category['items'][$object->id] = [];
		$category['items'][$object->id]['label'] = $object->getContextualDescriptor($parentModel);
		$category['items'][$object->id]['url'] = Url::to(['/'.$objectType.'/view', 'id' => $object->id, 'parent' => $parentModel->id, 'bare' => 1]);
	}
	if (!empty($category['items'])) {
		ArrayHelper::multisort($category['items'], 'label');
		$config['objects'][$categoryType] = $category;
	}
}
echo Html::tag('div', '', ['data-object-browser' => json_encode($config)]);