<?php
use canis\helpers\Html;
use yii\helpers\Url;
\canis\web\assetBundles\CanisLogViewerAsset::register($this);
$this->title = "View Service Log";
//$package = [];
echo Html::tag('div', '', [
    'data-log' => json_encode($package),
]);
