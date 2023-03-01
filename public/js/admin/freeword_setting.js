$(function(){

  $('#top_freeword_submit').on('click',function(e){

    e.preventDefault();

    $(this).closest('form').find("input").each(function() {
      $(this).removeAttr("style");
      $(this).parent().find("p").remove();
    });

    checkRes = validFreeword($(this).closest('form'));
    if(checkRes == false) {
      return false;
    }

    var data = $(this).closest('form').serialize();
    app.apiCustom('', data , {
      onSuccess: function(res){
        app.modal.tempo(2000,'',res.message,function(){
          location.reload(true);
        });
      },
      onError: function(res){
        app.modal.tempo(2000,'',res.responseJSON.error.message,function(){
        });
      }
    });
    return false;
  });

  var validFreeword = function(formObj) {
    var validFlg = true;
    $(formObj).find("input[type=text]").each(function() {
      var dispVal = $(this).val();
      if(dispVal == '') {
        $(this).attr("style", 'background-color: #f7e6e6;');
        $(this).parent().append("<p class='errors'>入力してください</p>");
        validFlg = false;
        return true;
      }
      var maxLen = $(this).attr("data-maxlength");
      if(dispVal.length > maxLen) {
        $(this).attr("style", 'background-color: #f7e6e6;');
        $(this).parent().append("<p class='errors'>"+ maxLen + "文字以内で入力してください</p>");
        validFlg = false;
      }
    });
    return validFlg;
  };

  // 上下ボタンの有効・無効の制御
  var ctlArrow = function() {
    // 一番上のUPをdisabled
    $("#fw-shubetsu-sort").find(".fw-up-btn").removeClass('is-disable');
    $("#fw-shubetsu-sort").find(".fw-up-btn").eq(0).addClass('is-disable');

    // 一番下のDOWNをdisabled
    $("#fw-shubetsu-sort").find(".fw-down-btn").removeClass('is-disable');
    $($("#fw-shubetsu-sort").find(".fw-down-btn").get().reverse()).eq(0).addClass('is-disable');
  };

  // 下ボタンにイベントを付与
  $("#fw-shubetsu-sort .fw-down-btn").on('click', function() {
    if($(this).hasClass('is-disable')) {
      return false;
    }
    var selfTr = $(this).closest('tr');
    var nextTr = $(selfTr).next();
    if(nextTr.length != 0) {
      selfTr.insertAfter(nextTr);
      ctlArrow();
    }
  });

  // 上ボタンにイベントを付与
  $("#fw-shubetsu-sort .fw-up-btn").on('click', function() {
    if($(this).hasClass('is-disable')) {
      return false;
    }
    var selfTr = $(this).closest('tr');
    var prevTr = $(selfTr).prev();
    if(prevTr.length != 0) {
      selfTr.insertBefore(prevTr);
      ctlArrow();
    }
  });

  // 表示非表示チェック
  $("#fw-shubetsu-sort .ctl_display_flg").on('change', function() {
    if($(this).prop('checked')) {
      $(this).closest('dl').find("input[name='freeword_setting[display_flg][]']").eq(0).val(1);
    } else {
      $(this).closest('dl').find("input[name='freeword_setting[display_flg][]']").eq(0).val(0);
    }
  });

  // プレースホルダ―の初期化
  $("#freeword_setting .reset-placeholder").on('click', function() {
    $("#freeword_setting").find("input[name='freeword_setting[place_holder][]']").each(function() {
      $(this).val($(this).attr('placeholder'));
    });
  });

  $("#originalFormTagHidden").val($("#originalFormTag").val());
  $("#originalSearchScriptHidden").val($("#originalSearchScript").val());

  ctlArrow();
});