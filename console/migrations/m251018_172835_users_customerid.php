<?php

use yii\db\Migration;

class m251018_172835_users_customerid extends Migration
{
    public function safeUp(){
        $this->execute("
            ALTER TABLE `users` add column `customer_id` int UNSIGNED NOT NULL default 1;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table users drop column customer_id;");
        return true;
    }
}
