<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201130_054922_AddMessagesField migration.
 */
class m201130_054922_AddMessagesField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_rules}}', 'messages', $this->text()->after('message'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%formidable_rules}}', 'messages');
        return false;
    }
}
