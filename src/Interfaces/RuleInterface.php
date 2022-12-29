<?php 

namespace Inspire\Formidable\Interfaces;

use Inspire\Formidable\Interfaces\FieldInterface;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormPosted;

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
	 * Does this rule apply to a form posted
	 * 
	 * @param  FormPosted $posted
	 * @return bool
	 */
	public function appliesTo(FormPosted $posted): bool;

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
	 * Validate this rule for a field
	 * 
	 * @param  string     $fieldName
	 * @param  FormPosted $posted
	 */
	public function validateRule(string $fieldName, FormPosted $posted);
}