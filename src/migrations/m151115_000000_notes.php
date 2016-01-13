<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\migrations;

class m151115_000000_notes extends \canis\db\Migration
{
    public function up()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();
        
        // provider
        $this->dropExistingTable('note');
        $this->createTable('note', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'title' => 'string DEFAULT NULL',
            'content' => 'longtext DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'created_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL',
            'modified_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
        ]);
        $this->addForeignKey('noteRegistry', 'note', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('noteObject', 'note', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('noteCreatedUser', 'note', 'created_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('noteModfiedUser', 'note', 'modified_user_id', 'user', 'id', 'SET NULL', 'SET NULL');

        $this->db->createCommand()->checkIntegrity(true)->execute();
        return true;
    }

    public function down()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        $this->dropExistingTable('note');

        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }
}
