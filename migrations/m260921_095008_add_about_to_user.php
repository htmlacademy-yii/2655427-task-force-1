<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds the about field to the user table.
 */
class m260921_095008_add_about_to_user extends Migration
{
    /**
     * Adds the about column.
     *
     * @return void
     */
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%user}}',
            'about',
            $this->text()->null()
        );
    }

    /**
     * Removes the about column.
     *
     * @return void
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%user}}', 'about');
    }
}
