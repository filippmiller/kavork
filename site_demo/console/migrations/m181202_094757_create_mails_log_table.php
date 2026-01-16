<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%mails_log}}`.
 */
class m181202_094757_create_mails_log_table extends Migration
{
  public $db = 'db';

  public $tableName = '{{%mails_log}}';

  public function safeUp()
  {
    $this->createTable($this->tableName, [
        'id' => $this->primaryKey()->notNull(),
        'name' => $this->string()->null(),
        'content' => $this->text()->null(),
        'params' => $this->text()->null(),
        'last_visitor_id' => $this->integer()->defaultValue(0)->null(),
        'count' => $this->integer()->defaultValue(0)->null(),
        'status' => $this->integer()->defaultValue(0)->null(),
        'mail_id' => $this->integer()->null(),
        'user_id' => $this->integer()->null(),
        'cafe_id' => $this->integer()->null(),
        'cteated_at' => $this->timestamp()->null(),
    ], 'ENGINE InnoDB');

    // creates index for column `id`
    $this->createIndex(
        'idx-mails_log-id',
        $this->tableName,
        'id'
    );

    // add foreign key for table `{{%mails}}`
    $this->addForeignKey(
        'fk-mails_log-mail_id-mails-id',
        $this->tableName,
        'mail_id',
        '{{%template_mails}}',
        'id',
        'SET NULL',
        'CASCADE'
    );
    // add foreign key for table `{{%user}}`
    $this->addForeignKey(
        'fk-mails_log-user_id-user-id',
        $this->tableName,
        'user_id',
        '{{%user}}',
        'id',
        'SET NULL',
        'CASCADE'
    );
    // add foreign key for table `{{%cafe}}`
    $this->addForeignKey(
        'fk-mails_log-cafe_id-cafe-id',
        $this->tableName,
        'cafe_id',
        '{{%cafe}}',
        'id',
        'SET NULL',
        'CASCADE'
    );

  }

  public function safeDown()
  {

    // drop index for column `id`
    $this->dropIndex(
        'idx-mails_log-id',
        $this->tableName
    );

    // drop foreign key for table `{{%mails}}`
    $this->dropForeignKey(
        'fk-mails_log-mail_id-mails-id',
        $this->tableName
    );
    // drop foreign key for table `{{%user}}`
    $this->dropForeignKey(
        'fk-mails_log-user_id-user-id',
        $this->tableName
    );
    // drop foreign key for table `{{%cafe}}`
    $this->dropForeignKey(
        'fk-mails_log-cafe_id-cafe-id',
        $this->tableName
    );
    $this->dropTable($this->tableName);
  }
}

