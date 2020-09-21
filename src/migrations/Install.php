<?php

namespace Ryssbowh\Formidable\migrations;

use Craft; 
use craft\db\Migration;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%formidable_forms}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'handle' => $this->string(255)->notNull(),
            'notify_id' => $this->integer(11),
            'notifyAdmin_id' => $this->integer(11),
            'notifyField' => $this->string(255),
            'notifyEmails' => $this->text(),
            'honeypot' => $this->boolean()->defaultValue(true),
            'honeypotField' => $this->string(255),
            'saveSubmissions' => $this->boolean()->defaultValue(true),
            'saveSpam' => $this->boolean()->defaultValue(true),
            'redirect' => $this->string(255),
            'method' => $this->string(255),
            'class' => $this->string(255),
            'color' => $this->string(255),
            'captcha' => $this->string(255),
            'submitLabel' => $this->string(255),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%formidable_notifications}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'method' => $this->string(255)->notNull(),
            'template' => $this->string(255),
            'html' => $this->text(),
            'sentFrom' => $this->string(255)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%formidable_fields}}', [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(11)->notNull(),
            'name' => $this->string(255)->notNull(),
            'order' => $this->integer(11)->notNull(),
            'type' => $this->string(255)->notNull(),
            'fieldClass' => $this->string(255),
            'containerClass' => $this->string(255),
            'labelClass' => $this->string(255),
            'instructions' => $this->text(),
            'options' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%formidable_submissions}}', [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(11)->notNull(),
            'read' => $this->boolean()->defaultValue(false),
            'adminNotified' => $this->boolean()->defaultValue(false),
            'notified' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%formidable_spams}}', [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(11)->notNull(),
            'ip' => $this->string(255),
            'values' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%formidable_submissions_values}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(255)->notNull(),
            'field' => $this->string(255)->notNull(),
            'submission_id' => $this->integer(11)->notNull(),
            'value' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%formidable_rules}}', [
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

        $this->createTable('{{%formidable_conditiongroups}}', [
            'id' => $this->primaryKey(),
            'rule_id' => $this->integer(11)->notNull(),
            'operator' => $this->string(5),
            'negate' => $this->boolean(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%formidable_conditions}}', [
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

        $this->createTable('{{%formidable_emails}}', [
            'id' => $this->primaryKey(),
            'submission_id' => $this->integer(11),
            'emails' => $this->json(),
            'sent' => $this->dateTime(),
            'forAdmin' => $this->boolean(false),
            'sentFrom' => $this->string(255),
            'subject' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->addForeignKey(null, '{{%formidable_forms}}', ['notify_id'], '{{%formidable_notifications}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%formidable_forms}}', ['notifyAdmin_id'], '{{%formidable_notifications}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%formidable_fields}}', ['form_id'], '{{%formidable_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_submissions}}', ['form_id'], '{{%formidable_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_spams}}', ['form_id'], '{{%formidable_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_submissions_values}}', ['submission_id'], '{{%formidable_submissions}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_rules}}', ['form_id'], '{{%formidable_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_conditiongroups}}', ['rule_id'], '{{%formidable_rules}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_conditions}}', ['rule_id'], '{{%formidable_conditions}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_conditions}}', ['conditiongroup_id'], '{{%formidable_conditiongroups}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%formidable_emails}}', ['submission_id'], '{{%formidable_submissions}}', ['id'], 'SET NULL', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%formidable_rule_field}}');
        $this->dropTableIfExists('{{%formidable_conditiongroups}}');
        $this->dropTableIfExists('{{%formidable_conditions}}');
        $this->dropTableIfExists('{{%formidable_conditionnable_rule}}');
        $this->dropTableIfExists('{{%formidable_rules}}');
        $this->dropTableIfExists('{{%formidable_submissions_values}}');
        $this->dropTableIfExists('{{%formidable_spams}}');
        $this->dropTableIfExists('{{%formidable_submissions}}');
        $this->dropTableIfExists('{{%formidable_fields}}');
        $this->dropTableIfExists('{{%formidable_forms}}');
        $this->dropTableIfExists('{{%formidable_notifications}}');
        Craft::$app->projectConfig->remove('formidable');
    }
}
