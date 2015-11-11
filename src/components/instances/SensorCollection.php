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


class SensorCollection extends Collection
{
	public function getParentPackageItems($maxDepth = false, $objectType = false)
	{
		$items = [];
		return $items;
	}

	public function getChildPackageItems($maxDepth = false, $objectType = false)
	{
		$items = [];
		$item = [];
		$all = $this->getAll($maxDepth, $objectType);
		$item['label'] = 'Sensors';
		$item['badge'] = count($all);
		$item['url'] = Url::to(['children', 'type' => 'sensor', 'object' => $this->model->id]);
		$item['background'] = true;
		$item['subitems'] = [];
		$hasDanger = false;
		$hasWarning = false;
		//\d($all);exit;
		$itemCount = 1;
		$itemLimit = 4;
		foreach ($all as $model) {
			if ($itemCount > $itemLimit && count($all) !== $itemLimit) {
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
				$subitem['badge'] = $model->dataObject->getDataPoint();
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