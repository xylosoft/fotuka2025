<?php

use yii\db\Migration;

class m260301_195036_customers_storage_used extends Migration
{
    public function safeUp(){
        $this->execute("ALTER TABLE `customers` add column storage_used BIGINT UNSIGNED NOT NULL DEFAULT 0");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table customers drop column storage_used;");
        return true;
    }
}
