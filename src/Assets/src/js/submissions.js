import Chart from './chart';

class Submission
{
	constructor(elem, data)
	{
		this.elem = elem;
		this.data = data;
		this.id = data.id;
		this.deleteUrl = '/admin/formidable/submissions/delete/'+this.id;
		this.elem.data('submission', this);
		if (data.read) {
			elem.find('.read').html(Craft.t('formidable', 'Read'));
		} else {
			elem.addClass('unread');
			elem.find('.read').html(Craft.t('formidable', 'New'));
		}
		elem.find('.form').html(data.form.name);
		elem.find('.created').html(data.dateCreated);
		elem.find('.notified').html(data.notified ? Craft.t('formidable', 'Yes') : Craft.t('formidable', 'No'));
		elem.find('.adminNotified').html(data.adminNotified ? Craft.t('formidable', 'Yes') : Craft.t('formidable', 'No'));
		this.modal = formidable.Modal;
		this.modal.setUrl('/admin/formidable/submissions/view/');
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
			if (confirm(Craft.t('formidable', 'Are you sure you want to delete this submission ?'))) {
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

class Submissions
{
	constructor()
	{
		this.container = $('#submissions-view');
		this.filters = this.container.find('form');
		this.exportAllUrl = '/admin/formidable/submissions/export-all';
		this.exportAll = $('#export-all-btn');
		this.switchView = $('.switch-view');
		this.tableView = new TableView;
		this.chartView = new ChartView;
		this.bindSwitchView();
		this.bindExportAll();
		this.tableView.load();
	}

	bindExportAll()
	{
		let _this = this;
		this.exportAll.click(function(e){
			e.preventDefault(); 
			_this.exportAllAsCsv();
		});
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

	exportAllAsCsv()
	{
		let _this = this;
		let data = {};
		data[Craft.csrfTokenName] = Craft.csrfTokenValue;
		return $.ajax({
			dataType: 'json',
			url: _this.exportAllUrl,
			data: data,
			method: 'POST'
		}).done(function(data){
			if (data.data) {
				_this.downloadFile(data.data, 'export.csv', 'text/csv');
			}
		});
	}

	downloadFile(data, fileName, type="text/plain") {
		const a = document.createElement("a");
		a.style.display = "none";
		document.body.appendChild(a);

		// Set the HREF to a Blob representation of the data to be downloaded
		a.href = window.URL.createObjectURL(
			new Blob([data], { type })
		);

		a.setAttribute("download", fileName);

		a.click();

		// Cleanup
		window.URL.revokeObjectURL(a.href);
		document.body.removeChild(a);
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

class ChartView
{
	constructor()
	{
		this.container = $('#chart-view');
		this.filters = this.container.find('form');
		this.url = '/admin/formidable/submissions/loadAll';
		this.chart = new Chart('chart-container', Craft.t('formidable', 'submissions'));
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
			data = _this.normalizeData(data.submissions);
			_this.chart.load(data.labels, data.data);
		});
	}

	normalizeData(submissions)
	{
		let forms = {};
		let labels = [];
		submissions.forEach(sub => {
			if (!labels.includes(sub.dateCreated)) {
				labels.push(sub.dateCreated);
			}
			if (!(sub.form_id in forms)) {
				forms[sub.form_id] = {
					label: sub.form.name,
					fill: false,
					borderColor: sub.form.color
				};
			}
		});
		Object.keys(forms).forEach(form_id => {
			forms[form_id].data = labels.map(x => 0);
		});
		submissions.forEach(sub => {
			forms[sub.form_id].data[labels.indexOf(sub.dateCreated)] ++;
		});

		return {
			labels: labels,
			data: Object.values(forms)
		};
	}
}

class TableView
{
	constructor()
	{
		this.container = $('#table-view');
		this.list = $('#submission-list');
		this.actions = $('#submission-actions');
		this.filters = this.container.find('form');
		this.url = '/admin/formidable/submissions/load';
		this.deleteSelectionUrl = '/admin/formidable/submissions/delete-many';
		this.exportSelectionUrl = '/admin/formidable/submissions/export';
		this.selectAllElem = this.container.find('#selectAll');
		this.pagination = this.container.find('#pagination');
		this.skeleton = this.list.find('.skeleton');
		this.selected = {};
		this.bindSelects();
		this.initSelectAll();
		this.bindActions();
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

	bindSelects()
	{
		let _this = this;
		$(document).on('click', '#submission-list .submission [type=checkbox]', function(){
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
			if ($(e.option).hasClass('delete')) {
				_this.deleteSelection().done(function(){
					_this.load();
				});
			} else {
				_this.exportSelectionAsCsv();
			}
		});
	}

	exportSelectionAsCsv()
	{
		let _this = this;
		let selected = Object.keys(this.selected);
		let data = {ids: selected};
		data[Craft.csrfTokenName] = Craft.csrfTokenValue;
		return $.ajax({
			dataType: 'json',
			url: _this.exportSelectionUrl,
			data: data,
			method: 'POST'
		}).done(function(data){
			if (data.data) {
				_this.downloadFile(data.data, 'export.csv', 'text/csv');
			}
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
		$.each(this.list.find('tr.submission'), function(i, item){
			_this.select($(item));
		});
	}

	unselectAll() 
	{
		let _this = this;
		$.each(this.list.find('tr.submission'), function(i, item){
			_this.unselect($(item));
		});
	}

	select(elem)
	{
		this.selected[elem.data('submission').id] = elem.data('submission');
		elem.find('[type=checkbox]').attr('checked', 'checked');
		this.actions.show();
	}

	unselect(elem)
	{
		delete this.selected[elem.data('submission').id];
		elem.find('[type=checkbox]').removeAttr('checked');
		if ($.isEmptyObject(this.selected)) {
			this.actions.hide();
		}
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
			url: _this.url
		}).done(function(data) {
			_this.populateTable(data.submissions);
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
		this.list.find('.submission').remove();
		if (data.length) {
			this.list.find('#no-submissions').hide();
		} else {
			this.list.find('#no-submissions').show();
		}
		let sub; let submission;
		data.forEach(function(data) {
			sub = _this.skeleton.clone().show().removeClass('skeleton').addClass('submission');
			sub.find('[type=checkbox]').attr('id', sub.find('[type=checkbox]').attr('id')+data.id);
			sub.find('label').attr('for', sub.find('label').attr('for')+data.id);
			submission = new Submission(sub, data);
			sub.appendTo(_this.list.find('tbody'));
		});
	}
}

export default Submissions;