<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\console\controllers;

use Yii;

class EnvController extends \canis\console\controllers\EnvController
{
	public function actionDb()
	{
		$this->stdout("Database: ". gethostbyname(CANIS_APP_DATABASE_HOST) .PHP_EOL);
	}
}
?>