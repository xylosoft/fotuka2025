<?php

use yii\db\Migration;

class m251016_230119_assets extends Migration
{
    public function safeUp(){
        $this->execute("
            CREATE TABLE `assets` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `customer_id` int UNSIGNED NOT NULL,
                `user_id` int UNSIGNED NOT NULL,
                `folder_id` int UNSIGNED NOT NULL,    
                `file_id` int UNSIGNED NOT NULL,
                `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
                `thumbnail_state` enum('pending','ready', 'unsupported') NOT NULL DEFAULT 'pending',
                `preview_state` enum('pending','ready', 'unsupported') NOT NULL DEFAULT 'pending',
                `title` varchar(100) DEFAULT NULL,
                `description` varchar(255) DEFAULT NULL,
                `thumbnail_url` varchar(500) null,
                `version` int not null DEFAULT 1,
                `deleted` timestamp NULL,
                `deleted_by_user_id` INT UNSIGNED NULL, 
                PRIMARY KEY (`id`),
                KEY `idx_customerid` (`customer_id`),
                KEY `idx_folder_id` (`folder_id`),
                KEY `idx_status` (`status`),
                INDEX idx_customer_created (customer_id, created DESC, id),
                FULLTEXT INDEX ft_title (title),
                FULLTEXT INDEX ft_description (description)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("drop table assets;");
        return true;
    }
}