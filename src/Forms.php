<?php
namespace Inspire\Forms;

use Craft;
use Inspire\Forms\Events\RegisterFieldsEvent;
use Inspire\Forms\Models\Fields\Text;
use Inspire\Forms\Services\FieldsService;
use Inspire\Forms\Services\FormsService;
use Inspire\Forms\Services\NotificationsService;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

class Forms extends Plugin
{
    public $controllerNamespace = "Inspire\\Forms\\Controllers";

    public $schemaVersion = '1.0.0';

    public static $plugin;

    protected $fields = [
        Text::class
    ];

    public function init()
    {
        parent::init();
        $this::$plugin = $this;
        $this->registerComponents();
        $this->registerCpRoutes();
        $this->registerProjectConfig();
        $this->registerFields();
    }

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['url'] = 'inspireforms/forms';
        $item['label'] = 'Forms';
        return $item;
    }

    protected function registerComponents()
    {
    	$this->setComponents([
    		'forms' => FormsService::class,
            'notifications' => NotificationsService::class,
            'fields' => FieldsService::class
    	]);
    }

    protected function registerFields()
    {
        $fields = $this->fields;
        Event::on(FieldsService::class, FieldsService::REGISTER_FIELDS, function (RegisterFieldsEvent $event) use ($fields)  {
            foreach ($fields as $field) {
                $event->addField(new $field);
            }
        });
    }

    protected function registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'inspireforms/forms' => 'inspireforms/forms',
                'inspireforms/forms/add' => 'inspireforms/forms/create',
                'inspireforms/forms/save' => 'inspireforms/forms/save',
                'inspireforms/forms/edit/<id:\d+>' => 'inspireforms/forms/edit',
                'inspireforms/forms/delete/<id:\d+>' => 'inspireforms/forms/delete',
                'inspireforms/notifications' => 'inspireforms/notifications',
                'inspireforms/notifications/add' => 'inspireforms/notifications/create',
                'inspireforms/notifications/save' => 'inspireforms/notifications/save',
                'inspireforms/notifications/edit/<id:\d+>' => 'inspireforms/notifications/edit',
                'inspireforms/notifications/delete/<id:\d+>' => 'inspireforms/notifications/delete',
                'inspireforms/fields/add/<handle:\w+>' => 'inspireforms/fields/create'
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
    }
}
