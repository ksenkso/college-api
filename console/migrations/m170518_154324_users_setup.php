<?php

use yii\db\Migration;

class m170518_154324_users_setup extends Migration
{
    public function up()
    {
    	$this->execute('ALTER TABLE `user` ADD COLUMN `group_id` INT (11) NULL');
    	$this->execute('ALTER TABLE `user` ADD COLUMN `first_name` VARCHAR (255) NULL');
	    $this->execute('ALTER TABLE `user` ADD COLUMN `last_name` VARCHAR (255) NULL');
    	$this->execute('ALTER TABLE `user` ADD COLUMN `patronymic` VARCHAR (255) NULL');
    	$this->execute('ALTER TABLE `user` ADD COLUMN `access_token` VARCHAR (255) NULL');

	    $this->batchInsert(
		    'auth_item',
		    ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'],
		    [
			    ['admin', 1, 'Admin', NULL, NULL, time(), time()],
			    ['student', 1, 'Student', NULL, NULL, time(), time()],
			    ['steward', 1, 'Admin', NULL, NULL, time(), time()],
			    ['teacher', 1, 'Admin', NULL, NULL, time(), time()],
			    ['navDashboard', 2, 'Navigate to Dashboard', NULL, NULL, time(), time()],
			    ['navCalendar', 2, 'Navigate to Calendar', NULL, NULL, time(), time()],
			    ['navStudents', 2, 'Navigate to Students', NULL, NULL, time(), time()],
			    ['navUsers', 2, 'Navigate to Users', NULL, NULL, time(), time()],
			    ['navGroups', 2, 'Navigate to Groups', NULL, NULL, time(), time()],
			    ['navCabinet', 2, 'Navigate to Cabinet', NULL, NULL, time(), time()],
			    ['navPortfolio', 2, 'Navigate to Portfolio', NULL, NULL, time(), time()],
			    ['navDocuments', 2, 'Navigate to Documents', NULL, NULL, time(), time()],
		    ]);

	    $this->batchInsert(
		    'auth_item_child',
		    ['parent', 'child'],
		    [
			    ['student', 'navDashboard'],
			    ['steward', 'navPortfolio'],
			    ['steward', 'navCabinet'],
			    ['teacher', 'navPortfolio'],
			    ['teacher', 'navCalendar'],
			    ['teacher', 'navStudents'],
			    ['teacher', 'navCabinet'],
			    ['teacher', 'navDocuments'],
			    ['teacher', 'navDashboard'],
			    ['admin', 'teacher'],
			    ['admin', 'navUsers'],
			    ['admin', 'navGroups'],
			    ['admin', 'navDashboard'],
		    ]
	    );

    	$this->insert(
    		'user',
		    [
		    	// 'id' => 1,
			    'group_id' => NULL,
			    'first_name' => 'system',
			    'last_name' => 'admin',
			    'patronymic' => '',
			    'username' => 'admin',
			    'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
			    'password_reset_token' => Yii::$app->security->generateRandomString(),
			    'auth_key' => Yii::$app->security->generateRandomString(),
			    'email' => 'admin@localhost',
			    'status' => 10,
			    'created_at' => time(),
			    'updated_at' => time()
		    ]
	    );

    	$this->batchInsert(
    		'auth_assignment',
		    ['item_name', 'user_id', 'created_at'],
		    [['admin', 1, time()]]
	    );

    }

    public function down()
    {

		$this->delete('auth_item_child');
		$this->delete('auth_item');
		$this->delete('auth_assignment');
		$this->delete('user');
		$this->delete('');
		$this->execute('ALTER TABLE `user` DELETE COLUMN group_id, first_name, last_name, patronymic, access_token');

    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
