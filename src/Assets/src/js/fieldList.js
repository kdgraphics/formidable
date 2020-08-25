import Field from './field';

class FieldList
{
	constructor(container)
	{
		this.container = container;
		this.addUrl = container.data('url');
		this.skeleton = container.find('.field-skeleton');
		this.fields = {};
		this.scan();
		this.drag = new Garnish.DragSort(container.find('li'), {
			handle:'.handle',
			axis: 'y',
			container: container.find('ul')
		});
	}

	scan()
	{
		let _this = this;
		this.container.find('ul li').each(function(ind, item) {
			let id = _this.newId();
			let field = $(item).data('id', id);
			field.find('textarea, input, select').each(function(ind, elem){
				$(elem).attr('name', 'fields['+id+']['+$(elem).attr('name')+']');
			});
			_this.fields[id] = new Field(field, id, _this);
		});
	}

	add(field)
	{
		this.fields.push(field);
	}

	newId()
	{
		return '_' + Math.random().toString(36).substr(2, 9);
	}

	removeField(id)
	{
		delete this.fields[id];
	}

	create(handle)
	{
		let url = this.addUrl+'/'+handle;
		let data = {};
		let _this = this;
		data[Craft.csrfTokenName] = Craft.csrfTokenValue;
		$.ajax({
			url: url,
			dataType: 'json',
			method: 'POST',
			data: data
		}).done(function(data){
			let id = _this.newId();
			let field = _this.skeleton
				.clone()
				.removeClass('field-skeleton')
				.addClass(handle)
				.show()
				.data('id', id);
			field.find('.content').append(data.html);
			field.find('textarea, input, select').each(function(ind, elem){
				$(elem).attr('name', 'fields['+id+']['+$(elem).attr('name')+']');
			});
			field.find('.type').html(data.friendlyName);
			_this.fields[id] = new Field(field, id, _this);
			_this.container.find('ul').append(field);
			_this.drag.addItems(field);
		});
	}

	closeAll()
	{
		for (const [key, field] of Object.entries(this.fields)) {
			field.close();
		}
	}

	openAll()
	{
		for (const [key, field] of Object.entries(this.fields)) {
			field.open();
		}
	}

	size()
	{
		return Object.keys(this.fields).length;
	}
}

export default FieldList;