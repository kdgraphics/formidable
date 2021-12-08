<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m211201_141820_AddGroups migration.
 */
class m211201_141820_AddGroups extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_fields}}', 'group_id', $this->integer()->after('form_id')->null());
        $this->addColumn('{{%formidable_fields}}', 'active', $this->boolean()->after('group_id')->defaultValue(true));
        $this->addForeignKey('formidable_fields_group_id_fk', '{{%formidable_fields}}', ['group_id'], '{{%formidable_fields}}', ['id'], 'SET NULL', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m211201_141820_AddGroups cannot be reverted.\n";
        return false;
    }
}
