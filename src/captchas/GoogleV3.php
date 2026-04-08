<?php

namespace Inspire\Formidable\captchas;

use Inspire\Formidable\assets\GoogleV3CaptchaBundle;
use Inspire\Formidable\Formidable;
use Inspire\Formidable\interfaces\CaptchaInterface;
use Inspire\Formidable\models\Form;
use Inspire\Formidable\models\FormSubmission;
use GuzzleHttp\Client;
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
     * Register captcha on the view
     */
    public function register()
    {
        if (!self::$registered) {
            self::$registered = true;
            \Craft::$app->view->registerJsFile('https://www.google.com/recaptcha/api.js?render=' . $this->getSiteKey(), ['position' => View::POS_HEAD]);
            \Craft::$app->view->registerAssetBundle(GoogleV3CaptchaBundle::class);
        }
    }

    /**
     * @inheritDoc
     */
    public function render(Form $form): string
    {
        $this->register();
        return \Craft::$app->view->renderTemplate('_formidable/captchas/googleV3', [
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
        $client = new Client();
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
                    $submission->_spam_reason = 'Google captcha score ' . $data['score'] . ' smaller than ' . $score;
                    $submission->addError('*', \Craft::t('formidable', 'Our spam protection software has detected a high probability that this is a spam or bot submission. If this is a real enquiry please try another browser just in case there is a problem with this one for you.'));
                }
                return $success;
            }
            $submission->_spam_reason = 'Unable to verify Google captcha';
            $submission->addError('*', \Craft::t('site', 'Unable to verify your captcha'));
            return false;
        }
        $submission->_spam_reason = 'Google captcha information not sent';
        $submission->addError('*', \Craft::t('site', 'Unable to verify your captcha'));
        return false;
    }
}
