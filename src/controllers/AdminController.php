<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;
use canis\daemon\Daemon;
use canis\sensorHub\components\base\Engine;

class AdminController extends Controller
{

    public function actionIndex()
    {
        $this->params['tasks'] = $tasks = $this->getTasks();
        if (isset($_GET['task'])) {
            Yii::$app->response->task = 'message';
            if (isset($tasks[$_GET['task']])) {
                call_user_func($tasks[$_GET['task']]['run']);
            } else {
                Yii::$app->response->content = 'Unknown task!';
                Yii::$app->response->taskOptions = ['state' => 'danger'];
            }
            return;
        }

        Yii::$app->response->view = 'index';
    }

    protected function getTasks()
    {
        $tasks = [];
        $tasks['view-cron-log'] = [];
        $tasks['view-cron-log']['title'] = 'View Cron Log';
        $tasks['view-cron-log']['description'] = 'View the cron log';
        $tasks['view-cron-log']['run'] = [$this, 'actionCronLog'];
        
        $tasks['view-daemon-log'] = [];
        $tasks['view-daemon-log']['title'] = 'View Daemon Log';
        $tasks['view-daemon-log']['description'] = 'View the daemon log';
        $tasks['view-daemon-log']['run'] = [$this, 'actionDaemonLog'];

        $tasks['flush-file-cache'] = [];
        $tasks['flush-file-cache']['title'] = 'Flush File Cache';
        $tasks['flush-file-cache']['description'] = 'Clear the file cache in Cascade';
        $tasks['flush-file-cache']['run'] = function () {
            Yii::$app->fileCache->flush();
            Yii::$app->response->content = 'File cache was flushed!';
            Yii::$app->response->taskOptions = ['state' => 'success', 'title' => 'Success'];
        };

        $tasks['flush-cache'] = [];
        $tasks['flush-cache']['title'] = 'Flush Memory Cache';
        $tasks['flush-cache']['description'] = 'Clear the memory cache in Cascade';
        $tasks['flush-cache']['run'] = function () {
            Yii::$app->cache->flush();
            Yii::$app->response->content = 'Memory cache was flushed!';
            Yii::$app->response->taskOptions = ['state' => 'success', 'title' => 'Success'];
        };

        $tasks['toggle-daemon'] = [];
        $tasks['toggle-daemon']['title'] = 'Toggle Daemon';
        $tasks['toggle-daemon']['description'] = 'Pause/Resume the daemon';
        $tasks['toggle-daemon']['run'] = function () {
            if (Daemon::isPaused()) {
                Daemon::resume();
                Yii::$app->response->content = 'Daemons were resumed!';
            } else {
                Daemon::pause();
                Yii::$app->response->content = 'Daemons were paused!';
            }
            Yii::$app->response->taskOptions = ['state' => 'success', 'title' => 'Success'];
        };
        return $tasks;
    }

    public function actionCronLog()
    {
        $base = [];
        $base['_'] = [];
        $base['_']['url'] = Url::to(['/admin/daemon-log']);
        $cronLog = Engine::getCronLog();
        if (Yii::$app->request->isAjax && !empty($_GET['package'])) {
            Yii::$app->response->data = $cronLog->getPackage($base);
            return;
        } elseif (Yii::$app->request->isAjax) {
            Yii::$app->response->taskOptions = ['title' => 'Cron Log', 'modalClass' => 'modal-xl', 'isForm' => false];
            Yii::$app->response->task = 'dialog';
        }
        $this->params['url'] = '/admin/cron-log';
        $this->params['log'] = $cronLog;
        $this->params['package'] = $cronLog->package($base);
        Yii::$app->response->view = 'view_log';
    }

    public function actionDaemonLog()
    {
        $base = [];
        $base['_'] = [];
        $base['_']['url'] = Url::to(['/admin/daemon-log']);

        $daemonLog = Engine::getDaemonLog();
        if (Yii::$app->request->isAjax && !empty($_GET['package'])) {
            Yii::$app->response->data = $daemonLog->getPackage(true);
            return;
        } elseif (Yii::$app->request->isAjax) {
            Yii::$app->response->taskOptions = ['title' => 'Daemon Log', 'modalClass' => 'modal-xl', 'isForm' => false];
            Yii::$app->response->task = 'dialog';
        }
        $this->params['url'] = '/admin/daemon-log';
        $this->params['log'] = $daemonLog;
        $this->params['package'] = $daemonLog->package($base);
        Yii::$app->response->view = 'view_log';
    }

}
?>