<?php 

namespace Inspire\Forms\Interfaces;

use Inspire\Forms\Models\Form;

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
	 * @return string
	 */
	public function render(): string;

	/**
	 * Verify captcha after posting
	 * 
	 * @return bool
	 */
	public function verify(Form $form, array $posted): bool;
}