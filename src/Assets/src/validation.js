window.formidable = {
	rule : {
        conditions: [],
        conditionGroups: [],
        requiresField: false,
        requiresValue: false,
        appliesTo: function (fieldName, field, form) {
            if (!this.conditions.length && !this.conditionGroups.length) {
                return true;
            }
            let conditionnables = this.conditions.concat(this.conditionGroups);
            let res = true;
            for (const i in conditionnables) {
                res = conditionnables[i].appliesTo(field, res, form);
            }
            return res;
        },
		validate: function(fieldName, field, form) {
            if (!this.appliesTo(fieldName, field, form)) {
                return true;
            }
            let result;
            if (this.requiresValue) {
                result = this.check(field, field.getValue(), this.value);
            } else if (this.requiresField) {
                result = this.check(field, field.getValue(), form.getField(this.value).getValue());
            } else {
                result = this.check(field, field.getValue());
            }
            return result;
        },
        getMessage: function (field) {
            let message = this.message.replace('{value}', field.getValue());
            let found = this.message.matchAll(/{field\.([\w-_]+)}/g);
            let result = found.next();
            while (!result.done){
                message = message.replace(result.value[0], field[result.value[1]])
                result = found.next();
            }
            found = this.message.matchAll(/{rule\.([\w-_]+)}/g);
            result = found.next();
            while (!result.done){
                message = message.replace(result.value[0], this[result.value[1]])
                result = found.next();
            }
            return message;
        }
	},
    field: {
        type: null,
        id: null,
        formId: null,
        getElem: function () {
            return document.querySelector('form[id='+this.formId+']').querySelector('[id='+this.id+']');
        },
        getWrapper: function () {
            return this.getElem().closest('.field');
        },
        getValue: function () {
            return this.getElem().value;
        },
        setError: function (message) {
            this.getElem().classList.add('is-invalid');
            let error = document.createElement("div");
            let text = document.createTextNode(message);
            error.appendChild(text);
            this.getWrapper().querySelector('.errors').appendChild(error);
        },
        clearErrors: function () {
            this.getElem().classList.remove('is-invalid');
            this.getWrapper().querySelector('.errors').innerHtml = '';
        }
    },
    condition: {
        type: null,
        field: null,
        operator: 'and',
        negate: false,
        value: null,
        appliesTo: function (result, form) {
            let rule = formidable.rules[this.type];
            let field = form.getField(this.field);
            if (rule.requiresValue) {
                let thisResult = rule.check(field, field.getValue(), this.value);
            } else if (rule.requiresField) {
                let thisResult = rule.check(field, field.getValue(), form.getField(this.value).getValue());
            } else {
                let thisResult = rule.check(field, field.getValue());
            }
            if (this.negate) {
                thisResult = !thisResult;
            }
            if (this.operator == 'and') {
                return (thisResult && result);
            }
            return (thisResult || result);
        }
    },
    conditionGroup: {
        conditions: [],
        operator: 'and',
        negate: false,
        appliesTo: function (field, result, form) {
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
        }
    },
    rules: {
        $RULES$
    },
    form: {
        rules: [],
        fields: {},
        id: null,
        getElem: function () {
            return document.querySelector('form[id='+this.id+']');
        },
        getField: function (name) {
            return this.fields[name];
        },
        clearErrors: function () {
            for (i in this.fields) {
                this.fields[i].clearErrors();
            }
        },
        validate: function () {
            let res = true;
            for (i in this.rules) {
                let rule = {...formidable.rule, ...this.rules[i]};
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
        }
    },
    forms: {},
    validateForm: function(form, event) {
        event.preventDefault();
        form = this.forms[form.id];
        form.clearErrors();
        if (!form.validate()) {
            event.preventDefault();
        }
    }
};
document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelector('form.formidable').addEventListener('submit', function(e){
        formidable.validateForm(this, e);
    });
});