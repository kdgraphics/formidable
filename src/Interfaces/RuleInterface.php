<?php 

namespace Ryssbowh\Formidable\Interfaces;

use Ryssbowh\Formidable\Interfaces\FieldInterface;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;

interface RuleInterface
{
	/**
	 * Name for this rule
	 * 
	 * @return string
	 */
	public function name(): string;

	/**
	 * Handle for this rule
	 * 
	 * @return string
	 */
	public function handle(): string;

	/**
	 * Default error message for this rule
	 * 
	 * @return string
	 */
	public function defaultMessage(): string;

	/**
	 * Name when rule is used in a condition
	 * 
	 * @return string
	 */
	public function conditionName(): string;

	/**
	 * Which field types this rule can handle
	 * 
	 * @return array
	 */
	public function validFieldTypes(): array;

	/**
	 * Does this rule apply to a form submission
	 * 
	 * @param  FormSubmission $submission
	 * @return bool
	 */
	public function appliesTo(FormSubmission $submission): bool;

	/**
	 * Check a value against another value
	 * 
	 * @param  FieldInterface $field
	 * @param  mixed      $value
	 * @param  mixed|null $against
	 * @throws RuleException
	 * @return bool
	 */
	public function check(FieldInterface $field, $value, $against = null): bool;

	/**
	 * Javascript code to check this rule. must return a function(field, value, against){}
	 * 
	 * @return string
	 */
	public function checkJs(): string;

	/**
	 * Validate this rule for a field
	 * 
	 * @param  string         $fieldName
	 * @param  FormSubmission $submission
	 */
	public function validateRule(string $fieldName, FormSubmission $submission);
}