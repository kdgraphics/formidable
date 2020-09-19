<?php

namespace Ryssbowh\Formidable\Events;

use Ryssbowh\Formidable\Interfaces\CaptchaInterface;
use yii\base\Event;

class RegisterCaptchasEvent extends Event
{
	protected $captchas = [];

	/**
	 * Registers a captcha on the event
	 * 
	 * @param CaptchaInterface $captcha
	 */
	public function addCaptcha(CaptchaInterface $captcha)
	{
		$this->captchas[$captcha->handle()] = $captcha;
	}

	/**
	 * Get all registered captchas
	 * 
	 * @return array
	 */
	public function getCaptchas(): array
	{
		return $this->captchas;
	}
}