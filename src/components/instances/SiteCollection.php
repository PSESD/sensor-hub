<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\instances;

use Yii;
use yii\helpers\Url;

class SiteCollection extends Collection
{
	public function getParentPackageItems($itemLimit = null, $maxDepth = false, $objectType = false, $parentObjectTypes = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 4;
		}
		$items = [];
		$siteCount = 0;
		$truncated = false;
		$itemCount = 1;
		$sites = $this->getAll($maxDepth, $objectType, $parentObjectTypes);
		foreach ($sites as $model) {
			if ($itemLimit && $itemCount > $itemLimit && count($sites) !== $itemLimit) {
				$truncated = true;
				break;
			}
			$itemCount++;
			$item = [];
			$item['label'] = $model->descriptor;
			//$item['url'] = Url::to(['/site/view', 'id' => $model->id]);
			$items[] = $item;
		}
		return ['sites' => ['label' => 'Sites', 'truncated' => $truncated, 'url' => Url::to(['parents', 'type' => 'site', 'object' => $this->model->id]), 'background' => true, 'badge' => count($sites), 'subitems' => $items]];
		// return $items;
	}

	public function getChildPackageItems($itemLimit = null, $maxDepth = false, $objectType = false, $parentObjectTypes = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 4;
		}
		$items = [];
		$item = [];
		$all = $this->getAll($maxDepth, $objectType, $parentObjectTypes);
		$item['label'] = 'Sites';
		$item['badge'] = count($all);
		$item['url'] = Url::to(['children', 'type' => 'site', 'object' => $this->model->id]);
		$item['background'] = true;
		$item['subitems'] = [];
		$hasDanger = false;
		$hasWarning = false;
		//\d($all);exit;
		$itemCount = 1;
		foreach ($all as $model) {
			if ($itemLimit && $itemCount > $itemLimit && count($all) !== $itemLimit) {
				$item['truncated'] = true;
				break;
			}
			$itemCount++;
			$subitem = [];
			$subitem['label'] = $model->getContextualDescriptor($this->model);
			$subitem['state'] = $model->dataObject->getSimpleState($model);
			if ($subitem['state'] === 'danger') {
				$hasDanger = true;
			}
			if ($subitem['state'] === 'warning') {
				$hasWarning = true;
			}
			$item['subitems'][$model->id] = $subitem;
		}
		$item['state'] = 'primary';
		$items['site-button'] = $item;
		return $items;
	}
}