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


class ServiceCollection extends Collection
{
	public function getPackageItems($maxDepth = false, $objectType = false)
	{
		$items = [];
		$item = [];
		$item['label'] = 'Services';
		$item['badge'] = count($this->getAll($maxDepth, $objectType));
		$item['url'] = Url::to(['/service', 'filter[object]' => $this->parentModel->id]);
		$items['service-button'] = $item;
		return $items;
	}
}