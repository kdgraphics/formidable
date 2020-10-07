<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201007_182208_AddFields migration.
 */
class m201007_182208_AddFields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_forms}}', 'frontEndValidation', $this->boolean()->defaultValue(false)->after('redirect'));
        $this->addColumn('{{%formidable_forms}}', 'submitClass', $this->string(255)->after('submitLabel'));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%formidable_forms}}', 'frontEndValidation');
        $this->dropColumn('{{%formidable_forms}}', 'submitClass');
        return true;
    }
}
