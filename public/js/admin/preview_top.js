$(function(){

    function url_query( query ) {
        query = query.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var expr = "[\\?&]"+query+"=([^&#]*)";
        var regex = new RegExp( expr );
        var results = regex.exec( window.location.href );
        if ( results !== null ) {
            return results[1];
        } else {
            return false;
        }
    }
    /**
   * post
   *
   * @param url string
   * @param data object
   * @param openWindow boolean
   */
   var postForm = function (device, data, openWindow) {
    $.ajax({
        type: 'POST',
        url: '/admin/company/get-params-preview?company_id=' + url_query('company_id'),
        data: data,
        timeout: 120 * 1000,
        dataType: 'json'

    }).done(function (res) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': res.data.token
            }
        });
        var time, $form, params, url;
        var parentId = 0;
        url = '/publish/preview-page/id/' + res.data.page_id + '/parent_id/'+ parentId  +'/device/' + device + '/';
        params = {action: url, method: 'post'};
        time = $.now();
        if (!!openWindow) {

            // open new "Tab"
            window.open('', 'formpost' + time);
      
            // open new "Window"
            // window.open('', 'formpost', 'width=1000,height=800,scrollbars=yes');
            params.target = 'formpost' + time;
            data.target = 'formpost' + time;
          }
        $form = $('<form/>', params);
        $form.append($('<input/>', {'type': 'hidden', 'name': '_token', 'value': res.data.token}));
        $form.append($('<input/>', {'type': 'hidden', 'name': 'navigation', 'value': res.data.navigation}));
        $form.append($('<input/>', {'type': 'hidden', 'name': 'company_id', 'value': url_query('company_id')}));
        if (res.data.notifications) {
            $.each(res.data.notifications, function (index, values) {
                $.each(values, function (i, v) {
                    $form.append($('<input/>', {'type': 'hidden', 'name': 'notifications['+ index +'][' + i +']', 'value': v}));
                })
            });
        }
        if (res.data.koma) {
            $.each(res.data.koma, function (index, values) {
                $.each(values, function (i, v) {
                    $form.append($('<input/>', {'type': 'hidden', 'name': 'koma['+ index +'][' + i +']', 'value': v}));
                })
            });
        }

        $form.appendTo($('body')).submit().remove();
    });
  };
    var $form = $('form');
	$('.btn-preview').on('click', function () {

        var device = $(this).attr('data-type');
        var data = [];
        data = $form.serializeArray();
        postForm(device, data, true);
		
		return false;
    });
    
});