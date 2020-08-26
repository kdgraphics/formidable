class Forms
{
	constructor()
	{
		this.list = $('#form-list');
		this.initDelete();
	}

	initDelete()
	{
		var _this = this;
		this.list.find('a.delete').click(function() {
			let link = $(this);
			let name = $(this).data('name');
            if (confirm(Craft.t('inspireforms', 'Are you sure you want to delete the form {name}', {name: name }))) {
                let data = {};
                data[Craft.csrfTokenName] = Craft.csrfTokenValue;
                let url = $(this).data('url');

                $.post(url, data, null, 'json')
                    .done( function(data) {
                        if (data.success) {
                            Craft.cp.displayNotice(Craft.t('inspireforms', 'Form successfully deleted.'));
                            link.parent().parent().remove();
                            
                            var count = _this.list.find('tbody tr').length;
                            
                            if (length == 0) {
                                _this.list.remove();
                                $('#no-forms').show();
                            }
                        }
                    }).fail(function(data){
                    	Craft.cp.displayError(data.responseJSON.error);
                    });
            }
        });
	}
}

$(document).ready(function(){
	new Forms;
});