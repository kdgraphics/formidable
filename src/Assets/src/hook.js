import TextField from './fields/TextField';
import EmailField from './fields/EmailField';
import SelectField from './fields/SelectField';
import SelectCategoryField from './fields/SelectCategoryField';
import TextareaField from './fields/TextareaField';
import SwitchField from './fields/SwitchField';
import NumberField from './fields/NumberField';
import FileField from './fields/FileField';
import RadioField from './fields/RadioField';
import CheckboxesField from './fields/CheckboxesField';

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
    select_category: SelectCategoryField
};