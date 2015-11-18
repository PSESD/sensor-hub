<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace canis\sensorHub\setup\tasks;

use canis\auth\models\Group;
use canis\sensorHub\models\Relation;

use Clue\React\Docker\Factory as DockerFactory;
use Clue\React\Docker\Client as DockerClient;
use Clue\React\Block;
/**
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Environment extends \canis\setup\tasks\Environment
{
    public function getExtraInput(&$input)
    {
        $input['s3'] = [];
        $input['s3']['accessKey'] = CANIS_APP_S3_ACCESS_KEY;
        $input['s3']['secretKey'] = CANIS_APP_S3_SECRET_KEY;
        $input['s3']['region'] = CANIS_APP_S3_REGION;
        $input['s3']['bucket'] = CANIS_APP_S3_BUCKET;
        $input['s3']['encrypt'] = CANIS_APP_S3_ENCRYPT;
        $input['s3']['rrs'] = CANIS_APP_S3_RRS;
        $input['s3']['serveLocally'] = CANIS_APP_S3_SERVE_LOCALLY;

        $input['app']['host'] = CANIS_APP_WEB_HOST;


    }
    public function getFields()
    {
        $webHostDefault = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

        $s3AccessKeyDefault = '';
        $s3SecretKeyDefault = '';
        $s3BucketDefault = '';
        $s3RegionDefault = '';
        $s3EncryptDefault = true;
        $s3RRSDefault = true;
        $s3ServeLocallyDefault = false;

        $fields = parent::getFields();
        $fields['s3'] = ['label' => 'S3', 'fields' => []];
        $fields['s3']['fields']['accessKey'] = ['type' => 'text', 'label' => 'Access Key', 'required' => true, 'value' => function () use ($s3AccessKeyDefault) { return defined('CANIS_APP_S3_ACCESS_KEY') && CANIS_APP_S3_ACCESS_KEY ? CANIS_APP_S3_ACCESS_KEY : $s3AccessKeyDefault; }];
        $fields['s3']['fields']['secretKey'] = ['type' => 'text', 'label' => 'Secret Key', 'required' => true, 'value' => function () use ($s3SecretKeyDefault) { return defined('CANIS_APP_S3_SECRET_KEY') && CANIS_APP_S3_SECRET_KEY ? CANIS_APP_S3_SECRET_KEY : $s3SecretKeyDefault; }];
        $fields['s3']['fields']['bucket'] = ['type' => 'text', 'label' => 'Bucket', 'required' => true, 'value' => function () use ($s3BucketDefault) { return defined('CANIS_APP_S3_BUCKET') && CANIS_APP_S3_BUCKET ? CANIS_APP_S3_BUCKET : $s3BucketDefault; }];
        $fields['s3']['fields']['region'] = ['type' => 'text', 'label' => 'Region', 'required' => true, 'value' => function () use ($s3RegionDefault) { return defined('CANIS_APP_S3_REGION') && CANIS_APP_S3_REGION ? CANIS_APP_S3_REGION : $s3RegionDefault; }];
        $fields['s3']['fields']['encrypt'] = ['type' => 'select', 'label' => 'Encrypt', 'required' => true, 'options' => ['true' => 'Yes', 'false' => 'No'], 'value' => function () use ($s3EncryptDefault) { return defined('CANIS_APP_S3_ENCRYPT') && CANIS_APP_S3_ENCRYPT ? CANIS_APP_S3_ENCRYPT : $s3EncryptDefault; }];
        $fields['s3']['fields']['rrs'] = ['type' => 'select', 'label' => 'Reduce Redudancy', 'required' => true, 'options' => ['true' => 'Yes', 'false' => 'No'], 'value' => function () use ($s3RRSDefault) { return defined('CANIS_APP_S3_ENCRYPT') && CANIS_APP_S3_RRS ? CANIS_APP_S3_RRS : $s3RRSDefault; }];
        $fields['s3']['fields']['serveLocally'] = ['type' => 'select', 'label' => 'Serve Locally', 'required' => true, 'options' => ['true' => 'Yes', 'false' => 'No'], 'value' => function () use ($s3ServeLocallyDefault) { return defined('CANIS_APP_S3_SERVE_LOCALLY') && CANIS_APP_S3_SERVE_LOCALLY ? CANIS_APP_S3_SERVE_LOCALLY : $s3ServeLocallyDefault; }];
        
        $fields['app']['fields']['host'] = ['type' => 'text', 'label' => 'Web Host', 'required' => true, 'value' => function () use ($webHostDefault) { return defined('CANIS_APP_WEB_HOST') && CANIS_APP_WEB_HOST ? CANIS_APP_WEB_HOST : $webHostDefault; }];

        return $fields;
    }
}
