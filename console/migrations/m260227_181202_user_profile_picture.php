<?php

use yii\db\Migration;

class m260227_181202_user_profile_picture extends Migration
{
    public function safeUp(){
        $this->execute("ALTER TABLE `users` add column `profile_picture` varchar(255) null;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table users drop column profile_picture;");
        return true;
    }
}
