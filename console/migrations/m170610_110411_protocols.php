<?php

use yii\db\Migration;

class m170610_110411_protocols extends Migration
{
    public function up()
    {

    	$this->addColumn(
    		'protocols',
		    'type',
		    'int(6) NOT NULL'
	    );

	    $this->addColumn(
		    'protocols',
		    'number',
		    'int(6) NOT NULL DEFAULT 0'
	    );

	    $this->alterColumn(
	    	'protocol',
		    'analysis',
		    'longtext NULL'
	    );

	    $this->alterColumn(
		    'protocol',
		    'form',
		    'varchar(255) NULL'
	    );

	    $this->alterColumn(
		    'protocol',
		    'organization',
		    'longtext NULL'
	    );

    	$this->createTable(
    		'protocol_type',
		    [
		    	'id' => $this->primaryKey(6),
			    'name' => $this->string(255)->notNull()
		    ]
	    );

    	$this->batchInsert(
    		'protocol_type',
		    ['id', 'name'],
		    [
		    	[1, 'Внеклассное мероприятие'],
			    [2, 'Протокол родительского собрания']
		    ]
	    );

    	$this->addColumn(
    		'family',
		    'trouble',
            "varchar(500) NULL DEFAULT ''"
	    );

    	$this->addColumn(
		    'family',
		    'edu_type',
		    "varchar(500) NULL DEFAULT ''"
	    );

	    $this->addColumn(
		    'family',
		    'consist',
		    "varchar(500) NULL DEFAULT ''"
	    );
	    $this->addColumn(
		    'family',
		    'purposes',
		    "varchar(500) NULL DEFAULT ''"
	    );

	    $this->alterColumn(
	    	'family',
		    'p_employment',
		    "varchar(500) NULL DEFAULT ''"
	    );

	    $this->alterColumn(
		    'family',
		    'p_address',
		    "varchar(500) NULL DEFAULT ''"
	    );

    }

    public function down()
    {
        $this->dropTable('protocol_type');

        return true;
    }

}
