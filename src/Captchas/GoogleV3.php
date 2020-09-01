<?php 

namespace Inspire\Forms\Captchas;

use Inspire\Forms\Interfaces\CaptchaInterface;

class GoogleV3 implements CaptchaInterface
{
	/**
	 * @inheritDoc
	 */
	public function handle(): string
	{
		return 'googleV3';
	}

	/**
	 * @inheritDoc
	 */
	public function friendlyName(): string
	{
		return 'Google V3';
	}
}