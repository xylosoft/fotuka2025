<?php

use yii\db\Migration;

class m260228_191738_assets_preview_url extends Migration
{
    public function safeUp(){
        $this->execute("ALTER TABLE `assets` add column `preview_url`  varchar(500) null AFTER thumbnail_url");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(){
        $this->execute("alter table assets drop column preview_url;");
        return true;
    }

}
