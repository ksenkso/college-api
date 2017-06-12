<?php

use yii\db\Migration;

class m170612_122536_groups_and_documents extends Migration
{
    public function up()
    {

    	$this->createTable(
    		'specs',
		    [
		    	'id' => $this->primaryKey(11),
			    'name' => $this->string(255),
			    'code' => $this->string(30)
		    ]
	    );

    	$this->batchInsert(
    		'specs',
		    ['id', 'name', 'code'],
		    [
		    	[1, 'Техническая эксплуатация и обслуживание электрического и электромеханического оборудования (по отраслям)', '13.02.11 '],
		    	[2, 'Теплоснабжение и теплотехническое оборудование', '13.02.02'],
			    [3, 'Программирование в компьютерных системах', '09.02.03'],
			    [4, 'Компьютерные системы и комплексы', '09.02.01'],
			    [5, 'Экономика и бухгалтерский учет (по отраслям)', '38.02.01'],
		    ]
	    );

    	$this->addColumn(
    		'group',
		    'spec_id',
		    'int(11) NOT NULL DEFAULT 3'
	    );



    }

    public function down()
    {
        echo "m170612_122536_groups_and_documents cannot be reverted.\n";

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
