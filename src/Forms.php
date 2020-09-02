<?php
namespace Inspire\Forms;

use Craft;
use Inspire\Forms\Captchas\GoogleV3;
use Inspire\Forms\Events\RegisterCaptchasEvent;
use Inspire\Forms\Events\RegisterFieldsEvent;
use Inspire\Forms\Models\Fields\CaptchaV3;
use Inspire\Forms\Models\Fields\Checkboxes;
use Inspire\Forms\Models\Fields\Email;
use Inspire\Forms\Models\Fields\File;
use Inspire\Forms\Models\Fields\LightSwitch;
use Inspire\Forms\Models\Fields\Number;
use Inspire\Forms\Models\Fields\Radio;
use Inspire\Forms\Models\Fields\Select;
use Inspire\Forms\Models\Fields\Text;
use Inspire\Forms\Models\Fields\Textarea;
use Inspire\Forms\Models\FormSubmission;
use Inspire\Forms\Models\Settings;
use Inspire\Forms\Services\CaptchasService;
use Inspire\Forms\Services\CsvExporterService;
use Inspire\Forms\Services\FieldsService;
use Inspire\Forms\Services\FormsService;
use Inspire\Forms\Services\NotificationsService;
use Inspire\Forms\Services\SpamService;
use Inspire\Forms\Services\SubmissionsService;
use Inspire\Forms\Twig\TwigForms;
use craft\base\Plugin;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\log\FileTarget;
use craft\services\ProjectConfig;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\Cp;
use yii\base\Event;
use yii\base\Model;
use yii\log\Logger;

class Forms extends Plugin
{
    public $controllerNamespace = "Inspire\\Forms\\Controllers";

    public $schemaVersion = '1.0.3';

    public $hasCpSettings = true;

    public static $plugin;

    const CONFIG_KEY = 'inspireforms';

    protected $availableFields = [
        Text::class,
        Email::class,
        Textarea::class,
        Number::class,
        LightSwitch::class,
        Select::class,
        Radio::class,
        Checkboxes::class,
        File::class
    ];

    protected $availableCaptchas = [
        GoogleV3::class
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
        $this->registerCaptchas();
        $this->registerTwigExtension();
        $this->registerFrontTemplates();
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'inspireforms/cp/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    /**
     * Log a specific form message 
     * 
     * @param $message [description]
     * @param $level   [description]
     */
    public static function log($message, $level = Logger::LEVEL_INFO)
    {
        \Craft::getLogger()->log($message, $level, 'inspireforms');
    }

    /**
     * Registers cp nav item
     */
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

    /**
     * Registers front templates
     */
    protected function registerFrontTemplates()
    {
        Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $event) {
            $event->roots[''][] = __DIR__ . '/templates/front';
        });
    }

    /**
     * Registers twig extensions
     */
    protected function registerTwigExtension()
    {
        Craft::$app->view->registerTwigExtension(new TwigForms);
    }

    /**
     * Registers Forms services
     */
    protected function registerComponents()
    {
    	$this->setComponents([
            'fields' => FieldsService::class,
    		'forms' => FormsService::class,
            'notifications' => NotificationsService::class,
            'submissions' => SubmissionsService::class,
            'csvExporter' => CsvExporterService::class,
            'spam' => SpamService::class,
            'captchas' => CaptchasService::class
    	]);
    }

    /**
     * Registers captcha services
     */
    protected function registerCaptchas()
    {
        $captchas = $this->availableCaptchas;
        Event::on(CaptchasService::class, CaptchasService::EVENT_REGISTER_CAPTCHAS, function (RegisterCaptchasEvent $event) use ($captchas)  {
            foreach ($captchas as $captcha) {
                $event->addCaptcha(new $captcha);
            }
        });
    }

    /**
     * Register form fields
     */
    protected function registerFields()
    {
        $fields = $this->availableFields;
        Event::on(FieldsService::class, FieldsService::EVENT_REGISTER_FIELDS, function (RegisterFieldsEvent $event) use ($fields)  {
            foreach ($fields as $field) {
                $event->addField(new $field);
            }
        });
    }

    /**
     * Register cp routes
     */
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
                'inspireforms/submissions/loadAll' => 'inspireforms/cp-submissions/load-all',
                'inspireforms/submissions/view/<id:\d+>' => 'inspireforms/cp-submissions/view',
                'inspireforms/submissions/delete/<id:\d+>' => 'inspireforms/cp-submissions/delete',
                'inspireforms/submissions/delete-many' => 'inspireforms/cp-submissions/deletemany',
                'inspireforms/submissions/export' => 'inspireforms/cp-submissions/export',
                'inspireforms/submissions/export-all' => 'inspireforms/cp-submissions/export-all',
                'inspireforms/spam' => 'inspireforms/cp-spam',
                'inspireforms/spam/load' => 'inspireforms/cp-spam/load',
                'inspireforms/spam/loadAll' => 'inspireforms/cp-spam/load-all',
                'inspireforms/spam/view/<id:\d+>' => 'inspireforms/cp-spam/view',
                'inspireforms/spam/delete/<id:\d+>' => 'inspireforms/cp-spam/delete',
                'inspireforms/spam/delete-many' => 'inspireforms/cp-spam/deletemany'
            ]);
        });
    }

    /**
     * Registers front end routes
     */
    protected function registerFrontRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'inspireforms/submit/<id:\d+>' => 'inspireforms/forms/submit',
            ]);
        });
    }

    /**
     * Project config events registering
     */
    protected function registerProjectConfig()
    {
        Craft::$app->projectConfig
            ->onAdd(Forms::CONFIG_KEY.'.'.NotificationsService::CONFIG_KEY.'.{uid}', [$this->notifications, 'handleChanged'])
            ->onUpdate(Forms::CONFIG_KEY.'.'.NotificationsService::CONFIG_KEY.'.{uid}', [$this->notifications, 'handleChanged'])
            ->onRemove(Forms::CONFIG_KEY.'.'.NotificationsService::CONFIG_KEY.'.{uid}', [$this->notifications, 'handleDeleted']);

        Craft::$app->projectConfig
            ->onAdd(Forms::CONFIG_KEY.'.'.FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleChanged'])
            ->onUpdate(Forms::CONFIG_KEY.'.'.FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleChanged'])
            ->onRemove(Forms::CONFIG_KEY.'.'.FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleDeleted']);

        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $e) {
            Forms::$plugin->notifications->rebuildConfig($e);
            Forms::$plugin->forms->rebuildConfig($e);
        });
    }
}
