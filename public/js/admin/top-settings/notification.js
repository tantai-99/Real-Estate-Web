$(function () {

  updateUpDown();

  $('body').on('click','.i-e-up', function(){
    var me = $(this);
    var tr = me.closest('tr');
    var prev = tr.prev();
    if(prev.length === 0){
      console.log('cannot up');
      return
    }
    console.log('up');
    prev.before(tr);
    updateUpDown();
  });


  $('body').on('click','.i-e-down', function(){
    var me = $(this);
    var tr = me.closest('tr');
    var next = tr.next();
    if(next.length === 0){
      console.log('cannot down');
      return;
    }
    next.after(tr);
    updateUpDown();
  });

  // Create notification
  $('.btn-noti-create').on('click',function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var data = form.find('.create-form').serialize();

    var previewData = data + '&preview=true';
    app.apiCustom('',previewData,{
      el: $('.tb-create'),
      onSuccess: function(data){
        var text = form.find("input[name='create[parent_page_id]']:checked").parent().text();

        var content = '<div class="section"><div class="section-title text-left"><h2 class="pt10 pb10">カテゴリー登録</div><table class="tb-basic tb-create-dialog tb-bordered"><tbody><tr><td width="30%">カテゴリー名</td><td width="70%">'+form.find('#create-title').val() + '</td></tr><tr><td>class名</td><td>'+ form.find('#create-class').val()+ '</td></tr><tr><td>設定お知らせ</td><td>'+text+'</td></tr></tbody></table></div>';
        var modal = app.modal.popup({
          title: '',
          modalContentsClass: 'notification-edit',
          modalBodyInnerClass: 'text-left',
          autoRemove: true,
          contents: content,
          header: false,
          ok: '登録',
          cancel: '閉じる',
          onClose: onClose
        });

        modal.show();
      },
      onError: function(xhr){
        // on error
      }
    });

    var onClose = function(ret){
      if (!ret) return;
      app.apiCustom('',data,{
        el: $('.tb-create'),
        onSuccess: function(data){
          form.find('#create-title').val('');
          form.find('#create-class').val('');
          insertRow(data.data);
        },
        onError: function(xhr){
          // on error
        }
      });
    };
  });

  $('body').on('click', '.btn-noti-update',function(e){
    var closeButton = '<div class="close-button-modal-square"><a class="modal-close-button"><i class="i-e-delete"></i></a></div>';
    e.preventDefault();
    var tr = $(this).closest('tr');
    var data = JSON.parse(tr.attr('data-details'));
    var raw = $('.edit-form').html();
    var html = $(raw);
    //enter data
    html.find('#edit-id').val(data.id);
    html.find('#edit-title').val(data.title);
    html.find('#edit-class').val(data.class);
    html.find('input[name="edit[parent_page_id]"][value='+ data.parent_page_id + ']').attr('checked', true);
    var modal = app.modal.popup({
      "contents": html,
      modalContentsClass: "notification-edit",
      ok: false,
      cancel:false,
      header:false
    });

    var header = modal.$el.find('.modal-contents');
    header.prepend(closeButton);
    modal.show();
  });

  $('body').on('click', '#edit-submit', function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var data  = form.serialize();
    app.apiCustom('',data, {
      el : $('.form-edit'),
      onSuccess: function(data){
        updateRow(data.data);
        closePopup();
      },
      onError: function(err){
        // after error
      }
    });
  });


  $('body').on('click', '.btn-noti-delete',function(e){
    e.preventDefault();
    var tr = $(this).closest('tr');
    var data = JSON.parse(tr.attr('data-details'));
    var raw = $('.delete-form').html();
    var html = $(raw);
    //enter data
    html.find('#delete-id').val(data.id);
    html.find('#delete-title').html(data.title);
    html.find('#delete-class').html(data.class);
    html.find('#delete-parent_page_id').html(titleNews[data.parent_page_id]);
    app.modal.popup({
      "contents": html,
      modalContentsClass: "notification-edit",
      ok: false,
      cancel:false,
      header:false
    }).show();
  });


  $('body').on('click', '#delete-submit', function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var data  = form.serialize();
    app.apiCustom('',data, {
      el: $('.delete-form'),
      onSuccess: function(data){
        deleteRow(data.data);
        closePopup();
      },
      onError: function(err){
        // after error
      }
    });
  });

  function insertRow(data){
    var table = $("table[data-id='" + data.parent_page_id + "']");
    var html = '<tr data-id="'+ data.id +'" ><td>' + data.title + '</td><td>' + data.class +'</td><td class="actions"><div class="sort"><i class="i-e-up"></i><i class="i-e-down"></i></div><div class="buttons"><input type="submit" value="' + window.updateText + '" class="btn-t-gray btn-noti-update"><input type="submit" value="'+ window.deleteText +'" class="btn-t-gray btn-noti-delete"></div></td></tr>';
    if(table){
      table.find('tbody').append(html);
      var tr = table.find("tr[data-id='" + data.id + "']");
      tr.attr('data-details',JSON.stringify(data));
      updateUpDown();
    }
  }

  function updateRow(data){
    var tr = $('table').find("tr[data-id='" + data.id + "']");

    var oldData = JSON.parse(tr.attr('data-details'));

    if(oldData.parent_page_id != data.parent_page_id){
      var newTable = $("table[data-id='" + data.parent_page_id + "']");
      var newTr = tr.clone().html();
      newTable.find('tbody').append('<tr data-id="'+ data.id +'">' + newTr + '</tr>');
      tr.remove();
      tr = newTable.find("tr[data-id='" + data.id + "']");
    }

    tr.attr('data-details',JSON.stringify(data));
    tr.children('td:first').html(data.title);
    tr.children('td:nth-child(2)').html(data.class);
    updateUpDown();
  }

  function deleteRow(data){
    var tr = $('table').find("tr[data-id='" + data.id + "']");
    tr.remove();
    updateUpDown();
  }

  function closePopup(){
    var modal = $('#modal');
    if(!modal.hasClass('is-hide')){
      modal.addClass('is-hide');
    }
    modal.empty();
  }

  // update button up/down
  function updateUpDown(){
    var tables = $('.tb-news-list');
    tables.each(function( index , table ) {
      var tbody = $(table).find('tbody');
      var rows = tbody.find('tr');
      rows.each(function(r_index, row){
        var $row = $(row);
        //clear
        $row.find('.i-e-up').removeClass('disabled');
        $row.find('.i-e-down').removeClass('disabled');

        var prev = $row.prev();
        // cannot up
        if(prev.length === 0){
          $row.find('.i-e-up').addClass('disabled');
        }

        //cannot down
        var next = $row.next();
        if(next.length === 0){
          $row.find('.i-e-down').addClass('disabled');
        }

      });
    });
  }

  // SETTINGS

  $('#notification-settings-submit').on('click', function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var tables = $('.tb-news-list');
    var sort = [];
    try{
      tables.each(function( index , table ) {
        var tbody = $(table).find('tbody');
        var table_id = $(table).attr('data-id');
        var rows = tbody.find('tr');
        var rowsArr = [];

        rows.each(function(r_index, row){
          var row_id = $(row).attr('data-id');
          rowsArr.push(row_id);
        });

        sort.push({
          id: table_id,
          rows: rowsArr
        });
      });
    }
    catch($e){
      console.log($e);
    }

    var data = form.serialize() ;
    var totalData = data + '&' + $.param({ 'sort' : sort }) + '&' + $.param({ 'page_settings' : true });
    app.apiCustom('',totalData,{
      popupError: true,
      onSuccess: function(data){
        app.modal.tempo(2500,'',data.message);
      }
    });
  });
  $('select').addClass("ghostSelect");
  $('#main').css({'min-width': '1150px'});
  if ($("#contents").width() - $('#g-header').width() > 60) {
      $('#g-header').css({'width': $("#contents").width()-1});
  }
  var tab2 = $('#side');
  if (tab2.length) {
    $(window).scroll(function () {
      $('#g-header').css({'width': $("#contents").width()});
    });
  }

  $("input[type='checkbox']").on('click', function(e){
    if ($(this).is(':checked')) {
      $(this).parent().find("input[type='hidden']").val(1);
    } else {
      $(this).parent().find("input[type='hidden']").val(0);
    }
  });
});