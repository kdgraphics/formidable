<?php

namespace Ryssbowh\Formidable\migrations;

use Craft;
use Ryssbowh\Formidable\Models\Records\Field;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Records\SubmissionValue;
use craft\db\Migration;
use craft\db\Query;

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
        $this->alterColumn('{{%formidable_submissions_values}}', 'field', $this->string(255)->null());
        $values = (new Query())
            ->select(["formidable_submissions_values.id as value_id", "field", "formidable_fields.id as field_id"])
            ->from("{{%formidable_submissions_values}}")
            ->leftJoin("{{%formidable_submissions}}", "formidable_submissions.id = formidable_submissions_values.submission_id")
            ->leftJoin("{{%formidable_fields}}", "formidable_fields.form_id = formidable_submissions.form_id AND formidable_fields.handle = formidable_submissions_values.field")
            ->all();
        foreach ($values as $value) {
            $value['field_id'] = $value['field_id'] ?? null;
            $this->update('{{%formidable_submissions_values}}', ['field' => $value['field_id']], ['id' => $value['value_id']]);
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
        echo "m211116_172433_V4Changes cannot be reverted.\n";
        return false;
    }
}
