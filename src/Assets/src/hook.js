import TextField from './fields/TextField';
import EmailField from './fields/EmailField';
import SelectField from './fields/SelectField';
import TextareaField from './fields/TextareaField';
import SwitchField from './fields/SwitchField';
import NumberField from './fields/NumberField';
import FileField from './fields/FileField';
import RadioField from './fields/RadioField';
import CheckboxesField from './fields/CheckboxesField';

import Required from './rules/Required';
import SmallerThan from './rules/SmallerThan';

window.formFields = {
	text: TextField,
	email: EmailField,
	select: SelectField,
	textarea: TextareaField,
	switch: SwitchField,
	number: NumberField,
	file: FileField,
	radio: RadioField,
	checkboxes: CheckboxesField,
};

window.formRules = {
	required: Required,
	smallerThan: SmallerThan
};