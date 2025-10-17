<?php

use yii\db\Migration;

class m251016_221318_user_to_users extends Migration
{
    public function safeUp(){
        $this->execute("rename table user to users;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("rename table users to user;");
        return true;
    }
}
