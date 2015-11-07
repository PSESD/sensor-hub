<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\instances;

use Yii;
use canis\sensorHub\models\Registry;

abstract class Collection extends \yii\base\Object
{

	protected $_parentModel;
	protected $_objects = [];

	public function __sleep()
	{
		if (is_object($this->_parentModel)) {
			$this->_parentModel = $this->_parentModel->id;
		}
		foreach ($this->_objects as $key => $item) {
			$this->_objects[$key][2] = null;
		}
		return array_keys((array) $this);
	}

	public function __wakeup()
	{
		if (!empty($this->_parentModel)) {
			$this->_parentModel = Registry::getObject($this->_parentModel);
		}
		foreach ($this->_objects as $key => $item) {
			if ($item[2] === null) {
				$this->_objects[$key][2] = Registry::getObject($key, false);
			}
		}
	}

	public function setParentModel($model)
	{
		$this->_parentModel = $model;
	}

	public function getParentModel()
	{
		if (!empty($this->_parentModel) && !is_object($this->_parentModel)) {
			$this->_parentModel = Registry::getObject($this->_parentModel);
		}
		return $this->_parentModel;
	}

	public function getAll($maxDepth = false, $objectType = false)
	{
		if ($objectType !== false && !is_array($objectType)) {
			$objectType = [$objectType];
		}
		$objects = [];
		foreach ($this->_objects as $key => $item) {
			if ($maxDepth !== false && $maxDepth < $item[0]) {
				continue;
			}
			if ($objectType !== false && !in_array($item[1], $objectType)) {
				continue;
			}
			$objects[$key] = $item[2];
		}
		return $objects;
	}

	public function add($depth, $objectType, $model)
	{
		$this->_objects[$model->id] = [$depth, $objectType, $model->dataObject];
	}

	abstract public function getPackageItems();

	public function getPackage($maxDepth = false, $objectType = false)
	{
		$package = [];
		$package['items'] = $this->getPackageItems($maxDepth, $objectType);
		return $package;
	}
}