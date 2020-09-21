<?php 

namespace Ryssbowh\Formidable\Captchas;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\CaptchaInterface;
use Ryssbowh\Formidable\Models\Form;
use craft\helpers\StringHelper;

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

	/**
	 * @inheritDoc
	 */
	public function render(): string
	{
		return \Craft::$app->view->renderTemplate('formidable/captchas/googleV3', [
			'captcha' => $this,
			'siteKey' => $this->getSiteKey()
		]);
	}

	/**
	 * Get site key
	 * 
	 * @return string
	 */
	public function getSiteKey(): string
	{
		return \Craft::parseEnv(Formidable::$plugin->settings->siteKey);
	}

	/**
	 * Get secret key
	 * 
	 * @return string
	 */
	public function getSecretKey(): string
	{
		return \Craft::parseEnv(Formidable::$plugin->settings->secretKey);
	}

	/**
	 * Get acceptable score
	 * 
	 * @return int
	 */
	public function getAcceptableScore(): int
	{
		return (int)Formidable::$plugin->getSettings()->acceptableScore / 10;
	}

	/**
	 * @inheritDoc
	 */
	public function verify(Form $form, array $posted): bool
	{
        $client = new \GuzzleHttp\Client();
        $secret = $this->getSecretKey();
        $score = $this->getAcceptableScore();
        if (isset($posted['_recaptcha_response'])) {
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'query' => [
                    'secret' => $secret,
                    'response' => $posted['_recaptcha_response']
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['score'] >= $score;
        }
        return false;
	}
}