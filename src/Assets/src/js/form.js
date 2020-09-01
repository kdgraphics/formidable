import FieldList from './fieldList';

class Form
{
	constructor()
	{
		this.form = $('.inspireform-form');
		this.advanced = this.form.find('.left .advanced');
		this.initUserEmailField();
		this.initAdminEmailField();
		this.initHoneypotField();
		this.initAdvanced();
		new Craft.HandleGenerator('#form-name', '#form-handle');
		if (this.advanced.find('.errors').length) {
			this.openAdvanced();
		}
		if ($('.fields-list').length) {
			this.fieldListContainer = $('.fields-list');
			this.fieldList = new FieldList($('.fields-list'));
			this.addFieldBtn = $('#registered-fields-btn');
			this.collapseBtn = $('#collapse-all');
			this.initAddFieldBtn();
			this.initCollapseBtn(); 
		}
	}

	initCollapseBtn()
	{
		let _this = this;
		this.collapseBtn.click(function(e){
			e.preventDefault();
			if (_this.fieldList.size() == 0) {
				return;
			}
			if ($(this).hasClass('collapsed')) {
				_this.fieldList.openAll();
				$(this).removeClass('collapsed');
				$(this).html($(this).data('label-collapse'));
			} else {
				_this.fieldList.closeAll();
				$(this).addClass('collapsed');
				$(this).html($(this).data('label-show'));
			}
		});
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

	initAdvanced()
	{
		let _this = this;
		this.advanced.find('.header').click(function(e){
			e.preventDefault();
			if ($(this).hasClass('closed')) {
				_this.openAdvanced();
			} else {
				_this.closeAdvanced();
			}
		});
	}

	closeAdvanced()
	{
		let _this= this;
		this.advanced.find('.content').slideUp('fast', function(){
			_this.advanced.find('.header').addClass('closed');
		});
	}

	openAdvanced()
	{
		let _this= this;
		this.advanced.find('.content').slideDown('fast', function(){
			_this.advanced.find('.header').removeClass('closed');
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

export default Form;