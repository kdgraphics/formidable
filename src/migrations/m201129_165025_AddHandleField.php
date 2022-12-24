<?php

namespace Inspire\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201129_165025_AddHandleField migration.
 */
class m201129_165025_AddHandleField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_fields}}', 'handle', $this->string(255)->after('name')->notNull()->defaultValue(''));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%formidable_fields}}', 'handle');
        return false;
    }
}
