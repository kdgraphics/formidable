import Field from './field';

class FieldList
{
	constructor(container)
	{
		this.container = container;
		this.addUrl = container.data('url');
		this.skeleton = container.find('.field-skeleton');
		this.fields = [];
		this.scan();
	}

	scan()
	{

	}

	add(field)
	{
		this.fields.push(field);
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
			let field = _this.skeleton.clone().removeClass('field-skeleton').show();
			field.find('content').append(data.html);
			field.find('.type').html(data.friendlyName);
			_this.fields.push(new Field(field));
			_this.container.find('ul').append(field);
		});
	}
}

export default FieldList;