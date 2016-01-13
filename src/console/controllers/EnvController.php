<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\console\controllers;

use Yii;

class EnvController extends \canis\console\controllers\EnvController
{
	public function actionDb()
	{
		$this->stdout("Database: ". gethostbyname(CANIS_APP_DATABASE_HOST) .PHP_EOL);
	}
}
?>