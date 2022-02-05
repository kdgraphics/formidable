<?php 

namespace Ryssbowh\Formidable\Captchas;

use Ryssbowh\Formidable\Assets\GoogleV3CaptchaBundle;
use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Interfaces\CaptchaInterface;
use Ryssbowh\Formidable\Models\Form;
use Ryssbowh\Formidable\Models\FormSubmission;
use craft\helpers\StringHelper;
use craft\web\View;

class GoogleV3 implements CaptchaInterface
{
	public static $registered = false;

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
	public function render(Form $form): string
	{
		if (!self::$registered) {
			self::$registered = true;
			\Craft::$app->view->registerJsFile('https://www.google.com/recaptcha/api.js?render=' . $this->getSiteKey(), ['position' => View::POS_HEAD]);
			\Craft::$app->view->registerAssetBundle(GoogleV3CaptchaBundle::class);
		}
		return \Craft::$app->view->renderTemplate('formidable/captchas/googleV3', [
			'form' => $form,
			'captcha' => $this,
			'site_key' => $this->getSiteKey()
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
	public function getAcceptableScore(): float
	{
		return (int)Formidable::$plugin->getSettings()->acceptableScore / 10;
	}

	/**
	 * @inheritDoc
	 */
	public function verify(Form $form, FormSubmission $submission, array $posted): bool
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
            if ($data['success']) {
            	$success = ($data['score'] >= $score);
            	if (!$success) {
            		$submission->_spam_reason = 'Google captcha score ('.$data['score'].')';
            	}
            	return $success;
            }
        }
        $submission->addError('*', \Craft::t('site', 'Unable to verify your captcha, please try again'));
        return true;
	}
}
