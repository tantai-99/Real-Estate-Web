
$(function () {
  $('.select-number').on('change',function(){
    var value = $(this).val();
    if(value && value > 0){
      $(this).closest('tr').find('.sel-custom').html(value);
      $(this).closest('tr').find('.sel-custom-agency').html(value);
      $(this).closest('tr').find('.sel-custom-agency-koma').html(value);
      var td = $(this).closest('tr').find('td');
      if(td.length > 0){
        td[0].innerHTML = value;
      }
    }
  });
  $('.select-custom-agency select').on('change',function() {
    var value = $(this).val();
    if ($(this).find('option:selected').text()) {
        value = $(this).find('option:selected').text();
    }
    $(this).parent().find('.sel-custom-agency-koma').html(value);
  });
    setHeight();
    $( window ).resize(function() {
        setHeight();
    });

    $('.select-custom-agency select').addClass("ghostSelect");
    $('.select-custom-noti select').addClass("ghostSelect");
    if (window.location.href.indexOf("detail") > -1) {
      if ($("#contents").width() - $('#g-header').width() > 60) {
        $('#g-header').css({'width': $("#contents").width()-1});
      }
      var tab2 = $('#side');
      if (tab2.length) {
          $(window).scroll(function () {
            $('#g-header').css({'width': $("#contents").width()});
          });
      }
    }

	// 迷惑メール条件管理画面
  var inputs = $("#search_area input:radio[name='range_option']");
  var checked = inputs.filter(":checked").val();
  var memberNo = $("#search_area #member_no").closest("tr");
  var allMemberVal = 0;

  inputs.on("click", function(){
    // 設定範囲のラジオボタンの付け外しを可能にする
    if($(this).val() === checked) {
      $(this).prop("checked", false);
      checked = '';
    } else {
      $(this).prop("checked", true);
      checked = $(this).val();
    }
    // 設定範囲 = 全会員の場合は会員Noをdisableにする
    toggleMemberNo(memberNo, $(this).val() == allMemberVal && $(this).val() === checked)
  });
  toggleMemberNo(memberNo, checked == allMemberVal)
  // 迷惑メール条件管理編集の設定範囲 = 全会員の場合、会員Noを非表示にする
  var inputs = $("#spam-edit input:radio[name='range_option']");
  var checked = inputs.filter(":checked").val();
  if(checked == allMemberVal) {
    inputs.closest("tr").next().addClass("is-hide");
    $(this).closest("tr").next().find("input[name='member_no_add']").prop("disabled", true);
    $(this).closest("tr").next().find("input[name='member_no']").prop("disabled", true);
  }
  inputs.on("click", function(){
    if($(this).val() == allMemberVal) {
      $(this).closest("tr").next().addClass("is-hide");
      $(this).closest("tr").next().find("input[name='member_no_add']").prop("disabled", true);
      $(this).closest("tr").next().find("input[name='member_no']").prop("disabled", true);
    } else {
      $(this).closest("tr").next().removeClass("is-hide");
      $(this).closest("tr").next().find("input[name='member_no_add']").prop("disabled", false);
      $(this).closest("tr").next().find("input[name='member_no']").prop("disabled", false);
    }
  });
  // 迷惑メール条件管理登録確認・削除確認の設定範囲 = 全会員の場合、会員Noを非表示にする
  var inputs = $(".spam input[name='range_option']");
  var checked = inputs.val();
  if(checked == allMemberVal) {
    inputs.closest("tr").next().addClass("is-hide");
  }
});

function setHeight() {
    if (window.location.pathname.substring(window.location.pathname.lastIndexOf("/") + 1) == 'original-edit') {
        var headerH = $('#g-header').height();
        var mainH = parseInt($('#main').css('padding-top')) + parseInt($('#main').css('padding-bottom'));
        var topicpathH = parseInt($('#topicpath').css('margin-bottom')) + $('#topicpath').height();
        var footerH = $('#g-footer').height() + parseInt($('#g-footer').css('margin-top')) + parseInt($('#g-footer').css('margin-bottom'));
        var height = window.innerHeight - headerH - mainH - topicpathH - footerH - 20;
        if (height > 300) {
            $('#main .main-contents .original').css({'height': height});
        } else {
            $('#main .main-contents .original').css({'height': '300px'});
        }
    }
}

function toggleMemberNo(memberNo, disable = true) {
  memberNo.find("th").toggleClass("is-disable", disable);
  memberNo.find("input[name='member_no']").prop("disabled", disable);
}