<?php 

namespace Inspire\Forms\Interfaces;

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
}