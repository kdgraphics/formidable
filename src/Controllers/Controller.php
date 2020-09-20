<?php 

namespace Ryssbowh\Formidable\Controllers;

use Ryssbowh\Formidable\Formidable;
use Ryssbowh\Formidable\Services\CaptchasService;
use Ryssbowh\Formidable\Services\FieldsService;
use Ryssbowh\Formidable\Services\FormsService;
use Ryssbowh\Formidable\Services\NotificationsService;
use Ryssbowh\Formidable\Services\RulesService;
use Ryssbowh\Formidable\Services\SpamService;
use Ryssbowh\Formidable\Services\SubmissionsService;
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

	protected function notifications(): NotificationsService
	{
		return Formidable::$plugin->notifications;
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