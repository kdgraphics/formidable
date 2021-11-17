<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use Ryssbowh\Formidable\Models\Records\Field;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Records\SubmissionValue;
use craft\db\Migration;

/**
 * m211116_172434_V4Changes migration.
 */
class m211116_172434_V4Changes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%formidable_submissions_values}}', 'field', $this->string(255));
        $this->addColumn('{{%formidable_submissions_values}}', 'dateDeleted',$this->dateTime()->after('dateUpdated'));
        $values = SubmissionValue::find()->all();
        foreach ($values as $value) {
            $value->field = null;
            $submission = Submission::find()->where(['id' => $value->submission_id])->one();
            if ($submission) {
                $field = Field::find()->where([
                    'form_id' => $submission->form_id,
                    'handle' => $value->field
                ])->one();
                if ($field) {
                    $value->field = $field->id;
                }
            }
            $value->save(false);
        }
        $this->alterColumn('{{%formidable_submissions_values}}', 'field', $this->integer(11));
        $this->renameColumn('{{%formidable_submissions_values}}', 'field', 'field_id');
        $this->addForeignKey('formidable_submissions_values_field_id_fk', '{{%formidable_submissions_values}}', ['field_id'], '{{%formidable_fields}}', ['id'], 'SET NULL', null);
        $this->addColumn('{{%formidable_forms}}', 'obfuscateFieldNames', $this->boolean()->defaultValue(false)->after('submitLabel'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m211116_172434_V4Changes cannot be reverted.\n";
        return false;
    }
}
