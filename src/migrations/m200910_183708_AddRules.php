<?php

namespace Inspire\Forms\migrations;

use Craft;
use craft\db\Migration;

/**
 * m200910_183708_AddRules migration.
 */
class m200910_183708_AddRules extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%inspireforms_rules}}', [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(11)->notNull(),
            'type' => $this->string(255)->notNull(),
            'value' => $this->float(),
            'message' => $this->text(),
            'fields' => $this->json(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%inspireforms_conditiongroups}}', [
            'id' => $this->primaryKey(),
            'rule_id' => $this->integer(11)->notNull(),
            'operator' => $this->string(5),
            'negate' => $this->boolean(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%inspireforms_conditions}}', [
            'id' => $this->primaryKey(),
            'rule_id' => $this->integer(11),
            'conditiongroup_id' => $this->integer(11),
            'field' => $this->string(255)->notNull(),
            'type' => $this->string(255)->notNull(),
            'negate' => $this->boolean(false),
            'operator' => $this->string(5),
            'value' => $this->string(255),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->addForeignKey(null, '{{%inspireforms_rules}}', ['form_id'], '{{%inspireforms_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%inspireforms_conditiongroups}}', ['rule_id'], '{{%inspireforms_rules}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%inspireforms_conditions}}', ['rule_id'], '{{%inspireforms_conditions}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%inspireforms_conditions}}', ['conditiongroup_id'], '{{%inspireforms_conditiongroups}}', ['id'], 'CASCADE', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%inspireforms_rule_field}}');
        $this->dropTableIfExists('{{%inspireforms_conditiongroups}}');
        $this->dropTableIfExists('{{%inspireforms_conditions}}');
        $this->dropTableIfExists('{{%inspireforms_conditionnable_rule}}');
        $this->dropTableIfExists('{{%inspireforms_rules}}');
        return false;
    }
}
