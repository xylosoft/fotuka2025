<?php

use yii\db\Migration;

class m260301_154938_users_googleid extends Migration
{
    public function safeUp()
    {
        // Add google_id column (Google "sub" is usually under 255 chars)
        $this->addColumn(
            'users',
            'google_id',
            $this->string(255)->null()->after('email')
        );

        // Add unique index (important!)
        $this->createIndex(
            'idx-user-google_id',
            'users',
            'google_id',
            true // unique
        );
    }

    public function safeDown()
    {
        $this->dropIndex(
            'idx-user-google_id',
            'users'
        );

        $this->dropColumn(
            'users',
            'google_id'
        );
    }
}
