<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\instances;

use Yii;

class CollectEvent extends \yii\base\Event
{
	public $type;
	public $maxDepth = true;
	public $depth = 1;
	protected $_collections = [];
	protected $_handled = [];

	public function getCollections()
	{
		return $this->_collections;
	}

	public function addCollection($objectType, $collection)
	{
		if (is_array($objectType)) {
			foreach ($objectType as $ot) {
				$this->addCollection($ot, $collection);
			}
			return $this;
		}
		$collection->type = $this->type;
		$this->_collections[$objectType] = $collection;
		return $this;
	}
	
	public function getCollection($objectType)
	{
		if (!isset($this->_collections[$objectType])) {
			return false;
		}
		return $this->_collections[$objectType];
	}

	public function pass($model, $data = [])
	{
		$object = $model->dataObject;
		$objectType = $object->getObjectType();
		if (!isset($this->_handled[$model->id])) {
			if (($collection = $this->getCollection($objectType))) {
				$collection->add($this->depth, $objectType, $model, $data);
			}
			$this->_handled[$model->id] = $objectType .':'. $object->object->id;
		}
	}
}