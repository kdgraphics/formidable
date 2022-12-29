class Form
{
    id;
    $form;
    $messages;
    assetUploader;

    constructor($form)
    {
        this.id = $form.attr('id');
        this.$form = $form;
        this.$messages = $('#' + this.id + '-messages');
        if (!this.$messages.length) {
            console.warn("The messages element (#" + this.id + "-messages) for the form " + this.id + " doesn't exist, messages won't be shown");
        }
        if (this.$form.hasClass('js-validate')) {
            this.initFormSubmit();
            console.log('Form ' + this.id + ' will be submitted by ajax');
        }
        if (this.$form.find('.js-upload-asset')) {
            import(/* webpackChunkName: "assetUploader" */ './assetUploader').then(chunk => {
                this.assetUploader = new chunk.AssetUploader;
                this.assetUploader.initFields(this.$form.find('.js-upload-asset'));
            });
        }
    }

    initFormSubmit()
    {
        this.$form.submit((e) => {
            e.preventDefault();
            this.hideSuccess();
            this.removeErrors();
            this.removeGlobalErrors();
            this.disableSubmit()
            let formData = new FormData(e.target);
            $.ajax({
                url: '/',
                data: formData,
                contentType: false,
                processData: false,
                method: 'POST',
                dataType: 'JSON'
            }).done(() => {
                this.showSuccess();
                this.reset();
            }).fail((response) => {
                if (response.responseJSON.errors) {
                    this.addErrors(response.responseJSON.errors);
                    this.scrollToMessages();
                }
            }).always(() => {
                this.reloadCaptcha();
                this.enableSubmit();
            });
        });
    }

    reset()
    {
        this.$form.trigger('reset');
    }

    enableSubmit()
    {
        this.$form.find('[type=submit]').attr('disabled', false);
    }

    disableSubmit()
    {
        this.$form.find('[type=submit]').attr('disabled', true);
    }

    reloadCaptcha()
    {
        window.googleV3Captcha.getCaptcha('submit').then((token) => {
            this.$form.find('[name=_recaptcha_response]').val(token);
        });
    }

    removeErrors()
    {
        this.$form.find('.invalid-feedback').remove();
    }

    removeGlobalErrors()
    {
        if (this.$messages.length) {
            this.$messages.find('.alert-danger').remove();
        }
    }

    addErrors(errors)
    {
        let globals = errors['*'] ?? false;
        delete errors['*'];
        if (globals) {
            this.addGlobalErrors(globals);
        }
        let container;
        for (let name in errors) {
            let list = errors[name];
            container = this.$form.find('#' + this.id + '-field-' + name + ' .errors');
            if (container) {
                for (let j in list) {
                    $('<div class="invalid-feedback">' + list[j] + '</div>').appendTo(container);
                }
            }
        }
    }

    addGlobalErrors(errors)
    {
        if (this.$messages.length) {
            for (let i in errors) {
                $('<div class="alert alert-danger">'+errors[i]+'</div>').appendTo(this.$messages);
            }
        }
    }

    scrollToMessages()
    {
        if (this.$messages.length) {
            $('html, body').animate({ scrollTop: this.$messages.offset().top - 170 }, 500);
        }
    }

    hideSuccess()
    {
        if (this.$messages.length) {
            this.$messages.find('.alert-success').hide();
        }
    }

    showSuccess()
    {
        if (this.$messages.length) {
            let success = this.$messages.find('.alert-success');
            if (success.length) {
                success.fadeIn();
                $('html, body').animate({ scrollTop: success.offset().top - 170 }, 500);
            }
        }
    }
}

export { Form };