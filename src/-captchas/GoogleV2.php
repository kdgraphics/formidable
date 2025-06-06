<?php 

namespace Inspire\Formidable\captchas;

use Inspire\Formidable\assets\GoogleV2CaptchaBundle;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\CaptchaInterface;
use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\FormSubmission;
use craft\helpers\StringHelper;
use craft\web\View;

class GoogleV2 implements CaptchaInterface
{
    public static $registered = false;

    /**
     * @inheritDoc
     */
    public function handle(): string
    {
        return 'googleV2';
    }

    /**
     * @inheritDoc
     */
    public function friendlyName(): string
    {
        return 'Google V2';
    }

    /**
     * Register captcha on the view
     */
    public function register()
    {
        if (!self::$registered) {
            self::$registered = true;
            \Craft::$app->view->registerJsFile('https://www.google.com/recaptcha/api.js', ['position' => View::POS_HEAD]);
        }
    }

    /**
     * @inheritDoc
     */
    public function render(Form $form): string
    {
        $this->register();
        return \Craft::$app->view->renderTemplate('_formidable/captchas/googleV2', [
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
     * @inheritDoc
     */
    public function verify(Form $form, FormSubmission $submission, array $posted): bool
    {
        $client = new \GuzzleHttp\Client();
        $secret = $this->getSecretKey();

        if (isset($posted['g-recaptcha-response'])) {
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'query' => [
                    'secret' => $secret,
                    'response' => $posted['g-recaptcha-response']
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['success']) {
                return true;
            }
            $submission->_spam_reason = 'Unable to verify Google captcha';
            $submission->addError('*', \Craft::t('site', 'Unable to verify your captcha, please complete it again'));
            return false;
        }
        $submission->_spam_reason = 'Google captcha information not sent';
        $submission->addError('*', \Craft::t('site', 'Unable to verify your captcha'));
        return false;
    }
}
