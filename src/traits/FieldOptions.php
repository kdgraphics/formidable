<?php

namespace Inspire\Formidable\traits;

use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\FormPosted;
use craft\elements\Category;
use craft\elements\Entry;

trait FieldOptions
{
    public $optionsType = 'custom';
    public array $customOptions = [];
    public array $groupUids = [];
    public array $sectionUids = [];

    protected $_options;

    /**
     * @inheritDoc
     **/
    public function defineOptions(): array
    {
        return ['optionsType', 'customOptions', 'groupUids', 'sectionUids'];
    }

    /**
     * @inheritDoc
     */
    public function defineRules(): array
    {
        return [
            ['optionsType', 'in', 'range' => ['custom', 'categories', 'entries'], 'skipOnEmpty' => false],
            ['groupUids', 'required', 'when' => function () {
                return $this->optionsType == 'categories';
            }],
            ['sectionUids', 'required', 'when' => function () {
                return $this->optionsType == 'entries';
            }],
            ['customOptions', 'required', 'when' => function () {
                return $this->optionsType == 'custom';
            }],
            ['customOptions', 'validateCustomOptions', 'when' => function () {
                return $this->optionsType == 'custom';
            }, 'skipOnEmpty' => false]
        ];
    }

    /**
     * Validate the custom options
     * 
     * @param string $attribute
     * @param array $param
     * @param $validator
     */
    public function validateCustomOptions($attribute, $param, $validator)
    {
        $options = $this->$attribute;
        $values = [];
        foreach ($options as $option) {
            if ($option['label'] == '') {
                $this->addError($attribute, \Craft::t('formidable', 'Label is missing'));
            }
            if (in_array($option['value'], $values)) {
                $this->addError($attribute, \Craft::t('formidable', 'Value "{value}" is used twice', ['value' => $option['value']]));
            }
            $values[] = $option['value'];
        }
    }

    /**
     * inheritDoc
     */
    public function getFrontEndRules(FormPosted $posted): array
    {
        return [
            [$this->handle, 'default', 'value' => []],
            [$this->handle, 'each', 'rule' => ['trim']],
            [$this->handle, 'each', 'rule' => ['in', 'range' => array_keys($this->availableOptions)]]
        ];
    }

    /**
     * Get all available options
     * 
     * @return array
     */
    public function getAvailableOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [];
            if ($this->optionsType == 'custom') {
                foreach ($this->customOptions as $option) {
                    $this->_options[$option['value']] = $option['label'];
                }
            }
            if ($this->optionsType == 'categories') {
                $this->_options = $this->getCategories();
            }
            if ($this->optionsType == 'entries') {
                $this->_options = $this->getEntries();
            }
        }
        return $this->_options;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        $options = parent::getOptions();
        if ($options['optionsType'] == 'custom') {
            $options['groupUids'] = [];
            $options['sectionUids'] = [];
        } else if ($options['optionsType'] == 'categories') {
            $options['sectionUids'] = [];
            $options['customOptions'] = [];
        } else if ($options['optionsType'] == 'entries') {
            $options['groupUids'] = [];
            $options['customOptions'] = [];
        }
        return $options;
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlValue(?FieldInterface $field, $value, bool $isOwn = true): string
    {
        if (!$isOwn) {
            return implode('<br/>', $value);
        }
        $populated = $field->getPopulatedValue($value);
        if ($field->optionsType == 'categories' or $field->optionsType == 'entries') {
            $populated = array_map(function ($elem) {
                return '<a target="_blank" href="' . $elem->getCpEditUrl() . '">'.$elem->title.'</a>';
            }, $populated);
        }
        return implode('<br/>', $populated);
    }

    /**
     * @inheritDoc
     */
    public static function getStringValue(?FieldInterface $field, $value, bool $isOwn = true): string
    {
        if (!$isOwn) {
            return implode(', ', $value);
        }
        $populated = $field->getPopulatedValue($value);
        if ($field->optionsType == 'categories' or $field->optionsType == 'entries') {
            $populated = array_map(function ($elem) {
                return $elem->title;
            }, $populated);
        }
        return implode(', ', $populated);
    }

    /**
     * Get fully populated values. Will fetch entries/categories from db
     * 
     * @param  mixed $value
     * @return array
     */
    public function getPopulatedValue($value): array
    {
        if (!$value) {
            return [];
        }
        if (!is_array($value)) {
            $value = [$value];
        }
        if ($this->optionsType == 'categories') {
            $out = Category::find()->id($value)->all();
        } else if ($this->optionsType == 'entries') {
            $out = Entry::find()->id($value)->all();
        } else {
            $out = [];
            foreach ($value as $value) {
                if ($option = $this->findCustomOptionByValue($value)) {
                    $out[$value] = $option['label'];
                } else {
                    $out[$value] = $value;
                }
            }
        }
        return $out;
    }

    /**
     * Load all categories from the selected group
     * 
     * @return array
     */
    protected function getCategories(): array
    {
        $groups = [];
        foreach ($this->groupUids as $uid) {
            $group = \Craft::$app->categories->getGroupByUid($uid);
            if ($group) {
                $groups[] = $group->handle;
            }
        }
        $out = [];
        foreach (Category::find()->group($groups)->all() as $category) {
            $out[$category->id] = $category->title;
        }
        return $out;
    }

    /**
     * get all entries (id, title)
     * 
     * @return array
     */
    protected function getEntries(): array
    {
        $sections = [];
        foreach ($this->sectionUids as $uid) {
            $section = \Craft::$app->sections->getSectionByUid($uid);
            if ($section) {
                $sections[] = $section->handle;
            }
        }
        $out = [];
        foreach (Entry::find()->section($sections)->all() as $entry) {
            $out[$entry->id] = $entry->title;
        }
        return $out;
    }

    /**
     * Find a custom option by value
     * 
     * @param  mixed $value
     * @return ?array
     */
    protected function findCustomOptionByValue($value): ?array
    {
        foreach ($this->customOptions as $option) {
            if ($option['value'] === $value) {
                return $option;
            }
        }
        return null;
    }
}