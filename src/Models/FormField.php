<?php

namespace Ryssbowh\Formidable\Models;

use Ryssbowh\Formidable\Exceptions\FormException;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Models\TemplateForm;
use craft\base\ElementInterface;
use craft\base\Field;
use yii\db\Schema;

class FormField extends Field
{
    public $allowedForms = [];
    public $templates = [];

    /**
     * @inheritDoc
     */
    public function getContentColumnType(): string {
        return Schema::TYPE_INTEGER;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsHtml()
    {
        $allForms = [];
        foreach (Formidable::$plugin->forms->all as $form) {
            $allForms[$form->uid] = $form->name;
        }
        return \Craft::$app->view->renderTemplate('formidable/cp/field-settings', [
            'forms' => $allForms,
            'allowedForms' => $this->allowedForms,
            'templates' => $this->templates
        ]);
    }

    /**
     * @inheritDoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
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
            $template = $this->templates[$form->uid] ?? null;
        }
        return new TemplateForm([
            'form' => $form,
            'template' => $template
        ]);
    }

    /**
     * @inheritDoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return $value->form ? $value->form->id : null;
    }

    /**
     * @inheritDoc
     */
    public function isValueEmpty($value, ElementInterface $element): bool {
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