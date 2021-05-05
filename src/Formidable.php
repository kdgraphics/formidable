<?php
namespace Ryssbowh\Formidable;

use Craft;
use Ryssbowh\Formidable\Assets\TestAsset;
use Ryssbowh\Formidable\Captchas\GoogleV3;
use Ryssbowh\Formidable\Events\{RegisterCaptchasEvent, RegisterFieldsEvent, RegisterRulesEvent, EmailEvent};
use Ryssbowh\Formidable\Models\Fields\{CaptchaV3, Checkboxes, Email, File, LightSwitch, Number, Radio, Select, Text, Textarea, SelectCategory};
use Ryssbowh\Formidable\Models\FormSubmission;
use Ryssbowh\Formidable\Models\Records\Submission;
use Ryssbowh\Formidable\Models\Rules\{BiggerThan, Contain, EqualTo, Off, On, Required, SameThan, Size, SmallerThan};
use Ryssbowh\Formidable\Models\Settings;
use Ryssbowh\Formidable\Services\{CaptchasService, ConditionsService, ConditionGroupsService, CsvExporterService, FieldsService, FormsService, NotificationsService, RulesService, SpamService, SubmissionsService, ScheduleService};
use Ryssbowh\Formidable\Twig\TwigForms;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\{RebuildConfigEvent, RegisterCpNavItemsEvent, RegisterTemplateRootsEvent, RegisterUrlRulesEvent};
use craft\log\FileTarget;
use craft\services\ProjectConfig;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\Cp;
use yii\base\Event;
use yii\base\Model;
use yii\log\Logger;

class Formidable extends Plugin
{
    public $controllerNamespace = "Ryssbowh\\Formidable\\Controllers";

    public $schemaVersion = '1.0.5';

    public $hasCpSettings = true;

    public static $plugin;

    protected $availableFields = [
        Text::class,
        Email::class,
        Textarea::class,
        Number::class,
        LightSwitch::class,
        Select::class,
        Radio::class,
        Checkboxes::class,
        File::class,
        SelectCategory::class
    ];

    protected $availableCaptchas = [
        GoogleV3::class
    ];

    protected $availableRules = [
        BiggerThan::class,
        Contain::class,
        EqualTo::class,
        Off::class,
        On::class,
        Required::class,
        SameThan::class,
        Size::class,
        SmallerThan::class
    ];

    public function init()
    {
        parent::init();
        $this::$plugin = $this;
        $fileTarget = new FileTarget([
            'logFile' => \Yii::$app->getRuntimePath() . '/logs/formidable.log',
            'categories' => ['formidable']
        ]);
        \Craft::getLogger()->dispatcher->targets[] = $fileTarget;
        $this->registerComponents();
        $this->registerCpRoutes();
        $this->registerNavItem();
        $this->registerFrontRoutes();
        $this->registerProjectConfig();
        $this->registerFields();
        $this->registerCaptchas();
        $this->registerRules();
        $this->registerTwigExtension();
        $this->registerFrontTemplates();
        $this->registerEmailWatchers();

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'Ryssbowh\\Formidable\\Console';
        }
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
            'formidable/cp/settings',
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
        \Craft::getLogger()->log($message, $level, 'formidable');
    }

    /**
     * Registers cp nav item
     */
    protected function registerNavItem()
    {
        $request = Craft::$app->getRequest();
        if ($request->isCpRequest) {
            $_this = $this;
            Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS,
                function(RegisterCpNavItemsEvent $event) use ($_this){
                    $event->navItems[] = [
                        'url' => 'formidable/submissions',
                        'label' => \Craft::t('formidable', 'Form Submissions'),
                        'subnav' => [
                            'formidable-submissions' => [
                                'url' => 'formidable/submissions',
                                'label' => \Craft::t('formidable', 'Submissions'),
                            ],
                            'formidable-spam' => [
                                'url' => 'formidable/submissions/spam',
                                'label' => \Craft::t('formidable', 'Spam'),
                            ]
                        ]
                    ];
                    if (\Craft::$app->config->getGeneral()->allowAdminChanges) {
                        $event->navItems[] = [
                            'url' => 'formidable',
                            'label' => \Craft::t('formidable', 'Forms'),
                            'subnav' => [
                                'formidable-forms' => [
                                    'url' => 'formidable/forms',
                                    'label' => \Craft::t('formidable', 'Forms')
                                ],
                                'formidable-notifications' => [
                                    'url' => 'formidable/notifications',
                                    'label' => \Craft::t('formidable', 'Notifications'),
                                ]
                            ]
                        ];
                    }
                });
        }
    }

    /**
     * Submissions and spams callback when emails are sent
     */
    protected function registerEmailWatchers()
    {
        $_this = $this;
        Event::on(NotificationsService::class, NotificationsService::EVENT_AFTER_SENDING_EMAIL, function (EmailEvent $event) use ($_this) {
            if ($event->type == 'spam') {
                $_this->spam->updateSpamAfterEmailSent($event->submitted, $event->type);
            } elseif ($event->submitted instanceof Submission) {
                $_this->submissions->updateSubmissionAfterEmailSent($event->submitted, $event->type);   
            }
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
            'captchas' => CaptchasService::class,
            'rules' => RulesService::class,
            'conditions' => ConditionsService::class,
            'conditionGroups' => ConditionGroupsService::class,
            'schedule' => ScheduleService::class,
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
     * Registers rules
     */
    protected function registerRules()
    {
        $rules = $this->availableRules;
        Event::on(RulesService::class, RulesService::EVENT_REGISTER_RULES, function (RegisterRulesEvent $event) use ($rules)  {
            foreach ($rules as $rule) {
                $event->addRule(new $rule);
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
            if (\Craft::$app->config->getGeneral()->allowAdminChanges) {
                $event->rules = array_merge($event->rules, [
                    'formidable' => 'formidable/cp-forms/form',
                    'formidable/forms' => 'formidable/cp-forms/form',
                    'formidable/forms/get/<id:\d+>' => 'formidable/cp-forms/get',
                    'formidable/forms/new' => 'formidable/cp-forms/new',
                    'formidable/forms/add' => 'formidable/cp-forms/form',
                    'formidable/forms/add/<tab:config>' => 'formidable/cp-forms/form',
                    'formidable/forms/add/<tab:fields>' => 'formidable/cp-forms/form',
                    'formidable/forms/add/<tab:rules>' => 'formidable/cp-forms/form',
                    'formidable/forms/save' => 'formidable/cp-forms/save',
                    'formidable/forms/<id:\d+>' => 'formidable/cp-forms/form',
                    'formidable/forms/<id:\d+>/<tab:config>' => 'formidable/cp-forms/form',
                    'formidable/forms/<id:\d+>/<tab:fields>' => 'formidable/cp-forms/form',
                    'formidable/forms/<id:\d+>/<tab:rules>' => 'formidable/cp-forms/form',
                    'formidable/forms/delete/<id:\d+>' => 'formidable/cp-forms/delete',
                    'formidable/notifications' => 'formidable/cp-notifications',
                    'formidable/notifications/get/<id:\d+>' => 'formidable/cp-notifications/get',
                    'formidable/notifications/add' => 'formidable/cp-notifications',
                    'formidable/notifications/save' => 'formidable/cp-notifications/save',
                    'formidable/notifications/edit/<id:\d+>' => 'formidable/cp-notifications',
                    'formidable/notifications/delete/<id:\d+>' => 'formidable/cp-notifications/delete',
                    'formidable/categories-groups' => 'formidable/cp-categories'
                ]);
            }
            $event->rules = array_merge($event->rules, [
                'formidable/submissions' => 'formidable/cp-submissions',
                'formidable/submissions/load' => 'formidable/cp-submissions/load',
                'formidable/submissions/loadAll' => 'formidable/cp-submissions/load-all',
                'formidable/submissions/set-as-read' => 'formidable/cp-submissions/set-as-read',
                'formidable/submissions/delete-many' => 'formidable/cp-submissions/deletemany',
                'formidable/submissions/export' => 'formidable/cp-submissions/export',
                'formidable/submissions/export-all' => 'formidable/cp-submissions/export-all',
                'formidable/submissions/spam' => 'formidable/cp-spam',
                'formidable/submissions/spam/load' => 'formidable/cp-spam/load',
                'formidable/submissions/spam/loadAll' => 'formidable/cp-spam/load-all',
                'formidable/submissions/spam/delete-many' => 'formidable/cp-spam/deletemany'
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
                'formidable/submit/<id:\d+>' => 'formidable/forms/submit',
            ]);
        });
    }

    /**
     * Project config events registering
     */
    protected function registerProjectConfig()
    {
        Craft::$app->projectConfig
            ->onAdd(FormsService::CONFIG_KEY.'.{uid}',              [$this->forms,           'handleChanged'])
            ->onUpdate(FormsService::CONFIG_KEY.'.{uid}',           [$this->forms,           'handleChanged'])
            ->onRemove(FormsService::CONFIG_KEY.'.{uid}',           [$this->forms,           'handleDeleted'])
            ->onAdd(NotificationsService::CONFIG_KEY.'.{uid}',      [$this->notifications,   'handleChanged'])
            ->onUpdate(NotificationsService::CONFIG_KEY.'.{uid}',   [$this->notifications,   'handleChanged'])
            ->onRemove(NotificationsService::CONFIG_KEY.'.{uid}',   [$this->notifications,   'handleDeleted']);

        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $e) {
            Formidable::$plugin->forms->rebuildConfig($e);
            Formidable::$plugin->notifications->rebuildConfig($e);
        });
    }

    protected function afterUninstall()
    {
        \Craft::$app->getProjectConfig()->remove('formidable');
    }
}
