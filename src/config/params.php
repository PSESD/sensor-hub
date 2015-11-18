<?php
$params = [];
$params['defaultStorageEngine'] = 'local';
$params['migrationAliases'] = [];
$params['migrationAliases'][] = '@canis/sensorHub/migrations';

$params['cloudStorageEngine'] = false;
if (defined('CANIS_APP_S3_ACCESS_KEY') && !empty(CANIS_APP_S3_ACCESS_KEY)) {
    $params['cloudStorageEngine'] = 's3';
}

$mailConfigPath = CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'mail.php';
if (file_exists($mailConfigPath)) {
    $params['mail'] = include $mailConfigPath;
}
return $params;