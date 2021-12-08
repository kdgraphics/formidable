window.formidable = {
    formRule : function ({type, value, message, messages, fields, conditions, conditionGroups}) {
        this.type = type;
        this.value = value;
        this.message = message;
        this.messages = messages;
        this.fields = fields;
        this.conditions = conditions;
        this.conditionGroups = conditionGroups;
        this.appliesTo = function (fieldName, field, form) {
            if (!this.conditions.length && !this.conditionGroups.length) {
                return true;
            }
            let conditionnables = this.conditions.concat(this.conditionGroups);
            let res = true;
            for (const i in conditionnables) {
                res = conditionnables[i].appliesTo(field, res, form);
            }
            return res;
        };
        this.validate = function (fieldName, field, form) {
            if (!this.appliesTo(fieldName, field, form)) {
                return true;
            }
            let result;
            let rule = formidable.rules[this.type];
            if (rule.requiresValue) {
                result = rule.check(field, field.getValue(), this.value);
            } else if (rule.requiresField) {
                result = rule.check(field, field.getValue(), form.getField(this.value).getValue());
            } else {
                result = rule.check(field, field.getValue());
            }
            return result;
        };
        this.getMessage = function (field) {
            let message;
            if (field.handle in this.messages && this.messages[field.handle] != '') {
                message = this.messages[field.handle];
            } else {
                message = this.message;
            }
            message = message.replace('{value}', field.getValue());
            let found = message.matchAll(/{field\.([\w-_]+)}/g);
            let result = found.next();
            while (!result.done){
                message = message.replace(result.value[0], field[result.value[1]])
                result = found.next();
            }
            found = message.matchAll(/{rule\.([\w-_]+)}/g);
            result = found.next();
            while (!result.done){
                message = message.replace(result.value[0], this[result.value[1]])
                result = found.next();
            }
            return message;
        };
    },
    field: function ({name, handle, type, id, formId}) {
        this.name = name;
        this.handle = handle;
        this.type = type;
        this.id = id;
        this.formId = formId;
        this.getElems = function () {
            if (this.type == 'null') {
                return [];
            }
            return document.querySelector('form[id='+this.formId+']').querySelectorAll('[id^='+this.id+']');
        };
        this.getWrapper = function () {
            if (this.type == 'null') {
                return null;
            }
            return this.getElems()[0].closest('.field');
        };
        this.getValue = function () {
            if (this.type == 'null') {
                return null;
            }
            let elems = this.getElems();
            if (this.type == 'array') {
                let val = [];
                for (i = 0; i < elems.length; i++) {
                    let elem = elems[i];
                    if (elem.type == 'checkbox' && elem.checked) {
                        val.push(elem.value);
                    }
                }
                return val;
            }
            if (elems[0].type == 'radio') {
                for (i = 0; i < elems.length; i++) {
                    let elem = elems[i];
                    if (elem.checked) {
                        return elem.value;
                    }
                }
                return null;
            }
            if (this.type == 'boolean' && elems[0].type == 'checkbox') {
                return elems[0].checked;
            }
            return elems[0].value;
        };
        this.setError = function (message) {
            for (i = 0; i < this.getElems().length; i++) {
                this.getElems()[i].classList.add('is-invalid');
            }
            let error = document.createElement("div");
            error.classList.add('invalid-feedback');
            let text = document.createTextNode(message);
            error.appendChild(text);
            this.getWrapper().querySelector('.errors').appendChild(error);
        };
        this.clearErrors = function () {
            for (i = 0; i < this.getElems().length; i++) {
                this.getElems()[i].classList.remove('is-invalid');
            }
            if (this.getWrapper()) {
                this.getWrapper().querySelector('.errors').innerHTML = '';
            }
        };
    },
    condition: function ({type, field, operator, negate, value}) {
        this.type = type;
        this.field = field;
        this.operator = operator;
        this.negate = negate;
        this.value = value;
        this.appliesTo = function (field, result, form) {
            let rule = formidable.rules[this.type];
            let thisResult = true;
            let thisField = form.getField(this.field);
            if (rule.requiresValue) {
                thisResult = rule.check(field, thisField.getValue(), this.value);
            } else if (rule.requiresField) {
                thisResult = rule.check(field, thisField.getValue(), form.getField(this.value).getValue());
            } else {
                thisResult = rule.check(field, thisField.getValue());
            }
            if (this.negate) {
                thisResult = !thisResult;
            }
            if (this.operator == 'and') {
                return (thisResult && result);
            }
            return (thisResult || result);
        };
    },
    conditionGroup: function ({conditions, operator, negate}) {
        this.conditions = conditions;
        this.operator = operator;
        this.negate = negate;
        this.appliesTo = function (field, result, form) {
            let thisResult = true;
            for (const i in this.conditions) {
                thisResult = this.conditions[i].appliesTo(field, result, form);
            }
            if (this.negate) {
                thisResult = !thisResult;
            }
            if (this.operator == 'and') {
                return (thisResult && result);
            }
            return (thisResult || result);
        };
    },
    form: function ({id, rules, fields}) {
        this.rules = rules;
        this.fields = fields;
        this.id = id;
        this.getElem = function () {
            return document.querySelector('form[id='+this.id+']');
        };
        this.getField = function (handle) {
            return this.fields[handle];
        };
        this.clearErrors = function () {
            for (i in this.fields) {
                this.fields[i].clearErrors();
            }
        };
        this.validate = function () {
            let res = true;
            for (i in this.rules) {
                let rule = this.rules[i];
                for (j in rule.fields) {
                    let fieldName = rule.fields[j];
                    let field = this.getField(fieldName);
                    if (!rule.validate(fieldName, field, this)) {
                        field.setError(rule.getMessage(field));
                        res = false;
                    }
                }
            }
            return res;
        };
    },
    forms: {},
    rules: {$RULES$},
    arrayEquals: function (a ,b) {
      return Array.isArray(a) &&
        Array.isArray(b) &&
        a.length === b.length &&
        a.every((val, index) => val === b[index]);
    },
    validateForm: function(form, submitEvent) {
        let formObject = this.forms[form.id];
        formObject.clearErrors();
        if (!formObject.validate()) {
            const event = new CustomEvent('formidable.validation.failure', {detail: {submitEvent: submitEvent}});
            form.dispatchEvent(event);
            return false;
        }
        const event = new CustomEvent('formidable.validation.success', {detail: {submitEvent: submitEvent}});
        form.dispatchEvent(event);
        return true;
    }
};