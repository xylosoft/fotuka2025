<?php

use yii\db\Migration;

class m260303_162438_add_google_oauth_fields_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('users', 'google_sub', $this->string(64)->null()->after('profile_update_date'));
        $this->addColumn('users', 'google_email', $this->string(190)->null()->after('google_sub'));

        // Store encrypted token blobs (base64 strings). Use TEXT.
        $this->addColumn('users', 'google_access_token_enc', $this->text()->null()->after('google_email'));
        $this->addColumn('users', 'google_refresh_token_enc', $this->text()->null()->after('google_access_token_enc'));

        // Unix timestamp when access token expires
        $this->addColumn('users', 'google_token_expires_at', $this->integer()->null()->after('google_refresh_token_enc'));

        $this->createIndex('idx_user_google_sub', 'users', 'google_sub');
        $this->createIndex('idx_user_google_email', 'users', 'google_email');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_user_google_email', 'users');
        $this->dropIndex('idx_user_google_sub', 'users');

        $this->dropColumn('users', 'google_token_expires_at');
        $this->dropColumn('users', 'google_refresh_token_enc');
        $this->dropColumn('users', 'google_access_token_enc');
        $this->dropColumn('users', 'google_email');
        $this->dropColumn('users', 'google_sub');
    }
}