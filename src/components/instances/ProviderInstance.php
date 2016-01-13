<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */
namespace psesd\sensorHub\components\instances;

use Yii;
use yii\helpers\ArrayHelper;
use canis\broadcaster\eventTypes\EventType;
use psesd\sensors\providers\ProviderInterface;
use psesd\sensors\base\Sensor as BaseSensor;

abstract class ProviderInstance extends Instance
{
    const COLLECT_DEPTH = 3;

    public function getObjectType()
    {
        return 'provider';
    }

    public function childModelsFromObjects()
    {
        $collections = $this->collectChildModelsFromObjects();
        return array_merge(
            $collections['sensor']->getAll(3), 
            $collections['resource']->getAll(1), 
            $collections['site']->getAll(1), 
            $collections['server']->getAll(1)
        );
    }

    public function getComponentPackage($itemLimit = null)
    {
        $c = [];
        $collections = $this->collectChildModels();
        $c['sensors'] = $collections['sensor']->getPackage($itemLimit);
        $c['resources'] = $collections['resource']->getPackage($itemLimit);
        $c['sites'] = $collections['site']->getPackage($itemLimit);
        $c['server'] = $collections['server']->getPackage($itemLimit);
        return $c;
    }

	static public function collectEventTypes()
    {
    	$events = [];
        $events['provider_created'] = [
            'name' => 'Sensor Provider Created',
            'descriptorString' => 'Sensor provider \'{{ provider.name }}\' was created'
        ];
        $events['provider_updated'] = [
            'name' => 'Sensor Provider Updated',
            'descriptorString' => 'Sensor provider \'{{ provider.name }}\' was updated'
        ];
        $events['provider_checked'] = [
            'name' => 'Sensor Provider Checked',
            'descriptorString' => 'Sensor provider \'{{ provider.name }}\' was checked',
            'priority' => EventType::PRIORITY_LOW
        ];
    	return $events;
    }

    abstract public function getProviderType();
    
    public function getObjectModel()
    {

    }

    public function initialize($log, $initialInitialize = true)
    {   
        $this->log = $log;
        $id = md5(microtime(true));
        $this->log->addInfo("Starting initializing provider ({$id}; {$this->model->system_id})");
        $result = $this->internalInitialize($this->object, null, $initialInitialize);
        $this->log->addInfo("Finished initializing provider ({$id}; {$this->model->system_id})");
        return $result;
    }

    public function internalUpdateRelations()
    {
        $_this = $this;
        $modelsChecked = [];

        $updateChildren = function($model) use($_this, &$modelsChecked, &$updateChildren) {
            if (in_array($model->id, $modelsChecked)) { return true; }
            $modelsChecked[] = $model->id;
            $currentChildRelations = $model->queryChildRelations()->all();
            $currentChildRelations = ArrayHelper::index($currentChildRelations, function ($relation) {
                return $relation->child_object_id;
            });

            $childModels = $model->dataObject->childModelsFromObjects();
            $baseRelation = ['parent_object_id' => $model->id, 'active' => 1];
            $childRelations = [];
            foreach ($childModels as $childModel) {
                $updateChildren($childModel);
                if (isset($currentChildRelations[$childModel->id])) {
                    unset($currentChildRelations[$childModel->id]);
                    continue;
                }
                $childRelation = $baseRelation;
                $childRelation['child_object_id'] = $childModel->id;
                $childRelations[] = $childRelation;
            }
            $model->setRelationModels($childRelations);
            $model->save();
            foreach ($currentChildRelations as $childRelation) {
                $childRelation->delete();
            }
        };
        return $updateChildren($this->model);
    }
}
?>