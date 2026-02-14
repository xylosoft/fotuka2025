<?php

use yii\db\Migration;

class m260213_192801_file_tmp_location extends Migration
{
    public function safeUp(){
        $this->execute("
            ALTER TABLE `files` add column `tmp_location` varchar(255) null;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table files drop column tmnp_location;");
        return true;
    }
}
