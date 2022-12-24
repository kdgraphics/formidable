<?php
namespace Inspire\Formidable;

use Craft;
use Inspire\Emails\Emails;
use Inspire\Emails\Records\Email as EmailRecord;
use Inspire\Formidable\Assets\TestAsset;
use Inspire\Formidable\Captchas\GoogleV2;
use Inspire\Formidable\Captchas\GoogleV3;
use Inspire\Formidable\Events\{RegisterCaptchasEvent, RegisterFieldsEvent, RegisterRulesEvent};
use Inspire\Formidable\Models\Fields\{CaptchaV3, Checkboxes, Email, File, Checkbox, Number, Radio, Select, Text, Textarea, SelectCategory, SelectEntry, Group, Hidden, CheckboxEntry, Date, CheckboxCategory, RadioCategory, RadioEntry};
use Inspire\Formidable\Models\FormField;
use Inspire\Formidable\Models\FormSubmission;
use Inspire\Formidable\Models\Records\Submission;
use Inspire\Formidable\Models\Rules\{BiggerThan, Contain, EqualTo, Off, On, Required, SameThan, Size, SmallerThan};
use Inspire\Formidable\Models\Settings;
use Inspire\Formidable\Services\{CaptchasService, ConditionsService, ConditionGroupsService, CsvExporterService, FieldsService, FormsService, RulesService, SpamService, SubmissionsService};
use Inspire\Formidable\Twig\TwigForms;
use craft\base\Model;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\{RebuildConfigEvent, RegisterCpNavItemsEvent, RegisterTemplateRootsEvent, RegisterUrlRulesEvent};
use craft\services\Fields;
use craft\services\Gc;
use craft\services\ProjectConfig;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\Cp;
use yii\base\Event;
use yii\log\Logger;

class Formidable extends Plugin
{
    public $controllerNamespace = "Ryssbowh\\Formidable\\Controllers";

    public string $schemaVersion = '6.2.0';

    public bool $hasCpSettings = true;

    public static $plugin;

    protected $availableFields = [
        Checkbox::class,
        CheckboxCategory::class,
        CheckboxEntry::class,
        Checkboxes::class,
        Date::class,
        Email::class,
        File::class,
        Group::class,
        Hidden::class,
        Number::class,
        Radio::class,
        RadioCategory::class,
        RadioEntry::class,
        Select::class,
        SelectCategory::class,
        SelectEntry::class,
        Text::class,
        Textarea::class,
    ];

    protected $availableCaptchas = [
        GoogleV3::class,
        GoogleV2::class,
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

    public function init(): void
    {
        parent::init();
        $this::$plugin = $this;
        $this->registerComponents();
        $this->registerCpRoutes();
        $this->registerPermissions();
        $this->registerNavItem();
        $this->registerFrontRoutes();
        $this->registerProjectConfig();
        $this->registerFields();
        $this->registerCaptchas();
        $this->registerRules();
        $this->registerTwigExtension();
        $this->registerFrontTemplates();
        $this->registerCraftField();
        $this->registerGc();
    }

    /**
     * Register Craft field
     */
    protected function registerCraftField()
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (Event $event) {
            $event->types[] = FormField::class;
        });
    }

    /**
     * Register permissions
     */
    protected function registerPermissions()
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => \Craft::t('formidable', 'Forms'),
                    'permissions' => [
                        'accessPlugin-formidable' => [
                            'label' => 'Access forms',
                            'nested' => [
                                'manageFormidableForms' => [
                                    'label' => 'Manage forms',
                                ],
                                'manageFormidableSubmissions' => [
                                    'label' => 'Manage submissions',
                                ]
                            ]
                        ]
                    ]
                ];
            }
        );
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel(): ?Model
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
                    if (\Craft::$app->user->checkPermission('manageFormidableSubmissions')) {
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
                    }
                    if (\Craft::$app->config->getGeneral()->allowAdminChanges and \Craft::$app->user->checkPermission('manageFormidableForms')) {
                        $event->navItems[] = [
                            'url' => 'formidable/forms',
                            'label' => \Craft::t('formidable', 'Forms')
                        ];
                    }
                }
            );
        }
    }

    /**
     * Register garbace collection
     */
    protected function registerGc()
    {
        Event::on(Gc::class, Gc::EVENT_RUN, function () {
            Formidable::$plugin->submissions->runGc();
            Formidable::$plugin->spam->runGc();
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
            'submissions' => SubmissionsService::class,
            'csvExporter' => CsvExporterService::class,
            'spam' => SpamService::class,
            'captchas' => CaptchasService::class,
            'rules' => RulesService::class,
            'conditions' => ConditionsService::class,
            'conditionGroups' => ConditionGroupsService::class
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
            $event->rules = array_merge($event->rules, [
                'formidable' => 'formidable/cp-formidable'
            ]);
            if (\Craft::$app->config->getGeneral()->allowAdminChanges) {
                $event->rules = array_merge($event->rules, [
                    'formidable/forms' => 'formidable/cp-forms/form',
                    'formidable/forms/add' => 'formidable/cp-forms/form',
                    'formidable/forms/add/<tab:config>' => 'formidable/cp-forms/form',
                    'formidable/forms/add/<tab:fields>' => 'formidable/cp-forms/form',
                    'formidable/forms/add/<tab:rules>' => 'formidable/cp-forms/form',
                    'formidable/forms/<id:\d+>' => 'formidable/cp-forms/form',
                    'formidable/forms/<id:\d+>/<tab:config>' => 'formidable/cp-forms/form',
                    'formidable/forms/<id:\d+>/<tab:fields>' => 'formidable/cp-forms/form',
                    'formidable/forms/<id:\d+>/<tab:rules>' => 'formidable/cp-forms/form',
                ]);
            }
            $event->rules = array_merge($event->rules, [
                'formidable/submissions' => 'formidable/cp-submissions',
                'formidable/submissions/export' => 'formidable/cp-submissions/export',
                'formidable/spam' => 'formidable/cp-spam'
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
            ->onRemove(FormsService::CONFIG_KEY.'.{uid}',           [$this->forms,           'handleDeleted']);

        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $e) {
            Formidable::$plugin->forms->rebuildConfig($e);
        });
    }

    protected function afterUninstall(): void
    {
        \Craft::$app->getProjectConfig()->remove('formidable');
    }
}
