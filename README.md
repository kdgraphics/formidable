# Formidable Forms

Build forms in the backend, notify user and admins and view submissions and spams.
Display them easily in the front end with templates.

# Forms

Forms can be added and edited in the backend. They update the project config, so doing a change to a form and pushing it to a dev/live site will update it there too.

Forms can have a redirection when submitted, if they don't they will reload the previous page and add a `?success=1` to the url.

Forms can have a user notification and an admin notification, see notifications. A user notification can only be enabled if the form has an email field.

Forms can have a honeypot, it's highly recommended and the field name for the honeypot **should not** be an existing form field.

Optionnaly forms can save submissions for admins to review, see submissions.

# Fields

Form builder comes with default fields, you can add fields or override fields.
Each field has a handle by which they are registered.

Default fields have the handles : text, email, checkboxes, file, switch, number, radio, select and textarea.

## Add

### Php file

To add a field php class, respond to the event `FieldsService::EVENT_REGISTER_FIELDS` :

```
Event::on(FieldsService::class, FieldsService::EVENT_REGISTER_FIELDS, function (RegisterFieldsEvent $event) {
	$field = new MyField;
    $event->addField($field);
});
```

if your field's handle is the same as one of the default field, it will replace it !

### Vue component

To add the corresponding field Vue components, respond to the event `FormsService::EVENT_HOOK_ASSETS` and add an `AssetBundle` class to it, linking to a javascript file.
```
Event::on(
    FormsService::class,
    FormsService::EVENT_REGISTER_ASSETS,
    function (HookAssets $event) {
        $event->addAsset(MyAsset::class);
    }
);
```

The javascript file will then register the component into the `formFields` js variable :
```
import TestField from './fields/TestField';

window.formFields.test = TestField;
```

If the field name is the same than an existing one it will replace it !

# Templating

Load a form with its handle : `{% set form = formidable.forms.getByHandle('contactUs') %}`

Render it with : `{{ form.render()|raw }}`

This will use the default template for forms, you can render it with your own template : `{{ form.render('myTheme/myTemplate')|raw }}`

Each field has a default template too, if you want to override it :

- Create the template in your theme, in the folder `themeName/formidable/`. Example if you want to override the text field template you'd write the template `themeName/formidable/fields/text.twig`
- Or if you are writing a custom form template you would include it by doing so :
```
{% for field in fields %}
	{% set error = errors[field.name] is defined ? errors[field.name] : false %}
	{% set oldValue = oldInput[field.name] is defined ? oldInput[field.name] : null %}
	{% include 'mytheme/mytemplate' %}
{% endfor %}
```

# Notifications

They define an email to be sent and can be attached to forms.

The email will be built either through direct html or through a custom template, the 2 variables `submission` and `notification` are available. When building through html, you can use twig.

# Captchas

The plugin comes with a Google captcha V3 by default.
You can add other captchas by responding to the event :
```
Event::on(CaptchasService::class, CaptchasService::EVENT_REGISTER_CAPTCHAS, function (RegisterCaptchasEvent $event) {
    $event->addCaptcha(new $captcha);
});
```

# Submissions

Submission can be viewed in the back end, they can be deleted and exported as csv.

# Spam

A form is considered as spam if the honeypot has been filled, in this case the submission will not be created and the previous page will be reloaded without any indication that something went wrong.
Spams attempts are saved in database and can be viewed in back end.