<?php
namespace psesd\sensorHub\console\controllers;

use Yii;
use yii\helpers\FileHelper;

ini_set('memory_limit', -1);

class PrivilegedController extends \canis\console\Controller
{
    public $verbose = false;
    public function actionIndex()
    {
        //$this->cleanVolumes();
    }

    public function actionEnsureCron()
    {
        $cronPrefix = '/var/www/cron';
        //evening.cron.sh  hourly.cron.sh  midnight.cron.sh  monthly.cron.sh  morning.cron.sh  weekly.cron.sh
        $cronFiles = [
            'evening' => [
                'file' => $cronPrefix . DIRECTORY_SEPARATOR . 'evening.cron.sh',
                'command' => '/var/www/bin/yii cron/trigger-evening'
            ],
            'hourly' => [
                'file' => $cronPrefix . DIRECTORY_SEPARATOR . 'hourly.cron.sh',
                'command' => '/var/www/bin/yii cron/trigger-hourly'
            ],
            'midnight' => [
                'file' => $cronPrefix . DIRECTORY_SEPARATOR . 'midnight.cron.sh',
                'command' => '/var/www/bin/yii cron/trigger-midnight'
            ],
            'monthly' => [
                'file' => $cronPrefix . DIRECTORY_SEPARATOR . 'monthly.cron.sh',
                'command' => '/var/www/bin/yii cron/trigger-monthly'
            ],
            'morning' => [
                'file' => $cronPrefix . DIRECTORY_SEPARATOR . 'morning.cron.sh',
                'command' => '/var/www/bin/yii cron/trigger-morning'
            ],
            'weekly' => [
                'file' => $cronPrefix . DIRECTORY_SEPARATOR . 'weekly.cron.sh',
                'command' => '/var/www/bin/yii cron/trigger-weekly'
            ],
        ];
        foreach ($cronFiles as $id => $cron) {
            if (!is_file($cron['file'])) {
                $this->out("Cron file for {$id} doesn't exist ({$cron['file']})");
                continue;
            }
            $contents = file_get_contents($cron['file']);
            $commentCheck = "# Application Cron Event ({$id})";
            if (strpos($contents, $commentCheck) === false) {
                $contents .= "\n{$commentCheck}\n{$cron['command']}\n";
                file_put_contents($cron['file'], $contents);
                $this->out("Set up cron: {$id}");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        return array_merge(parent::options($id), []);
    }
}
