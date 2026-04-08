<?php

namespace Inspire\Formidable\models;

use Craft;
use Inspire\Formidable\events\FormEvent;
use Inspire\Formidable\Formidable;

class FormViewHelper
{
    public static function afterValidate(Form $form): void
    {
        foreach ($form->fields as $field) {
            $field->validate();
        }
        foreach ($form->rules as $rule) {
            $rule->validate();
        }
    }

    public static function hasNestedErrors(Form $form): bool
    {
        foreach ($form->fields as $field) {
            if ($field->hasErrors()) {
                return true;
            }
        }
        foreach ($form->rules as $rule) {
            if ($rule->hasErrors()) {
                return true;
            }
        }

        return false;
    }

    public static function buildRules(Form $form): array
    {
        return [
            [['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'notifySpamEmails', 'redirect', 'class', 'method', 'submitLabel', 'color', 'submitClass', 'notifyAdmin_key', 'notify_key', 'notifySpam_key', 'htmlId', 'spamCheck'], 'string'],
            [['name', 'handle', 'notifyField', 'honeypotField', 'notifyEmails', 'redirect', 'class', 'submitLabel', 'color', 'submitClass'], 'trim'],
            [['id'], 'integer'],
            [['honeypot', 'saveSubmissions', 'saveSpam', 'frontEndValidation', 'obfuscateFieldNames', 'autocomplete'], 'boolean', 'trueValue' => true, 'falseValue' => false],
            [['notifyEmails', 'notifySpamEmails'], 'validateEmails'],
            ['handle', 'validateHandle'],
            ['method', 'in', 'range' => ['GET', 'POST']],
            ['notifyEmails', 'required', 'when' => static function ($model) {
                return $model->notifyAdmin_key;
            }],
            ['notifySpamEmails', 'required', 'when' => static function ($model) {
                return $model->notifySpam_key;
            }],
            ['captcha', function ($attribute) use ($form) {
                if (!in_array($form->$attribute, array_keys($form->getCaptchas()), true)) {
                    $form->addError($attribute, \Craft::t('formidable', 'Captcha is not valid'));
                }
            }],
            ['notifyField', 'required', 'when' => static function ($model) {
                return $model->notify_key;
            }],
            ['honeypotField', 'required', 'when' => static function ($model) {
                return $model->honeypot;
            }],
            [['name', 'handle', 'method', 'submitLabel'], 'required'],
            [['notify_key', 'notifyAdmin_key'], 'default', 'value' => null, 'when' => static function ($model, $attribute) {
                return empty($model->$attribute);
            }],
        ];
    }

    public static function render(Form $form, ?string $template = null, array $variables = []): string
    {
        $template = $template ?: 'formidable/forms/form';

        $event = new FormEvent([
            'form' => $form,
            'errors' => $form->getSessionErrors(),
            'oldInput' => $form->getOldInput(),
        ]);
        $form->trigger(Form::EVENT_RENDER, $event);

        $variables = array_merge($variables, [
            'form' => $form,
            'fields' => $form->activeRootFields,
            'errors' => $event->errors,
            'oldInput' => $event->oldInput,
        ]);

        return Craft::$app->getView()->renderTemplate($template, $variables);
    }

    public static function buildFrontJs(Form $form): string
    {
        Formidable::$plugin->forms->enableFrontJs();
        $js = "
document.addEventListener('DOMContentLoaded', (event) => {
    formidable.forms['{$form->generateId()}'] = new formidable.form ({
        id: '{$form->generateId()}',
        rules: [";
        foreach ($form->getRules() as $rule) {
            $js .= $rule->frontJs() . ",
            ";
        }
        $js .= "],
        fields: {
        ";
        foreach ($form->getActiveFields() as $field) {
            $js .= "'" . $field->handle . '\': ' . $field->frontJs($form) . ',
            ';
        }
        $js .= "
        }
    });
});
";
        if ($form->frontEndValidation) {
            $js .= <<<JS
document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('{$form->generateId()}').addEventListener('submit', function(e){
        if (!formidable.validateForm(this, e)) {
            e.preventDefault();
        }
    });
});
JS;
        }
        if ($form->honeypot) {
            $js .= <<<JS
document.addEventListener('DOMContentLoaded', (event) => {
    var css = {position: 'absolute', left: '-100000px', overflow: 'hidden'};
    var elem = document.getElementById('{$form->generateId()}').querySelector('[id={$form->handle}-field-{$form->honeypotField}]');
    for (var property in css) {
        elem.style[property] = css[property];
    }
});
JS;
        }

        return $js;
    }
}
