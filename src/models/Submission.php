<?php

namespace Inspire\Formidable\models;

use Inspire\Formidable\Formidable;
use craft\base\Model;

class Submission extends Model
{
    public $id;
    public $form_id;
    public $siteId;
    public $siteName = null;
    public $read = false;
    public $notified = false;
    public $adminNotified = false;
    public $dateCreated;
    public $dateUpdated;
    public $dateDeleted;
    public $uid;

    protected $_values;
    protected $_form;

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        return [
            [['id', 'form_id', 'siteId', 'read', 'notified', 'adminNotified', 'dateUpdated', 'dateCreated', 'dateDeleted', 'uid', 'values'], 'safe']
        ];
    }

    /**
     * Values setter
     *
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->_values = $values;
    }

    /**
     * Get values
     *
     * @return array
     */
    public function getValues(): array
    {
        if ($this->_values === null) {
            $this->_values = Formidable::$plugin->submissions->getValuesForSubmission($this);
        }

        return $this->_values;
    }

    /**
     * Get Form
     *
     * @return ?Form
     */
    public function getForm(): ?Form
    {
        if ($this->_form === null and $this->form_id) {
            $this->_form = Formidable::$plugin->forms->getById($this->form_id);
        }
        return $this->_form;
    }

    /**
     * Get a value styled for a field handle
     *
     * @param  string $fieldHandle
     * @param  string|null $style the style for the value, null for the value's default
     * @return mixed
     */
    public function getValue(string $fieldHandle, string $style = 'raw')
    {
        $value = array_values(array_filter($this->values, function ($value) use ($fieldHandle) {
            $field = $value->field;
            return ($field and $field->handle == $fieldHandle);
        }))[0] ?? null;

        if (!$value) {
            return null;
        }

        return $value->getValue($style);
    }

    /**
     * Get a value model by id
     *
     * @param  int    $id
     * @return ?SubmissionValue
     */
    public function getValueById(int $id): ?SubmissionValue
    {
        return array_filter($this->values, function ($value) use ($id) {
            return $value->id == $id;
        })[0] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), ['form', 'values']);
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $array = parent::toArray($fields, $expand, $recursive);

        if ($this->dateCreated instanceof \DateTime) {
            $array['dateCreated'] = $this->dateCreated->format('d/m/Y H:i');
        }

        return $array;
    }
}
