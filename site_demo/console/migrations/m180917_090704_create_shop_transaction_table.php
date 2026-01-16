<?php

use yii\db\Migration;
use \frontend\modules\shop\models\ShopTransaction;

/**
 * Handles the creation of table `show_transaction`.
 */
class m180917_090704_create_shop_transaction_table extends Migration
{
	public $tableName = '{{%shop_transaction}}';
	public $productTableName = '{{%shop_product}}';
	public $saleTableName = '{{%shop_sale}}';

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable($this->tableName, [
			'id'                => $this->primaryKey(),
			'operation_type_id' => $this->integer()->notNull(),
			'cafe_id'           => $this->integer()->notNull(),
			'sale_id'           => $this->integer()->null(),
			'product_id'        => $this->integer()->null(),

			'quantity' => $this->integer()->notNull(),
			'price'    => $this->float()->notNull(),
			'sum'      => $this->float()->notNull(),
			'cost'     => $this->float()->notNull(),
			'vat'      => $this->json(),
			'comment'  => $this->text(),

			'data'     => $this->json(),

			'created_by' => $this->integer()->defaultValue(0),
			'updated_by' => $this->integer()->defaultValue(0),
			'updated_at' => $this->dateTime(),
			'created_at' => $this->dateTime(),
		], 'ENGINE InnoDB');

    $this->execute('set time_zone = \'-4:00\';');
    $this->execute('SET sql_mode = \'\';');

    $this->execute('INSERT INTO shop_transaction 
    (
     operation_type_id, cafe_id,sale_id,product_id,
    quantity,price,`sum`,cost,vat,comment,
    `data`,
    created_by,updated_by,updated_at,created_at
    )

    SELECT 
    '.ShopTransaction::OPERATION_TYPE_SALE.',
      if(d.item_id=0,d.item_cafe,a.cafe_id),
      d.order_id,
      if(d.item_id=0,null,d.item_id),
      
      d.count,d.price,ROUND(d.price*d.count,2),ROUND((d.price+d.tps+d.tvq)*d.count,2),
      CONCAT(\'[{"name":"tps","vat":\',d.tps,\'},{"name":"tvq","vat":\',d.tvq,\'}]\'),
      null,
      
      if(d.item_id=0,CONCAT(\'[{"product_title":"\',d.item_name,\'"}]\'),null),
      
      d.user_id,
      d.user_id,
      FROM_UNIXTIME(d.`time`),
      FROM_UNIXTIME(d.`time`)
    from order_list d
    LEFT JOIN shop_product a ON d.item_id=a.id
    ');


    $this->execute('INSERT INTO shop_transaction 
    (
     operation_type_id, cafe_id,sale_id,product_id,
    quantity,price,`sum`,cost,vat,comment,
    `data`,
    created_by,updated_by,updated_at,created_at
    )

    SELECT 
    if(d.is_leaving=0,'.ShopTransaction::OPERATION_TYPE_INCOME.','.ShopTransaction::OPERATION_TYPE_WRITE_OFF.'),
      a.cafe_id,
      null,
      d.item_id,
      
      1,a.price,a.price,a.price,
      null,
      null,
      null,
      
      d.user_id,
      d.user_id,
      FROM_UNIXTIME(d.`time`),
      FROM_UNIXTIME(d.`time`)
    from goods_transit d
    LEFT JOIN shop_product a ON d.item_id=a.id
    ');

		$this->addForeignKey('FK-transaction-to-product', $this->tableName, 'product_id', $this->productTableName, 'id', 'RESTRICT', 'CASCADE');

    /*$this->execute('DELIMITER $$
        CREATE DEFINER=`debian-sys-maint`@`localhost` FUNCTION `sum_array_cells`(`input_array` JSON) RETURNS double
            NO SQL
        BEGIN
            DECLARE array_length INTEGER(11);
            DECLARE retval DOUBLE(19,2);
            DECLARE cell_value DOUBLE(19,2);
            DECLARE idx INT(11);
        
            SELECT json_length( input_array ) INTO array_length;
        
            SET retval = 0.0;
        
            SET idx = 0;
        
            WHILE idx < array_length DO
              SELECT json_extract( input_array, concat( \'$[\', idx, \']\' ) )
                    INTO cell_value;
        
                SET retval = retval + cell_value;
                SET idx = idx + 1;
            END WHILE;
        
            RETURN retval;
        END$$
        DELIMITER ;');*/
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('FK-transaction-to-product', $this->tableName);

		$this->dropTable($this->tableName);
    //$this->execute('DROP FUNCTION IF EXISTS `sum_array_cells`');

	}
}
