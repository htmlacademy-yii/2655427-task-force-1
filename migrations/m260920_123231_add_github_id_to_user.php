<?php

use yii\db\Migration;

class m260920_123231_add_github_id_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%user}}',
            'github_id',
            $this->integer()->null()
        );

        $this->createIndex(
            'idx-user-github_id',
            '{{%user}}',
            'github_id',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropIndex(
            'idx-user-github_id',
            '{{%user}}'
        );

        $this->dropColumn(
            '{{%user}}',
            'github_id'
        );
    }
}
