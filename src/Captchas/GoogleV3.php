<?php 

namespace Inspire\Forms\Captchas;

use Inspire\Forms\Forms;
use Inspire\Forms\Interfaces\CaptchaInterface;
use Inspire\Forms\Models\Form;
use craft\helpers\StringHelper;

class GoogleV3 implements CaptchaInterface
{
	const SESSION_KEY_VALUE = 'inspireforms.captcha-uid.value';
	const SESSION_KEY_NAME = 'inspireforms.captcha-uid.name';
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
		return \Craft::$app->view->renderTemplate('inspireforms/captchas/googleV3', ['captcha' => $this]);
	}

	/**
	 * @inheritDoc
	 */
	public function verify(Form $form, array $posted): bool
	{
        $client = new \GuzzleHttp\Client();
        $secret = Forms::$plugin->getSettings()->secretKey;
        $score = Forms::$plugin->getSettings()->acceptableScore / 10;
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