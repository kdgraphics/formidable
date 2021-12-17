<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m211216_123906_ChangeRulesFieldColumn migration.
 */
class m211216_123906_ChangeRulesFieldColumn extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%formidable_rules}}', 'fields', $this->longText()->null());
        $this->alterColumn('{{%formidable_rules}}', 'message', $this->longText()->null());
        $this->alterColumn('{{%formidable_rules}}', 'messages', $this->longText()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m211216_123906_ChangeRulesFieldColumn cannot be reverted.\n";
        return false;
    }
}
