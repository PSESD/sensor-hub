<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensors\servers;

class DockerServer extends LinuxServer
{
	public function getObjectTypeDescriptor()
    {
        return 'Docker Server';
    }
}