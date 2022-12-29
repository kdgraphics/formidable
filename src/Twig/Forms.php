<?php 

namespace Inspire\Formidable\Twig;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Models\Settings;
use Inspire\Formidable\Services\CaptchasService;
use Inspire\Formidable\Services\FormsService;
use Inspire\Formidable\Services\SpamService;
use Inspire\Formidable\Services\SubmissionsService;

class Forms
{
    public function getFormByHandle(string $handle, ?string $htmlId = null): ?Form
    {
        $form = $this->getForms()->getByHandle($handle);
        $this->setHtmlId($form, $htmlId);
        return $form;
    }

    public function setHtmlId(?Form $form, ?string $htmlId)
    {
        if ($form and $htmlId !== null) {
            $form->htmlId = $htmlId;
        }
    }

    public function getSettings(): Settings
    {
        return Formidable::$plugin->settings;
    }

    public function getForms(): FormsService
    {
        return Formidable::$plugin->forms;
    }

    public function getSubmissions(): SubmissionsService
    {
        return Formidable::$plugin->submissions;
    }

    public function getSpam(): SpamService
    {
        return Formidable::$plugin->spam;
    }

    public function getCaptchas(): CaptchasService
    {
        return Formidable::$plugin->captchas;
    }
}