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
		Event::on(Application::className(), BroadcasterModule::EVENT_COLLECT_EVENT_TYPES, [$this, 'collectEventTypes']);
        Event::on(Application::className(), BroadcasterModule::EVENT_COLLECT_EVENT_HANDLERS, [$this, 'collectEventHandlers']);

     //    Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Engine::className(), 'backupInstances']);
     //    Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Engine::className(), 'cleanBackups']);
     //    Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Engine::className(), 'cleanVolumes']);
     //    Event::on(Cron::className(), Cron::EVENT_MORNING, [Engine::className(), 'cloudifyBackups']);

     //    Event::on(TickDaemon::className(), TickDaemon::EVENT_TICK, [Engine::className(), 'checkSensors']);
    	// Event::on(TickDaemon::className(), TickDaemon::EVENT_TICK, [Engine::className(), 'ensureCertificates']);
     //    Event::on(TickDaemon::className(), TickDaemon::EVENT_TICK, [Engine::className(), 'collectBackups']);

    	// Event::on(TickDaemon::className(), TickDaemon::EVENT_TICK, [Engine::className(), 'checkUninitialized']);
     //    Event::on(TickDaemon::className(), TickDaemon::EVENT_POST_TICK, [Engine::className(), 'failUninitialized']);
    }

    public function collectEventTypes($event)
    {
        $containers = [];
        $userClass = Yii::$app->classes['User'];
        if ($userClass) {
            $containers[] = $userClass::className();
        }
        // $containers[] = \canis\appFarm\components\applications\ApplicationInstance::className();
        // $containers[] = \canis\appFarm\components\applications\ServiceInstance::className();
        // $containers[] = \canis\appFarm\components\applications\sensors\SensorInstance::className();
        $event->module->collectEventTypes($containers);
    }

    public function collectEventHandlers($event)
    {
        $event->module->registerHandlers([
            'web_client' => \canis\broadcaster\handlers\WebClient::className(),
            'ifttt_maker' => \canis\broadcaster\handlers\IftttMaker::className(),
            'webhook' => \canis\broadcaster\handlers\Webhook::className(),
            'email' => \canis\broadcaster\handlers\Email::className()
        ]);
    }
}