<?php

namespace Inspire\Formidable\migrations;

use Craft;
use craft\db\Migration;
use Inspire\Formidable\models\Records\Field;

/**
 * m220921_081021_ChangedFileMultipleAttribute migration.
 */
class m220921_081021_ChangedFileMultipleAttribute extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $fields = Field::find()->where(['type' => 'file'])->all();
        foreach ($fields as $field) {
            $options = json_decode($field->options, true);
            if ($options['multiple'] ?? false) {
                $options['maxFiles'] = 100;
            } else {
                $options['maxFiles'] = 1;
            }
            unset($options['multiple']);
            $field->options = $options;
            $field->save(false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220921_081021_ChangedFileMultipleAttribute cannot be reverted.\n";
        return false;
    }
}
