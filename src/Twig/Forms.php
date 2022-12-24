<?php 

namespace Inspire\Formidable\Twig;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Models\Form;
use Inspire\Formidable\Services\CaptchasService;
use Inspire\Formidable\Services\FormsService;
use Inspire\Formidable\Services\SpamService;
use Inspire\Formidable\Services\SubmissionsService;

class Forms
{
    public function getFormByHandle(string $handle): ?Form
    {
        return $this->getForms()->getByHandle($handle);
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