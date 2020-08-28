<?php 

namespace Inspire\Forms\Interfaces;

use Inspire\Forms\Models\FormSubmission;

interface FieldInterface
{
	/**
	 * Machine name for this field
	 * 
	 * @return string
	 */
	public function handle(): string;

	/**
	 * Friendly name for this field
	 * 
	 * @return string
	 */
	public function friendlyName(): string;

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
	public function getSiteRules(): array;

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
	 * Transform a submission value
	 * 
	 * @param mixed $value
	 */
	public static function getSubmissionValue($value);
}