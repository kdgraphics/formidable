<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m210811_095404_AddHtmlIdField migration.
 */
class m210811_095404_AddHtmlIdField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_forms}}', 'htmlId', $this->string(255)->after('class'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210811_095404_AddHtmlIdField cannot be reverted.\n";
        return false;
    }
}
