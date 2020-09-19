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
		return Forms::$plugin->forms;
	}

	protected function fields(): FieldsService
	{
		return Forms::$plugin->fields;
	}

	protected function notifications(): NotificationsService
	{
		return Forms::$plugin->notifications;
	}

	protected function submissions(): SubmissionsService
	{
		return Forms::$plugin->submissions;
	}

	protected function spam(): SpamService
	{
		return Forms::$plugin->spam;
	}

	protected function captchas(): CaptchasService
	{
		return Forms::$plugin->captchas;
	}

	protected function rules(): RulesService
	{
		return Forms::$plugin->rules;
	}
}