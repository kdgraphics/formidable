<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use craft\db\Migration;

/**
 * m210330_052329_RebuildConfig migration.
 */
class m210330_052329_RebuildConfig extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $forms = Formidable::$plugin->forms->getAll();
        foreach ($forms as $form) {
            Formidable::$plugin->forms->save($form);
        }
        \Craft::$app->projectConfig->remove('formidable.fields');
        \Craft::$app->projectConfig->remove('formidable.rules');
        \Craft::$app->projectConfig->remove('formidable.conditions');
        \Craft::$app->projectConfig->remove('formidable.conditionGroups');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210330_052329_RebuildConfig cannot be reverted.\n";
        return false;
    }
}
