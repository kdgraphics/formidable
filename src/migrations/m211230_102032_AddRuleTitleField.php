<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m211230_102032_AddRuleTitleField migration.
 */
class m211230_102032_AddRuleTitleField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_rules}}', 'title', $this->string(255)->after('type')->defaultValue(''));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%formidable_rules}}', 'title');
    }
}
