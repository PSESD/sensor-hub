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


class ServerCollection extends Collection
{
	public function getParentPackageItems($itemLimit = null, $maxDepth = false, $objectType = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 4;
		}
		$items = [];
		foreach ($this->getAll($maxDepth, $objectType) as $model) {
			$item = [];
			$item['label'] = $model->getContextualDescriptor($this->model);
			$item['url'] = Url::to(['/server/view', 'id' => $model->id]);
			$item['background'] = true;
			$item['state'] = 'default';
			$items[] = $item;
		}
		return $items;
	}

	public function getChildPackageItems($itemLimit = null, $maxDepth = false, $objectType = false)
	{
		if ($itemLimit === null) {
			$itemLimit = 4;
		}
		$items = [];
		$item = [];
		$item['label'] = 'Servers';
		$item['url'] = Url::to(['children', 'type' => 'server', 'object' => $this->model->id]);
		$all = $this->getAll($maxDepth, $objectType);
		$item['badge'] = count($all);
		return $items;
	}
}