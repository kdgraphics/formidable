<?php 

namespace Inspire\Formidable\Controllers;

use Inspire\Formidable\Formidable;
use Inspire\Formidable\Services\CaptchasService;
use Inspire\Formidable\Services\FieldsService;
use Inspire\Formidable\Services\FormsService;
use Inspire\Formidable\Services\RulesService;
use Inspire\Formidable\Services\SpamService;
use Inspire\Formidable\Services\SubmissionsService;
use craft\web\Controller as CraftController;
use craft\web\Request;
use craft\web\Session;

class Controller extends CraftController
{
	protected function request(): Request
	{
		return \Craft::$app->request;
	}

	protected function session(): Session
	{
		return \Craft::$app->session;
	}

	protected function forms(): FormsService
	{
		return Formidable::$plugin->forms;
	}

	protected function fields(): FieldsService
	{
		return Formidable::$plugin->fields;
	}

	protected function submissions(): SubmissionsService
	{
		return Formidable::$plugin->submissions;
	}

	protected function spam(): SpamService
	{
		return Formidable::$plugin->spam;
	}

	protected function captchas(): CaptchasService
	{
		return Formidable::$plugin->captchas;
	}

	protected function rules(): RulesService
	{
		return Formidable::$plugin->rules;
	}
}