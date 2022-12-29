<?php

namespace Inspire\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m261011_150804_AddSpamCheck migration.
 */
class m211011_150804_AddSpamCheck extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_forms}}', 'spamCheck', $this->string(255)->after('class')->defaultValue(''));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%formidable_forms}}', 'spamCheck');
        return false;
    }
}
