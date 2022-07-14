<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use Ryssbowh\Formidable\Models\Records\Field;
use craft\db\Migration;

/**
 * m220714_083352_ModifySelectCategoriesField migration.
 */
class m220714_083352_ModifySelectCategoriesField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $fields = Field::find()->where(['type' => 'select_category'])->all();
        foreach ($fields as $field) {
            $options = json_decode($field->options, true);
            if (isset($options['category'])) {
                $options['groups'] = [$options['category']];
                unset($options['category']);
                $field->options = $options;
                $field->save(false);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220714_083352_ModifySelectCategoriesField cannot be reverted.\n";
        return false;
    }
}
