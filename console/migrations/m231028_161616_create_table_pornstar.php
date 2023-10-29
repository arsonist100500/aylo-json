<?php

use yii\db\Migration;
use yii\db\pgsql\QueryBuilder;

class m231028_161616_create_table_pornstar extends Migration
{
    protected const TABLE_NAME = 'pornstar';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'hash' => $this->string(64)->notNull(),
            'name' => $this->string()->notNull(),
            'aliases' => $this->json()->notNull()->defaultValue('[]'),
            'license' => $this->string(16)->notNull(),
            'wlStatus' => $this->tinyInteger()->notNull(),
            'link' => $this->string()->notNull(),
            'attributes' => $this->json()->notNull()->defaultValue('{}'),
            'stats' => $this->json()->notNull()->defaultValue('{}'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('pornstar_hash', self::TABLE_NAME, 'hash', QueryBuilder::INDEX_HASH);
        $this->createIndex('pornstar_name', self::TABLE_NAME, 'name', QueryBuilder::INDEX_HASH); // todo GIN index
        $this->createIndex('pornstar_aliases', self::TABLE_NAME, 'aliases', QueryBuilder::INDEX_B_TREE);
        $this->createIndex('pornstar_license', self::TABLE_NAME, 'license', QueryBuilder::INDEX_HASH);
        $this->createIndex('pornstar_wlStatus', self::TABLE_NAME, 'wlStatus', QueryBuilder::INDEX_HASH);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
