<?php

namespace Inspire\Formidable\events;

use Inspire\Formidable\services\FormsService;
use yii\base\Event;

class FormSubmissionEvent extends Event
{
    /**
     * Will be a FormSubmission model for a spam or invalid submission
     * Submission model for a valid submission
     *
     * @var FormSubmission|Submission
     */
    public $submission;

    /**
     * @var Form
     */
    public $form;

    /**
     * @var int|null
     * @see
     * FormsService::SPAM
     * FormsService::VALID
     * FormsService::INVALID
     */
    public $status;

    /**
     * Posted values
     *
     * @var array
     */
    public $posted = [];
}
