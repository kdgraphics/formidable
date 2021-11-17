import TextField from './fields/TextField';
import EmailField from './fields/EmailField';
import SelectField from './fields/SelectField';
import SelectCategoryField from './fields/SelectCategoryField';
import SelectEntryField from './fields/SelectEntryField';
import TextareaField from './fields/TextareaField';
import CheckboxField from './fields/CheckboxField';
import NumberField from './fields/NumberField';
import FileField from './fields/FileField';
import RadioField from './fields/RadioField';
import CheckboxesField from './fields/CheckboxesField';

window.formFields = {
	text: TextField,
	email: EmailField,
	select: SelectField,
	textarea: TextareaField,
	checkbox: CheckboxField,
	number: NumberField,
	file: FileField,
	radio: RadioField,
	checkboxes: CheckboxesField,
    select_category: SelectCategoryField,
    select_entry: SelectEntryField
};