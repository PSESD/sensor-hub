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


class SensorCollection extends Collection
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
			$itemLimit = 4;
		}
		$items = [];
		$item = [];
		$all = $this->getAll($maxDepth, $objectType, $parentObjectTypes);
		$item['label'] = 'Sensors';
		$item['badge'] = count($all);
		$item['url'] = Url::to(['children', 'type' => 'sensor', 'object' => $this->model->id]);
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
			$subitem['state'] = $model->dataObject->getSimpleState();
			if ($subitem['state'] === 'danger') {
				$hasDanger = true;
			}
			if ($subitem['state'] === 'warning') {
				$hasWarning = true;
			}
			if ($model->dataObject->hasDataPoint()) {
				$subitem['badge'] = ucfirst($model->state) . ' ('. $model->dataObject->getDataPoint(true) .')';
			} else {
				$subitem['badge'] = ucfirst($model->state);
			}
			$item['subitems'][$model->id] = $subitem;
		}
		if ($hasDanger) {
			$item['state'] = 'danger';
		} elseif ($hasWarning) {
			$item['state'] = 'warning';
		} elseif (empty($all)) {
			$item['state'] = 'primary';
		} else {
			$item['state'] = 'success';
		}
		$items['sensor-button'] = $item;
		return $items;
	}
}