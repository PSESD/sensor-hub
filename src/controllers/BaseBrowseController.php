<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\controllers;

use Yii;
use yii\helpers\Url;
use canis\base\language\Noun;
use canis\sensorHub\models\Instance;
use canis\sensorHub\models\Site;
use canis\sensorHub\models\Server;
use canis\sensorHub\models\Resource;
use canis\sensorHub\models\Sensor;

abstract class BaseBrowseController extends Controller
{
	abstract public function getTopObjectId();
	
	public function config()
	{
		$config = $this->getObjectTypeConfig($this->getTopObjectId());
		if (!$config) {
			throw new \Exception("Invalid top object ID");
		}
		$config['packageUrl'] = Url::to(['package']);
		$config['types'] = [];
		foreach (['site', 'resource', 'server', 'sensor'] as $type) {
			$config['types'][$type] = $this->getObjectTypeConfig($type);
		}
		return $config;
	}

	public function getObjectTypeConfig($id)
	{
		$config = [];
		$config['id'] = $id;
		$config['orderBy'] = ['name' => SORT_ASC];
		switch ($id) {
			case 'site':
				$config['modelClass'] = Site::class;
				$noun = new Noun('site');
			break;
			case 'resource':
				$config['modelClass'] = Resource::class;
				$noun = new Noun('resource');
			break;
			case 'server':
				$config['modelClass'] = Server::class;
				$noun = new Noun('server');
			break;
			case 'sensor':
				$config['modelClass'] = Sensor::class;
				$noun = new Noun('sensor');
			break;
			default:
				return false;
			break;
		}
		$config['title'] = $noun->upperPlural;
		$config['name'] = $noun->package;
		return $config;
	}

	/**
     * The landing page for the application.
     */
    public function actionIndex()
    {
    	$this->params['config'] = $this->config();
   		Yii::$app->response->view = '@canis/sensorHub/views/base/index';
    }

    public function actionPackage()
    {
    	$package = ['timestamp' => time(), 'items' => []];
    	$config = $this->config();
    	$itemClass = $config['modelClass'];
    	$items = $itemClass::find()->where(['active' => 1]);
    	if (!empty($config['orderBy'])) {
    		$items->orderBy($config['orderBy']);
    	}
    	$items = $items->all();
    	foreach ($items as $item) {
    		$package['items'][$item->id] = $item->dataObject->package;
    	}
    	Yii::$app->response->data = $package;
    }
}
?>