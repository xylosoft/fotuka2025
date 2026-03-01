<?php

use yii\db\Migration;

class m260301_155921_users_firstlast_name extends Migration
{
    public function safeUp()
    {
        // Add first_name column
        $this->addColumn(
            'users',
            'first_name',
            $this->string(100)->null()->after('username')
        );

        // Add last_name column
        $this->addColumn(
            'users',
            'last_name',
            $this->string(100)->null()->after('first_name')
        );
    }

    public function safeDown()
    {
        $this->dropColumn('users', 'last_name');
        $this->dropColumn('users', 'first_name');
    }
}
