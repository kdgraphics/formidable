<?php

namespace Inspire\Forms\migrations;

use Craft;
use craft\db\Migration;

/**
 * m200909_051145_AddFieldsFields migration.
 */
class m200909_051145_AddFieldsFields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%inspireforms_forms}}', 'saveSpam', $this->boolean(true));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%inspireforms_forms}}', 'saveSpam');
        return true;
    }
}
