<?php 

namespace Inspire\Formidable\Services;

use Inspire\Formidable\Events\FieldEvent;
use Inspire\Formidable\Events\FormEvent;
use Inspire\Formidable\Events\RegisterFieldsEvent;
use Inspire\Formidable\Exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\Records\Field as FieldRecord;
use Inspire\Formidable\Models\Records\Form as FormRecord;
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
     * @var array
     */
    protected $_fields;

    /**
     * Reset internal cache
     */
    public function reset()
    {
        $this->_fields = null;
    }

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
     * @param  Form    $form
     * @param  boolean $all include fields within groups as well
     * @return array
     */
    public function getForForm(Form $form, bool $all = false): array
    {
        return array_values(array_filter($this->all(), function ($field) use ($all, $form) {
            if ($form->id != $field->form_id) {
                return false;
            }
            if (!$all and $field->group_id != null) {
                return false;
            }
            return true;
        }));
    }

    /**
     * Get all fields for a group
     * 
     * @param  int    $groupId
     * @return array
     */
    public function getForGroup(int $groupId): array
    {
        return array_values(array_filter($this->all(), function ($field) use ($groupId) {
            return $field->group_id == $groupId;
        }));
    }

    /**
     * Populate an array of fields data into field instances
     * 
     * @param  array  $fieldsData
     * @return array
     */
    public function populateFromData(array $fieldsData): array
    {
        $fields = [];
        foreach ($fieldsData as $index => $data) {
            if (isset($data['id']) and is_numeric($data['id'])) {
                $field = $this->getById($data['id']);
            } else {
                $field = clone $this->getRegistered($data['type']);
            }
            if ($data['type'] == 'group') {
                $groupFields = [];
                foreach ($data['fields'] ?? [] as $fieldData) {
                    $fieldData['group_id'] = null;
                    if (isset($fieldData['id']) and is_numeric($fieldData['id'])) {
                        $field2 = $this->getById($fieldData['id']);
                    } else {
                        $field2 = clone $this->getRegistered($fieldData['type']);
                    }
                    $field2->setAttributes($fieldData);
                    $field2->group = $field;
                    $field2->validate();
                    $groupFields[] = $field2;
                }
                $field->fields = $groupFields;
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
        $fields = array_values(array_filter($this->all(), function ($field) use ($id) {
            return $field->id == $id;
        }));
        return $fields[0] ?? null;
    }

    /**
     * Get field model by uid
     * 
     * @param  string $uid
     * @return ?FieldInterface
     */
    public function getByUid(string $uid): ?FieldInterface
    {
        $fields = array_values(array_filter($this->all(), function ($field) use ($uid) {
            return $field->uid == $uid;
        }));
        return $fields[0] ?? null;
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
        $this->_fields = null;
    }

    /**
     * Get all defined fields
     * 
     * @return array
     */
    protected function all(): array
    {
        if ($this->_fields === null) {
            $fields = [];
            $records = FieldRecord::find()->orderBy(['order' => SORT_ASC])->all();
            foreach ($records as $record) {
                try {
                    $fields[] = $record->toModel();
                } catch (FormException $e) {}
            }
            $this->_fields = $fields;
        }
        return $this->_fields;
    }
}
