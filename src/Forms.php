<?php
namespace Inspire\Forms;

use Craft;
use Inspire\Forms\Events\RegisterFieldsEvent;
use Inspire\Forms\Models\Fields\Text;
use Inspire\Forms\Services\FieldsService;
use Inspire\Forms\Services\FormsService;
use Inspire\Forms\Services\NotificationsService;
use Inspire\Forms\Services\SpamService;
use Inspire\Forms\Services\SubmissionsService;
use Inspire\Forms\Twig\TwigForms;
use craft\base\Plugin;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\log\FileTarget;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\Cp;
use yii\base\Event;
use yii\log\Logger;

class Forms extends Plugin
{
    public $controllerNamespace = "Inspire\\Forms\\Controllers";

    public $schemaVersion = '1.0.0';

    public static $plugin;

    protected $availableFields = [
        Text::class
    ];

    public function init()
    {
        parent::init();
        $this::$plugin = $this;
        $fileTarget = new FileTarget([
            'logFile' => \Yii::$app->getRuntimePath() . '/logs/inspireforms.log',
            'categories' => ['inspireforms']
        ]);
        \Craft::getLogger()->dispatcher->targets[] = $fileTarget;
        $this->registerComponents();
        $this->registerCpRoutes();
        $this->registerNavItem();
        $this->registerFrontRoutes();
        $this->registerProjectConfig();
        $this->registerFields();
        $this->registerTwigExtension();
        $this->registerFrontTemplates();
    }

    public static function log($message, $level = Logger::LEVEL_INFO)
    {
        \Craft::getLogger()->log($message, $level, 'inspireforms');
    }

    protected function registerNavItem()
    {
        $_this = $this;
        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) use ($_this){
                $event->navItems[] = [
                    'url' => 'inspireforms/forms',
                    'label' => 'Forms',
                    'badgeCount' => $_this->submissions->countUnread()
                ];
        });
    }

    protected function registerFrontTemplates()
    {
        Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $event) {
            $event->roots[''][] = __DIR__ . '/templates/front';
        });
    }

    protected function registerTwigExtension()
    {
        Craft::$app->view->registerTwigExtension(new TwigForms);
    }

    protected function registerComponents()
    {
    	$this->setComponents([
            'fields' => FieldsService::class,
    		'forms' => FormsService::class,
            'notifications' => NotificationsService::class,
            'submissions' => SubmissionsService::class,
            'spam' => SpamService::class
    	]);
    }

    protected function registerFields()
    {
        $fields = $this->availableFields;
        Event::on(FieldsService::class, FieldsService::EVENT_REGISTER_FIELDS, function (RegisterFieldsEvent $event) use ($fields)  {
            foreach ($fields as $field) {
                $event->addField(new $field);
            }
        });
    }

    protected function registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'inspireforms' => 'inspireforms/cp-forms',
                'inspireforms/forms' => 'inspireforms/cp-forms',
                'inspireforms/forms/add' => 'inspireforms/cp-forms/create',
                'inspireforms/forms/save' => 'inspireforms/cp-forms/save',
                'inspireforms/forms/edit/<id:\d+>' => 'inspireforms/cp-forms/edit',
                'inspireforms/forms/delete/<id:\d+>' => 'inspireforms/cp-forms/delete',
                'inspireforms/notifications' => 'inspireforms/cp-notifications',
                'inspireforms/notifications/add' => 'inspireforms/cp-notifications/create',
                'inspireforms/notifications/save' => 'inspireforms/cp-notifications/save',
                'inspireforms/notifications/edit/<id:\d+>' => 'inspireforms/cp-notifications/edit',
                'inspireforms/notifications/delete/<id:\d+>' => 'inspireforms/cp-notifications/delete',
                'inspireforms/fields/add/<handle:\w+>' => 'inspireforms/cp-fields/create',
                'inspireforms/submissions' => 'inspireforms/cp-submissions',
                'inspireforms/submissions/load' => 'inspireforms/cp-submissions/load',
                'inspireforms/submissions/get/<id:\d+>' => 'inspireforms/cp-submissions/get',
                'inspireforms/submissions/view/<id:\d+>' => 'inspireforms/cp-submissions/view',
                'inspireforms/submissions/delete/<id:\d+>' => 'inspireforms/cp-submissions/delete',
                'inspireforms/submissions/delete-many' => 'inspireforms/cp-submissions/deletemany'
            ]);
        });
    }

    protected function registerFrontRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'inspireforms/submit/<id:\d+>' => 'inspireforms/forms/submit',
            ]);
        });
    }

    protected function registerProjectConfig()
    {
        Craft::$app->projectConfig
            ->onAdd(NotificationsService::CONFIG_KEY.'.{uid}', [$this->notifications, 'handleChanged'])
            ->onUpdate(NotificationsService::CONFIG_KEY.'.{uid}', [$this->notifications, 'handleChanged'])
            ->onRemove(NotificationsService::CONFIG_KEY.'.{uid}', [$this->notifications, 'handleDeleted']);

        Craft::$app->projectConfig
            ->onAdd(FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleChanged'])
            ->onUpdate(FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleChanged'])
            ->onRemove(FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleDeleted']);

        Craft::$app->projectConfig
            ->onAdd(FieldsService::CONFIG_KEY.'.{uid}', [$this->fields, 'handleChanged'])
            ->onUpdate(FieldsService::CONFIG_KEY.'.{uid}', [$this->fields, 'handleChanged'])
            ->onRemove(FieldsService::CONFIG_KEY.'.{uid}', [$this->fields, 'handleDeleted']);
    }
}
