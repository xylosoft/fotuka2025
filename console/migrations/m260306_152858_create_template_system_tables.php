<?php

use yii\db\Migration;

class m260306_152858_create_template_system_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%templates}}', [
            'template_id' => $this->primaryKey(),
            'customer_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'name' => $this->string(100)->notNull(),
            'allow_downloads' => $this->boolean()->notNull()->defaultValue(0),
            'password_enabled' => $this->boolean()->notNull()->defaultValue(0),
            'theme_json' => $this->text()->null(),
            'deleted' => $this->timestamp()->null()->defaultValue(null),
            'deleted_by_user_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'created_by_user_id' => $this->integer()->unsigned()->null(),
            'updated_by_user_id' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex('ux_templates_customer_name', '{{%templates}}', ['customer_id', 'name'], true);
        $this->createIndex('idx_templates_customer_deleted', '{{%templates}}', ['customer_id', 'deleted']);

        $this->createTable('{{%template_sections}}', [
            'section_id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'section_key' => $this->string(64)->notNull(),
            'type' => $this->string(50)->notNull(),
            'label' => $this->string(100)->null(),
            'row_no' => $this->integer()->notNull()->defaultValue(1),
            'width' => $this->integer()->notNull()->defaultValue(12),
            'height' => $this->integer()->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_locked' => $this->boolean()->notNull()->defaultValue(0),
            'text' => $this->text()->null(),
            'custom_field_id' => $this->integer()->null(),
            'background_color' => $this->string(20)->null(),
            'text_color' => $this->string(20)->null(),
            'image_count' => $this->integer()->notNull()->defaultValue(0),
            'settings_json' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex('ux_template_sections_section_key', '{{%template_sections}}', 'section_key', true);
        $this->createIndex('idx_template_sections_template_sort', '{{%template_sections}}', ['template_id', 'sort_order']);

        $this->createTable('{{%template_custom_fields}}', [
            'custom_field_id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'field_key' => $this->string(64)->notNull(),
            'name' => $this->string(100)->notNull(),
            'slug' => $this->string(100)->notNull(),
            'text_color' => $this->string(20)->null(),
            'font_size' => $this->integer()->null(),
            'font_weight' => $this->string(20)->null(),
            'font_style' => $this->string(20)->null(),
            'text_align' => $this->string(20)->null(),
            'settings_json' => $this->text()->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex('ux_template_custom_fields_field_key', '{{%template_custom_fields}}', 'field_key', true);
        $this->createIndex('ux_template_custom_fields_template_slug', '{{%template_custom_fields}}', ['template_id', 'slug'], true);
        $this->createIndex('idx_template_custom_fields_template_sort', '{{%template_custom_fields}}', ['template_id', 'sort_order']);

        $this->createTable('{{%published_pages}}', [
            'published_page_id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'folder_id' => $this->integer()->unsigned()->notNull(),
            'customer_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'uri' => $this->string(150)->notNull(),
            'page_title' => $this->string(255)->null(),
            'password_required' => $this->boolean()->notNull()->defaultValue(0),
            'page_password' => $this->string(255)->null(),
            'allow_downloads' => $this->boolean()->notNull()->defaultValue(0),
            'published_at' => $this->dateTime()->notNull(),
            'deleted' => $this->timestamp()->null()->defaultValue(null),
            'deleted_by_user_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'created_by_user_id' => $this->integer()->unsigned()->null(),
            'updated_by_user_id' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex('ux_published_pages_uri', '{{%published_pages}}', 'uri', true);
        $this->createIndex('idx_published_pages_template', '{{%published_pages}}', 'template_id');
        $this->createIndex('idx_published_pages_folder', '{{%published_pages}}', 'folder_id');
        $this->createIndex('idx_published_pages_customer_deleted', '{{%published_pages}}', ['customer_id', 'deleted']);

        $this->createTable('{{%published_page_custom_field_values}}', [
            'value_id' => $this->primaryKey(),
            'published_page_id' => $this->integer()->notNull(),
            'custom_field_id' => $this->integer()->notNull(),
            'value' => 'MEDIUMTEXT NULL',
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'ux_published_page_custom_field_values_page_field',
            '{{%published_page_custom_field_values}}',
            ['published_page_id', 'custom_field_id'],
            true
        );

        $this->createTable('{{%published_page_section_assets}}', [
            'id' => $this->primaryKey(),
            'published_page_id' => $this->integer()->notNull(),
            'section_id' => $this->integer()->notNull(),
            'asset_id' => $this->integer()->unsigned()->notNull(),
            'slot_no' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'ux_published_page_section_assets_page_section_slot',
            '{{%published_page_section_assets}}',
            ['published_page_id', 'section_id', 'slot_no'],
            true
        );

        $this->createIndex('idx_published_page_section_assets_asset', '{{%published_page_section_assets}}', 'asset_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%published_page_section_assets}}');
        $this->dropTable('{{%published_page_custom_field_values}}');
        $this->dropTable('{{%published_pages}}');
        $this->dropTable('{{%template_custom_fields}}');
        $this->dropTable('{{%template_sections}}');
        $this->dropTable('{{%templates}}');
    }
}