<?php

namespace Inspire\Forms\migrations;

use Craft;
use craft\db\Migration;

/**
 * m200829_050137_AddValuesSpam migration.
 */
class m200829_050137_AddValuesSpam extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%inspireforms_spams}}', 'values', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%inspireforms_spams}}', 'values');
        return false;
    }
}
