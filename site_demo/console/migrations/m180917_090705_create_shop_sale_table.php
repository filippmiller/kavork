<?php

use yii\db\Migration;

/**
 * Handles the creation of table `show_transaction`.
 */
class m180917_090705_create_shop_sale_table extends Migration
{
  public $tableName = '{{%shop_sale}}';
  public $cafeTableName = '{{%cafe}}';

  public $visitorTableName = '{{%visitor}}';
  public $visitorLogTableName = '{{%visitor_log}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable($this->tableName, [
        'id' => $this->primaryKey(),
        'cafe_id' => $this->integer()->notNull(),
        'visitor_id' => $this->integer()->null(),
        'visitor_log_id' => $this->integer()->null(),

        'comment' => $this->text(),
        'data' => $this->json(),

        'pay_state' => $this->integer()->null()->defaultValue(0),
        'pay_man' => $this->integer()->null(),

        'created_by' => $this->integer()->defaultValue(0),
        'updated_by' => $this->integer()->defaultValue(0),
        'updated_at' => $this->dateTime(),
        'created_at' => $this->dateTime(),
    ], 'ENGINE InnoDB');

    $this->execute('set time_zone = \'-4:00\';');
    $this->execute('SET sql_mode = \'\';');
    $this->execute('INSERT INTO shop_sale (id, cafe_id, visitor_id,visitor_log_id,comment,pay_state,created_by,updated_by,updated_at,created_at)

    SELECT 
      d.order_id,
      if(d.item_id=0,d.item_cafe,a.cafe_id),
      if(d.visitor_id=0,null,d.visitor_id),
      b.id,
      null,
      d.pay_state+1,
      d.user_id,
      d.user_id,
      FROM_UNIXTIME(d.`time`),
      FROM_UNIXTIME(d.`time`)
    from order_list d
    LEFT JOIN shop_product a ON d.item_id=a.id
    LEFT JOIN visitor_log b on b.id = d.visit_id
    group by d.order_id
    ');

    $this->addForeignKey('FK-sale-to-cafe', $this->tableName, 'cafe_id', $this->cafeTableName, 'id', 'RESTRICT', 'CASCADE');
    $this->addForeignKey('FK-sale-to-visitor', $this->tableName, 'visitor_id', $this->visitorTableName, 'id', 'SET NULL', 'CASCADE');
    $this->addForeignKey('FK-sale-to-visitor_log', $this->tableName, 'visitor_log_id', $this->visitorLogTableName, 'id', 'SET NULL', 'CASCADE');
    $this->addForeignKey('FK-sale-to-pay_man', $this->tableName, 'pay_man', $this->visitorLogTableName, 'id', 'SET NULL', 'CASCADE');

    // add foreign key for table `{{%shop_sale}}`
    $this->addForeignKey(
        'fk-transaction-sale_id-shop_sale-id',
        '{{%transaction}}',
        'sale_id',
        '{{%shop_sale}}',
        'id',
        'SET NULL',
        'CASCADE'
    );

    $this->execute("INSERT INTO `transaction` (`cafe_id`,`cost`,`sum`, `method`, `visit_id`, `sale_id`, `visitor_id`, `created_at`) 
        SELECT s.cafe_id,ROUND(sum(cost),2) as sum_cost,ROUND(sum(`sum`),2) as sum_,pay_state,null,s.id,visitor_id,s.created_at 
        FROM `shop_sale` s
        left join shop_transaction t on t.sale_id=s.id
        WHERE s.pay_state>0
        GROUP BY pay_state,visitor_log_id,s.id,visitor_id,s.created_at
        HAVING sum_cost>0;");

    $this->addForeignKey('FK-transaction-to-sale', '`transaction`', 'sale_id', '{{%shop_sale}}', 'id', 'RESTRICT', 'CASCADE');

  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropForeignKey('FK-transaction-to-sale', $this->tableName);
    $this->dropForeignKey('FK-sale-to-visitor_log', $this->tableName);
    $this->dropForeignKey('FK-sale-to-pay_man', $this->tableName);
    $this->dropForeignKey('FK-sale-to-visitor', $this->tableName);
    $this->dropForeignKey('FK-sale-to-cafe', $this->tableName);
    // drop foreign key for table `{{%shop_sale}}`
    $this->dropForeignKey(
        'fk-transaction-sale_id-shop_sale-id',
        '{{%transaction}}'
    );
    $this->dropTable($this->tableName);
  }
}
