# Formidable Forms

Build forms in the backend, notify user and admins and view submissions and spams.
Display them easily in the front end with templates.

# Changelog

### 4.1.2
- Renamed switch fields in migration

### 4.1.1
- Forgot production assets

### 4.1.0
- Added events
- Admin and spam emails accepts env variables

### 4.0.2
- Fixed front name issue

### 4.0.1
- fixed dateCreated issue
- fixed dateDeleted issue
- fixed checkboxes string value issue

### 4.0.0
- Changed submissions logic
- Added events for emails
- Added option to attach files to emails
- Field names obsfuscation
- Hide honeypot with css absolute
- Interface improvements in submissions
- Allow multiple on select/select entry/select category
- Files size
- Switch field renamed to Checkbox
- Form html ids to only contain a-zA-Z_ to be compatible with google captcha V3 actions
- The action sent to google captcha is the form id

### 3.0.7 Fixes
- Fields saved incorrectly
- Boolean validation

### 3.0.6 File fields fixes
- File fields can accept multiple files
- Fields handle can now be changed

### 3.0.5
- render function can now be called with variables as 2nd argument

### 3.0.4
- added spam check api

### 3.0.3 
- Added email check on the api "Stop forum spam"

### 3.0.2
- Make sure fields have a readable value (file)

### 3.0.1
- Field clases fixes

### 3.0.0
- Remade rendering logic (see below)

# V4 breaking changes

Submission and how to access their values have been changed, particulary important for emails :

```
{{ submission.getValue('fieldHandle') }}           //raw value, will break for array fields (select, select entry, files etc)
{{ submission.getValue('fieldHandle', 'string') }} //Safe string value
{{ submission.getValue('fieldHandle', 'html') }}   //Safe html value
```

# V3 breaking changes

The way fields are rendered haven been changed. You can set variables in your custom form templates to control classes :

- form_class
- submit_class
- submit_container_class
- honeypot_class
- fields_class

And fields templates can now be overriden by creating the template `formidable/fields/field` which should look like this at minimum :
```
{% include field.frontEndTemplate() %}
```
You can then set some variables to control the classes :
- container_class
- field_class
- label_class
- field_choice_container_class (for checkboxes, radio)
- field_choice_label_class (for checkboxes, radio)

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
	{% set old_value = oldInput[field.name] is defined ? oldInput[field.name] : null %}
	{% include 'mytheme/mytemplate' %}
{% endfor %}
```

# Emails

Since v2 this plugin uses the [Emails plugin](https://gitlab.inspireservera.com/craft/emails) to send emails.  
So you will need a cron every minute to be able to send emails, calling `queue/run`.

# Captchas

The plugin comes with a Google captcha V3 by default.
You can add other captchas by responding to the event :
```
Event::on(CaptchasService::class, CaptchasService::EVENT_REGISTER_CAPTCHAS, function (RegisterCaptchasEvent $event) {
    $event->addCaptcha(new $captcha);
});
```

# Events

Examples :

Email events
```
Event::on(SubmissionsService::class, SubmissionsService::EVENT_BEFORE_SEND_USER_EMAIL, function ($e) {
    $submission = $e->submission; //Submission object
    $form = $e->form;
    $email = $e->email;
    $e->isValid = false; //This will stop the email being sent
});

Event::on(SubmissionsService::class, SubmissionsService::EVENT_BEFORE_SEND_ADMIN_EMAIL, function ($e) {
    $submission = $e->submission; //Submission object
    $form = $e->form;
    $email = $e->email;
    $e->isValid = false; //This will stop the email being sent
});
```
Other events :
```
FormsService::EVENT_BEFORE_VALIDATING_SUBMISSION //At this stage we know it's not a spam
FormsService::EVENT_ON_VALID_SUBMISSION
FormsService::EVENT_ON_INVALID_SUBMISSION
FormsService::EVENT_ON_SPAM_SUBMISSION
```

# Obfuscation

Field names can automatically be obfuscated, giving them an unreadable name. It should help with spam, but there are a few disadvantages :
- Browsers auto populate will stop working
- If the user session expires, he/she will be unable to post and will have to reload.

# Submissions

Submission can be viewed in the back end, they can be deleted and exported as csv.

# Spam

A form is considered as spam if the honeypot has been filled, in this case the submission will not be created and the previous page will be reloaded without any indication that something went wrong.
Spams attempts are saved in database and can be viewed in back end.
