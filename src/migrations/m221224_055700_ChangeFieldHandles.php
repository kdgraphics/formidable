<?php

namespace Inspire\Formidable\migrations;

use Craft;
use Inspire\Formidable\Models\Records\Field;
use craft\db\Migration;

/**
 * m221224_055700_ChangeFieldHandles migration.
 */
class m221224_055700_ChangeFieldHandles extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        foreach (Field::find()->all() as $field) {
            $field->type = str_replace('_', '-', $field->type);
            $field->save(false);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m221224_055700_ChangeFieldHandles cannot be reverted.\n";
        return false;
    }
}
