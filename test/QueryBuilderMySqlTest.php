<?php

class QueryBuilderMySqlTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // Enable logging
        ORM::configure('logging', true);

        // Set up the dummy database connection
        $db = new MockMySqlPDO('sqlite::memory:');
        ORM::set_db($db);
    }

    public function tearDown() {
        ORM::reset_config();
        ORM::reset_db();
    }

    public function testInsertOnDuplicateKeyUpdateNewRowCreated() {
        $widget = ORM::for_table('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->on_duplicate_key_update(array('name' => "'Fred'"));
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10') ON DUPLICATE KEY UPDATE `name` = 'Fred'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertOnDuplicateKeyUpdateNewRowCreatedWithExpression() {
        $widget = ORM::for_table('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->on_duplicate_key_update(array('age' => "`age` + 1"));
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10') ON DUPLICATE KEY UPDATE `age` = `age` + 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertOnDuplicateKeyUpdateUpdateExistingRow() {
        $widget = ORM::for_table('widget')->find_one(1);
        $widget->name = "George";
        $widget->age = 12;
        $widget->on_duplicate_key_update(array('age' => "12"));
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('George', '12') ON DUPLICATE KEY UPDATE `age` = 12";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertOnDuplicateKeyIgnoreNewRowCreatedIdEqualsId() {
        $widget = ORM::for_table('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->on_duplicate_key_ignore();
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10') ON DUPLICATE KEY UPDATE `id` = `id`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testInsertOnDuplicateKeyIgnoreNewRowCreatedCustomIdEqualsCustomId() {
        $widget = ORM::for_table('widget')->use_id_column('primary_key')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->on_duplicate_key_ignore();
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10') ON DUPLICATE KEY UPDATE `primary_key` = `primary_key`";
        $this->assertEquals($expected, ORM::get_last_query());
    }
}

