<?php
namespace canis\sensorHub;

use Yii;

class Extension implements \yii\base\BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@canis/sensorHub', __DIR__);
        $app->registerMigrationAlias('@canis/sensorHub/migrations');
    }
}