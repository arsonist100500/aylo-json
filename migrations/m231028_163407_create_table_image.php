<?php

use yii\db\Migration;
use yii\db\pgsql\QueryBuilder;

class m231028_163407_create_table_image extends Migration
{
    protected const TABLE_NAME = 'image';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'pornstar_id' => $this->integer()->notNull(),
            'hash' => $this->string(40)->notNull(),
            'types' => $this->json()->notNull()->defaultValue('[]'),
            'url' => $this->string()->notNull(),
            'cached' => $this->string(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('image_pornstar_id', self::TABLE_NAME, 'pornstar_id', QueryBuilder::INDEX_HASH);
        $this->createIndex('image_hash', self::TABLE_NAME, 'hash', QueryBuilder::INDEX_HASH);
        $this->createIndex('image_cached', self::TABLE_NAME, 'cached', QueryBuilder::INDEX_HASH);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
