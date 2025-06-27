<?php

namespace Inspire\Formidable;

use Craft;
use craft\errors\InvalidPluginException;
use Inspire\Emails\Emails;
use Inspire\Emails\Records\Email as EmailRecord;
use Inspire\Formidable\captchas\GoogleV2;
use Inspire\Formidable\captchas\GoogleV3;
use Inspire\Formidable\events\{RegisterCaptchasEvent, RegisterFieldsEvent, RegisterRulesEvent};
use Inspire\Formidable\models\Fields\{Checkboxes, Email, File, Checkbox, Number, Radio, Select, Text, Textarea, SelectCategory, SelectEntry, Group, Hidden, CheckboxEntry, Date, CheckboxCategory, RadioCategory, RadioEntry, PhoneNumber};
use Inspire\Formidable\models\FormField;
use Inspire\Formidable\models\Rules\{BiggerThan, Contain, EqualTo, Off, On, Required, SameThan, Size, SmallerThan};
use Inspire\Formidable\models\Settings;
use Inspire\Formidable\services\{CaptchasService, ConditionsService, ConditionGroupsService, CsvExporterService, FieldsService, FormsService, RulesService, SpamService, SubmissionsService};
use Inspire\Formidable\twig\TwigForms;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\{RebuildConfigEvent, RegisterCpNavItemsEvent, RegisterTemplateRootsEvent, RegisterUrlRulesEvent};
use craft\services\Fields;
use craft\services\Gc;
use craft\services\ProjectConfig;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\Cp;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\log\Logger;
use Inspire\Formidable\assets\PhoneInputAsset;
use Inspire\Formidable\assets\FormValidationAsset;
use craft\ckeditor\web\assets\ckeditor\CkeditorAsset;

/**
 * @property-read CaptchasService $captchas
 * @property-read ConditionGroupsService $conditionGroups
 * @property-read ConditionsService $conditions
 * @property-read CsvExporterService $csvExporter
 * @property-read FieldsService $fields
 * @property-read FormsService $forms
 * @property-read RulesService $rules
 * @property-read SpamService $spam
 * @property-read SubmissionsService $submissions
 */
class Formidable extends Plugin
{
    /** @var string */
    public string $schemaVersion = '7.0.1';

    /** @var bool */
    public bool $hasCpSettings = true;

    /** @var Formidable */
    public static self $plugin;

    /** @var array */
    protected array $availableFields = [
        Text::class,
        Date::class,
        Email::class,
        Textarea::class,
        Number::class,
        Radio::class,
        RadioEntry::class,
        RadioCategory::class,
        File::class,
        Hidden::class,
        Select::class,
        SelectCategory::class,
        SelectEntry::class,
        Checkbox::class,
        Checkboxes::class,
        CheckboxEntry::class,
        CheckboxCategory::class,
        Group::class,
        PhoneNumber::class,
    ];

    /** @var array */
    protected array $availableCaptchas = [
        GoogleV3::class,
        GoogleV2::class,
    ];

    /** @var array */
    protected array $availableRules = [
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

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this::$plugin = $this;

        $this->registerPermissions();
        $this->registerComponents();
        $this->registerProjectConfig();
        $this->registerFields();
        $this->registerCaptchas();
        $this->registerRules();
        $this->registerTwigExtension();
        $this->registerFrontTemplates();
        $this->registerCraftField();
        $this->registerCpRoutes();
        $this->registerFrontRoutes();
        $this->registerNavItem();
        $this->registerGc();

        if (Craft::$app->request->isSiteRequest) {
            Craft::$app->view->registerAssetBundle(PhoneInputAsset::class);
            Craft::$app->view->registerAssetBundle(FormValidationAsset::class);
        }

        if (Craft::$app->request->isCpRequest) {
            Craft::$app->view->registerAssetBundle(CkeditorAsset::class);
        }
    }

    /**
     * @return void
     */
    protected function registerCraftField(): void
    {
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(Event $event) {
                $event->types[] = FormField::class;
            }
        );
    }

    /**
     * @return void
     */
    protected function registerPermissions(): void
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => Craft::t('formidable', 'Forms'),
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
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
     * @param mixed $message
     * @param int $level
     * @return void
     */
    public static function log(mixed $message, int $level = Logger::LEVEL_INFO): void
    {
        Craft::getLogger()->log($message, $level, 'formidable');
    }

    /**
     * @return void
     */
    protected function registerNavItem(): void
    {
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                Cp::class,
                Cp::EVENT_REGISTER_CP_NAV_ITEMS,
                function(RegisterCpNavItemsEvent $event) {
                    if (Craft::$app->user->checkPermission('manageFormidableSubmissions')) {
                        $event->navItems[] = [
                            'url' => 'formidable/submissions',
                            'label' => Craft::t('formidable', 'Form Submissions'),
                            'subnav' => [
                                'formidable-submissions' => [
                                    'url' => 'formidable/submissions',
                                    'label' => Craft::t('formidable', 'Submissions'),
                                ],
                                'formidable-spam' => [
                                    'url' => 'formidable/submissions/spam',
                                    'label' => Craft::t('formidable', 'Spam'),
                                ]
                            ]
                        ];
                    }
                    if (Craft::$app->config->getGeneral()->allowAdminChanges and Craft::$app->user->checkPermission('manageFormidableForms')) {
                        $event->navItems[] = [
                            'url' => 'formidable/forms',
                            'label' => Craft::t('formidable', 'Forms')
                        ];
                    }
                }
            );
        }
    }

    /**
     * @return void
     */
    protected function registerGc(): void
    {
        Event::on(
            Gc::class,
            Gc::EVENT_RUN,
            function() {
                Formidable::$plugin->submissions->runGc();
                Formidable::$plugin->spam->runGc();
            }
        );
    }

    /**
     * @return void
     */
    protected function registerFrontTemplates(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots[''][] = __DIR__ . '/templates/front';
            }
        );
    }

    /**
     * @return void
     */
    protected function registerTwigExtension(): void
    {
        Craft::$app->view->registerTwigExtension(new TwigForms);
    }

    /**
     * @return void
     */
    protected function registerComponents(): void
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
     * @return void
     */
    protected function registerCaptchas(): void
    {
        Event::on(
            CaptchasService::class,
            CaptchasService::EVENT_REGISTER_CAPTCHAS,
            function(RegisterCaptchasEvent $event) {
                foreach ($this->availableCaptchas as $captcha) {
                    $event->addCaptcha(new $captcha);
                }
            }
        );
    }

    /**
     * @return void
     */
    protected function registerRules(): void
    {
        Event::on(
            RulesService::class,
            RulesService::EVENT_REGISTER_RULES,
            function(RegisterRulesEvent $event) {
                foreach ($this->availableRules as $rule) {
                    $event->addRule(new $rule);
                }
            }
        );
    }

    /**
     * @return void
     */
    protected function registerFields(): void
    {
        Event::on(
            FieldsService::class,
            FieldsService::EVENT_REGISTER_FIELDS,
            function (RegisterFieldsEvent $event) {
                foreach ($this->availableFields as $field) {
                    $event->addField(new $field);
                }
            }
        );
    }

    /**
     * @return void
     */
    protected function registerCpRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                if (Craft::$app->config->getGeneral()->allowAdminChanges) {
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
                        'formidable/categories-groups' => 'formidable/cp-categories',
                        'formidable/entry-sections' => 'formidable/cp-sections'
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
            }
        );
    }

    /**
     * @return void
     */
    protected function registerFrontRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'formidable/submit/<id:\d+>' => 'formidable/forms/submit',
                ]);
            }
        );
    }

    /**
     * @return void
     */
    protected function registerProjectConfig(): void
    {
        Craft::$app->projectConfig
            ->onAdd(FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleChanged'])
            ->onUpdate(FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleChanged'])
            ->onRemove(FormsService::CONFIG_KEY.'.{uid}', [$this->forms, 'handleDeleted']);

        Event::on(
            ProjectConfig::class,
            ProjectConfig::EVENT_REBUILD,
            function(RebuildConfigEvent $e) {
                Formidable::$plugin->forms->rebuildConfig($e);
            }
        );
    }

    /**
     * @return void
     * @throws \yii\db\Exception
     */
    protected function afterInstall(): void
    {
        if (!Emails::$plugin->emails->getByKey('formidable_spam')) {
            $email = new EmailRecord([
                'key' => 'formidable_spam',
                'subject' => 'New spam on {{ siteName }}',
                'body' => 'A new spam has been caught on {{ form.name }}',
                'heading' => 'When a spam is caught'
            ]);
            $email->save(false);
        }
    }

    /**
     * @return void
     * @throws Throwable
     * @throws InvalidPluginException
     */
    protected function beforeInstall(): void
    {
        Craft::$app->plugins->installPlugin('inspire-emails');
    }

    /**
     * @return void
     */
    protected function afterUninstall(): void
    {
        Craft::$app->getProjectConfig()->remove('formidable');
    }
}
