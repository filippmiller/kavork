<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%franchisee_payments}}`.
 */
class m190401_075856_create_franchisee_payments_table extends Migration
{
  public $db = 'db';

  public $tableName = '{{%franchisee_payments}}';

  public $tableFranchisee = '{{%franchisee}}';

  public function safeUp()
  {

    $dbh = Yii::$app->getDb();
    $dbh = $dbh->createCommand("SHOW COLUMNS FROM franchisee;")->queryAll();
    $dbh = \yii\helpers\ArrayHelper::map($dbh, 'Field', 'Field');
    if (!isset($dbh['max_cafe'])) {
      $this->addColumn($this->tableFranchisee, 'max_cafe', $this->integer()->defaultValue(1)->after('name'));
    }
    if (!isset($dbh['date_end'])) {
      $this->addColumn($this->tableFranchisee, 'date_end', $this->dateTime()->after('name'));
    }
    if (!isset($dbh['tariff_id'])) {
      $this->addColumn($this->tableFranchisee, 'tariff_id', $this->integer()->after('name'));
    }

    $this->createTable($this->tableName, [
        'id' => $this->primaryKey()->notNull()->comment('ID'),
        'franchisee_id' => $this->integer()->null(),
        'count' => $this->integer()->notNull(),
        'sum' => $this->float()->notNull(),
        'code' => $this->string()->notNull(),
        'status' => $this->integer()->null(),
        'tariff_id' => $this->integer()->null(),
        'comment' => $this->string()->null(),
        'data' => $this->json(),
        'created_at' => $this->string()->null(),
    ]);

    $this->execute("ALTER TABLE `franchisee_tariffs` ENGINE = INNODB;");
    $this->execute("ALTER TABLE `transaction` ENGINE = INNODB;");

    // creates index for column `id`
    $this->createIndex(
        'idx-franchisee_payments-id',
        $this->tableName,
        'id'
    );

    // add foreign key for table `{{%franchisee}}`
    $this->addForeignKey(
        'fk-franchisee_payments-franchisee_id-franchisee-id',
        $this->tableName,
        'franchisee_id',
        '{{%franchisee}}',
        'id'
    );


    $this->addForeignKey(
        'fk-franchisee_payments-tariff_id-tariff-id',
        $this->tableName,
        'tariff_id',
        '{{%franchisee_tariffs}}',
        'id'
    );
  }

  public function safeDown()
  {

    // drop index for column `id`
    $this->dropIndex(
        'idx-franchisee_payments-id',
        $this->tableName
    );

    // drop foreign key for table `{{%franchisee}}`
    $this->dropForeignKey(
        'fk-franchisee_payments-franchisee_id-franchisee-id',
        $this->tableName
    );

    // drop foreign key for table `{{%franchisee}}`
    $this->dropForeignKey(
        'fk-franchisee_payments-tariff_id-tariff-id',
        $this->tableName
    );
    $this->dropTable($this->tableName);
  }
}

