$(function(){

  $('#global_navigation_select_submit').prop('disabled',true);
  /**
   * select onchange
   */
  window.loadGlobalNavigation = function(target){
    var value = $(target).val();
    var valid = (value && value.length > 0)? true: false;
    $('#global_navigation_select_submit').prop('disabled',!valid);
    var td = $(target).closest('tr').find('td');
    $(target).closest('tr').find('.sel-custom').html(value);
    if(td.length > 0){
      td[0].innerHTML = value;
    }
  };

  $('#global_navigation_select_submit').on('click',function(e){
    e.preventDefault();
    var data = $(this).closest('form').serialize();
    app.apiCustom('', data , {
      onSuccess: function(res){
        app.modal.tempo(2000,'',res.message,function(){
          location.reload(true);
        });
      },
      onError: function(err){

      }
    });
  })
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

  $('.read-setting-glonavi, .read-glonavi').on('click', function(e){
    e.preventDefault();
    var url = $(this).attr('href');
    app.modal.tempo(2000,'','読み込みが完了しました',function(){
      location.href = url;
    });
  });
});