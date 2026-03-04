<?php

use yii\db\Migration;

class m260304_121505_create_labels_tables extends Migration
{
    public function safeUp()
    {
        // labels table
        $this->createTable('labels', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(80)->notNull(),
        ]);

        $this->createIndex(
            'ux_labels_name',
            'labels',
            ['name'],
            true
        );

        // asset_labels table
        $this->createTable('asset_labels', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'customer_id' => $this->integer()->unsigned()->notNull(),
            'asset_id' => $this->bigInteger()->unsigned()->notNull(),
            'label_id' => $this->bigInteger()->unsigned()->notNull(),
            'confidence' => $this->tinyInteger()->unsigned()->null(), // 0..100
        ]);

        // Prevent duplicate labels on the same asset for the same customer
        $this->createIndex(
            'ux_asset_labels_customer_asset_label',
            'asset_labels',
            ['customer_id', 'asset_id', 'label_id'],
            true
        );

        // Fast search: label -> assets (scoped by customer)
        $this->createIndex(
            'ix_asset_labels_customer_label_asset',
            'asset_labels',
            ['customer_id', 'label_id', 'asset_id']
        );

        // Fast fetch: asset -> labels
        $this->createIndex(
            'ix_asset_labels_customer_asset',
            'asset_labels',
            ['customer_id', 'asset_id']
        );
    }

    public function safeDown()
    {
        $this->dropTable('asset_labels');
        $this->dropTable('labels');
    }
}