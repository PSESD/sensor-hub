<?php
use canis\acl\security\Gatekeeper;
use canis\web\unifiedMenu\ApplicationComponent as UnifiedMenuComponent;
use canis\base\FileStorage;
use psesd\sensorHub\components\base\ClassManager;
use psesd\sensorHub\components\web\View;
use psesd\sensorHub\components\web\Response;
use psesd\sensorHub\Bootstrap;

use yii\log\FileTarget;
use yii\caching\DummyCache;
use yii\caching\FileCache;
use yii\redis\Cache as RedisCache;
use yii\i18n\Formatter as I18nFormatter;

defined('CANIS_ROLE_LEVEL_OWNER') 		|| define('CANIS_ROLE_LEVEL_OWNER', 600); // owner levels: 501-600
defined('CANIS_ROLE_LEVEL_MANAGER')		|| define('CANIS_ROLE_LEVEL_MANAGER', 500); // manager levels: 401-500
defined('CANIS_ROLE_LEVEL_EDITOR')		|| define('CANIS_ROLE_LEVEL_EDITOR', 400); // editor levels: 301-400
defined('CANIS_ROLE_LEVEL_COMMENTER')	|| define('CANIS_ROLE_LEVEL_COMMENTER', 300); // commenter levels: 201-300; doesn't exist in system
defined('CANIS_ROLE_LEVEL_VIEWER')		|| define('CANIS_ROLE_LEVEL_VIEWER', 200); // viewer levels: 101-200
defined('CANIS_ROLE_LEVEL_BROWSER')		|| define('CANIS_ROLE_LEVEL_BROWSER', 100); // viewer levels: 1-100

/* unline the other config files, base.php has a environment first approach */
if (!defined('CANIS_APP_CONFIG_PATH')) {
	$base = [];
} else {
	$localBasePath = CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'base.php';
	if (!file_exists($localBasePath)) {
		$base = [];
	} else {
		$base = include $localBasePath;
	}
}
$base['basePath'] = CANIS_APP_PATH;
$base['vendorPath'] = CANIS_APP_VENDOR_PATH;
$base['runtimePath'] = CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'runtime';
if (!isset($base['bootstrap'])) {
	$base['bootstrap'] = [];
}
$base['bootstrap'][] = 'collectors';
$base['bootstrap'][] = Bootstrap::class;
$base['extensions'] = include(CANIS_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'yiisoft' . DIRECTORY_SEPARATOR . 'extensions.php');

if (!isset($base['params'])) {
	$paramConfigPath = CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'params.php';
	if (!file_exists($paramConfigPath)) {
		$paramConfigPath = __DIR__ . DIRECTORY_SEPARATOR . 'params.php';
	}
	$base['params'] = include $paramConfigPath;
}

if (!isset($base['modules'])) {
    $base['modules'] = [];
}

$modulesConfigPath = CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'modules.php';
if (!file_exists($modulesConfigPath)) {
    $modulesConfigPath = __DIR__ . DIRECTORY_SEPARATOR . 'modules.php';
}
$base['modules'] = array_merge($base['modules'], include $modulesConfigPath);

if (!isset($base['components'])) {
	$base['components'] = [];
}
if (!isset($base['components']['classes'])) {
	$base['components']['classes'] = [
        'class' => ClassManager::className(),
    ];
}
if (!isset($base['components']['unifiedMenu'])) {
	$base['components']['unifiedMenu'] = [
        'class' => UnifiedMenuComponent::className(),
        'menus' => [
        	'account-management' => [
        		'label' => 'Account Management',
        		'providers' => [
        			\canis\userManager\controllers\ManageController::className(),
        			\canis\broadcaster\controllers\BaseController::className()
        		]
        	]
        ]
    ];
}
if (!isset($base['components']['db'])) {
	$base['components']['db'] = include 'database.php';
}
if (!isset($base['components']['collectors'])) {
	$collectorsConfigPath = CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'collectors.php';
	if (file_exists($collectorsConfigPath)) {
		$base['components']['collectors'] = include $collectorsConfigPath;
	} else {
    	$base['components']['collectors'] = include 'collectors.php';
	}
}
if (!isset($base['components']['cache'])) {
	if (isset($base['components']['redis'])) {
		$base['components']['cache'] = [
			'class' => RedisCache::className()
		];
	} else {
		$base['components']['cache'] = [
			'class' => DummyCache::className()
		];
	}
}
if (!isset($base['components']['fileCache'])) {
	$base['components']['fileCache'] = [
		'class' => FileCache::className()
	];
}
if (!isset($base['components']['gk'])) {
	$base['components']['gk'] = [
		'class' => Gatekeeper::className()
	];
}
if (!isset($base['components']['mailer'])) {
	$base['components']['mailer'] = [
		'class' => yii\swiftmailer\Mailer::className(),
		'enableSwiftMailerLogging' => true
	];
	if (isset($base['params']['mail']['transport'])) {
		$base['components']['mailer']['transport'] = $base['params']['mail']['transport'];
	}
}
if (class_exists('psesd\sensorHub\models\User')) {
	$base['components']['user'] = [
	    'class' => 'canis\auth\web\User',
	    'enableAutoLogin' => true,
	    'identityClass' => 'psesd\sensorHub\models\User',
	    'loginUrl' => ['/login'],
	];
}

if (!isset($base['components']['fileStorage'])) {
	$base['components']['fileStorage'] = [
		'class' => FileStorage::className()
	];
}
if (!isset($base['components']['view'])) {
	$base['components']['view'] = [
		'class' => View::className()
	];
}
if (!isset($base['components']['response'])) {
	$base['components']['response'] = [
		'class' => Response::className()
	];
}
if (!isset($base['components']['log'])) {
	$base['components']['log'] = [];
}
if (!isset($base['components']['log']['flushInterval'])) {
	$base['components']['log']['flushInterval'] = 1;
}
if (!isset($base['components']['log']['traceLevel'])) {
	$base['components']['log']['traceLevel'] = YII_DEBUG ? 3 : 0;
}
if (!isset($base['components']['log']['targets'])) {
	$base['components']['log']['targets'] = [];
}

$base['components']['log']['targets'][] = [
    'class' => FileTarget::className(),
    'levels' => ['error', 'warning'],
];

$base['components']['log']['targets'][] = [
    'class' => FileTarget::className(),
    'categories' => ['yii\swiftmailer\Logger::add'],
    'logFile' => "@runtime/logs/mail.log",
    'exportInterval' => 1
];
if (!isset($base['components']['formatter'] )) {
    $base['components']['formatter'] = [
        'class' => I18nFormatter::className(),
        'dateFormat' => 'MM/dd/yyyy',
    ];
}

if (YII_DEBUG) {
	if (!isset($base['components']['errorHandler'])) {
		$base['components']['errorHandler'] = [];
	}
	$base['components']['errorHandler']['discardExistingOutput'] = false;
}
return $base;
?>
