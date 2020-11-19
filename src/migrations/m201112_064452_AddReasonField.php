<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201112_064452_AddReasonField migration.
 */
class m201112_064452_AddReasonField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_spams}}', 'reason', $this->string(255)->after('notified'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%formidable_spams}}', 'reason');
        return false;
    }
}
