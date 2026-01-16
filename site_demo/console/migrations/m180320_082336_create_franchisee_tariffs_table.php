<?php
use yii\db\Migration;
/**
* Handles the creation of table `{{%franchisee_tariffs}}`.
*/
class m180320_082336_create_franchisee_tariffs_table extends Migration
{
    public $db = 'db';

    public $tableName = '{{%franchisee_tariffs}}';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->notNull()->comment('ID'),
            'name' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'label' => $this->integer()->null()->defaultValue(0),
            'cafe_count' => $this->integer()->notNull(),
            'roles' => $this->text()->notNull(),
            'day_price' => $this->float()->notNull(),
            'days_period' => $this->integer()->notNull()->defaultValue(30),
            'active' => $this->integer()->null(),
            'created_at' => $this->string()->null(),
        ]);

        // creates index for column `id`
        $this->createIndex(
            'idx-franchisee_tariffs-id',
            $this->tableName,
            'id'
        );


    }

    public function safeDown()
    {

        // drop index for column `id`
        $this->dropIndex(
            'idx-franchisee_tariffs-id',
            $this->tableName
        );

        $this->dropTable($this->tableName);
    }
}

