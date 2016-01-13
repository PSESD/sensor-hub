<?php

namespace psesd\sensorHub\models;

use Yii;
use canis\registry\models\Registry;

trait SensorObjectTrait {
    public function activate($recurse = true)
    {
        if ($this->hasAttribute('active')) {
			$this->active = 1;
			$this->save();
			if ($recurse) {
				foreach ($this->childModels(false) as $models) {
					foreach ($models as $model) {
						if (method_exists($model, 'activate')) {
							$model->activate(false);
						}
					}
				}
			}
		}
    }
    public function deactivate($recurse = true)
    {
        if ($this->hasAttribute('active')) {
			$this->active = 0;
			$this->save();
			if ($recurse) {
				foreach ($this->childModels(false) as $models) {
					foreach ($models as $model) {
						if (method_exists($model, 'deactivate')) {
							$model->deactivate(false);
						}
					}
				}
			}
		} else {
			$this->delete();
		}
    }
}