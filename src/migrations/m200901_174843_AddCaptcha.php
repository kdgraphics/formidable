<?php

namespace Inspire\Forms\migrations;

use Craft;
use craft\db\Migration;

/**
 * m200901_174843_AddCaptcha migration.
 */
class m200901_174843_AddCaptcha extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%inspireforms_forms}}', 'captcha', $this->string(255));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%inspireforms_forms}}', 'captcha');
        return true;
    }
}
