<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\sensorHub\migrations;

class m150000_000000_initial_core extends \canis\db\Migration
{
    public function up()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();
        $this->db->createCommand('ALTER DATABASE '. CANIS_APP_DATABASE_DBNAME .' charset=utf8mb4')->execute();

        // provider
        $this->dropExistingTable('provider');
        $this->createTable('provider', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'system_id' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'last_check' => 'datetime DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('providerRegistry', 'provider', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');


        // server
        $this->dropExistingTable('server');
        $this->createTable('server', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'provider_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('serverRegistry', 'server', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('serverProvider', 'server', 'provider_id', 'provider', 'id', 'CASCADE', 'CASCADE');

        // site
        $this->dropExistingTable('site');
        $this->createTable('site', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'provider_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('siteRegistry', 'site', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('siteProvider', 'site', 'provider_id', 'provider', 'id', 'CASCADE', 'CASCADE');

        // service
        $this->dropExistingTable('service');
        $this->createTable('service', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('serviceRegistry', 'service', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('serviceObject', 'service', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');

         // service_reference
        $this->dropExistingTable('service_reference');
        $this->createTable('service_reference', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'service_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'type' => 'ENUM(\'binding\', \'connection\', \'other\') DEFAULT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('serviceReferenceRegistry', 'service_reference', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('serviceReferenceObject', 'service_reference', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('serviceReferenceService', 'service_reference', 'service_id', 'service', 'id', 'CASCADE', 'CASCADE');

        // resource_reference
        $this->dropExistingTable('resource_reference');
        $this->createTable('resource_reference', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'resource_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'type' => 'ENUM(\'dedicated\', \'shared\', \'other\') DEFAULT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('resourceReferenceRegistry', 'resource_reference', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('resourceReferenceObject', 'resource_reference', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('resourceReferenceResource', 'resource_reference', 'resource_id', 'resource', 'id', 'CASCADE', 'CASCADE');


        // resource
        $this->dropExistingTable('resource');
        $this->createTable('resource', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'type' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('resourceRegistry', 'resource', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('resourceObject', 'resource', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');

        // sensor
        $this->dropExistingTable('sensor');
        $this->createTable('sensor', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'state' => 'ENUM(\'unchecked\', \'normal\', \'high\', \'low\', \'error\', \'checkFail\') DEFAULT \'unchecked\'',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'resolution_attempts' => 'int DEFAULT 0',
            'last_resolution_attempt' => 'datetime DEFAULT NULL',
            'last_check' => 'datetime DEFAULT NULL',
            'next_check' => 'datetime DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL'
        ]);

        $this->createIndex('sensorObject', 'sensor', 'object_id', false);
        $this->createIndex('sensorState', 'sensor', 'state', false);
        $this->addForeignKey('sensorRegistry', 'sensor', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('sensorObject', 'sensor', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');

        // sensor_event
        $this->dropExistingTable('sensor_event');
        $this->createTable('sensor_event', [
            'id' => 'bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'sensor_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'old_state' => 'ENUM(\'unchecked\', \'normal\', \'high\', \'low\', \'error\', \'checkFail\') DEFAULT NULL',
            'new_state' => 'ENUM(\'unchecked\', \'normal\', \'high\', \'low\', \'error\', \'checkFail\') DEFAULT NULL',
            'data' => 'longblob DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL'
        ]);
        $this->createIndex('sensorEventSensor', 'sensor_event', 'sensor_id', false);
        $this->createIndex('sensorEventOldState', 'sensor_event', 'old_state', false);
        $this->createIndex('sensorEventNewState', 'sensor_event', 'new_state', false);
        $this->createIndex('sensorEventCreated', 'sensor_event', 'created', false);
        $this->createIndex('sensorEventCreatedSensor', 'sensor_event', 'sensor_id,created', false);
        $this->addForeignKey('sensorEventSensor', 'sensor_event', 'sensor_id', 'sensor', 'id', 'CASCADE', 'CASCADE');


        // sensor_data
        $this->dropExistingTable('sensor_data');
        $this->createTable('sensor_data', [
            'id' => 'bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'sensor_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'value' => 'decimal(10, 2) NOT NULL',
            'created' => 'datetime DEFAULT NULL'
        ]);
        $this->createIndex('sensorDataSensor', 'sensor_data', 'sensor_id', false);
        $this->createIndex('sensorDataSensorCreated', 'sensor_data', 'sensor_id,created', false);
        $this->addForeignKey('sensorDataSensor', 'sensor_data', 'sensor_id', 'sensor', 'id', 'CASCADE', 'CASCADE');

        $this->db->createCommand()->checkIntegrity(true)->execute();
        return true;
    }

    public function down()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        $this->dropExistingTable('provider');
        $this->dropExistingTable('site');
        $this->dropExistingTable('asset');
        $this->dropExistingTable('sensor');
        $this->dropExistingTable('sensor_event');

        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }
}
