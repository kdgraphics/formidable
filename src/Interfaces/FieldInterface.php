<?php 

namespace Ryssbowh\Formidable\Interfaces;

use Ryssbowh\Formidable\Models\FormSubmission;

interface FieldInterface
{	
	/**
	 * Value getter
	 * 
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Value setter
	 */
	public function setValue($value);
	
	/**
	 * Machine name for this field
	 * 
	 * @return string
	 */
	public function handle(): string;

	/**
	 * Name of the field for front end
	 * 
	 * @return string
	 */
	public function getFrontName(): string;

	/**
	 * Friendly name for this field
	 * 
	 * @return string
	 */
	public function friendlyName(): string;

	/**
	 * Get field type
	 * 
	 * @return FieldTypeInterface
	 */
	public function fieldType(): FieldTypeInterface;

	/**
	 * Template to render in back end
	 *  
	 * @return string
	 */
	public function backendTemplate(): string;

	/**
	 * Extra back end rules
	 * 
	 * @return array
	 */
	public function getExtraCpRules(): array;

	/**
	 * Rules when submitting this field through the front end
	 * 
	 * @return array
	 */
	public function getFrontEndRules(FormSubmission $submission): array;

	/**
	 * Treatment before submission is validated
	 * 
	 * @param FormSubmission $submission
	 */
	public function beforeSubmissionValidated(FormSubmission $submission);

	/**
	 * Treatment after submission is validated
	 * 
	 * @param FormSubmission $submission
	 */
	public function afterSubmissionValidated(FormSubmission $submission);

	/**
	 * Transform a submission value into Html
	 * 
	 * @param mixed $value
	 * @return  string
	 */
	public static function getHtmlValue($value): string;

	/**
	 * Get value for csv export
	 * 
	 * @param mixed $value
	 * @return mixed
	 */
	public static function getCsvValue($value): string;

	/**
	 * Get value as string
	 * 
	 * @param  mixed $value
	 * @return string
	 */
	public static function getStringValue($value): string;
}