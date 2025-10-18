<?php

use yii\db\Migration;

class m251016_220110_folders extends Migration
{
    public function safeUp(){
        $this->execute("
            CREATE TABLE `folders` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `parent_id` int UNSIGNED NULL,
                `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `customer_id` int UNSIGNED NOT NULL,
                `user_id` int UNSIGNED NOT NULL,    
                `name` varchar(50) not null,
                `folder_size` int UNSIGNED not null default 0,
                `asset_count` int UNSIGNED not null default 0,
                `status` enum('active','inactive','deleted') default 'active',
                `thumbnail_id` int UNSIGNED not null default 0,
                `deleted` timestamp NULL,
                `deleted_by_user_id` INT UNSIGNED NULL,
                primary key (id),
                INDEX `idx_parent_id` (`parent_id`),
                INDEX `idx_customer_id` (`customer_id`),
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_status` (`status`)            
           ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("drop table folders;");
        return true;
    }
}

