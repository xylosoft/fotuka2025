<?php

use yii\db\Migration;

class m251016_230123_files extends Migration
{
    public function safeUp(){
        $this->execute("
            CREATE TABLE `files` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `customer_id` int UNSIGNED NOT NULL,
                `user_id` int UNSIGNED NOT NULL,
                `type` enum('image','video','audio','document','spreadsheet', 'presentation', 'archive', 'code', 'font', '3d', 'other') DEFAULT NULL,
                `width` int UNSIGNED NULL,
                `height` int UNSIGNED NULL,
                `thumbnail` enum('pending','done','unsupported') DEFAULT 'pending',
                `preview` enum('pending','done','unsupported') DEFAULT 'pending',
                `filename` varchar(255) DEFAULT NULL,
                `extension` varchar(10) DEFAULT NULL,
                `orientation` enum('horizontal','vertical') DEFAULT NULL,
                `filesize` int UNSIGNED NOT NULL,
                `pages` int DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_customerid` (`customer_id`),
                KEY `idx_user_id` (`user_id`),
                FULLTEXT INDEX ft_filename (filename),
                INDEX idx_customer_extension (customer_id, extension),
                INDEX idx_filesize (filesize)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("drop table files;");
        return true;
    }
}
