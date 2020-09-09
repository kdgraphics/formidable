<?php 

namespace Inspire\Forms\Services;

use Inspire\Forms\Events\RegisterCaptchasEvent;
use Inspire\Forms\Interfaces\CaptchaInterface;
use craft\base\Component;

class CaptchasService extends Component
{
	const EVENT_REGISTER_CAPTCHAS = 'register_captchas';

    /**
     * Captchas keyed by handle
     * @var array
     */
    protected $registeredCaptchas;

    /**
     * Get all registered fields 
     * 
     * @return array
     */
    public function getAllRegistered(): array
    {
        if (is_null($this->registeredCaptchas)) {
            $event = new RegisterCaptchasEvent;
            $this->trigger(self::EVENT_REGISTER_CAPTCHAS, $event); 
            $this->registeredCaptchas = $event->getCaptchas();
        }
        return $this->registeredCaptchas;
    }

    /**
     * Get all captchas as an array index by the handle
     * 
     * @return array
     */
    public function getAllAsArray(): array
    {
        $captchas = [];
        foreach ($this->getAllRegistered() as $captcha) {
            $captchas[$captcha->handle()] = [
                'name' => $captcha->friendlyName(),
                'handle' => $captcha->handle()
            ];
        }
        return $captchas;
    }

    /**
     * Get a registered captcha by handle
     * 
     * @param  string $handle
     * @return ?CaptchaInterface
     */
    public function getRegistered(string $handle): ?CaptchaInterface
    {
        return $this->getAllRegistered()[$handle] ?? null;
    }
}