<?php 

namespace Inspire\Formidable\Interfaces;

use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\FormSubmission;

interface CaptchaInterface
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
	 * Renders this captcha
	 * 
	 * @param Form $form
	 * @return string
	 */
	public function render(Form $form): string;

	/**
	 * Verify captcha after posting, returns false if should be considered as spam
	 * 
	 * @param  Form           $form
	 * @param  FormSubmission $submission
	 * @param  array          $posted
	 * @return bool
	 */
	public function verify(Form $form, FormSubmission $submission, array $posted): bool;
}
