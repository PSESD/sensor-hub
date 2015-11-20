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


class ResourceCollection extends Collection
{
	public function getParentPackageItems($itemLimit = null, $maxDepth = false, $objectType = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 4;
		}
		$items = [];
		return $items;
	}

	public function getChildPackageItems($itemLimit = null, $maxDepth = false, $objectType = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 4;
		}
		$items = [];
		$item = [];
		$all = $this->getAll($maxDepth, $objectType);
		$item['label'] = 'Resources';
		$item['url'] = Url::to(['children', 'type' => 'resource', 'object' => $this->model->id]);
		$item['background'] = true;
		$item['subitems'] = [];

		$item['subitems']['resources'] = ['label' => 'Provided Resources', 'subitems' => []];
		$item['subitems']['resourceReferences'] = ['label' => 'Used Resources', 'subitems' => []];

		$hasDanger = false;
		$hasWarning = false;
		$itemCount = 1;
		$allResources = $this->getAll($maxDepth, 'resource');
		foreach ($allResources as $model) {
			if ($itemLimit && $itemCount > $itemLimit && count($allResources) !== $itemLimit) {
				$item['subitems']['resources']['truncated'] = true;
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
			$item['subitems']['resources']['subitems'][$model->id] = $subitem;
		}

		$itemCount = 1;
		$allReferencesRaw = $this->getAll($maxDepth, 'resourceReference');
		$allReferences = [];
		foreach ($allReferencesRaw as $model) {
			$allReferences[$model->resource->id] = $model;
		}
		foreach ($allReferences as $model) {
			if ($itemCount > $itemLimit && count($allReferences) !== $itemLimit) {
				$item['subitems']['resourceReferences']['truncated'] = true;
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
			$item['subitems']['resourceReferences']['subitems'][$model->resource->id] = $subitem;
		}


		$item['badge'] = count($allResources) + count($allReferences);
		if (empty($item['subitems']['resources']['subitems'])) {
			unset($item['subitems']['resources']);
		}
		if (empty($item['subitems']['resourceReferences']['subitems'])) {
			unset($item['subitems']['resourceReferences']);
		}
		$item['state'] = 'primary';
		$items['resource-button'] = $item;
		return $items;
	}
}