# Formidable Forms

**This is the version for Craft 4.x, for the Craft 3.x version visit the v5 branch**

Build forms in the backend, notify user and admins and view submissions and spams.
Display them easily in the front end with templates.

# Changelog

### Version 7 is under development but not ready/published. See branch v6 for latest Craft 4 version.

### 7.0.0
- Migrated to Vue 3
- Error handling in cp is handled by PHP rather than Js
- Added some cp fields validations
- Removed the ability to register fields by other plugins
- Replaced cp urls by action urls
- Merged forms and submissions cp navs
- Changed namespaces
- Fields `SelectEntry`, `SelectCategory`, `RadioEntry`, `RadioCategory`, `CheckboxEntry` and `CheckboxCategory` are removed and handled by the base field instead (`Select`, `Radio` and `Checkboxes`)
- Added a url to upload a file on the spot and turn into an asset, see [assetUploader.js](examples/assetUploader.js) for an example. In the example the class `.js-upload-asset` must be added to the input in the field config or in the field template
- The css to hide the honeypot can be changed in config
- There are now reserved handles which fields cannot take
- The frequency after which a check on the spammer database is considered as spam is now a config, defaults to 10
- Failure when sending emails will not be reported to the user, an exception will still be thrown though

### Breaking changes
- The Craft fields of types Form must be resaved as the namespaces have changed
- Twig global `formidable` has been removed, use `craft.formidable` instead. Examples :
  - `{% set form = craft.formidable.getFormByHandle('contact-us') %}`
  - `{% set form = craft.formidable.getFormByHandle('contact-us', 'contact_us_2') %} //Change the html id here in case you have 2 of the same form on the same page`
  - `{% do craft.formidable.setHtmlId(form, 'contact_us_2') %} //Or change it later`
  - `{% do craft.formidable.captchas.getRegistered('googleV3').register() %}`
- `extensions` replaced by `accepts` on File fields which will need checked
- Templating :
  - `field_choice_label_class` renamed to `field_option_label_class` in `fields/radio.twig` and `fields/checkboxes.twig`
  - `field_choice_container_class` renamed to `field_option_container_class` in `fields/radio.twig` and `fields/checkboxes.twig`
  - `fields/select-entry.twig`, `fields/select-category.twig` replaced by `select.twig`
  - `fields/radio-entry.twig`, `fields/radio-category.twig` replaced by `radio.twig`
  - `fields/checkbox-entry.twig`, `fields/checkbox-category.twig` replaced by `checkboxes.twig`
  - Removed function `Field::setHandle`, use `{% craft.formidable.setHtmlId(form, 'contact_us_2') %}` instead
- Frontend validation is removed, to validate send an ajax request and display errors in your js. Example [form.js](examples/form.js)
- `FormSubmission` renamed to `FormPosted`
- `FormSubmissionEvent` renamed to `FormPostedEvent` and has a variable `FormPosted $posted` and optionally a `Submission $submission` variable which can be null for non valid or spam submissions.
- How to access submission values on email templates has changed :
  - `{{ submission.getValue('field-handle') }} //This is the same than doing :`
  - `{{ submission.getValue('field-handle').asString() }}`
  - `{{ submission.getValue('field-handle').asHtml() }}`
  - `{{ submission.getValue('field-handle').asRaw() }} //This returns the raw value which can be an array`

### 6.1.4
- Fixed error with group hasErrors() function

### 6.1.3
- Added captcha register methods so they can be used manually without a formidable form : `{% do formidable.captchas.getRegistered('googleV3').register() %}`

### 6.1.2
- Fixed an error when filtering on submissions with both a date and a search

### 6.1.1
- Fixed typing error

### 6.1.0
- Added the option to skip the queue for email sending, and send emails instantly. By default queue won't be used
- Added `FormEmailException` thrown when an email fails to send
- Removed file field "multiple" and replaced by "maxFiles"

### 6.0.1
- Fixed multiple files missing multiple attribute

### 6.0.0
- Craft 4 changes
- Changed templates prefix from formidable to \_formidable

# Forms

Forms can be added and edited in the backend. They update the project config, so doing a change to a form and pushing it to a dev/live site will update it there too.

Forms can have a redirection when submitted, if they don't they will reload the same page with an empty form.

Forms can have a user notification and an admin notification, see notifications. A user notification can only be enabled if the form has an email field.

Forms can have a honeypot, it's highly recommended and the field name for the honeypot **must not** be an existing form field.

Optionnaly forms can save submissions for admins to review, see submissions.

# Templating

Load a form with its handle : `{% set form = craft.formidable.getFormByHandle('contactUs') %}`

Render it with : `{{ form.render()|raw }}`

This will use the default template for forms, you can render it with your own template : `{{ form.render('forms/myTemplate') }}`

Each field has a default template too, if you want to override it :

- Create the template in your theme, in the folder `themeName/templates/_formidable/`. Example if you want to override the text field template you'd write the template `themeName/templates/_formidable/fields/text.twig`
- Or if you are writing a custom form template you would include it by doing so :
```
{% set errors = posted.errors ?? [] %}

{% for field in form.activeRootFields %}
    {% include "mytheme/mytemplate" with {
        errors: errors[field.handle] ?? false,
        old_value: posted[field.handle] ?? field.value
    } %}
{% endfor %}
```

# Emails

Since v2 this plugin uses the [Emails plugin](https://gitlab.inspireservera.com/craft/emails) to send emails.  
So you may need a cron every minute to be able to send emails, calling `queue/run`.

# Captchas

The plugin comes with a Google captcha V3 by default.
You can add other captchas by responding to the event :
```
Event::on(CaptchasService::class, CaptchasService::EVENT_REGISTER_CAPTCHAS, function (RegisterCaptchasEvent $event) {
    $event->addCaptcha(new $captcha);
});
```
You can use those captchas manually also : `{% do craft.formidable.captchas.getRegistered('googleV3').register() %}` to register them on the page

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

# Emailing

The submission emails will receive the following variables : `form` and `submission`

The values for the submission can be retrieved as such : 
- `{{ submission.getValue('field-handle') }} //This is the same than doing :`
- `{{ submission.getValue('field-handle').asString() }}`
- `{{ submission.getValue('field-handle').asHtml() }}`
- `{{ submission.getValue('field-handle').asRaw() }} //This returns the raw value which can be an array`

# Spam

A form is considered as spam on the following conditions :
- if the honeypot is missing from the post
- if the honeypot has been populated
- if the captcha doesn't pass

Spams attempts are saved in database and can be viewed in back end.

# Craft field

New type of field "Form" that lets the user choose a form as another field. A different template for each of the form can be defined in the backend
