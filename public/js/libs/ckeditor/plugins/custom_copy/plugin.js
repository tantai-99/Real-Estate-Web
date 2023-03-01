(function(){

    var command= {
        exec:function(editor){
            var iframe = $('#' + editor.element.getId()).parent().find('iframe').contents();
            var element = $('.element-list-title');
            element.append('<input type="text" name="focusTxt">');
            element.find('input[name="focusTxt"]').focus().remove();
            var title = $('input[name="tdk[title]"]').val();
            if (editor.getData() != '') {
                app.modal.confirm('', '現在入力されている内容は破棄されます。上書きしてよろしいですか？', function (ret) {
                    if (!ret) {
                        return;
                    }
                    iframe.find('body').html('<p>'+title+'</p>');
                    editor.document.fire( 'click' );
                });
            } else {
                iframe.find('body').html('<p>'+title+'</p>');
                editor.document.fire( 'click' );
            }
        }
    },

    name = 'custom_copy';
    CKEDITOR.plugins.add(name, {
        init:function(editor){
            editor.addCommand(name, command);
            editor.ui.addButton('custom_copy',{
                    label:'ページタイトルをコピーする',
                    icon: null,
                    command: name
            });
        }
    });
})();