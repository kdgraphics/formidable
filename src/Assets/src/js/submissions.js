class Submission
{
	constructor(elem, data)
	{
		this.elem = elem;
		this.data = data;
		this.id = data.id;
		this.deleteUrl = '/admin/inspireforms/submissions/delete/'+this.id;
		this.elem.data('submission', this);
		if (data.read) {
			elem.find('.read').html(Craft.t('inspireforms', 'Read'));
		} else {
			elem.addClass('unread');
			elem.find('.read').html(Craft.t('inspireforms', 'New'));
		}
		elem.find('.form').html(data.form.name);
		elem.find('.created').html(data.dateCreated);
		elem.find('.notified').html(data.notified ? Craft.t('inspireforms', 'Yes') : Craft.t('inspireforms', 'No'));
		elem.find('.adminNotified').html(data.adminNotified ? Craft.t('inspireforms', 'Yes') : Craft.t('inspireforms', 'No'));
		this.modal = InspireForms.Modal;
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
			if (confirm(Craft.t('inspireforms', 'Are you sure you want to delete this submission ?'))) {
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
				});
			}
		});
	}

	remove()
	{
		this.elem.remove();
	}
}

var PreviewSubmissionModal = Garnish.Modal.extend({

	submissionId : null,
	desiredWidth: function() {
		if (Garnish.isMobileBrowser(true)) {
			return $(window).width() - 40;
		} else {
			return 400;
		}
	},

	init: function(settings) {
		settings = $.extend(this.defaultSettings, settings);
		let _this = this;
		this.$container = $('<div class="modal submissionmodal hidden">\
			<a class="icon delete close" title="Close" href="#"></a>\
			<div class="body"></div>\
		</div>').appendTo(Garnish.$bod);

		this.$container.find('.close').click(function(e){
			_this.hide();
		});

        this.base(this.$container, $.extend({
            resizable: true
        }, settings));
	},

	open: function(id) {
		if (id == this.submissionId) {
			return this.show();
		}
		this.submissionId = id;
		let data = {};
		data[Craft.csrfTokenName] = Craft.csrfTokenValue;
		let _this = this;
		$.ajax({
			url: '/admin/inspireforms/submissions/view/'+id,
			dataType: 'json',
			data: data,
			method: 'POST'
		}).done(function (data){
			_this.$container.find('.body').html(data.html);
			_this.$container.removeClass('hidden');
			_this.show();
		}).fail(function() {
			alert(error);
		});
	}
});

class Submissions
{
	constructor()
	{
		this.container = $('#submissions-view');
		this.list = $('#submission-list');
		this.actions = $('#submission-actions');
		this.filters = this.container.find('form');
		this.deleteSelectionUrl = '/admin/inspireforms/submissions/delete-many';
		this.exportSelectionUrl = '/admin/inspireforms/submissions/export';
		this.selectAllElem = this.container.find('#selectAll');
		this.pagination = this.container.find('#pagination');
		this.skeleton = this.list.find('.skeleton');
		this.tab = $('body').find('#tab-submissions');
		this.selected = {};
		this.load();
		let _this = this;
		this.filters.find('input[type=text], select').change(function(){
			_this.load();
		});
		this.filters.on('reset', function(){
			setTimeout(function() {
				_this.load();
			}, 1);
		});
		this.initSelectAll();
		this.bindSelects();
		this.bindActions();
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
				_this.exportSelection();
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

	load(page = 0)
	{
		let action = this.filters.attr('action');
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
			url: action
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


$(document).ready(function(){
	window.InspireForms = {
		Submissions: new Submissions,
		Modal: new PreviewSubmissionModal({autoShow: false})
	};
});