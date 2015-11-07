<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensors\serviceReferences;

interface ServiceReferenceInterface
{
	public function getObject();
	public function getObjectType();
	public function getService();
}