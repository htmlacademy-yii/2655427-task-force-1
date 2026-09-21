<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds GitHub ID to the user table.
 */
class m260920_123231_add_github_id_to_user extends Migration
{
    /**
     * Adds the GitHub ID column and unique index.
     *
     * @return void
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
     * Removes the GitHub ID column and unique index.
     *
     * @return void
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
