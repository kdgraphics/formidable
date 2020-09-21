<?php 

namespace Ryssbowh\Formidable\Services;

use Ryssbowh\Formidable\Events\FieldEvent;
use Ryssbowh\Formidable\Events\FormEvent;
use Ryssbowh\Formidable\Events\RegisterFieldsEvent;
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
    const EVENT_BEFORE_SAVE = 'before_save';
    const EVENT_AFTER_SAVE = 'after_save';
    const EVENT_BEFORE_APPLY_DELETE = 'before_apply_delete';
    const EVENT_AFTER_DELETE = 'after_delete';
    const EVENT_BEFORE_DELETE = 'before_delete';

    const CONFIG_KEY = Formidable::CONFIG_KEY . '.fields';

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
            $fields[] = $record->toModel();
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
                $field = $this->getRegistered($data['type']);
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
     * @param  array  $fields
     */
    public function deleteMany(array $fields)
    {
        foreach ($fields as $field) {
            $this->delete($field);
        }
    }

    /**
     * Save a field through config
     * 
     * @param  FieldInterface $field
     * @param  Form           $form
     * @return bool
     */
    public function save(FieldInterface $field, Form $form): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new FieldEvent([
                'field' => $field,
                'isNew' => empty($field->id),
            ]));
        }

        $field->uid = $field->id ? $field->uid : StringHelper::UUID();
        $projectConfig = \Craft::$app->getProjectConfig();
        $configData = $field->getConfig();
        $configData['form'] = $form->uid;
        $configPath = self::CONFIG_KEY . '.' . $field->uid;
        $projectConfig->set($configPath, $configData);

        if (!$field->id) {
            $field->id = Db::idByUid(FieldRecord::tableName(), $field->uid);
        }

        return true;
    }

    /**
     * Save many fields
     * 
     * @param  array  $rules
     * @param  Form   $form
     */
    public function saveMany(array $fields, Form $form)
    {
        foreach ($fields as $field) {
            $this->save($field, $form);
        }
    }

    /**
     * Handle project config changes
     */
    public function handleChanged(ConfigEvent $event)
    {
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;
        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $field = $this->getRecordByUid($uid);
            $isNew = $field->getIsNewRecord();
            $field->name = $data['name'];
            $field->form_id = Formidable::$plugin->forms->getByUid($data['form'])->id;
            $field->order = $data['order'];
            $field->type = $data['type'];
            $field->options = $data['options'];
            $field->fieldClass = $data['fieldClass'];
            $field->containerClass = $data['containerClass'];
            $field->labelClass = $data['labelClass'];
            $field->instructions = $data['instructions'];
            $field->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new FieldEvent([
                'field' => $field->toModel(),
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * Delete field model
     * 
     * @param  FieldInterface $model
     * @return bool
     */
    public function delete(FieldInterface $model): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_DELETE, new FieldEvent([
                'field' => $model,
            ]));
        }
        
        \Craft::$app->getProjectConfig()->remove(self::CONFIG_KEY . '.' . $model->uid);

        return true;
    }

    /**
     * Handle a deleted config
     * 
     * @param ConfigEvent $event
     */
    public function handleDeleted(ConfigEvent $event)
    {
        $uid = $event->tokenMatches[0];

        $field = $this->getByUid($uid);

        if (!$field) {
            return;
        }

        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_DELETE, new FieldEvent([
                'field' => $field,
            ]));
        }

        \Craft::$app->getDb()->createCommand()
            ->delete(FieldRecord::tableName(), ['uid' => $uid])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new FieldEvent([
                'field' => $field,
            ]));
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