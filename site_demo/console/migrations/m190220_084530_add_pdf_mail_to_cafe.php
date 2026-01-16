<?php

use yii\db\Migration;

/**
 * Class m190220_084530_add_pdf_mail_to_cafe
 */
class m190220_084530_add_pdf_mail_to_cafe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $this->addColumn('cafe','pdf_to_mail',$this->integer(1)->defaultValue(1)->after('width'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190220_084530_add_pdf_mail_to_cafe cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190220_084530_add_pdf_mail_to_cafe cannot be reverted.\n";

        return false;
    }
    */
}
