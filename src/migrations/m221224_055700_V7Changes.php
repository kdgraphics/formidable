<?php

namespace Inspire\Formidable\migrations;

use Craft;
use Inspire\Formidable\Exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\records\FieldRecord;
use Inspire\Formidable\records\SpamRecord;
use Inspire\Formidable\records\SpamValueRecord;
use Inspire\Formidable\records\SubmissionValueRecord;
use craft\db\Migration;
use craft\helpers\Json;

/**
 * m221224_055700_V7Changes migration.
 */
class m221224_055700_V7Changes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        foreach (FieldRecord::find()->all() as $field) {
            $options = Json::decode($field->options);
            if (in_array($field->type, ['checkboxes', 'select', 'radio'])) {
                $options['optionsType'] = 'custom';
                $options['customOptions'] = $options['choices'];
                unset($options['choices']);
            }
            if ($field->type == 'file') {
                $accept = array_map(function ($ext) {
                    return '.' . $ext;
                }, explode(',', $options['extensions']));
                $options['accepts'] = implode(',', $accept);
                unset($options['extensions']);
            }
            if (in_array($field->type, ['checkbox_category', 'select_category', 'radio_category'])) {
                $uids = [];
                foreach ($options['groups'] ?? [] as $handle) {
                    $group = \Craft::$app->categories->getGroupByHandle($handle);
                    if ($group) {
                        $uids[] = $group->uid;
                    }
                }
                $options['groupUids'] = $uids;
                $options['optionsType'] = 'categories';
                $options['customOptions'] = [];
                unset($options['groups']);
            }
            if (in_array($field->type, ['checkbox_entry', 'select_entry', 'radio_entry'])) {
                $options['sectionUids'] = $options['sections'];
                $options['optionsType'] = 'entries';
                $options['customOptions'] = [];
                unset($options['sections']);
            }
            if (isset($options['choiceContainerClass'])) {
                $options['optionContainerClass'] = $options['choiceContainerClass'];
                unset($options['choiceContainerClass']);
            }
            if (isset($options['choiceLabelClass'])) {
                $options['optionLabelClass'] = $options['choiceLabelClass'];
                unset($options['choiceLabelClass']);
            }
            if (in_array($field->type, ['checkbox_entry', 'checkbox_category'])) {
                $field->type = 'checkboxes';
            }
            if (in_array($field->type, ['select_entry', 'select_category'])) {
                $field->type = 'select';
            }
            if (in_array($field->type, ['radio_entry', 'radio_category'])) {
                $field->type = 'radio';
            }
            $field->options = $options;
            $field->save(false);
        }
        foreach (SubmissionValueRecord::find()->all() as $value) {
            if ($value->type == 'select') {
                $field = $value->field_id ? Formidable::$plugin->fields->getById($value->field_id) : null;
                if ($field) {
                    $_value = Json::decode($value->value);
                    $options = $field->availableOptions;
                    $newValue = [];
                    foreach ($_value as $elem) {
                        $new = array_search($elem, $options);
                        if ($new !== false) {
                            $newValue[] = $new;
                        } else {
                            $newValue[] = $elem;
                        }
                    }
                    $value->value = $newValue;
                }
            }
            if (in_array($value->type, ['checkbox_entry', 'checkbox_category'])) {
                $value->type = 'checkboxes';
            }
            if (in_array($value->type, ['radio_entry', 'radio_category'])) {
                $value->type = 'radio';
            }
            if (in_array($value->type, ['select_entry', 'select_category'])) {
                $value->type = 'select';
            }
            $value->save(false);
        }
        $this->dropColumn('{{%formidable_forms}}', 'frontEndValidation');
        $this->addColumn('{{%formidable_spams}}', 'user-agent', $this->text());
        $this->createTable('{{%formidable_spams_values}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(255),
            'field_id' => $this->integer(11),
            'spam_id' => $this->integer(11)->notNull(),
            'value' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
        $this->addForeignKey('formidable_spamvalues_spam_id_fk', '{{%formidable_spams_values}}', ['spam_id'], '{{%formidable_spams}}', ['id'], 'CASCADE', null);
        $this->addForeignKey('formidable_spam_values_field_id_fk', '{{%formidable_spams_values}}', ['field_id'], '{{%formidable_fields}}', ['id'], 'SET NULL', null);
        foreach (SpamRecord::findWithTrashed()->all() as $spam) {
            $values = Json::decode($spam->values);
            $form = Formidable::$plugin->forms->getById($spam->form_id);
            foreach ($values as $handle => $value) {
                $field = null;
                try {
                    $field = $form->getField($handle);
                } catch (FormException $e) {}
                $newValue = new SpamValueRecord([
                    'field_id' => $field ? $field->id : null,
                    'type' => $field ? $field->handle() : null,
                    'value' => Json::encode($value),
                    'spam_id' => $spam->id,
                    'dateCreated' => $spam->dateCreated,
                    'dateUpdated' => $spam->dateUpdated
                ]);
                $newValue->save(false);
            }
        }
        $this->dropColumn('{{%formidable_spams}}', 'values');
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m221224_055700_V7Changes cannot be reverted.\n";
        return false;
    }
}
