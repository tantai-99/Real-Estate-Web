$(function () {

  var mode = $('#new_or_edit').data('mode');
  var cnt = 255;//フリーテキストname属性用

  var onLoad = function () {
    if (mode == 'edit') {
      displayRow();
      sortRow();
    }
    showImage();
    init();
  }

  var init = function () {
    initOptions();
    initArrow();
  }

  /**
   * 「行を追加」option設定
   */
  var initOptions = function () {

    var addOptions = '<option value="">選択してください</option>';
    var items = $('.outline:visible .item-group');
    for (var i = 0; i < items.length; i++) {

      // remove junk free element
      if (items.eq(i).hasClass('freetext') && items.eq(i).hasClass('is-hide')) {
        items.eq(i).remove();
        continue;
      }

      // skip display element
      if (!items.eq(i).hasClass('is-hide')) {
        continue;
      }

      // get class
      var className = items.eq(i).attr('class').split(' ');
      for (var j = 0; j < className.length; j++) {
        if (className[j] != 'item-group' && className[j] != 'is-hide') {

          // push option element
          addOptions += '<option value='+className[j]+'>'+items.eq(i).data('label')+'</option>';
          break;
        }
      }
    }
    addOptions += '<option value="free">フリーテキスト</option>';

    var select = $('.outline:visible .item-add select');
    select.find('option').remove();
    select.append(addOptions);
  }

  var $outline = $('.outline:visible');
  var $itemList = $outline.find('.item-list');
  var elem = $itemList.find('input, textarea, select');

  /**
   * 行の表示・非表示
   */
  var displayRow = function () {

    for (var i = 0; i < elem.length; i++) {
      if (!elem.eq(i).data('default-show')) {
        elem.eq(i).attr('disabled', 'disabled');
        elem.eq(i).closest('.item-group').addClass('is-hide');
      }
    }
  }

  /**
   * 行の並び替え
   */
  var sortRow = function () {

    var res = [];
    for (var i = 0; i < elem.length; i++) {
      var sort = elem.eq(i).data('default-sort');
      if ($.isNumeric(sort)) {
        res[sort] = elem.eq(i).closest('.item-group');
      }
    }
    $itemList.append(res);
  }

  /**
   * imgタグ生成
   */
  var showImage = function () {

    var imgElem = ['outline_logo_id', 'outline_img_id'];
    for (var i = 0; i < imgElem.length; i++) {
      var selector = '#'+imgElem[i];
      var val = $(selector).val();
      if ($.isNumeric(val) && val > 0) {
        var html = '<img src="/image/hp-image?id='+val+'">';
        $(selector).closest('.select-image').find('a').html('').append(html);
      }
    }
  }

  /**
   * 矢印の非活性化
   * @param shiftBtns
   * @param btnType
   * @param target
   */
  var disabledArrow = function (shiftBtns, btnType, target) {

    for (var i = 0; i < shiftBtns.length; i++) {
      if ((i == 0 && btnType == 'up') || (i == shiftBtns.length-1 && btnType == 'down')) {
        if (target == 'self') {
          shiftBtns.eq(i).addClass('is-disable');
        } else {
          shiftBtns.eq(i).parent().addClass('is-disable');
        }
      } else {

        if (target == 'self') {
          shiftBtns.eq(i).removeClass('is-disable');
        } else {
          shiftBtns.eq(i).parent().removeClass('is-disable');
        }
      }
    }
  };

  /**
   * 矢印の初期化
   */
  var initArrow = function () {

    var col = $('.col:visible');
    for (var i = 0; i < col.length; i++) {
      var parts = col.eq(i).find('.page-element-body');
      for (var j = 0; j < parts.length; j++) {
        disabledArrow(parts.eq(j).find('.i-e-down:visible'), 'down', 'self');
        disabledArrow(parts.eq(j).find('.i-e-up:visible'), 'up', 'self');
      }
    }
  };

  $('.item-group-add-btn').click(function () {

    var option = $(this).prev('select').find('option:selected');

    // not selected
    if (!option.val()) {
      return;
    }

    // selected item
    var target = $(this).closest('.page-element').find('.'+option.val());

    if (target.length > 0) {
      target.removeClass('is-hide');
      target.find('input, textarea, select').removeAttr('disabled');
      $(this).closest('.page-element').find('.item-list').append(target);


    } else if (option.val() == 'free') {

      option = $('.outline_access_head:first').clone(true);
      option
          .attr({
            'class': '',
            'data-label': 'フリーテキスト'
          })
          .addClass('item-group freetext outline_free_'+(cnt+1)+'_head');
      option.find('dt input')
          .attr({
            'name': 'outline[free_'+(cnt+1)+'][head]',
            'id': '',
            'placeholder': 'フリーテキスト',
            'value': 'フリーテキスト'
          });
      option.find('dd input')
          .attr({
            'name': 'outline[free_'+(cnt+1)+'][body]',
            'id': '',
            'placeholder': 'フリーテキスト'
          });
      option.find(':text').removeAttr('disabled');

      $(this).closest('.page-element').find('.item-list').append(option);
      cnt++;
    }
    init();
  });

  $('.add-parts').click(function () {
    init();
  });

  // 要素の削除
  // - 内部的にはhide
  $('.hide-btn').click(function () {

    var target = $(this).closest('.item-group');
    app.modal.confirm('削除', '削除してよろしいですか？', function (res) {
      if (!res) {
        return;
      }
      target.addClass('is-hide');
      target.find('input, textarea, select').attr('disabled', 'disabled');
      init();
    });
  });

  onLoad();

})

