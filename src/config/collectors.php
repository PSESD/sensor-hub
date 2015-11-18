<?php
$lazyLoad = !(defined('IS_CONSOLE') && IS_CONSOLE);

$config = [
    'class' => 'canis\collector\Component',
    'cacheTime' => 120,
    'collectors' => [
        'roles' => include(CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'roles.php'),
        'identityProviders' => include(CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'identityProviders.php'),
        'storageEngines' => [
            'class' => 'canis\storage\components\Collector',
            'initialItems' => [
                'local' => [
                    'object' => [
                        'class' => 'canis\storage\components\handlers\Local',
                        'bucketFormat' => '{year}.{month}',
                        'baseDir' => CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'storage',
                    ],
                    'ensureGroups' => ['top'],
                ],
            ],
        ]
    ],
];

if (defined('CANIS_APP_S3_ACCESS_KEY') && !empty(CANIS_APP_S3_ACCESS_KEY)) {
    $s3 = [
        'object' => [
            'class' => 'canis\storage\components\handlers\S3',
            'bucketFormat' => CANIS_APP_ID . '.{year}.{month}',
            'accessKey' => CANIS_APP_S3_ACCESS_KEY,
            'secretKey' => CANIS_APP_S3_SECRET_KEY,
            'bucket' => CANIS_APP_S3_BUCKET,
            'reducedRedundancy' => CANIS_APP_S3_RRS,
            'region' => CANIS_APP_S3_REGION,
            'encrypt' => CANIS_APP_S3_ENCRYPT,
            'serveLocally' => CANIS_APP_S3_SERVE_LOCALLY
        ],
        'ensureGroups' => ['top'],
    ];
    $config['collectors']['storageEngines']['initialItems']['s3'] = $s3;
}
return $config;
