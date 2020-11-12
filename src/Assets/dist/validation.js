String.prototype.capitalize = function() {
  return this.charAt(0).toUpperCase() + this.slice(1)
}

window.formidable = {
    rule : function ({value, message, fields, conditions, conditionGroups, requiresField, requiresValue, check}) {
        this.value = value;
        this.message = message;
        this.fields = fields;
        this.conditions = conditions;
        this.conditionGroups = conditionGroups;
        this.requiresField = requiresField;
        this.requiresValue = requiresValue;
        this.check = check;
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
            if (this.requiresValue) {
                result = this.check(field, field.getValue(), this.value);
            } else if (this.requiresField) {
                result = this.check(field, field.getValue(), form.getField(this.value).getValue());
            } else {
                result = this.check(field, field.getValue());
            }
            return result;
        };
        this.getMessage = function (field) {
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
            return message.capitalize();
        };
    },
    field: function ({name, type, id, formId, options}) {
        this.name = name;
        this.type = type;
        this.id = id;
        this.formId = formId;
        this.options = options;
        this.getElems = function () {
            return document.querySelector('form[id='+this.formId+']').querySelectorAll('[id^='+this.id+']');
        };
        this.getWrapper = function () {
            return this.getElems()[0].closest('.field');
        };
        this.getValue = function () {
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
            let text = document.createTextNode(message);
            error.appendChild(text);
            this.getWrapper().querySelector('.errors').appendChild(error);
        };
        this.clearErrors = function () {
            for (i = 0; i < this.getElems().length; i++) {
                this.getElems()[i].classList.remove('is-invalid');
            }
            this.getWrapper().querySelector('.errors').innerHTML = '';
        };
    },
    condition: function ({type, field, operator, negate, value}) {
        this.type = type;
        this.field = field;
        this.operator = operator;
        this.negate = negate;
        this.value = value;
        this.appliesTo = function (result, form) {
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
        this.getField = function (name) {
            return this.fields[name];
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
    arrayEquals: function (a ,b) {
      return Array.isArray(a) &&
        Array.isArray(b) &&
        a.length === b.length &&
        a.every((val, index) => val === b[index]);
    },
    validateForm: function(form, event) {
        form = this.forms[form.id];
        form.clearErrors();
        if (!form.validate()) {
            console.log('no');
            event.preventDefault();
        }
    }
};

document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelector('form.formidable').addEventListener('submit', function(e){
        formidable.validateForm(this, e);
    });
});