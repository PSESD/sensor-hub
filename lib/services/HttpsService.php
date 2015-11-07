<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensors\services;

class HttpsService extends HttpService
{
	public function getDefaultName()
	{
		return 'Web (SSL)';
	}
	public function getDefaultId()
	{
		return 'https';
	}
}