<?php

use yii\db\Migration;

class m260301_195048_folders_storage_used extends Migration
{
    public function safeUp(){
        $this->execute("ALTER TABLE `folders` add column storage_used BIGINT UNSIGNED NOT NULL DEFAULT 0");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table folders drop column storage_used;");
        return true;
    }

}
