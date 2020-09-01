var PreviewModal = Garnish.Modal.extend({

	id : null,
	url: null,

	desiredWidth: function() {
		if (Garnish.isMobileBrowser(true)) {
			return $(window).width() - 40;
		} else {
			return 400;
		}
	},

	setUrl: function(url) {
		this.url = url;
	},

	init: function(settings) {
		settings = $.extend(this.defaultSettings, settings);
		let _this = this;
		this.$container = $('<div class="modal inspireformsmodal hidden">\
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
		if (id == this.id) {
			return this.show();
		}
		this.id = id;
		let data = {};
		data[Craft.csrfTokenName] = Craft.csrfTokenValue;
		let _this = this;
		$.ajax({
			url: _this.url+id,
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

export default PreviewModal;