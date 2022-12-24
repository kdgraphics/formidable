<?php

namespace Inspire\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201112_065634_AllowDateDeletedNull migration.
 */
class m201112_065634_AllowDateDeletedNull extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%formidable_spams}}', 'dateDeleted', 'datetime null');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m201112_065634_AllowDateDeletedNull cannot be reverted.\n";
        return false;
    }
}
