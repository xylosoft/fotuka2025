<?php

use yii\db\Migration;

class m260316_173300_removing_page_title_from_website_publication extends Migration
{
    public function safeUp(){
        $this->execute("alter table website_publication drop column page_title;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table website_publication add column ");
        return true;
    }
}


