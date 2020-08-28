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
		this.choices = container.find('.field.choices');
		if (this.advanced.find('.errors').length) {
			this.openAdvanced();
		}
		Craft.initUiElements(container);
		this.initHeaderClick();
		this.initNameKeyUp();
		this.initDelete();
		this.initAdvanced();
		if (this.choices.length) {
			this.initAddChoice();
			this.initDeleteChoice();
			this.initUpDownsChoice();
			this.rebuildChoicesIds();
		}
	}

	initAddChoice()
	{
		let _this = this;
		this.container.on('click', '.add-choice', function(e){
			e.preventDefault();
			_this.addChoice();
		});
	}

	initUpDownsChoice()
	{
		let _this = this;
		this.container.on('click', '.move-choice .down', function(e){
			e.preventDefault();
			let elem = $(this).closest('li.choice');
			if (elem.nextAll('li.choice').length != 0) {
				elem.insertAfter(elem.next());
			}
			_this.rebuildChoicesIds();
		});
		this.container.on('click', '.move-choice .up', function(e){
			e.preventDefault();
			let elem = $(this).closest('li.choice');
			if (elem.prevAll('li.choice').length != 0) {
				elem.insertBefore(elem.prev());
			}
			_this.rebuildChoicesIds();
		});
	}

	initDeleteChoice()
	{
		let _this = this;
		this.container.on('click', '.delete-choice', function(e){
			e.preventDefault();
			$(this).parent().parent().remove();
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

	initDelete()
	{
		var _this = this;
		this.header.find('a.delete').click(function(e){
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

	addChoice()
	{
		let elem = this.choices.find('.skeleton').clone().removeClass('skeleton').addClass('choice').show();
		elem.find('input').attr('required', 'required');
		this.choices.find('ul').append(elem);
		this.rebuildChoicesIds();
	}

	rebuildChoicesIds()
	{
		let _this = this;
		$.each(this.choices.find('li.choice:not(.skeleton)'), function(ind, elem) {
			$(elem).find('input.value').attr('name', 'fields['+_this.id+'][choices]['+ind+'][value]');
			$(elem).find('input.label').attr('name', 'fields['+_this.id+'][choices]['+ind+'][label]');
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