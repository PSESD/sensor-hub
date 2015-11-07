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
	public function getPackageItems($maxDepth = false, $objectType = false)
	{
		$items = [];
		$item = [];
		$item['label'] = 'Resources';
		$item['badge'] = count($this->getAll($maxDepth, $objectType));
		if (!is_object($this->parentModel)) {
			\d($this);exit;
		}
		$item['url'] = Url::to(['/resource', 'filter[object]' => $this->parentModel->id]);
		$items['resource-button'] = $item;
		return $items;
	}
}