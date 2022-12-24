<?php

namespace Inspire\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m210520_080522_FixForeignKey migration.
 */
class m210520_080522_FixForeignKey extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('formidable_conditions_rule_id_fk', '{{%formidable_conditions}}');
        $this->addForeignKey('formidable_conditions_rule_id_fk', '{{%formidable_conditions}}', ['rule_id'], '{{%formidable_rules}}', ['id'], 'CASCADE', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210520_080522_FixForeignKey cannot be reverted.\n";
        return false;
    }
}
