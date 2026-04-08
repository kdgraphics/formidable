<?php

namespace Inspire\Formidable\services;

use Craft;
use Inspire\Formidable\events\FormSubmissionEvent;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\models\Form as FormModel;
use Inspire\Formidable\models\FormSubmission;
use Inspire\SpamCheck\SpamCheck;
use yii\web\View;

class FormSubmissionService
{
    public const FRONT_JS_FILE = __DIR__ . '/../assets/dist/validation.js';

    public function __construct(private FormsService $formsService)
    {
    }

    public function enableFrontJs(): void
    {
        if ($this->formsService->frontJsRegistered) {
            return;
        }

        $js = file_get_contents(self::FRONT_JS_FILE);
        $rules = '';

        foreach (Formidable::$plugin->rules->getAllRegistered() as $rule) {
            $rules .= $rule->handle() . ": {
                requiresValue: " . ($rule->requiresValue ? 'true' : 'false') . ",
                requiresField: " . ($rule->requiresField ? 'true' : 'false') . ",
                check: {$rule->checkJs()}
            },
            ";
        }

        $js = str_replace('$RULES$', $rules, $js);
        Craft::$app->view->registerJs($js, View::POS_END);
        $this->formsService->frontJsRegistered = true;
    }

    public function submit(FormModel $form): array
    {
        $session = Craft::$app->session;
        $posted = Craft::$app->request->getBodyParams();
        $values = $this->normalizePostedValues($form, $posted);

        $session->setFlash(FormsService::SESSION_OLDINPUT_KEY . '.' . $form->handle, $values);

        $formSubmission = new FormSubmission($values);
        $formSubmission->setForm($form);
        $formSubmission->setRules(Formidable::$plugin->rules->getFormRules($form));

        foreach ($form->fields as $field) {
            $field->beforeSubmissionValidated($formSubmission);
        }

        if ($this->isSpamSubmission($form, $formSubmission, $posted, $values)) {
            return $this->onSpam($form, $formSubmission, $posted);
        }

        $event = new FormSubmissionEvent([
            'submission' => $formSubmission,
            'form' => $form,
            'posted' => $posted,
        ]);
        $this->formsService->trigger(FormsService::EVENT_BEFORE_VALIDATING_SUBMISSION, $event);

        if ($event->status === FormsService::INVALID) {
            return $this->onInvalid($form, $formSubmission, $posted);
        }
        if ($event->status === FormsService::SPAM) {
            return $this->onSpam($form, $formSubmission, $posted);
        }

        $formSubmission->validate(null, false);
        if (!$formSubmission->getErrors()) {
            foreach ($form->fields as $field) {
                $field->afterSubmissionValidated($formSubmission);
            }

            $submission = Formidable::$plugin->submissions->create($form, $formSubmission);
            $session->setFlash(FormsService::SESSION_SUCCESS_KEY . '.' . $form->handle, true);

            return [FormsService::VALID, $submission, $posted];
        }

        return $this->onInvalid($form, $formSubmission, $posted);
    }

    private function normalizePostedValues(FormModel $form, array &$posted): array
    {
        $values = [];

        foreach ($form->fields as $field) {
            $postedValue = $posted[$field->frontName] ?? null;
            unset($posted[$field->frontName]);
            $posted[$field->handle] = $postedValue;
            $values[$field->handle] = $postedValue;
        }

        if (!$form->honeypot) {
            return $values;
        }

        if ($form->obfuscateFieldNames) {
            $honeypotField = $form->obfuscateFieldName($form->honeypotField);
            $posted[$form->honeypotField] = $posted[$honeypotField] ?? 'missing honeypot from post';
            unset($posted[$honeypotField]);

            return $values;
        }

        $posted[$form->honeypotField] = $posted[$form->honeypotField] ?? 'missing honeypot from post';

        return $values;
    }

    private function isSpamSubmission(FormModel $form, FormSubmission $formSubmission, array $posted, array $values): bool
    {
        if ($form->honeypot && !empty(trim($posted[$form->honeypotField]))) {
            $formSubmission->_spam_reason = Craft::t('formidable', 'Honeypot was filled');
            $formSubmission->addError('*', Craft::t('formidable', 'Our spam protection software has detected a high probability that this is a spam or bot submission. If this is a real enquiry please try another browser just in case there is a problem with this one for you.'));
            Formidable::$plugin->spam->create($form, $formSubmission, $posted);

            return true;
        }

        if ($form->captcha && !$form->getCaptcha()->verify($form, $formSubmission, $posted)) {
            Formidable::$plugin->spam->create($form, $formSubmission, $posted);

            return true;
        }

        if (!$form->spamCheck || !$form->hasField($form->spamCheck)) {
            return false;
        }

        $result = SpamCheck::$plugin->spamCheck->email(trim($values[$form->spamCheck]));
        if (!$result || $result['frequency'] <= 10) {
            return false;
        }

        $formSubmission->_spam_reason = Craft::t('formidable', 'Known spammer');
        $formSubmission->addError('*', Craft::t('formidable', 'Our spam protection software has detected a high probability that this is a spam or bot submission. If this is a real enquiry please try another browser just in case there is a problem with this one for you.'));
        Formidable::$plugin->spam->create($form, $formSubmission, $posted);

        return true;
    }

    private function onSpam(FormModel $form, FormSubmission $formSubmission, array $posted): array
    {
        Craft::$app->session->setFlash(
            FormsService::SESSION_ERRORS_KEY . '.' . $form->handle,
            $formSubmission->errors
        );

        return [FormsService::SPAM, $formSubmission, $posted];
    }

    private function onInvalid(FormModel $form, FormSubmission $formSubmission, array $posted): array
    {
        Craft::$app->session->setFlash(
            FormsService::SESSION_ERRORS_KEY . '.' . $form->handle,
            $formSubmission->errors
        );

        return [FormsService::INVALID, $formSubmission, $posted];
    }
}
