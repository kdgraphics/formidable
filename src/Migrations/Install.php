<?php

namespace Inspire\Forms\migrations;

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
        $this->createTable('{{%inspireforms_forms}}', [
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
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%inspireforms_notifications}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'method' => $this->string(255)->notNull(),
            'template' => $this->string(255),
            'html' => $this->text(),
            'sentFrom' => $this->string(255)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%inspireforms_fields}}', [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(11)->notNull(),
            'name' => $this->string(255)->notNull(),
            'order' => $this->integer(11)->notNull(),
            'type' => $this->string(255)->notNull(),
            'options' => $this->text(),
            'active' => $this->boolean()->defaultValue(true),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable('{{%inspireforms_submissions}}', [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(11)->notNull(),
            'read' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull()
        ]);

        $this->createTable('{{%inspireforms_spams}}', [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(11)->notNull(),
            'ip' => $this->string(255),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->notNull()
        ]);

        $this->createTable('{{%inspireforms_submissions_values}}', [
            'id' => $this->primaryKey(),
            'field_id' => $this->integer(11)->notNull(),
            'submission_id' => $this->integer(11)->notNull(),
            'value' => $this->text()
        ]);

        $this->addForeignKey(null, '{{%inspireforms_forms}}', ['notify_id'], '{{%inspireforms_notifications}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%inspireforms_forms}}', ['notifyAdmin_id'], '{{%inspireforms_notifications}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%inspireforms_fields}}', ['form_id'], '{{%inspireforms_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%inspireforms_submissions}}', ['form_id'], '{{%inspireforms_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%inspireforms_spams}}', ['form_id'], '{{%inspireforms_forms}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%inspireforms_submissions_values}}', ['field_id'], '{{%inspireforms_fields}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%inspireforms_submissions_values}}', ['submission_id'], '{{%inspireforms_submissions}}', ['id'], 'CASCADE', null);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%inspireforms_submissions_values}}');
        $this->dropTableIfExists('{{%inspireforms_spams}}');
        $this->dropTableIfExists('{{%inspireforms_submissions}}');
        $this->dropTableIfExists('{{%inspireforms_fields}}');
        $this->dropTableIfExists('{{%inspireforms_forms}}');
        $this->dropTableIfExists('{{%inspireforms_notifications}}');
        Craft::$app->projectConfig->remove('inspireforms');
    }
}
