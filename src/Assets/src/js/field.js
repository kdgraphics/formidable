class Field
{
	constructor(container, id, fieldList)
	{
		this.id = id;
		this.fieldList = fieldList;
		this.container = container;
		this.header = container.find('>.header');
		this.content = container.find('>.content');
		this.advanced = container.find('.advanced');
		if (this.advanced.find('.errors').length) {
			this.openAdvanced();
		}
		Craft.initUiElements(container);
		this.initHeaderClick();
		this.initNameKeyUp();
		this.initDelete();
		this.initAdvanced();
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

	initDelete()
	{
		var _this = this;
		this.content.find('.actions a.delete').click(function(e){
			e.preventDefault();
			_this.fieldList.removeField(_this.id);
			_this.container.remove();
		});
	}

	initNameKeyUp()
	{
		let _this = this;
		this.content.find('#name').keyup(function(e){
			let name = $(this).val().replace(/[^0-9a-zA-Z_-]/g, '');
			console.log(name);
			$(this).val(name);
			_this.header.find('.name').html(name);
		});
	}

	initHeaderClick()
	{
		let _this = this;
		this.header.click(function(e){
			if ($(this).hasClass('closed')) {
				_this.open();
			} else {
				_this.close();
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

	close()
	{
		let _this= this;
		this.closeAdvanced();
		this.content.slideUp('fast', function(){
			_this.header.addClass('closed');
		});
	}

	open()
	{
		let _this= this;
		this.content.slideDown('fast', function(){
			_this.header.removeClass('closed');
		});
	}
}

export default Field;