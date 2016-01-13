<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\components\instances;

use Yii;
use yii\helpers\Url;


class ServiceCollection extends Collection
{
	public function getParentPackageItems($itemLimit = null, $maxDepth = false, $objectType = false, $parentObjectTypes = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 4;
		}
		$items = [];
		return $items;
	}

	public function getChildPackageItems($itemLimit = null, $maxDepth = false, $objectType = false, $parentObjectTypes = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 3;
		}
		$items = [];
		$item = [];
		$all = $this->getAll($maxDepth, $objectType, $parentObjectTypes);
		$item['label'] = 'Services';
		$item['url'] = Url::to(['children', 'type' => 'service', 'object' => $this->model->id]);
		$item['background'] = true;
		$item['subitems'] = [];
		$item['subitems']['services'] = ['label' => 'Provided Services', 'subitems' => []];
		$item['subitems']['serviceReferences'] = ['label' => 'Bound Services', 'subitems' => []];

		$hasDanger = false;
		$hasWarning = false;
		$itemCount = 1;
		$allServices = $this->getAll($maxDepth, 'service', $parentObjectTypes);
		foreach ($allServices as $model) {
			if ($itemLimit && $itemCount > $itemLimit && count($allServices) !== $itemLimit) {
				$item['subitems']['services']['truncated'] = true;
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
			$item['subitems']['services']['subitems'][$model->id] = $subitem;
		}

		$itemCount = 1;
		$allReferencesRaw = $this->getAll($maxDepth, 'serviceReference', $parentObjectTypes);
		$allReferences = [];
		foreach ($allReferencesRaw as $model) {
			$allReferences[$model->service_id] = $model;
		}
		foreach ($allReferences as $model) {
			if ($itemCount > $itemLimit && count($allReferences) !== $itemLimit) {
				$item['subitems']['serviceReferences']['truncated'] = true;
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
			$item['subitems']['serviceReferences']['subitems'][$model->service_id] = $subitem;
		}
		//\d($item['subitems']['services']);exit;
		//print_r([count($allServices), count($allReferences)]);exit;
		$item['badge'] = count($allServices) + count($allReferences);

		if (empty($item['subitems']['services']['subitems'])) {
			unset($item['subitems']['services']);
		}
		if (empty($item['subitems']['serviceReferences']['subitems'])) {
			unset($item['subitems']['serviceReferences']);
		}
		//unset($item['subitems']['services']);
		$item['state'] = 'primary';
		$items['service-button'] = $item;
		return $items;
	}
}