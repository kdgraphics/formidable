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
import Group from './fields/Group';
import Date from './fields/DateField';
import Hidden from './fields/Hidden';
import CheckboxEntryField from './fields/CheckboxEntryField';
import CheckboxCategoryField from './fields/CheckboxCategoryField';
import RadioCategoryField from './fields/RadioCategoryField';
import RadioEntryField from './fields/RadioEntryField';
import PhoneNumberField from './fields/PhoneNumberField';

window.formFields = {
	text: TextField,
	email: EmailField,
	textarea: TextareaField,
	date: Date,
	select: SelectField,
	select_category: SelectCategoryField,
    select_entry: SelectEntryField,
	number: NumberField,
	file: FileField,
	radio: RadioField,
	checkbox: CheckboxField,
	checkboxes: CheckboxesField,
	checkbox_entry: CheckboxEntryField,
    checkbox_category: CheckboxCategoryField,
	phone_number: PhoneNumberField,
    radio_category: RadioCategoryField,
    radio_entry: RadioEntryField,
    group: Group,
    hidden: Hidden
};