<?php

namespace Inspire\Formidable\services;

use Inspire\Formidable\models\Form as FormModel;

class FormErrorService
{
    public function gatherErrors(FormModel $form): array
    {
        $errors = [];

        if ($formErrors = $form->getErrors()) {
            $errors['form'] = $formErrors;
        }

        foreach ($form->fields as $index => $field) {
            if ($fieldErrors = $field->getErrors()) {
                $errors['fields'][$index] = $fieldErrors;
            }
        }

        foreach ($form->rules as $index => $rule) {
            if ($ruleErrors = $rule->getErrors()) {
                $errors['rules'][$index]['errors'] = $ruleErrors;
            }

            foreach ($rule->conditions as $conditionIndex => $condition) {
                if ($conditionErrors = $condition->getErrors()) {
                    $errors['rules'][$index]['conditions'][$conditionIndex] = $conditionErrors;
                }
            }

            foreach ($rule->conditionGroups as $groupIndex => $group) {
                if ($groupErrors = $group->getErrors()) {
                    $errors['rules'][$index]['conditionsGroups'][$groupIndex]['errors'] = $groupErrors;
                }

                foreach ($group->condition as $conditionIndex => $condition) {
                    if ($conditionErrors = $condition->getErrors()) {
                        $errors['rules'][$index]['conditionsGroups'][$groupIndex]['conditions'][$conditionIndex] = $conditionErrors;
                    }
                }
            }
        }

        return $errors;
    }
}
