<?php

use yii\db\Migration;

/**
 * Template Builder v2:
 * - JSON-driven WYSIWYG templates
 * - Publication records per folder
 *
 * Notes:
 * - LONGTEXT is used intentionally because Yii2 does not expose mediumText()
 *   and template definitions / publication payloads can grow quickly.
 * - Foreign keys are not enforced here because existing app table names may vary.
 *   Add FKs later if your user/customer/folder tables are stable.
 */
class m260310_120539_create_template_builder_v2_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%website_template}}', [
            'id'                   => $this->primaryKey(),
            'user_id'              => $this->integer()->notNull(),
            'customer_id'          => $this->integer()->null(),
            'name'                 => $this->string(200)->notNull(),
            'definition_json'      => 'LONGTEXT NOT NULL',
            'created_at'           => $this->integer()->notNull(),
            'updated_at'           => $this->integer()->notNull(),
            'deleted'              => $this->integer()->null(),
            'deleted_by_user_id'   => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx_website_template_user', '{{%website_template}}', ['user_id']);
        $this->createIndex('idx_website_template_customer', '{{%website_template}}', ['customer_id']);
        $this->createIndex('idx_website_template_deleted', '{{%website_template}}', ['deleted']);
        $this->createIndex('idx_website_template_name', '{{%website_template}}', ['name']);

        $this->createTable('{{%website_publication}}', [
            'id'                   => $this->primaryKey(),
            'template_id'          => $this->integer()->notNull(),
            'folder_id'            => $this->integer()->notNull(),
            'user_id'              => $this->integer()->notNull(),
            'customer_id'          => $this->integer()->null(),
            'page_title'           => $this->string(255)->null(),
            'uri'                  => $this->string(255)->notNull(),
            'template_snapshot_json' => 'LONGTEXT NOT NULL',
            'values_json'          => 'LONGTEXT NULL',
            'is_password_protected'=> $this->boolean()->notNull()->defaultValue(false),
            'password_hash'        => $this->string(255)->null(),
            'allow_download_all'   => $this->boolean()->notNull()->defaultValue(false),
            'created_at'           => $this->integer()->notNull(),
            'updated_at'           => $this->integer()->notNull(),
            'deleted'              => $this->integer()->null(),
            'deleted_by_user_id'   => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx_website_publication_template', '{{%website_publication}}', ['template_id']);
        $this->createIndex('idx_website_publication_folder', '{{%website_publication}}', ['folder_id']);
        $this->createIndex('idx_website_publication_user', '{{%website_publication}}', ['user_id']);
        $this->createIndex('idx_website_publication_customer', '{{%website_publication}}', ['customer_id']);
        $this->createIndex('idx_website_publication_uri', '{{%website_publication}}', ['uri']);
        $this->createIndex('idx_website_publication_deleted', '{{%website_publication}}', ['deleted']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%website_publication}}');
        $this->dropTable('{{%website_template}}');
    }
}