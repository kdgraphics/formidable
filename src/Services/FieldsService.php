<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\FieldEvent;
use Ryssbowh\Formidable\Events\FormEvent;
use Ryssbowh\Formidable\Events\RegisterFieldsEvent;
use Ryssbowh\Formidable\Exceptions\FormException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\Records\Field as FieldRecord;
use Ryssbowh\Formidable\Models\Records\Form as FormRecord;
use craft\base\Component;
use craft\events\ConfigEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;

class FieldsService extends Component
{
    const EVENT_REGISTER_FIELDS = 'register_fields';

    /**
     * Fields keyed by handle
     * @var array
     */
    protected $registeredFields;

    /**
     * Get all registered fields 
     * 
     * @return array
     */
    public function getAllRegistered(): array
    {
        if (is_null($this->registeredFields)) {
            $event = new RegisterFieldsEvent;
            $this->trigger(self::EVENT_REGISTER_FIELDS, $event); 
            $this->registeredFields = $event->getFields();
        }
        return $this->registeredFields;
    }

    /**
     * Get a registered field by handle
     * 
     * @param  string $handle
     * @return ?FieldInterface
     */
    public function getRegistered(string $handle): ?FieldInterface
    {
        return $this->getAllRegistered()[$handle] ?? null;
    }

    /**
     * Get all fields for a form
     * 
     * @param  Form   $form
     * @return array
     */
    public function getFormFields(Form $form): array
    {
        $fields = [];
        foreach (FieldRecord::find()->where(['form_id' => $form->id])->orderBy(['order' => SORT_ASC])->all() as $record) {
            try {
                $fields[] = $record->toModel();
            } catch (FormException $e) {}
        }
        return $fields;
    }

    /**
     * Validate an array of fields data
     * 
     * @param  array  $fieldsData
     * @return array
     */
    public function validateData(array $fieldsData): array
    {
        $fields = [];
        foreach ($fieldsData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $field = $this->getById($data['id']);
            } else {
                $field = clone $this->getRegistered($data['type']);
            }
            $field->setAttributes($data);
            $field->validate();
            $fields[$index] = $field;
        }
        return $fields;
    }

    /**
     * Get field record by uid
     * 
     * @param  string $uid
     * @return FieldRecord
     */
    public function getRecordByUid(string $uid): FieldRecord
    {
        return FieldRecord::findOne(['uid' => $uid]) ?? new FieldRecord(['uid' => $uid]);
    }

    /**
     * Get field model by id
     * 
     * @param  int $id
     * @return ?FieldInterface
     */
	public function getById(int $id): ?FieldInterface
	{
		$field = FieldRecord::find()->where(['id' => $id])->one();
        return $field ? $field->toModel() : null;
	}

    /**
     * Get field model by uid
     * 
     * @param  string $uid
     * @return ?FieldInterface
     */
	public function getByUid(string $uid): ?FieldInterface
    {
        $field = FieldRecord::find()->where(['uid' => $uid])->one();
        return $field ? $field->toModel() : null;
    }

    /**
     * Delete many fields
     * 
     * @param  int    $formId
     * @param  array  $keepIds
     */
    public function deleteForForm(int $formId, array $keepIds)
    {
        $fields = FieldRecord::find()->where(['form_id' => $formId])
            ->andWhere(['not in', 'id', $keepIds])->all();
        foreach ($fields as $field) {
            $field->delete();
        }
    }

    /**
     * Checks a field name exists for a form id
     * 
     * @param  string $name
     * @param  int    $form
     * @return bool
     */
    public function existsForForm(string $name, int $form): bool
    {
        return is_object(FieldRecord::find()->where(['name' => $name, 'form_id' => $form])->one());
    }
}