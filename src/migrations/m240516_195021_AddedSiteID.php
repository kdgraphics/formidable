<?php

namespace Inspire\Formidable\migrations;

use Craft;
use craft\db\Migration;
use Inspire\Formidable\Models\Records\Field;

class m240516_195021_AddedSiteID extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%formidable_submissions}}', 'siteId', $this->integer(11)->notNull()->after('form_id')->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%formidable_submissions}}', 'siteId');
    }
}
