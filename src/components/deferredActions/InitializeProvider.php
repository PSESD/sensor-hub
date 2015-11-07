<?php
namespace canis\sensorHub\components\deferredActions;

use Yii;

class InitializeProvider extends \canis\deferred\components\Action
{
    public function run()
    {
        $model = $this->config['model'];

        $model->initializationLog = $this->result;
        $this->result->addInfo("Starting...");
        if ($model->save() && $model->initializeData(true)) {
            $this->result->addInfo("Provider was initialized!");
            $this->result->message = 'Provider was initialized';
            $this->result->isSuccess = true;
        } else {
            $this->result->addError("Provider could not be initialized");
            $this->result->message = 'Provider could not be initialized!';
            $this->result->isSuccess = false;
        }
        return true;
    }

    public function getDescriptor()
    {
        $name = 'Unknown Provider';
        $model = $this->config['model'];
        if (isset($model->getIntitializeData()->object->name)) {
            $name = $model->getIntitializeData()->object->getName();
        }
        return 'Initialize Provider: '. $name;
    }

    public function getResultConfig()
    {
        return [
            'class' => \canis\deferred\components\LogResult::className(),
        ];
    }

    public function requiredConfigParams()
    {
        return array_merge(parent::requiredConfigParams(), ['model']);
    }
}
?>
