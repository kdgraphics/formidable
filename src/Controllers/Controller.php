<?php 

namespace Inspire\Forms\Controllers;

use Inspire\Forms\Forms;
use Inspire\Forms\Services\FieldsService;
use Inspire\Forms\Services\FormsService;
use Inspire\Forms\Services\NotificationsService;
use Inspire\Forms\Services\SpamService;
use Inspire\Forms\Services\SubmissionsService;
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
}