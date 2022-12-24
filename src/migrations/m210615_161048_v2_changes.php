<?php

namespace Inspire\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m210615_161048_v2_changes migration.
 */
class m210615_161048_v2_changes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('formidable_forms_notifyAdmin_id_fk', '{{%formidable_forms}}');
        $this->dropForeignKey('formidable_forms_notifySpam_id_fk', '{{%formidable_forms}}');
        $this->dropForeignKey('formidable_forms_notify_id_fk', '{{%formidable_forms}}');

        $this->renameColumn('{{%formidable_forms}}', 'notify_id', 'notify_key');
        $this->renameColumn('{{%formidable_forms}}', 'notifyAdmin_id', 'notifyAdmin_key');
        $this->renameColumn('{{%formidable_forms}}', 'notifySpam_id', 'notifySpam_key');

        $this->alterColumn('{{%formidable_forms}}', 'notify_key', $this->string(255));
        $this->alterColumn('{{%formidable_forms}}', 'notifyAdmin_key', $this->string(255));
        $this->alterColumn('{{%formidable_forms}}', 'notifySpam_key', $this->string(255));

        $this->dropTable('{{%formidable_notifications}}');
        $this->dropTable('{{%formidable_emails}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210615_161048_v2_changes cannot be reverted.\n";
        return false;
    }
}
