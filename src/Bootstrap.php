<?php
namespace psesd\sensorHub;

use Yii;
use psesd\sensorHub\components\base\Daemon as SensorDaemon;

use yii\base\Application;
use yii\base\Event;
use yii\base\BootstrapInterface;
use canis\cron\Cron;
use canis\daemon\Daemon;
use canis\daemon\TickDaemon;
use canis\broadcaster\Module as BroadcasterModule;

class Bootstrap implements \yii\base\BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@psesd/sensorHub', __DIR__);
        $app->registerMigrationAlias('@psesd/sensorHub/migrations');
		Event::on(Application::className(), BroadcasterModule::EVENT_COLLECT_EVENT_TYPES, [$this, 'collectEventTypes']);
        Event::on(Application::className(), BroadcasterModule::EVENT_COLLECT_EVENT_HANDLERS, [$this, 'collectEventHandlers']);

     //    Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Engine::className(), 'backupInstances']);
     //    Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Engine::className(), 'cleanBackups']);
     //    Event::on(Cron::className(), Cron::EVENT_MIDNIGHT, [Engine::className(), 'cleanVolumes']);
     //    Event::on(Cron::className(), Cron::EVENT_MORNING, [Engine::className(), 'cloudifyBackups']);

     //    Event::on(TickDaemon::className(), TickDaemon::EVENT_TICK, [Engine::className(), 'checkSensors']);
    	// Event::on(TickDaemon::className(), TickDaemon::EVENT_TICK, [Engine::className(), 'ensureCertificates']);
     //    Event::on(TickDaemon::className(), TickDaemon::EVENT_TICK, [Engine::className(), 'collectBackups']);

        Event::on(Daemon::className(), Daemon::EVENT_REGISTER_DAEMONS, [$this, 'registerDaemon']);
    }

    public function registerDaemon($event)
    {
        $sensorDaemon = SensorDaemon::getInstance();
        $event->controller->registerDaemon('sensor', $sensorDaemon);
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