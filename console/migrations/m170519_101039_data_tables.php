<?php

use yii\db\Migration;

class m170519_101039_data_tables extends Migration
{
    public function up()
    {

    	$this->createTable(
    		'events',
		    [
		    	'id' => \yii\db\Schema::TYPE_PK,
			    'user_id' => \yii\db\Schema::TYPE_INTEGER,
			    'title' => \yii\db\Schema::TYPE_STRING,
			    'timestamp' => \yii\db\Schema::TYPE_INTEGER,
			    'description' => \yii\db\Schema::TYPE_STRING,
			    'type_id' => \yii\db\Schema::TYPE_INTEGER,
			    'reported' => \yii\db\Schema::TYPE_INTEGER,
			    'report_type' => \yii\db\Schema::TYPE_INTEGER,
			    'form' => \yii\db\Schema::TYPE_STRING,
			    'results' => \yii\db\Schema::TYPE_STRING,
			    'responsible' => \yii\db\Schema::TYPE_STRING
		    ]
	    );
    	$this->createTable(
    		'attachments',
		    [
		    	'id' => \yii\db\Schema::TYPE_PK,
			    'title' => \yii\db\Schema::TYPE_STRING,
			    'thumbnail' => \yii\db\Schema::TYPE_STRING,
			    'source' => \yii\db\Schema::TYPE_STRING,
			    'user_id' => \yii\db\Schema::TYPE_INTEGER,
			    'type' => \yii\db\Schema::TYPE_INTEGER
		    ]
	    );
    	$this->createTable(
    		'document_types',
		    [
		    	'd_type_id' => \yii\db\Schema::TYPE_PK,
			    'name' => \yii\db\Schema::TYPE_STRING
		    ]
	    );
    	$this->createTable(
    		'event_types',
		    [
		    	'id' => \yii\db\Schema::TYPE_PK,
			    'name' => \yii\db\Schema::TYPE_STRING,
			    'color' => \yii\db\Schema::TYPE_STRING
		    ]
	    );

    	$this->batchInsert(
    		'event_types',
		    ['name', 'color'],
		    [
		    	['Спортивное', '#def'],
			    ['Культурное', '#ac4'],
			    ['Встреча с психологом', '#e0af98'],
			    ['Встреча с родителями', '#989ae0'],
		    ]
	    );



    	$this->createTable(
    		'family',
		    [
		        'id' => \yii\db\Schema::TYPE_PK,
			    'user_id' => \yii\db\Schema::TYPE_INTEGER,
			    'p_name' => \yii\db\Schema::TYPE_STRING,
			    'p_employment' => \yii\db\Schema::TYPE_STRING,
			    'p_phone' => \yii\db\Schema::TYPE_STRING,
			    'p_address' => \yii\db\Schema::TYPE_STRING
		    ]
	    );
    	$this->createTable(
    		'group',
		    [
		    	'id' => \yii\db\Schema::TYPE_PK,
			    'name' => \yii\db\Schema::TYPE_STRING,
			    'abbreviation' => \yii\db\Schema::TYPE_STRING,
			    'year' => \yii\db\Schema::TYPE_STRING
		    ]
	    );

    	$this->createTable(
    		'portfolio',
		    [
		    	'id' => \yii\db\Schema::TYPE_PK,
			    'user_id' => $this->integer(11)->notNull(),
			    'record_type' => $this->integer(11)->notNull(),
			    'datePlace' => $this->string(255)->null(),
			    'content' => $this->string(255)->null(),
			    'description' => $this->string(255)->null()
		    ]
	    );
    	$this->createTable(
    		'protocol',
		    [
		    	'id' => $this->primaryKey(11),
			    'user_id' => $this->integer(11)->notNull(),
			    'theme' => $this->string(255)->notNull(),
			    'purposes' => $this->string(255)->notNull(),
			    'form' => $this->string(255)->notNull(),
			    'date' => $this->integer(11)->notNull(),
			    'plan' => $this->text()->null(),
			    'organization' => $this->text()->null(),
			    'analysis' => $this->text()->null(),
			    'conclusions' => $this->text()->null()
		    ]
	    );

    	$this->createTable(
    		'tmp_hours',
		    [
		    	'hours_id' => $this->primaryKey(11),
			    'date' => $this->date()->notNull(),
			    'student_id'=> $this->integer(11)->notNull(),
			    'group_id' => $this->integer(11)->notNull(),
			    'hours' => $this->string(4)->notNull()->defaultExpression('0000'),
			    'hours_good' => $this->string(4)->notNull()->defaultExpression('0'),
			    'is_good' => $this->integer(1)
		    ]
	    );
    	$this->createTable(
    		'today',
		    [
		    	'id' => $this->primaryKey(11),
			    'student_id' => $this->integer(11)->notNull(),
			    'group_id' => $this->integer(11)->notNull(),
			    'hours' => $this->string(4)->notNull(),
			    'hours_good' => $this->string(4)->notNull(),
			    'is_good' => $this->integer(1)->notNull()->defaultExpression('0')
		    ]
	    );

    	$this->addColumn('user', 'group_id', 'int(11) not null');
	    $this->addColumn('user', 'first_name', 'varchar(255) null');
	    $this->addColumn('user', 'last_name', 'varchar(255) null');
	    $this->addColumn('user', 'patronymic', 'varchar(255) null');
	    $this->addColumn('user', 'access_token', 'int(11) not null');
	    $this->addColumn('user', 'address', 'varchar(255) null');
	    $this->addColumn('user', 'phone', 'varchar(20) null');
    	$this->addColumn('user', 'sex', 'varchar(3) null');
    	$this->addColumn('user', 'birth_date', 'int(11) null');

    	$this->batchInsert(
    		'user',
		    [
		    	'id',
			    'username',
			    'auth_key',
			    'password_hash',
			    'password_reset_token',
			    'email',
			    'status',
			    'created_at',
			    'updated_at',
			    'group_id',
			    'first_name',
			    'last_name',
			    'patronymic',
			    'access_token',
			    'address',
			    'phone',
			    'sex',
			    'birth_date'],
		    [
				[
					1,
					'admin',
					'8oEuzmQg974WnRPNY5n0An_oHoZrRMLN',
					Yii::$app->security->generatePasswordHash(getenv('ADMIN_PASSWORD')),
					'8YiUsybdkBCfIteMC2EMoy_3uu3pSWPs',
					'admin@localhost',
					10,
					1495142241,
					1495142241,
					1,
					'system',
					'admin',
					'S',
					'hjyUDZcGTfdSeEUaKuT8Q3rQ-xfnYuGc',
					NULL,
					NULL,
					'',
					0
				]
		    ]
	    );
    	$this->batchInsert(
    		'auth_item',
		    ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'],
		    [
			    ['admin',1,'Администратор',NULL,NULL,1495142075,1495142075],
			    ['navCabinet',2,'Navigate to Cabinet',NULL,NULL,1495142075,1495142075],
			    ['navCalendar',2,'Navigate to Calendar',NULL,NULL,1495142075,1495142075],
			    ['navDashboard',2,'Navigate to Dashboard',NULL,NULL,1495142075,1495142075],
			    ['navDocuments',2,'Navigate to Documents',NULL,NULL,1495142075,1495142075],
			    ['navGroups',2,'Navigate to Groups',NULL,NULL,1495142075,1495142075],
			    ['navPortfolio',2,'Navigate to Portfolio',NULL,NULL,1495142075,1495142075],
			    ['navStudents',2,'Navigate to Students',NULL,NULL,1495142075,1495142075],
			    ['navUsers',2,'Navigate to Users',NULL,NULL,1495142075,1495142075],
			    ['steward',1,'Стаорста',NULL,NULL,1495142075,1495142075],
			    ['student',1,'Студент',NULL,NULL,1495142075,1495142075],
			    ['teacher',1,'Учитель',NULL,NULL,1495142075,149514207]

		    ]
	    );
    	$this->batchInsert(
    		'auth_item_child',
		    ['parent', 'child'],
		    [
			    ['steward','navCabinet'],
			    ['teacher','navCabinet'],
			    ['teacher','navCalendar'],
			    ['admin','navDashboard'],
			    ['student','navDashboard'],
			    ['teacher','navDashboard'],
			    ['teacher','navDocuments'],
			    ['admin','navGroups'],
			    ['steward','navPortfolio'],
			    ['teacher','navPortfolio'],
			    ['teacher','navStudents'],
			    ['admin','navUsers'],
			    ['admin','teacher']
		    ]
	    );

    	$this->insert(
    		'group',
		    [
		    	1,
			    '',
			    getenv('ADMIN_GROUP'),
			    ''
		    ]
	    );

    	$this->createTable(
    		'user_meta',
		    [
		    	'id' => $this->primaryKey(11),
			    'user_id' => $this->integer(11)->notNull(),
			    'meta_key' => $this->string(50)->notNull(),
			    'meta_value' => $this->string(1500)->notNull()
		    ]
	    );
    }

    public function down()
    {
        echo "m170519_101039_data_tables cannot be reverted.\n";

        return false;
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
