<?php
/**
 * @link https://www.psesd.org
 *
 * @copyright Copyright (c) 2016 Puget Sound ESD
 * @license https://raw.githubusercontent.com/PSESD/sensor-hub/master/LICENSE
 */

namespace psesd\sensorHub\migrations;

class m151118_000000_contacts extends \canis\db\Migration
{
    public function up()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();
        
        // contact
        $this->dropExistingTable('contact');
        $this->createTable('contact', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'object_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'is_billing' => 'bool NOT NULL DEFAULT 0',
            'is_technical' => 'bool NOT NULL DEFAULT 0',
            'first_name' => 'string DEFAULT NULL',
            'last_name' => 'string DEFAULT NULL',
            'email' => 'string DEFAULT NULL',
            'phone' => 'string DEFAULT NULL',
            'note' => 'string DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'created_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL',
            'modified_user_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
        ]);
        $this->addForeignKey('contactRegistry', 'contact', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('contactObject', 'contact', 'object_id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('contactCreatedUser', 'contact', 'created_user_id', 'user', 'id', 'SET NULL', 'SET NULL');
        $this->addForeignKey('contactModfiedUser', 'contact', 'modified_user_id', 'user', 'id', 'SET NULL', 'SET NULL');

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
