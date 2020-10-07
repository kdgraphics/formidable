window.formidable = {
	rule : function (value, message, fields, conditions, groups, requiresField, requiresValue, checkFunction) {
        this.value = value;
        this.message = message;
        this.fields = fields;
        this.conditions = conditions;
        this.conditionGroups = groups;
        this.requiresField = requiresField;
        this.requiresValue = requiresValue;
        this.check = checkFunction;
        function appliesTo (fieldName, field, form) {
            if (!this.conditions.length && !this.conditionGroups.length) {
                return true;
            }
            let conditionnables = this.conditions.concat(this.conditionGroups);
            let res = true;
            for (const i in conditionnables) {
                res = conditionnables[i].appliesTo(field, res, form);
            }
            return res;
        }
		function validate (fieldName, field, form) {
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
        }
        function getMessage (field) {
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
        return {
            check: check,
            appliesTo: appliesTo,
            validate: validate,
            getMessage: getMessage
        }
	},
    field: function (name, type, id, formId, options) {
        this.name = name;
        this.type = type;
        this.id = id;
        this.formId = formId;
        this.options = options;
        function getElem () {
            return document.querySelector('form[id='+this.formId+']').querySelector('[id='+this.id+']');
        }
        function getWrapper () {
            return this.getElem().closest('.field');
        }
        function getValue () {
            return this.getElem().value;
        }
        function setError (message) {
            this.getElem().classList.add('is-invalid');
            let error = document.createElement("div");
            let text = document.createTextNode(message);
            error.appendChild(text);
            this.getWrapper().querySelector('.errors').appendChild(error);
        }
        function clearErrors () {
            this.getElem().classList.remove('is-invalid');
            this.getWrapper().querySelector('.errors').innerHtml = '';
        }
        return {
            getElem: getElem,
            getWrapper: getWrapper,
            setError: setError,
            clearErrors: clearErrors
        }
    },
    condition: function (type, field, operator, negate, value) {
        this.type = type;
        this.field = field;
        this.operator = operator;
        this.negate = negate;
        this.value = value;
        function appliesTo (result, form) {
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
        return {
            appliesTo: appliesTo
        }
    },
    conditionGroup: function (conditions, operator, negate) {
        this.conditions = conditions;
        this.operator = operator;
        this.negate = negate;
        function appliesTo (field, result, form) {
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
        return {
            appliesTo: appliesTo
        }
    },
    form: function (id, rules, fields) {
        this.rules = rules;
        this.fields = fields;
        this.id = id;
        function getElem () {
            return document.querySelector('form[id='+this.id+']');
        }
        function getField (name) {
            return this.fields[name];
        }
        function clearErrors () {
            for (i in this.fields) {
                this.fields[i].clearErrors();
            }
        }
        function validate () {
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
        }
        return {
            getElem: getElem,
            getField: getField,
            clearErrors: clearErrors,
            validate: validate,
        }
    },
    rules: {
        $RULES$
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