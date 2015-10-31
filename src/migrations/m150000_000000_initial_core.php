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

        // source
        $this->dropExistingTable('source');
        $this->createTable('source', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'system_id' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'last_check' => 'datetime DEFAULT NULL',
            'next_check' => 'datetime DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('sourceRegistry', 'source', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

        // site
        $this->dropExistingTable('site');
        $this->createTable('site', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'source_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('siteRegistry', 'site', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('siteSource', 'site', 'source_id', 'source', 'id', 'CASCADE', 'CASCADE');

        // asset
        $this->dropExistingTable('asset');
        $this->createTable('asset', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'source_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'type' => 'string NOT NULL',
            'name' => 'string NOT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);
        $this->addForeignKey('assetRegistry', 'asset', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('assetSource', 'asset', 'source_id', 'source', 'id', 'CASCADE', 'CASCADE');

        // sensor
        $this->dropExistingTable('sensor');
        $this->createTable('sensor', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'system_id' => 'string NOT NULL',
            'state' => 'ENUM(\'unchecked\', \'normal\', \'high\', \'low\', \'error\', \'checkFail\') DEFAULT \'unchecked\'',
            'data' => 'longblob DEFAULT NULL',
            'resolution_attempts' => 'int DEFAULT 0',
            'last_resolution_attempt' => 'datetime DEFAULT NULL',
            'checked' => 'datetime DEFAULT NULL',
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

        return true;
    }

    public function down()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        $this->dropExistingTable('source');
        $this->dropExistingTable('site');
        $this->dropExistingTable('asset');
        $this->dropExistingTable('sensor');
        $this->dropExistingTable('sensor_event');

        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }
}
