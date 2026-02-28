<?php

use yii\db\Migration;

class m260228_135927_user_profile_update_date extends Migration
{
    public function safeUp(){
        $this->execute("ALTER TABLE `users` add column `profile_update_date` INT UNSIGNED NULL AFTER profile_picture");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table users drop column profile_update_date;");
        return true;
    }
}
