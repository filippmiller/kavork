<?php

use yii\db\Migration;

/**
 * Class m190214_081926_certificate_fix
 */
class m190214_081926_certificate_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $this->execute("UPDATE `cafe_auth_item` SET `name` = 'certificate' WHERE `cafe_auth_item`.`name` = 'сertificate';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190214_081926_certificate_fix cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190214_081926_certificate_fix cannot be reverted.\n";

        return false;
    }
    */
}
