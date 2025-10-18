<?php

use yii\db\Migration;

class m251018_165542_customers extends Migration
{
    public function safeUp(){
        $this->execute("
            CREATE TABLE `customers` (
              `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
              `display_name` varchar(100) DEFAULT NULL,
              `ip_country_code` varchar(5) DEFAULT NULL,
              `referral_url` varchar(255) DEFAULT NULL,
              `seo_name` varchar(50) DEFAULT NULL,
              `status` enum('active','inactive','deleted') DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `seo_name` (`seo_name`)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("drop table customers;");
        return true;
    }
}
