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
	public function getParentPackageItems($maxDepth = false, $objectType = false)
	{
		$items = [];
		foreach ($this->getAll($maxDepth, $objectType) as $model) {
			$item = [];
			$item['label'] = $model->getContextualDescriptor($this->model);
			$item['url'] = Url::to(['/server/view', 'id' => $model->id]);
			$items[] = $item;
		}
		return $items;
	}

	public function getChildPackageItems($maxDepth = false, $objectType = false)
	{
		$items = [];
		$item = [];
		$item['label'] = 'Servers';
		$item['url'] = Url::to(['children', 'type' => 'server', 'object' => $this->model->id]);
		$all = $this->getAll($maxDepth, $objectType);
		$item['badge'] = count($all);
		return $items;
	}
}