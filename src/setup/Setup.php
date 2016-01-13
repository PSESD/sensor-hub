<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\setup;


/**
 * Setup Perform the web setup for the application.
 */
class Setup extends \canis\setup\Setup
{
	public function getSetupTaskConfig()
	{
		$tasks = [];
		$tasks[] = [
			'class' => tasks\Environment::className()
		];
		$tasks[] = [
			'class' => \canis\setup\tasks\Database::className()
		];
		$tasks[] = [
			'class' => tasks\Groups::className()
		];
		$tasks[] = [
			'class' => tasks\Acl::className()
		];
		$tasks[] = [
			'class' => tasks\AdminUser::className()
		];
		$tasks[] = [
			'class' => tasks\Collectors::className()
		];
		return $tasks;
	}
}
