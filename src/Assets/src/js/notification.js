class Notification
{
	constructor()
	{
		this.form = $('.notification-form');
		this.list = $('#notification-list');
		this.initMethodField();
		this.initDelete();
	}

	initDelete()
	{
		var _this = this;
		this.list.find('a.delete').click(function() {
			let link = $(this);
			let name = $(this).data('name');
            if (confirm(Craft.t('inspireforms', 'Are you sure you want to delete the notification {name}', {name: name }))) {
                let data = {};
                data[Craft.csrfTokenName] = Craft.csrfTokenValue;
                let url = $(this).data('url');

                $.post(url, data, null, 'json')
                    .done( function(data) {
                        if (data.success) {
                            Craft.cp.displayNotice(Craft.t('inspireforms', 'Notification successfully deleted.'));
                            link.parent().parent().remove();
                            
                            var count = _this.list.find('tbody tr').length;
                            
                            if (length == 0) {
                                _this.list.remove();
                                $('#no-notification').show();
                            }
                        }
                        else {
                        	Craft.cp.displayError(Craft.t('inspireforms','Notification was not deleted. Please try again.'));
                        }
                    });
            }
        });
	}

	initMethodField()
	{
		this.changeMethodField();
		let _this = this;
		this.form.find('#method').change(function(){
			_this.changeMethodField();
		});
	}

	changeMethodField()
	{
		let show = 'html';
		let hide = 'template';
		if (this.form.find('#method').val() == 'template') {
			show = 'template';
			hide = 'html';
		}
		this.form.find('#'+show).attr('required', 'required');
		this.form.find('#'+show+'-field').show();
		this.form.find('#'+hide).removeAttr('required');
		this.form.find('#'+hide+'-field').hide();
	}
}

$(document).ready(function(){
	new Notification;
});