import TextField from './fields/TextField';
import EmailField from './fields/EmailField';
import SelectField from './fields/SelectField';
import TextareaField from './fields/TextareaField';
import SwitchField from './fields/SwitchField';
import NumberField from './fields/NumberField';
import FileField from './fields/FileField';

window.formFields = {
	text: TextField,
	email: EmailField,
	select: SelectField,
	textarea: TextareaField,
	switch: SwitchField,
	number: NumberField,
	file: FileField,
};