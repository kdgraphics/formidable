/*global $*/

class AssetUploader
{
    initFields($fields)
    {
        $.each($fields, (i, input) => {
            let $input = $(input);
            if (!$input.data('volume')) {
                console.warn('Asset uploader : Input ' + $input.attr('id') + ' is missing a volume data');
                return;
            }
            $input.change(() => {
                this.uploadAssets($input);
            });
        });
    }

    uploadAssets($input)
    {
        $input.parent().find('.invalid-feedback.upload').remove();
        let files = $input[0].files;
        if (!files.length) {
            return;
        }
        let data = new FormData;
        data.append('action', 'formidable/forms/upload-assets');
        data.append('volumeUid', $input.data('volume'));
        data.append('folder', $input.data('folder'));
        for (let file of files) {
            data.append('files[]', file);
        }
        $.ajax({
            method: 'post',
            url: '/',
            processData: false,
            contentType: false,
            headers: {
                Accept: "application/json; charset=utf-8",
                "X-CSRF-Token": window.Globals.csrfToken
            },
            data: data
        }).done((data) => {
            this.addUploads(data, $input);
        }).always(() => {
            $input.val('');
        }).fail(() => {
            $input.insertAfter('<div class="invalid-feedback upload">There was a problem uploading your files</div>');
        });
    }

    addUploads(data, $input)
    {
        let $container = $input.prev();
        if (!$container.hasClass('upload-container')) {
            $container = $('<div class="upload-container"></div>');
            $input.before($container);
        }
        let $elem;
        for (let upload of data) {
            $elem = $('<div class="upload"><input type="hidden" value="' + upload.id + '" name="' + $input.attr('name') + '"></div>');
            let $descriptor = $('<span class="descriptor">' + upload.title + '</span>');
            if (upload.kind == 'image') {
                $descriptor = '<img class="descriptor" src="' + upload.url + '">';
            }
            $elem.append($descriptor);
            $elem.append('<a href="#" class="js-delete">Delete</a>');
            this.initDelete($elem);
            $container.append($elem);
        }
    }

    initDelete($elem)
    {
        $elem.find('.js-delete').click((e) => {
            e.preventDefault();
            $(e.target).closest('.upload').remove();
        });
    }
}

export { AssetUploader };
