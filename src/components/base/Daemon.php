<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\components\base;

use Yii;
use canis\daemon\Daemon as DaemonBase;
use yii\helpers\Console;
use canis\caching\Cacher;
use canis\sensorHub\models\Sensor;

/**
 * Daemon [[@doctodo class_description:canis\base\Daemon]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Daemon extends DaemonBase
{
	public $continue = true;
	public $headPlan = [
		'sensorHandler',
		'sensorHandler'
	];
	protected $_heads = [];

    public function getDescriptor()
    {
        return 'Sensor Daemon';
    }

    public function getSpeedLimit()
    {
        return 1;
    }
    
    public function run($controller, $args = [])
    {
        $_this = $this;
        $args = array_values($args);
        if (empty($args) || !in_array($args[0], ['sensorHandler'])) {
        	$headNumber = 0;
        	foreach ($this->headPlan as $head) {
        		$headNumber++;
        		$headId = $head .'-' . $headNumber;
        		$this->_heads[$headId] = $this->startHead($controller, $head, $headId, false);
        	}
        	$this->loop->addPeriodicTimer(5, function() use (&$_this) {
        		if (static::isPaused()) {
        			$anyRunning = false;
        			foreach ($_this->_heads as $head) {
        				if (isset($head) && $head->isRunning()) {
        					$anyRunning = true;
        				}
        			}
        			if (!$anyRunning) {
        				Yii::$app->end(0);
        			}
        		}
        	});
        	$this->loop->run();
        } elseif ($args[0] === 'sensorHandler') {
            $this->sensorHandlerHead($args[1]);
        } else {
            die("Unknown sub command");
        }
    }

    public function startHead($controller, $headType, $headId, $restarting)
    {
    	if (!$restarting) {
    		// Console::output(Console::ansiFormat('Starting ' . $headId, [Console::FG_CYAN]));
    	} else {
    		// Console::output(Console::ansiFormat('Restarting ' . $headId, [Console::FG_CYAN]));
    	}
    	$_this = $this;
    	$process = new \React\ChildProcess\Process($this->getSubCommand($controller, [$headType, $headId]));
    	$process->on('exit', function($exitCode, $termSignal) use (&$_this, &$controller, $headType, $headId) {
            if ($exitCode !== 0) {
    			Console::stderr(Console::ansiFormat("Broadcast head {$headType}:{$headId} exited with error code {$exitCode}", [Console::FG_RED]));
                sleep(10);
            }
            if (static::isPaused()) {
            	Yii::$app->end(0);
            }
            $_this->_heads[$headId] = $_this->startHead($controller, $headType, $headId, true);
        });
        $this->loop->addTimer(0.0001, function($timer) use ($process, &$_this) {
            $process->start($timer->getLoop());
            $process->stdout->on('data', function($output) use ($_this) {
                $stdout = fopen('php://stdout', 'w+');
                fwrite($stdout,$output);
            });
            $process->stderr->on('data', function($output) use ($_this) {
                $stderr = fopen('php://stderr', 'w+');
                fwrite($stderr,$output);
            });
        });
        sleep(5);
        return $process;
    }

    public function tickCallback($ticks)
    {
    	if (static::isPaused()) {
    		return true;
    	}
    	if ($ticks > 100) {
    		return true;
    	}
        return false;
    }

    private function checkFailTaken($id, $headId)
    {
        $key = __CLASS__ . __FUNCTION__ . $id;
        $checkResult = Cacher::get($key);
        if (!empty($checkResult) && $checkResult !== $headId) {
            return true;
        }
        Cacher::set($key, $headId, 60*10);
        return false;
    }

    public function sensorHandlerHead($headId)
    {
        $limitPerTick = 10;
        $tickCallback = [$this, 'tickCallback'];
        $ticks = 0;
        $self = $this;
        $exitStatus = 0;
        $timer = $this->loop->addPeriodicTimer(rand(1, 10)/1000, function() use ($self, $headId, $limitPerTick, $tickCallback, &$exitStatus, &$ticks, &$timer) {
            $ticks++;
            $sensorsToCheck = Sensor::find()->where(['and', ['active' => 1], '[[next_check]] <= "' . date("Y-m-d G:i:s") .'"'])->orderBy(['next_check' => SORT_ASC])->limit($limitPerTick)->all();
            $skipCount = 0;
            $itemCount = count($sensorsToCheck);
            $sleepAfter = $itemCount === 0;
            foreach ($sensorsToCheck as $sensor) {
                if ($self->checkFailTaken('Sensor.'.$sensor->id.'-'.$sensor->next_check, $headId)) {
                    $skipCount++;
                    // echo "Another head is checking {$sensor->id}...".PHP_EOL; flush();
                    continue;
                }
                //  echo "{$headId} checking {$sensor->id}...".PHP_EOL; flush();
                if(!$sensor->dataObject->check($self->loop)) {
                    $sleepAfter = true;
                }
            }
            if ($skipCount === $limitPerTick) {
                $self->loop->cancelTimer($timer);
                $exitStatus = 1;
            }
            if (call_user_func($tickCallback, $ticks)) {
                $self->loop->cancelTimer($timer);
            }
            if ($sleepAfter) {
                sleep(5);
            }
        });
        $this->loop->run();
        Yii::$app->end($exitStatus);
    }
}
