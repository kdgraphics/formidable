import FieldList from './fieldList';

class Form
{
	constructor()
	{
		this.form = $('.inspireform-form');
		this.initUserEmailField();
		this.initAdminEmailField();
		this.initHoneypotField();
		new Craft.HandleGenerator('#name', '#handle');
		if ($('.fields-list').length) {
			this.fieldListContainer = $('.fields-list');
			this.fieldList = new FieldList($('.fields-list'));
			this.addFieldBtn = $('#registered-fields-btn');
			this.initAddFieldBtn();
		}
		// Craft.initUiElements(this.form);
	}

	initAddFieldBtn()
	{
		let _this = this;
		let menu = this.addFieldBtn.data('menubtn');
		menu.menu.on('optionselect', function(e){
			_this.fieldList.create($(e.selectedOption).data('handle'));
		});
	}

	initHoneypotField()
	{
		this.changeHoneypotField();
		let _this = this;
		$('#honeypot').change(function(){
			_this.changeHoneypotField();
		});
	}

	initUserEmailField()
	{
		this.changeUserEmailField();
		let _this = this;
		$('#notify_id').change(function(){
			_this.changeUserEmailField();
		});
	}

	initAdminEmailField()
	{
		this.changeAdminEmailField();
		let _this = this;
		$('#notifyAdmin_id').change(function(){
			_this.changeAdminEmailField();
		});
	}

	changeHoneypotField()
	{
		if (!$('#honeypot').hasClass('on')) {
			$('#honeypotField').removeAttr('required');
			$('#honeypotField').closest('.field').hide();
		} else {
			$('#honeypotField').attr('required', 'required');
			$('#honeypotField').closest('.field').show();
		}
	}

	changeUserEmailField()
	{
		if ($('#notify_id').val() == 0) {
			$('#notifyField').removeAttr('required');
			$('#notifyField').closest('.field').hide();
		} else {
			$('#notifyField').attr('required', 'required');
			$('#notifyField').closest('.field').show();
		}
	}

	changeAdminEmailField()
	{
		if ($('#notifyAdmin_id').val() == 0) {
			$('#notifyEmails').removeAttr('required');
			$('#notifyEmails').closest('.field').hide();
		} else {
			$('#notifyEmails').attr('required', 'required');
			$('#notifyEmails').closest('.field').show();
		}
	}
}

$(document).ready(function(){
	new Form;
});