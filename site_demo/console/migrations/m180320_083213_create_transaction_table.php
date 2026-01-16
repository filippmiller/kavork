<?php
use yii\db\Migration;
/**
* Handles the creation of table `{{%transaction}}`.
*/
class m180320_083213_create_transaction_table extends Migration
{
    public $db = 'db';

    public $tableName = '{{%transaction}}';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->notNull()->comment('ID'),
            'sum' => $this->float()->notNull(),
            'cost' => $this->float()->notNull(),
            'method' => $this->integer()->notNull()->defaultValue(0),
            'terminal_ans' => $this->integer(1)->defaultValue(0),
            'visit_id' => $this->integer()->null(),
            'sale_id' => $this->integer()->null(),
            'visitor_id' => $this->integer()->null(),
            'pay_man' => $this->integer()->null(),
            'cafe_id' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
        ]);

        // creates index for column `id`
        $this->createIndex(
            'idx-transaction-id',
            $this->tableName,
            'id'
        );

        // add foreign key for table `{{%visitor_log}}`
        $this->addForeignKey(
            'fk-transaction-visit_id-visitor_log-id',
            $this->tableName,
            'visit_id',
            '{{%visitor_log}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        // add foreign key for table `{{%visitor}}`
        $this->addForeignKey(
            'fk-transaction-visitor_id-visitor-id',
            $this->tableName,
            'visitor_id',
            '{{%visitor}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
      $this->addForeignKey(
          'fk-transaction-pay_man-visitor-id',
          $this->tableName,
          'pay_man',
          '{{%visitor_log}}',
          'id',
          'SET NULL',
          'CASCADE'
      );
    }

    public function safeDown()
    {

        // drop index for column `id`
        $this->dropIndex(
            'idx-transaction-id',
            $this->tableName
        );

        // drop foreign key for table `{{%visitor_log}}`
        $this->dropForeignKey(
            'fk-transaction-visit_id-visitor_log-id',
            $this->tableName
        );
        // drop foreign key for table `{{%visitor}}`
        $this->dropForeignKey(
            'fk-transaction-visitor_id-visitor-id',
            $this->tableName
        );
        $this->dropForeignKey(
            'fk-transaction-pay_man-visitor-id',
            $this->tableName
        );
        $this->dropTable($this->tableName);
    }
}

