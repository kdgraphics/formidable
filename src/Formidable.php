<?php

namespace Inspire\Formidable;

use Craft;
use craft\errors\InvalidPluginException;
use Inspire\Emails\Emails;
use Inspire\Emails\Records\Email as EmailRecord;
use Inspire\Formidable\captchas\GoogleV2;
use Inspire\Formidable\captchas\GoogleV3;
use Inspire\Formidable\events\RegisterCaptchasEvent;
use Inspire\Formidable\events\RegisterFieldsEvent;
use Inspire\Formidable\events\RegisterRulesEvent;
use Inspire\Formidable\models\Fields\Checkboxes;
use Inspire\Formidable\models\Fields\Email;
use Inspire\Formidable\models\Fields\File;
use Inspire\Formidable\models\Fields\Checkbox;
use Inspire\Formidable\models\Fields\Number;
use Inspire\Formidable\models\Fields\Radio;
use Inspire\Formidable\models\Fields\Select;
use Inspire\Formidable\models\Fields\Text;
use Inspire\Formidable\models\Fields\Textarea;
use Inspire\Formidable\models\Fields\SelectCategory;
use Inspire\Formidable\models\Fields\SelectEntry;
use Inspire\Formidable\models\Fields\Group;
use Inspire\Formidable\models\Fields\Hidden;
use Inspire\Formidable\models\Fields\CheckboxEntry;
use Inspire\Formidable\models\Fields\Date;
use Inspire\Formidable\models\Fields\CheckboxCategory;
use Inspire\Formidable\models\Fields\RadioCategory;
use Inspire\Formidable\models\Fields\RadioEntry;
use Inspire\Formidable\models\Fields\PhoneNumber;
use Inspire\Formidable\models\FormField;
use Inspire\Formidable\models\Rules\BiggerThan;
use Inspire\Formidable\models\Rules\Contain;
use Inspire\Formidable\models\Rules\EqualTo;
use Inspire\Formidable\models\Rules\Off;
use Inspire\Formidable\models\Rules\OnRule;
use Inspire\Formidable\models\Rules\Required;
use Inspire\Formidable\models\Rules\SameThan;
use Inspire\Formidable\models\Rules\Size;
use Inspire\Formidable\models\Rules\SmallerThan;
use Inspire\Formidable\models\Settings;
use Inspire\Formidable\services\CaptchasService;
use Inspire\Formidable\services\ConditionsService;
use Inspire\Formidable\services\CountryResolverService;
use Inspire\Formidable\services\ConditionGroupsService;
use Inspire\Formidable\services\CsvExporterService;
use Inspire\Formidable\services\FieldsService;
use Inspire\Formidable\services\FormsService;
use Inspire\Formidable\services\RulesService;
use Inspire\Formidable\services\SpamService;
use Inspire\Formidable\services\SubmissionsService;
use Inspire\Formidable\twig\TwigForms;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
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

/**
 * @property-read CaptchasService $captchas
 * @property-read ConditionGroupsService $conditionGroups
 * @property-read ConditionsService $conditions
 * @property-read CountryResolverService $countryResolver
 * @property-read CsvExporterService $csvExporter
 * @property-read FieldsService $fields
 * @property-read FormsService $forms
 * @property-read RulesService $rules
 * @property-read SpamService $spam
 * @property-read SubmissionsService $submissions
 */
class Formidable extends Plugin
{
    private const FORMS_NAV_ICON = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"></path></svg>';
    private const SUBMISSIONS_NAV_ICON = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M496 0a80 80 0 1 1 0 160A80 80 0 1 1 496 0zM48 64l337.1 0c-.7 5.2-1.1 10.6-1.1 16c0 45.6 27.2 84.8 66.3 102.3L275.2 313.6c-11.4 8.5-27 8.5-38.4 0L19.2 150.4C7.1 141.3 0 127.1 0 112C0 85.5 21.5 64 48 64zM294.4 339.2L490.8 191.9c1.7 .1 3.4 .1 5.2 .1c5.4 0 10.8-.4 16-1.1L512 384c0 35.3-28.7 64-64 64L64 448c-35.3 0-64-28.7-64-64L0 176 217.6 339.2c22.8 17.1 54 17.1 76.8 0z"></path></svg>';

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
        OnRule::class,
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
        $this->hasCpSettings = Craft::$app->config->getGeneral()->allowAdminChanges;

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
    }

    /**
     * @return void
     */
    protected function registerCraftField(): void
    {
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (Event $event) {
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
            function (RegisterUserPermissionsEvent $event) {
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
                function (RegisterCpNavItemsEvent $event) {
                    if (Craft::$app->user->checkPermission('manageFormidableSubmissions')) {
                        $event->navItems[] = [
                            'url' => 'formidable/submissions',
                            'label' => Craft::t('formidable', 'Form Submissions'),
                            'icon' => self::SUBMISSIONS_NAV_ICON,
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
                            'label' => Craft::t('formidable', 'Forms'),
                            'icon' => self::FORMS_NAV_ICON,
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
            function () {
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
            function (RegisterTemplateRootsEvent $event) {
                $event->roots[''][] = __DIR__ . '/templates/front';
            }
        );
    }

    /**
     * @return void
     */
    protected function registerTwigExtension(): void
    {
        Craft::$app->view->registerTwigExtension(new TwigForms());
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
            'conditionGroups' => ConditionGroupsService::class,
            'countryResolver' => CountryResolverService::class,
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
            function (RegisterCaptchasEvent $event) {
                foreach ($this->availableCaptchas as $captcha) {
                    $event->addCaptcha(new $captcha());
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
            function (RegisterRulesEvent $event) {
                foreach ($this->availableRules as $rule) {
                    $event->addRule(new $rule());
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
                    $event->addField(new $field());
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
            function (RegisterUrlRulesEvent $event) {
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
            function (RegisterUrlRulesEvent $event) {
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
            ->onAdd(FormsService::CONFIG_KEY . '.{uid}', [$this->forms, 'handleChanged'])
            ->onUpdate(FormsService::CONFIG_KEY . '.{uid}', [$this->forms, 'handleChanged'])
            ->onRemove(FormsService::CONFIG_KEY . '.{uid}', [$this->forms, 'handleDeleted']);

        Event::on(
            ProjectConfig::class,
            ProjectConfig::EVENT_REBUILD,
            function (RebuildConfigEvent $e) {
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
