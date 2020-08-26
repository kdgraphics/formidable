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
            $fields[$record->id] = $record->toModel();
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
     * Delete field model
     * 
     * @param  FieldInterface $model
     * @return bool
     */
    public function delete(FieldInterface $model): bool
    {
        return \Craft::$app->getDb()->createCommand()
            ->softDelete('{{%inspireforms_fields}}', ['uid' => $model->uid])
            ->execute();
    }
}