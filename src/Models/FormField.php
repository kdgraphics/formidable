<?php

namespace Inspire\Formidable\Models;

use Inspire\Formidable\Exceptions\FormException;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\TemplateForm;
use craft\base\ElementInterface;
use craft\base\Field;
use yii\db\Schema;

class FormField extends Field
{
    public $allowedForms = [];
    public $templates = [];
    public $defaultTemplate;

    /**
     * @inheritDoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_INTEGER;
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = ['defaultTemplate', 'required'];
        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsHtml(): ?string
    {
        $allForms = [];
        foreach (Formidable::$plugin->forms->all as $form) {
            $allForms[$form->uid] = $form->name;
        }
        return \Craft::$app->view->renderTemplate('formidable/cp/field-settings', [
            'forms' => $allForms,
            'field' => $this
        ]);
    }

    /**
     * @inheritDoc
     */
    public function normalizeValue($value, ElementInterface $element = null): mixed
    {
        if ($value instanceof TemplateForm) {
            return $value;
        }
        $form = null;
        try {
            if ($value) {
                $form = Formidable::$plugin->forms->getById($value);
            }
        } catch (FormException $e) {}
        $template = null;
        if ($form) {
            $template = (isset($this->templates[$form->uid]) and $this->templates[$form->uid]) ? $this->templates[$form->uid] : $this->defaultTemplate;
        }
        return new TemplateForm([
            'form' => $form,
            'template' => $template
        ]);
    }

    /**
     * @inheritDoc
     */
    public function serializeValue($value, ElementInterface $element = null): mixed
    {
        return $value->form ? $value->form->id : null;
    }

    /**
     * @inheritDoc
     */
    public function isValueEmpty($value, ElementInterface $element): bool
    {
        if ($value instanceof TemplateForm) {
          return ($value->form === null);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $forms = ['' => 'Select Form'];
        foreach ($this->getForms() as $form) {
            $forms[$form->id] = $form->name;
        }
        return \Craft::$app->view->renderTemplate('formidable/cp/field-html', [
            'forms' => $forms,
            'name' => $this->handle,
            'value' => $value,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getForms(): array
    {
        if ($this->allowedForms == '') {
            $this->allowedForms = [];
        }
        if ($this->allowedForms == '*') {
            return Formidable::$plugin->forms->all;
        }
        $out = [];
        foreach ($this->allowedForms as $uid) {
            if ($form = Formidable::$plugin->forms->getByUid($uid)) {
                $out[] = $form;
            }
        }
        return $out;
    }

    /**
     * @inheritDoc
     */
    static public function displayName(): string {
        return \Craft::t('formidable', 'Form');
    }
}
