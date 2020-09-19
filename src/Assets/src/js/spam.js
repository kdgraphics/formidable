class Spam
{
	constructor(elem, data)
	{
		this.elem = elem;
		this.data = data;
		this.id = data.id;
		this.deleteUrl = '/admin/formidable/spam/delete/'+this.id;
		this.elem.data('spam', this);
		elem.find('.form').html(data.form.name);
		elem.find('.created').html(data.dateCreated);
		elem.find('.ip').html(data.ip);
		this.modal = formidable.Modal;
		this.modal.setUrl('/admin/formidable/spam/view/');
		this.initOpen();
		this.initDelete();
	}

	initOpen()
	{
		let _this = this;
		this.elem.find('td:not(.thin), .expand').click(function(e){
			e.preventDefault();
			_this.open();
		});
	}

	open()
	{
		this.modal.open(this.data.id);
		this.elem.removeClass('unread');
	}

	initDelete()
	{
		let _this = this;
		this.elem.find('.delete').click(function(){
			if (confirm(Craft.t('formidable', 'Are you sure you want to delete this spam ?'))) {
				let data = {};
				data[Craft.csrfTokenName] = Craft.csrfTokenValue;
				let elem = $(this);
				$.ajax({
					url: _this.deleteUrl,
					dataType: 'json',
					method: 'POST',
					data: data
				}).done(function(data){
					_this.remove();
					Craft.cp.displayNotice(data.message);
				}).fail(function(data){
                    Craft.cp.displayError(data.responseJSON.error);
                });
			}
		});
	}

	remove()
	{
		this.elem.remove();
	}
}
import Chart from './chart';

class Spams
{
	constructor()
	{
		this.container = $('#spam-view');
		this.filters = this.container.find('form');
		this.switchView = $('.switch-view');
		this.tableView = new TableView;
		this.chartView = new ChartView;
		this.bindSwitchView();
		this.tableView.load();
	}

	bindSwitchView()
	{
		let _this = this;
		this.switchView.click(function(e){
			e.preventDefault();
			if ($(this).hasClass('chart')) {
				_this.toChartView();
			} else {
				_this.toTableView();
			}
		});
	}

	toTableView()
	{
		this.chartView.hide();
		this.activeView = this.tableView;
		this.activeView.show();
	}

	toChartView()
	{
		this.tableView.hide();
		this.activeView = this.chartView;
		this.activeView.show();
	}
}

class TableView
{
	constructor()
	{
		this.container = $('#table-view');
		this.list = $('#spam-list');
		this.actions = $('#spam-actions');
		this.deleteAll = $('#delete-all-btn');
		this.filters = this.container.find('form');
		this.deleteSelectionUrl = '/admin/formidable/spam/delete-many';
		this.selectAllElem = this.container.find('#selectAll');
		this.pagination = this.container.find('#pagination');
		this.skeleton = this.list.find('.skeleton');
		this.loadUrl = '/admin/formidable/spam/load';
		this.selected = {};
		this.load();
		this.bindFilters();
		this.initSelectAll();
		this.bindSelects();
		this.bindActions();
	}

	show()
	{
		this.container.show();
		this.actions.show();
		this.load();
	}

	hide()
	{
		this.container.hide();
		this.actions.hide();
	}

	bindFilters()
	{
		let _this = this;
		this.filters.find('input[type=text], select').change(function(){
			_this.load();
		});
		this.filters.on('reset', function(){
			setTimeout(function() {
				_this.load();
			}, 1);
		});
	}

	bindSelects()
	{
		let _this = this;
		$(document).on('click', '#spam-list .spam [type=checkbox]', function(){
			if ($(this).is(':checked')) {
				_this.select($(this).closest('tr'));
			} else {
				_this.unselect($(this).closest('tr'));
			}
		});
	}

	bindActions()
	{
		let _this = this;
		this.actions.data('menubtn').on('optionSelect', function(e){
			_this.deleteSelection().done(function(){
				_this.load();
			});
		});
		this.deleteAll.click(function(e){
			e.preventDefault(); 
			_this.deleteAllSpam();
		});
	}

	deleteSelection()
	{
		this.actions.hide();
		let _this = this;
		let selected = Object.keys(this.selected);
		let data = {ids: selected};
		data[Craft.csrfTokenName] = Craft.csrfTokenValue;
		return $.ajax({
			dataType: 'json',
			url: _this.deleteSelectionUrl,
			data: data,
			method: 'POST'
		}).done(function(data){
			selected.forEach(function (id) {
				_this.selected[id].remove();
			});
			_this.selected = [];
			Craft.cp.displayNotice(data.message);
		});
	}

	initSelectAll()
	{
		let _this = this;
		this.selectAllElem.change(function(){
			if ($(this).is(':checked')) {
				_this.selectAll();
			} else {
				_this.unselectAll();
			}
		});
	}

	selectAll() 
	{
		let _this = this;
		$.each(this.list.find('tr.spam'), function(i, item){
			_this.select($(item));
		});
	}

	unselectAll() 
	{
		let _this = this;
		$.each(this.list.find('tr.spam'), function(i, item){
			_this.unselect($(item));
		});
	}

	select(elem)
	{
		this.selected[elem.data('spam').id] = elem.data('spam');
		elem.find('[type=checkbox]').attr('checked', 'checked');
		this.actions.show();
	}

	unselect(elem)
	{
		delete this.selected[elem.data('spam').id];
		elem.find('[type=checkbox]').removeAttr('checked');
		if ($.isEmptyObject(this.selected)) {
			this.actions.hide();
		}
	}

	load(page = 0)
	{
		let data = this.filters.serializeArray();
		data.push({name: 'page', value: page});
		let _this = this;
		if (this.selectAllElem.is(':checked')) {
			this.selectAllElem.trigger('click');
		}
		$.ajax({
			method: 'POST',
			data: data,
			dataType: 'json',
			url: _this.loadUrl
		}).done(function(data) {
			_this.populateTable(data.spams);
			_this.pagination.html(data.pagination);
			_this.bindPager();
		});
	}

	bindPager()
	{
		let _this = this;
		this.pagination.find('a').click(function(e){
			e.preventDefault();
		});
		this.pagination.find('li:not(.active):not(.disabled)').click(function(e){
			_this.load($(this).find('a').data('page'));
		});
	}

	populateTable(data)
	{
		let _this = this;
		this.list.find('.spam').remove();
		if (data.length) {
			this.list.find('#no-spam').hide();
		} else {
			this.list.find('#no-spam').show();
		}
		let sub; let spam;
		data.forEach(function(data) {
			sub = _this.skeleton.clone().show().removeClass('skeleton').addClass('spam');
			sub.find('[type=checkbox]').attr('id', sub.find('[type=checkbox]').attr('id')+data.id);
			sub.find('label').attr('for', sub.find('label').attr('for')+data.id);
			spam = new Spam(sub, data);
			sub.appendTo(_this.list.find('tbody'));
		});
	}
}

class ChartView
{
	constructor()
	{
		this.container = $('#chart-view');
		this.filters = this.container.find('form');
		this.url = '/admin/formidable/spam/loadAll';
		this.chart = new Chart('chart-container', Craft.t('formidable', 'spams'));
		this.bindFilters();
	}

	bindFilters()
	{
		let _this = this;
		this.filters.find('input[type=text], select').change(function(){
			_this.load();
		});
		this.filters.on('reset', function(){
			setTimeout(function() {
				_this.load();
			}, 1);
		});
	}

	show()
	{
		this.container.show();
		this.load();
	}

	hide()
	{
		this.container.hide();
	}

	active()
	{
		return this.container.is(':visible');
	}

	load()
	{
		let data = this.filters.serializeArray();
		let _this = this;
		$.ajax({
			method: 'POST',
			data: data,
			dataType: 'json',
			url: _this.url
		}).done(function(data) {
			data = _this.normalizeData(data.spams);
			_this.chart.load(data.labels, data.data);
		});
	}

	normalizeData(spams)
	{
		let forms = {};
		let labels = [];
		spams.forEach(spam => {
			if (!labels.includes(spam.dateCreated)) {
				labels.push(spam.dateCreated);
			}
			if (!(spam.form_id in forms)) {
				forms[spam.form_id] = {
					label: spam.form.name,
					fill: false,
					borderColor: spam.form.color
				};
			}
		});
		Object.keys(forms).forEach(form_id => {
			forms[form_id].data = labels.map(x => 0);
		});
		spams.forEach(spam => {
			forms[spam.form_id].data[labels.indexOf(spam.dateCreated)] ++;
		});

		return {
			labels: labels,
			data: Object.values(forms)
		};
	}
}

export default Spams;