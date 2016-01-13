<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\controllers;

use Yii;
use yii\helpers\Url;
use canis\language\Noun;
use psesd\sensorHub\models\Instance;
use psesd\sensorHub\models\Site;
use psesd\sensorHub\models\Server;
use psesd\sensorHub\models\Resource;
use canis\registry\models\Registry;
use psesd\sensorHub\models\Sensor;
use yii\helpers\ArrayHelper;

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
   		Yii::$app->response->view = '@psesd/sensorHub/views/base/index';
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


    public function actionView()
    {
        if (empty($_GET['id']) || !($objectModel = Registry::getObject($_GET['id']))) {
            throw new \yii\web\NotFoundHttpException("Object not found");
        }
        $parentModel = false;
        if (!empty($_GET['parent']) && !($parentModel = Registry::getObject($_GET['parent']))) {
            throw new \yii\web\NotFoundHttpException("Object not found");
        }
        Yii::$app->response->params['objectModel'] = $objectModel;
        Yii::$app->response->params['parentModel'] = $parentModel;
        $object = $objectModel->dataObject;
        $children = $object->collectChildModels();
        $initial = [];
        $initial['object'] = $objectModel->dataObject->getPackage(true);
        $initial['parentObject'] = false;
        $parentId = null;
        if ($parentModel) {
            $parentId = $_GET['parent'];
            $initial['parentObject'] = $parentModel->dataObject->getPackage();
        }
        $this->params['parentId'] = $parentId;
        $this->params['initial'] = $initial;
        if (!empty($_GET['package'])) {
            Yii::$app->response->data = $initial;
        }
        if (!empty($_GET['bare'])) {
            $this->layout = false;
        } else {
            Yii::$app->response->taskOptions = ['title' => $objectModel->descriptor, 'modalClass' => 'modal-xl', 'isForm' => false];
            Yii::$app->response->task = 'dialog';
        }
        Yii::$app->response->view = '@psesd/sensorHub/views/base/view';
    }

    public function actionChildren()
    {
    	$config = $this->config();
    	$itemClass = $config['modelClass'];
    	if (empty($_GET['object']) || !($objectModel = $itemClass::find()->where(['id' => $_GET['object']])->one())) {
    		throw \yii\web\NotFoundHttpException("Object not found");
    	}
    	Yii::$app->response->params['parentModel'] = $objectModel;
    	$object = $objectModel->dataObject;
    	$children = $object->collectChildModels();
    	if (empty($_GET['type']) || !isset($children[$_GET['type']])) {
    		throw \yii\web\NotFoundHttpException("Object type not found");
    	}
        //\d($children[$_GET['type']]);exit;
        Yii::$app->response->params['objects'] = $this->getObjects($children[$_GET['type']], $_GET['type'], $objectModel);
        if (!empty($_GET['refresh'])) {
            Yii::$app->response->data = ['objects' => Yii::$app->response->params['objects']];
            return;
        }
    	Yii::$app->response->params['objectType'] = $_GET['type'];
    	Yii::$app->response->taskOptions = ['title' => $objectModel->descriptor .'\'s '.ucfirst($_GET['type']).'s', 'modalClass' => 'modal-sm', 'isForm' => false];
        Yii::$app->response->task = 'dialog';
   		Yii::$app->response->view = '@psesd/sensorHub/views/base/browse';
    }


    public function actionParents()
    {
    	$config = $this->config();
    	$itemClass = $config['modelClass'];
    	if (empty($_GET['object']) || !($objectModel = $itemClass::find()->where(['id' => $_GET['object']])->one())) {
    		throw \yii\web\NotFoundHttpException("Object not found");
    	}
    	Yii::$app->response->params['parentModel'] = $objectModel;
    	$object = $objectModel->dataObject;
    	$parents = $object->collectParentModels();
    	if (empty($_GET['type']) || !isset($parents[$_GET['type']])) {
    		throw \yii\web\NotFoundHttpException("Object type not found");
    	}
    	Yii::$app->response->params['objects'] = $this->getObjects($parents[$_GET['type']], $_GET['type'], $objectModel);
        if (!empty($_GET['refresh'])) {
            Yii::$app->response->data = ['objects' => Yii::$app->response->params['objects']];
            return;
        }
    	Yii::$app->response->taskOptions = ['title' => $objectModel->descriptor .'\'s '.ucfirst($_GET['type']).'s', 'modalClass' => 'modal-sm', 'isForm' => false];
        Yii::$app->response->task = 'dialog';
   		Yii::$app->response->view = '@psesd/sensorHub/views/base/browse';
    }

    protected function getObjects($objects, $objectType, $parentModel)
    {   
        $all = [];
        $categories = [];
        $categories['service'] = ['service' => 'Provided Services', 'serviceReference' => 'Bound Services'];
        $categories['resource'] = ['resource' => 'Provided Resources', 'resourceReference' => 'Used Resource'];
        $categories['site'] = ['site' => 'Sites'];
        $categories['server'] = ['server' => 'Servers'];
        $categories['sensor'] = ['sensor' => 'Sensors'];
        foreach ($categories[$objectType] as $categoryType => $categoryLabel) {
            $category = ['label' => $categoryLabel, 'items' => []];
            $depth = false;
            if (!($parentModel instanceof Site) && in_array($categoryType, ['resourceReference', 'serviceReference'])) {
                $depth = 1;
            }
            foreach ($objects->getAll($depth, $categoryType) as $model) {
                $category['items'][$model->id] = [];
                $category['items'][$model->id]['state'] = $model->dataObject->getSimpleState();;
                $category['items'][$model->id]['label'] = $model->getContextualDescriptor($parentModel);
                $category['items'][$model->id]['url'] = Url::to(['/'.$objectType.'/view', 'id' => $model->id, 'parent' => $parentModel->id, 'bare' => 1]);
            }
            if (!empty($category['items'])) {
                ArrayHelper::multisort($category['items'], 'label');
                $all[$categoryType] = $category;
            }
        }
        return $all;
    }
}
?>