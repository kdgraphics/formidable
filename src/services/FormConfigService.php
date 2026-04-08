<?php

namespace Inspire\Formidable\services;

use Craft;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\models\Records\Form as FormRecord;
use craft\events\ConfigEvent;

class FormConfigService
{
    public function __construct(private FormsService $formsService)
    {
    }

    /**
     * @return array{0: FormRecord, 1: bool}
     */
    public function applyChanged(ConfigEvent $event): array
    {
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            $form = $this->formsService->getRecordByUid($uid);
            $isNew = $form->getIsNewRecord();

            $this->applyFormAttributes($form, $data);
            $this->syncFields($form, $data['fields'] ?? []);
            $this->syncRules($form, $data['rules'] ?? []);

            $transaction->commit();

            return [$form, $isNew];
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    private function applyFormAttributes(FormRecord $form, array $data): void
    {
        $form->name = $data['name'];
        $form->handle = $data['handle'];
        $form->notifyAdmin_key = $data['notifyAdmin_key'];
        $form->notify_key = $data['notify_key'];
        $form->notifySpam_key = $data['notifySpam_key'];
        $form->notifyField = $data['notifyField'];
        $form->notifyEmails = $data['notifyEmails'];
        $form->notifySpamEmails = $data['notifySpamEmails'];
        $form->honeypot = $data['honeypot'];
        $form->honeypotField = $data['honeypotField'];
        $form->saveSubmissions = $data['saveSubmissions'];
        $form->frontEndValidation = $data['frontEndValidation'];
        $form->saveSpam = $data['saveSpam'];
        $form->spamCheck = $data['spamCheck'] ?? '';
        $form->redirect = $data['redirect'];
        $form->class = $data['class'];
        $form->htmlId = $data['htmlId'];
        $form->method = $data['method'];
        $form->submitLabel = $data['submitLabel'];
        $form->submitClass = $data['submitClass'];
        $form->obfuscateFieldNames = $data['obfuscateFieldNames'] ?? false;
        $form->color = $data['color'];
        $form->captcha = $data['captcha'];
        $form->save(false);
    }

    private function syncFields(FormRecord $form, array $fieldsData): void
    {
        $fieldIds = [];

        foreach ($fieldsData as $index => $fieldData) {
            $field = $this->saveField($form, $fieldData, $index);
            $fieldIds[] = $field->id;

            if (($fieldData['type'] ?? null) !== 'group') {
                continue;
            }

            foreach ($fieldData['fields'] ?? [] as $groupIndex => $groupFieldData) {
                $groupFieldData['group_id'] = $field->id;
                $fieldIds[] = $this->saveField($form, $groupFieldData, $groupIndex)->id;
            }
        }

        Formidable::$plugin->fields->deleteForForm($form->id, $fieldIds);
    }

    private function syncRules(FormRecord $form, array $rulesData): void
    {
        $ruleIds = [];

        foreach ($rulesData as $ruleData) {
            $rule = Formidable::$plugin->rules->getRecordByUid($ruleData['uid']);
            $rule->form_id = $form->id;
            $rule->type = $ruleData['type'];
            $rule->title = $ruleData['title'] ?? '';
            $rule->message = $ruleData['message'];
            $rule->messages = $ruleData['messages'] ?? '{}';
            $rule->value = $ruleData['value'];
            $rule->fields = $ruleData['fields'];
            $rule->save(false);
            $ruleIds[] = $rule->id;

            $this->syncConditionGroups($rule->id, $ruleData['conditionGroups'] ?? []);
            $this->syncRuleConditions($rule->id, $ruleData['conditions'] ?? []);
        }

        Formidable::$plugin->rules->deleteForForm($form->id, $ruleIds);
    }

    private function syncConditionGroups(int $ruleId, array $groupsData): void
    {
        $groupIds = [];

        foreach ($groupsData as $groupData) {
            $group = Formidable::$plugin->conditionGroups->getRecordByUid($groupData['uid']);
            $group->rule_id = $ruleId;
            $group->operator = $groupData['operator'];
            $group->negate = $groupData['negate'];
            $group->save(false);
            $groupIds[] = $group->id;

            $conditionIds = [];
            foreach ($groupData['conditions'] ?? [] as $conditionData) {
                $condition = Formidable::$plugin->conditions->getRecordByUid($conditionData['uid']);
                $condition->conditiongroup_id = $group->id;
                $condition->type = $conditionData['type'];
                $condition->negate = $conditionData['negate'];
                $condition->operator = $conditionData['operator'];
                $condition->value = $conditionData['value'];
                $condition->field = $conditionData['field'];
                $condition->save(false);
                $conditionIds[] = $condition->id;
            }

            Formidable::$plugin->conditions->deleteForGroup($group->id, $conditionIds);
        }

        Formidable::$plugin->conditionGroups->deleteForRule($ruleId, $groupIds);
    }

    private function syncRuleConditions(int $ruleId, array $conditionsData): void
    {
        $conditionIds = [];

        foreach ($conditionsData as $conditionData) {
            $condition = Formidable::$plugin->conditions->getRecordByUid($conditionData['uid']);
            $condition->rule_id = $ruleId;
            $condition->type = $conditionData['type'];
            $condition->negate = $conditionData['negate'];
            $condition->operator = $conditionData['operator'];
            $condition->value = $conditionData['value'];
            $condition->field = $conditionData['field'];
            $condition->save(false);
            $conditionIds[] = $condition->id;
        }

        Formidable::$plugin->conditions->deleteForRule($ruleId, $conditionIds);
    }

    private function saveField(FormRecord $form, array $fieldData, int $order): object
    {
        $field = Formidable::$plugin->fields->getRecordByUid($fieldData['uid']);
        $field->group_id = $fieldData['group_id'] ?? null;
        $field->active = $fieldData['active'];
        $field->name = $fieldData['name'];
        $field->handle = $fieldData['handle'];
        $field->form_id = $form->id;
        $field->order = $order;
        $field->type = $fieldData['type'];
        $field->options = $fieldData['options'];
        $field->fieldClass = $fieldData['fieldClass'];
        $field->containerClass = $fieldData['containerClass'];
        $field->labelClass = $fieldData['labelClass'];
        $field->instructions = $fieldData['instructions'];
        $field->save(false);

        return $field;
    }
}
