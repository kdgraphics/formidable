<?php 

namespace Inspire\Forms\Services;

use Inspire\Forms\Events\FieldEvent;
use Inspire\Forms\Events\FormEvent;
use Inspire\Forms\Events\RegisterFieldsEvent;
use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\FieldInterface;
use Inspire\Forms\Models\Form;
use Inspire\Forms\Models\Records\Field as FieldRecord;
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
    const EVENT_REGISTER_FIELDS = 'register_fields';
	const CONFIG_KEY = 'inspireforms.2-fields';

    /**
     * Fields keyed by form id
     * @var array
     */
	protected $formFields;
    /**
     * Fields keyed by handle
     * @var array
     */
    protected $registeredFields;
    /**
     * Fields keyed by id
     * @var array
     */
    protected $fields;

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
        if (is_null($this->formFields)) {
            foreach ($this->getAll() as $model) {
                $this->formFields[$model->form_id][$model->id] = $model;
            }
        }
        return $this->formFields[$form->id] ?? [];
    }

    /**
     * Get all fields
     * 
     * @return array
     */
    public function getAll(): array
    {
        if (is_null($this->fields)) {
            $this->fields = [];
            foreach (FieldRecord::find()->orderBy(['order' => SORT_ASC])->all() as $record) {
                $this->fields[$record->id] = $record->toModel();
            }
        }
        return $this->fields;
    }

	/**
     * Get field record by uid
     * 
     * @param  string $uid
     * @return FieldRecord
     */
	public function getRecordByUid(string $uid): FieldRecord
	{
		return FieldRecord::findOne(['uid' => $uid]) ?? new FieldRecord();
	}

    /**
     * Get field model by id
     * 
     * @param  int $id
     * @return ?FieldInterface
     */
	public function getById(int $id): ?FieldInterface
	{
		return $this->getAll()[$id] ?? null;
	}

    /**
     * Get field model by uid
     * 
     * @param  string $uid
     * @return ?FieldInterface
     */
	public function getByUid(string $uid): ?FieldInterface
    {
        return ArrayHelper::firstWhere($this->getAll(), 'uid', $uid, true);
    }

    public function saveMany(array $fields, Form $form, bool $validate = false)
    {
        foreach ($fields as $field) {
            $this->save($field, $form, $validate);
        }
    }

    /**
     * Save a form. Adds/Modifies the project config
     * 
     * @param  FieldInterface $model
     * @param  Form           $form
     * @param  bool           $validate
     * @return bool
     */
	public function save(FieldInterface $model, Form $form, bool $validate = false): bool
	{
		$isNew = empty($model->id);
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE, new FieldEvent([
                'field' => $model,
                'isNew' => $isNew,
            ]));
        }

        if ($validate && !$model->validate()) {
            return false;
        }

        $model->form_id = $form->id;
        if ($isNew) {
            $model->uid = StringHelper::UUID();
        } else {
            $model->uid = Db::uidById('{{%inspireforms_fields}}', $model->id);
        }

        $projectConfig = \Craft::$app->getProjectConfig();

        $configData = [
            'name' => $model->name,
            'form_id' => $form->id,
            'order' => $model->order,
            'fieldClass' => $model->fieldClass,
            'containerClass' => $model->containerClass,
            'instructions' => $model->instructions,
            'type' => $model->handle(),
            'options' => $model->rebuildOptions(true)
        ];

        $configPath = self::CONFIG_KEY . '.' . $model->uid;
        $projectConfig->set($configPath, $configData);

        if ($isNew) {
            $model->id = Db::idByUid('{{%inspireforms_fields}}', $model->uid);
        }

        return true;
	}

    public function deleteMany(array $fields)
    {
        foreach ($fields as $field) {
            $this->delete($field);
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
	 * Handle a added or modified config
	 * 
	 * @param ConfigEvent $event
	 */
	public function handleChanged(ConfigEvent $event)
    {
        $fieldUid = $event->tokenMatches[0];
        $data = $event->newValue;

        $transaction = \Craft::$app->getDb()->beginTransaction();

        try {
            $record = $this->getRecordByUid($fieldUid);
            $isNew = $record->getIsNewRecord();

            $record->name = $data['name'];
            $record->form_id = $data['form_id'];
            $record->order = $data['order'];
            $record->type = $data['type'];
            $record->options = $data['options'];
            $record->fieldClass = $data['fieldClass'];
            $record->containerClass = $data['containerClass'];
            $record->instructions = $data['instructions'];
            $record->uid = $fieldUid;

            $record->save(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->fields = $this->formFields = null;

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new FieldEvent([
                'field' => $record->toModel(),
                'isNew' => $isNew,
            ]));
        }
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
            ->delete('{{%inspireforms_fields}}', ['uid' => $uid])
            ->execute();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE, new FieldEvent([
                'field' => $field,
            ]));
        }
    }
}