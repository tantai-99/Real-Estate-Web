$(function () {

  var tablePublish = $('#table-publish');
  
  var $contents = $('#contents');

  var rowBgClass = 'bg',
    urlSimple = '/publish/simple',
    urlDetail = '/publish/detail';

  var data = {};
  var saveData = {
      option: 'release',
      pages: [],
  };
  var publish2all = false;
  var publicLoad = false;

  var init = {

    onLoad: function () {
      table.toggCheckArticle(this);
      table.rowBgColor();
      publish.showNewStatus(true);
      table.init();

      //初期表示時は変更ボタンを押せないようにする
      $('.update-setting-btn, .update-setting-article-btn').each(function(index) {
        $(this).attr("disabled", "disabled");
        $(this).addClass("is-lock").addClass("is-disable");
        var chekbox = $(this).closest("tr").find(":checkbox");
         //自動更新はボタンを常に表示
         if (chekbox.prop('checked')) $(this).removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");
      });
    
      //チェックボックス押下時に変更ボタンの挙動を設定する
      $('.update_flg').each(function(index) {
        $(this).on("change", function () {
          //自動更新は除く
          var btn = $(this).closest("tr");
          if (btn.hasClass("auto-publish")) {
            return;
          }
          var btn = $(this).closest("tr").find("a");
          if(btn.hasClass("is-disable")) {
             btn.removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");
          }else{
            btn.attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");
          }
        });
      });

      // 簡易の場合
      if (location.href.indexOf(urlSimple) != -1) {
        if(typeof all_upload_flg !== 'undefined' && all_upload_flg == 1) {
          $("#table-publish").find("tbody").eq(0).find("input[type=checkbox]").each(function() {
            var parentTr = $(this).closest("tr");
            if($(parentTr).find(".i-l-update").length && $(parentTr).find(".is-draft").length == 0) {
              if($(parentTr).hasClass("auto-publish") === false) {
                $(this).closest("tr").addClass("auto-publish");
              }
            }
          });

          // 表示されている(:visible)行がすべてclass:auto-publishを持っている場合は全チェックを外せなくする
          var disable_allcheck = true;
          $("#table-publish").find("tbody").eq(0).find("input[type=checkbox]:visible").each(function() {
            if(!$(this).closest("tr").hasClass("auto-publish")) {
              disable_allcheck = false;
              return false;
            }
          });
          if(disable_allcheck) {
            $(".all-check").prop('disabled', true);
          }
        }
      }

      // 詳細かつ、all_upload_flg = 1の場合
      if (location.href.indexOf(urlDetail) != -1 && all_upload_flg == 1) {

        // 現在公開中をチェック
        tablePublish.find("tbody").eq(0).find("input[type=checkbox]").each(function() {
          // チェック済みは無視
          if($(this).prop('checked')) {
            return true;
          }
          var parentTr = $(this).closest("tr");
          if($(parentTr).find(".current-status").hasClass("status-public")) {
            if (parentTr.hasClass('row-article')) {
                publicLoad = true;
                saveData.pages = [];
            }
            $(this).click();

            var hiddenStatus = $(parentTr).find(".hidden-params-area").eq(0);
            $(hiddenStatus).closest('td').find("span").remove();
            $(hiddenStatus).before('<span>公開（更新）<span>');
            $(hiddenStatus).find(".new_release_flg").eq(0).val("1");
            $(hiddenStatus).find(".new_release_at").eq(0).val("");
            $(hiddenStatus).find(".new_close_flg").eq(0).val("");
            $(hiddenStatus).find(".new_close_at").eq(0).val(0);

            // 自動更新を設定しチェックを外せなくする
            $(this).closest("tr").addClass("auto-publish");
          }
        });
        tablePublish.find("tbody").eq(0).find('.no-display-article').each(function() {
            if($(this).find(".current-status").hasClass("status-public")) {
                $(this).find('.update_flg').val('1');
                $(this).find(".new_release_flg").eq(0).val("1");
                $(this).find(".new_release_at").eq(0).val("");
                $(this).find(".new_close_flg").eq(0).val("");
                $(this).find(".new_close_at").eq(0).val(0);
            }
        })

        // すべてclass:auto-publishを持っている場合は全チェックを外せなくする
        var disable_allcheck = true;
        tablePublish.find("tbody").eq(0).find("input[type=checkbox]").each(function() {
          if(!$(this).closest("tr").hasClass("auto-publish")) {
            disable_allcheck = false;
            return false;
          }
        });
        if(disable_allcheck) {
          $(".all-check").prop('disabled', true);
        }
      }

      //ATHOME_HP_DEV-6225
      if (typeof prereservedPages != 'undefined' && prereservedPages) {
        $.each(prereservedPages, function(id, value) {
          if (tablePublish.find('#'+id).closest('.no-display-article').length) {
            tablePublish.find('#'+id).val(value);
            if (id.indexOf('_update') != -1) {
              var pageId = id.split('_')[1];
              saveData.pages[pageId] = Boolean(value);
            }
            var arrElement = tablePublish.find('#page_article_update');
            if (arrElement.length && !arrElement.prop('checked')) {
              arrElement.prop('checked', true).change();
            }
          }
        });
      }
    },
    init: function () {
    }
  };

  var publish = {

    showNewStatus: function (init) {

      if (init == null) {
        init = false;
      }

      var $r,
        $current,
        currentStatus,
        currentReleaseAt,
        currentCloseAt,
        newReleaseFlg,
        newReleaseAt,
        newCloseFlg,
        newCloseAt,
        newStatus,
        $newStatusArea,
        hiddenParams,
        html,
        msg,
        hasNew = false;

      var rows = $('.publish-detail tr');

      for (var i = 0; i < rows.length; i++) {

        // init
        $r = $current = currentStatus = currentReleaseAt = currentCloseAt = newReleaseFlg = newReleaseAt = newCloseFlg = newCloseAt = newStatus = $newStatusArea = hiddenParams = msg = html = 0;

        // row
        $r = rows.eq(i);

        // current
        $current = $r.find('.current-status');
        if ($current.hasClass('status-draft')) {
          currentStatus = '下書き';
        }
        if ($current.hasClass('status-public')) {
          currentStatus = '公開';
        }
        currentReleaseAt = $r.find('.current-release-at').text();
        currentCloseAt = $r.find('.current-close-at').text();

        // new
        newReleaseFlg = Number($r.find('.new_release_flg').val());
        newReleaseAt = $r.find('.new_release_at').val();
        if (newReleaseAt == 0) {
          newReleaseAt = Number(newReleaseAt);
        }
        newCloseFlg = Number($r.find('.new_close_flg').val());
        newCloseAt = $r.find('.new_close_at').val();
        if (newCloseAt == 0) {
          newCloseAt = Number(newCloseAt);
        }

        // taget area
        $newStatusArea = $r.find('.new-release');

        // get hidden params
        hiddenParams = $r.find('.hidden-params-area').html();

        // reset
        $newStatusArea.html('');

        // // no change
        // if (!newReleaseFlg && !newCloseFlg) {
        //    $newStatusArea.html('<div class="hidden-params-area is-hide">' + hiddenParams + '</div>');
        //    continue;
        // }

        // new status
        if (newReleaseFlg && !newReleaseAt) {
          newStatus = '公開';
          if (currentStatus == '公開') {
            newStatus = '公開（更新）';
          }

        } else if (newCloseFlg && !newCloseAt) {
          newStatus = '下書き';
        } else {
          newStatus = currentStatus;
        }

        html = '<span>' + newStatus + '</span>';

        // is release schedule
        if (newReleaseAt) {
          msg = '修正反映';
          if (currentStatus == '下書き') {
            msg = '公開予定';
          }
          html += '<span class="watch">' + msg + '<span> ' + newReleaseAt + '</span></span>';
        }

        // is close schedule
        if (newCloseAt) {
          html += '<span class="watch">公開終了<span> ' + newCloseAt + '</span></span>';
        }

        html += '<div class="hidden-params-area is-hide">' + hiddenParams + '</div>';

        if ($r.hasClass('row-article')) {
            html = '<span>-</span>';
        }

        $newStatusArea.append(html);

        hasNew = true;
      }

      if (init && hasNew) {
        table.showDiffPages();
      }

      // #3731 fix: update table sorter whenever data table has changed, include init and update (with modal)
      tablePublish.trigger('update');
    },

    setUpdate: {

      btn: $('.update-setting-btn'),

      submit: $('.submit-testsite, .submit-publish, .submit-allupload, .submit-publish-subsutitute, .submit-publish-testsite-now,  .submit-publish-testsite-reserve, .submit-allupload-testsite, .submit-allupload-subsutitute'),

      show: function (self) {

        //ボタンがdisable状態の場合は何もしない
        if($(self).hasClass("is-disable")) return;

        // target
        var $row = $(self).closest('tr'),
          $releaseFlg = $row.find('.new_release_flg'),
          $releaseAt = $row.find('.new_release_at'),
          $closeFlg = $row.find('.new_close_flg'),
          $closeAt = $row.find('.new_close_at');

        // value
        var currentStatus = publish.setUpdate.getCurrentStatus($row),
          releaseFlg = Number($releaseFlg.val() == 1),
          closeFlg = Number($closeFlg.val() == 1),
          newReleaseAt = $releaseAt.val(),
          newCloseAt = $closeAt.val();

        if (newReleaseAt == 0) {
          newReleaseAt = Number(newReleaseAt);
        }
        if (newCloseAt == 0) {
          newCloseAt = Number(newCloseAt);
        }

        var $contens = publish.setUpdate.getHtml(currentStatus, releaseFlg, newReleaseAt, closeFlg, newCloseAt, $row.hasClass('auto-publish'));

        if(typeof all_upload_flg !== 'undefined' && all_upload_flg == 1 && $(self).closest('tr').find('.must-publish').length == 0) {
          $contens.prepend('<div class="alert-strong" style="text-align:left;">本番サイトへ「共通設定」の反映後、日時指定での公開予約ができるようになります。</div>');
        } else if($(self).closest('tr').find('.must-publish').length) {
          $contens.prepend('<div class="alert-strong" style="text-align:left;">日時指定での修正反映はできません。一度公開停止を行うことで、日時指定をすることができます。</div>');
        }

        var modal = app.modal.popup({
          title: '公開設定',
          contents: $contens,
          modalBodyInnerClass: 'align-top',
          ok: '保存',
          autoRemove: false
        });

        modal.show();

        modal.onClose = function (ret) {

          if (!ret) {
            return;
          }

          // init
          modal.$el.find(':text:hidden').val('').end().find(':radio:hidden').prop('checked', false);
          modal.$el.find('.errors').remove();


          // validate
          if (currentStatus == 'draft' && !publish.setUpdate.validateDraft(modal)) {
            return false;
          }

          if (currentStatus == 'public' && !publish.setUpdate.validatePublic(modal)) {
            return false;
          }

          // get params
          var releaseFlg, newReleaseAt = 0, closeFlg, newCloseAt = 0;

          var val = modal.$el.find('form').serializeArray();
          for (var i = 0; i < val.length; i++) {

            if (val[i].name == 'radio-new_release_at' && val[i].value !== 'none') {
              releaseFlg = 1;
              continue;
            }

            if (val[i].name == 'text-new_release_at' && val[i].value != '') {
              newReleaseAt = val[i].value;
              continue;
            }

            if (val[i].name == 'radio-new_close_at' && val[i].value !== 'none') {
              closeFlg = 1;
              continue;
            }

            if (val[i].name == 'text-new_close_at' && val[i].value != '') {
              newCloseAt = val[i].value;
            }

          }

          // set params
          $releaseFlg.val(releaseFlg);
          $releaseAt.val(newReleaseAt);
          $closeFlg.val(closeFlg);
          $closeAt.val(newCloseAt);

          // show new status
          publish.showNewStatus();

        }

        /**
         * datepicker
         */
        var op = {
          closeText: '閉じる',
          currentText: '現在日時',
          timeOnlyTitle: '日時を選択',
          timeText: '時間',
          hourText: '時',
          minuteText: '分',
          secondText: '秒',
          millisecText: 'ミリ秒',
          microsecText: 'マイクロ秒',
          timezoneText: 'タイムゾーン',
          prevText: '&#x3c;前',
          nextText: '次&#x3e;',
          monthNames: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
          monthNamesShort: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
          dayNames: ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
          dayNamesShort: ['日', '月', '火', '水', '木', '金', '土'],
          dayNamesMin: ['日', '月', '火', '水', '木', '金', '土'],
          weekHeader: '週',
          yearSuffix: '年',
          dateFormat: 'yy年mm月dd日',
          timeFormat: 'HH時',
          showOn: 'button',
          buttonImage: '/images/common/icon_date.png',
          buttonImageOnly: true,
          buttonText: 'Select date',
          controlType: 'select',
          oneLine: true,
          separator: "",
        };

        $('.datepicker').datetimepicker(op);

        // toggle child elements
        $(':radio[name=radio-update]').change(function () {

          var target = $(this).closest('.modal-publishing').find('.modal-datetime-setting');
          publish.setUpdate.toggleDatetime($(this), 'release', target);

        });

        // toggle datetime input box
        $(':radio[name=radio-new_release_at], :radio[name=radio-new_close_at]').change(function () {

          var target = $(this).closest('dd').find('.modal-datetime-area');
          publish.setUpdate.toggleDatetime($(this), 'reserve', target);

        });
      },

      /**
       * 現在の公開ステータスを取得
       * @param $row
       * @returns {string}
       */
      getCurrentStatus: function ($row) {

        if ($row.find('.current-status').hasClass('status-public')) {
          return 'public';
        }
        if ($row.find('.current-status').hasClass('status-draft')) {
          return 'draft';
        }

      },

      /**
       * モーダルコンテンツ
       * @type {string}
       */
      html: '<div class="modal-publishing">' +
      '<form name="modal-form">' +
      '<div class="modal-publishing-new">' +
      '<dl>' +
      '<dt><label><input type="radio" name="radio-update" value="draft">下書き</label></dt>' +
      '<dd></dd>' +
      '</dl>' +
      '<dl>' +
      '<dt><label><input type="radio" name="radio-update" value="release">公開</label></dt>' +
      '<dd>' +
      '<dl class="modal-datetime-setting">' +
      '<dt>公開日：</dt>' +
      '<dd>' +
      '<label><input type="radio" name="radio-new_release_at" value="now">更新後すぐ</label>' +
      '<label><input type="radio" name="radio-new_release_at" value="reserve">日時指定</label>' +
      '<div class="modal-datetime-area">' +
      '<input type="text" name="text-new_release_at" value="" class="modal-datetime datepicker" maxlength="14">' +
      '</div>' +
      '</dd>' +
      '<dt>停止日：</dt>' +
      '<dd>' +
      '<label><input type="radio" name="radio-new_close_at" value="none">停止しない</label>' +
      '<label><input type="radio" name="radio-new_close_at" value="reserve">日時指定</label>' +
      '<div class="modal-datetime-area">' +
      '<input type="text" name="text-new_close_at" value="" class="modal-datetime datepicker" maxlength="14">' +
      '</div>' +
      '</dd>' +
      '</dl>' +
      '</dd>' +
      '</dl>' +
      '</div>' +
      '<div class="modal-publishing-edit">' +
      '<dl>' +
      '<dt>修正反映日：</dt>' +
      '<dd>' +
      '<label><input type="radio" name="radio-new_release_at" value="none">反映しない</label>' +
      '<label><input type="radio" name="radio-new_release_at" value="now">更新後すぐ</label>' +
      '<label><input type="radio" name="radio-new_release_at" value="reserve">日時指定</label>' +
      '<div class="modal-datetime-area">' +
      '<input type="text" name="text-new_release_at" value="" class="modal-datetime datepicker" maxlength="14">' +
      '</div>' +
      '</dd>' +
      '<dt>公開停止日：</dt>' +
      '<dd>' +
      '<label><input type="radio" name="radio-new_close_at" value="none">停止しない</label>' +
      '<label><input type="radio" name="radio-new_close_at" value="now">更新後すぐ</label>' +
      '<label><input type="radio" name="radio-new_close_at" value="reserve">日時指定</label>' +
      '<div class="modal-datetime-area">' +
      '<input type="text" name="text-new_close_at" value="" class="modal-datetime datepicker" maxlength="14">' +
      '</div>' +
      '</dd>' +
      '</dl>' +
      '</div>' +
      '</form>' +
      '</div>',

      /**
       * HTML取得
       * @param currentStatus
       * @param releaseFlg
       * @param newReleaseAt
       * @param closeFlg
       * @param newCloseAt
       * @returns {*|jQuery|HTMLElement}
       */
      getHtml: function (currentStatus, releaseFlg, newReleaseAt, closeFlg, newCloseAt, mustPublish) {

        var $contens = $(publish.setUpdate.html);

        switch (currentStatus) {
          case 'draft':
            $contens.find('.modal-publishing-edit').remove();
            break;
          case 'public':
            $contens.find('.modal-publishing-new').remove();
            break;
          default:
            break;
        }

        if (newReleaseAt) {
          $contens.find(':radio[name=radio-new_release_at]:last').attr('checked', 'checked');
          $contens.find('.modal-datetime:first').val(newReleaseAt);
        }
        else if (releaseFlg && currentStatus == 'public') {
          $contens.find(':radio[name=radio-new_release_at]:nth(1)').attr('checked', 'checked');
          $contens.find('.modal-datetime-area:first').addClass('is-hide');
        }
        else {
          $contens.find(':radio[name=radio-new_release_at]:first').attr('checked', 'checked');
          $contens.find('.modal-datetime-area:first').addClass('is-hide');
        }

        if (newCloseAt) {
          $contens.find(':radio[name=radio-new_close_at]:last').attr('checked', 'checked');
          $contens.find('.modal-datetime:last').val(newCloseAt);
        }
        else if (closeFlg) {
          $contens.find(':radio[name=radio-new_close_at]:nth(1)').attr('checked', 'checked');
          $contens.find('.modal-datetime-area:last').addClass('is-hide');
        }
        else {
          $contens.find(':radio[name=radio-new_close_at]:first').attr('checked', 'checked');
          $contens.find('.modal-datetime-area:last').addClass('is-hide');
        }

        // 全上げフラグ ON or 自動更新ページ -> 更新予約不可
        if ((all_upload_flg || mustPublish) && currentStatus == 'public') {
          //if (all_upload_flg && currentStatus == 'public' || isCreator && currentStatus == 'public') {
          $contens.find(':radio[name=radio-new_release_at]:last').parent().addClass('is-hide');
          //$contens.find(':radio[name=radio-new_close_at]:last').parent().addClass('is-hide');
          //$contens.find('.modal-datetime').addClass('is-hide');

        } else if (all_upload_flg && currentStatus == 'draft') {
          //} else if ((all_upload_flg || mustPublish) && currentStatus == 'draft') {
          //} else if (all_upload_flg && currentStatus == 'draft' || isCreator && currentStatus == 'draft') {
          $contens.find(':radio[name=radio-new_release_at]:last').parent().addClass('is-hide');
          //$contens.find(':radio[name=radio-new_close_at]:last').closest('dd').addClass('is-hide').prev().addClass('is-hide');
          // $contens.find('.modal-datetime').addClass('is-hide');
        }

        if (currentStatus == 'public') {
          return $contens;
        }

        // 下書き or 公開
        if (releaseFlg) {
          $contens.find(':radio[name=radio-update]:last').attr('checked', 'checked');
        }
        else {
          $contens.find(':radio[name=radio-update]:first').attr('checked', 'checked');
          $contens.find('.modal-datetime-setting').addClass('is-hide');
        }

        return $contens;
      },

      /**
       * 日付入力欄の表示/非表示
       * @param $self
       * @param val
       * @param target
       */
      toggleDatetime: function ($self, val, target) {

        if ($self.val() == val) {
          target.removeClass('is-hide');
          return;
        }
        target.addClass('is-hide');

      },

      /**
       * バリデーション
       * @param modal
       * @returns {boolean}
       */
      validateDraft: function (modal) {

        // 日付のバリデーション
        var validateFlg = true,
          msg;

        var datetime = modal.$el.find(':text:visible'),
          publishDate = 0,
          draftDate = 0;

        for (var i = 0; i < datetime.length; i++) {

          // 入力形式チェック
          var value = datetime.eq(i).val();
          if (!value.match(/[0-9]{4}年[0-9]{2}月[0-9]{2}日[0-9]{2}時/)) {
            validateFlg = false;
            msg = '<div class="errors">日付の形式を確認してください<br>（例）2015年03月28日00時</div>';
            datetime.eq(i).next().after(msg);
            continue;
          }

          // 日付、時刻の形式チェック
          var num = value.match(/\d/g).join(''),
            yyyy = num.substr(0, 4),
            mm = num.substr(4, 2),
            dd = num.substr(6, 2),
            hh = num.substr(8, 2);

          if (!publish.setUpdate.validDate(yyyy, mm, dd) || 24 < hh) {
            validateFlg = false;
            msg = '<div class="errors">日付の形式を確認してください<br>（例）2015年03月28日00時</div>';
            datetime.eq(i).next().after(msg);
            continue;
          }

          // 現在時刻以降かチェック
          if (Number(num) <= publish.setUpdate.getNowDatetime()) {
            validateFlg = false;
            msg = '<div class="errors">現在日時以降を設定してください</div>';
            datetime.eq(i).next().after(msg);
            continue;
          }

          // 公開開始日と停止日の比較用
          var name = datetime.eq(i).attr('name');
          if (name.match(/release/)) {
            publishDate = Number(num);
          }
          if (name.match(/close/)) {
            draftDate = Number(num);
          }
        }

        // 公開停止日 <= 開始日
        if (publishDate && draftDate && draftDate <= publishDate) {
          validateFlg = false;
          msg = '<div class="errors">公開開始日より前の日時を設定してください</div>';
          modal.$el.find('img.ui-datepicker-trigger:last').after(msg);
        }

        return validateFlg;
      },

      /**
       * 物件検索設定で物件リクエストを使用するにチェックが入っている場合、該当の物件リクエストを下書きにできないようにする
       * @param modal
       * @returns {boolean}
       */
      validateEstateRequest: function (modal) {
        var close = modal.$el.find(':radio[name=radio-new_close_at]:checked');
        var isInvalidEstateRequest = false;
        if (tableRowPageTitle.match(/物件リクエスト（居住用賃貸）/) && has_form_request_livinglease) {
          if (close.val() == 'now' || close.val() == 'reserve') {
            isInvalidEstateRequest = true;
          }
        }
        if (tableRowPageTitle.match(/物件リクエスト（事業用賃貸）/) && has_form_request_officelease) {
          if (close.val() == 'now' || close.val() == 'reserve') {
            isInvalidEstateRequest = true;
          }
        }
        if (tableRowPageTitle.match(/物件リクエスト（居住用売買）/) && has_form_request_livingbuy) {
          if (close.val() == 'now' || close.val() == 'reserve') {
            isInvalidEstateRequest = true;
          }
        }
        if (tableRowPageTitle.match(/物件リクエスト（事業用売買）/) && has_form_request_officebuy) {
          if (close.val() == 'now' || close.val() == 'reserve') {
            isInvalidEstateRequest = true;
          }
        }

        if (isInvalidEstateRequest) {
          msg = '<div class="errors">物件検索設定で「物件リクエストを利用する」にチェックが入っているため、<br>このページは「下書き」状態にできません</div>';
          modal.$el.find('.modal-publishing-edit').after(msg);
          return false;
        }
        return true;
      },

      validatePublic: function (modal) {
        var release = modal.$el.find(':radio[name=radio-new_release_at]:checked'),
          close = modal.$el.find(':radio[name=radio-new_close_at]:checked');

        if ((release.val() == 'now' && close.val() == 'now') || (release.val() == 'reserve' && close.val() == 'reserve' && release.closest('dd').find(':text').val() == close.parent().closest('dd').find(':text').val())) {
          msg = '<div class="errors">公開日と停止日が同じです</div>';
          modal.$el.find('dd:last').after(msg);
          return false;
        }
        if (!publish.setUpdate.validateEstateRequest(modal)) {
            return false;
        }
        return publish.setUpdate.validateDraft(modal);

      },

      /**
       * 日付形式のバリデーション
       * @param y
       * @param m
       * @param d
       * @returns {boolean}
       */
      validDate: function (y, m, d) {
        var dt = new Date(y, m - 1, d);
        return (dt.getFullYear() == y && dt.getMonth() == m - 1 && dt.getDate() == d);
      },

      /**
       * 現在時刻（時間まで）取得
       * @returns {number}
       */
      getNowDatetime: function () {

        var date = new Date();

        var y = String(date.getFullYear());
        var m = date.getMonth() + 1;
        var d = date.getDate();
        var h = date.getHours();
        if (m < 10) {
          m = '0' + String(m);
        }
        if (d < 10) {
          d = '0' + String(d);
        }
        if (h < 10) {
          h = '0' + String(h);
        }
        return Number(y + m + d + h);
      }


    },

    submit: {

      /**
       * submitの前処理
       * @param e
       */
      before: function (e) {

        var $clicked = $(e.currentTarget);

        // clickされたボタン判定
        var btn;
        if ($clicked.hasClass('submit-testsite')) {
          btn = 'testsite';
        }
        else if ($clicked.hasClass('submit-publish')) {
          btn = 'publish';
        }
        else if ($clicked.hasClass('submit-allupload')) {
          btn = 'allupload';
        }
        else if ($clicked.hasClass('submit-publish-subsutitute')) {
          btn = 'publish-subsutitute';
        }
        else if ($clicked.hasClass('submit-publish-testsite-now')) {
          btn = 'publish-testsite-now';
        }
        else if ($clicked.hasClass('submit-publish-testsite-reserve')) {
          btn = 'publish-testsite-reserve';
        }
        else if ($clicked.hasClass('submit-allupload-testsite')) {
          btn = 'publish-allupload-testsite';
        }
        else if ($clicked.hasClass('submit-allupload-subsutitute')) {
          btn = 'publish-allupload-subsutitute';
        }
        else {
          return;
        }

        // 押されたボタンを判定用
        $('#btn_hidden').remove();
        $('<input>').attr('type', 'hidden')
          .attr('name', 'clickBtn')
          .attr('value', btn)
          .attr('id', 'btn_hidden')
          .appendTo($clicked);

        // table.$rows.find(':checkbox:hidden').prop('checked', false);
      },

      /**
       * submit
       *
       * @param $form
       * @param $trigger
       * @param onSuccess
       */
      apiForm: function ($form, $trigger, onSuccess) {

        var url = $form.attr('data-api-action');

        $form.on('submit', function (e) {
          e.preventDefault();

          if ($form.hasClass('is-loading')) {
            return false;
          }
          $form.addClass('is-loading');

          if ($trigger) {
            $trigger.addClass('is-disable');
          }

          app.api(url, $form.serialize(), function (res) {
              $('.is-error').removeClass('is-error');
              $('.errors').empty();
              if (res.errors) {
                $.each(res.errors, function (id, errors) {
                  var target = $('#' + id).addClass('is-error');
                  // 不動産お役立ち情報へのリンクの場合、リンク先にエラーメッセージを出し、チェックボックスを無効化
                  if (target.closest('.no-display-article').length != 0) {
                    target = $('#page_article_update').addClass('is-error');
                    target.prop('checked', false).change();
                    target.prop('disabled', true);
                  }          
                  var $container = target.parent();
                  var $errors = $('.errors.error-' + id);

                  if (!$errors.length) {
                    while ($container.length) {
                      $errors = $container.find('.errors');
                      if ($errors.length) {
                        break;
                      }
                      $container = $container.parent();
                    }
                  }
                  if ($errors.length) {
                    $errors = $errors.eq(0);
                    $.each(errors, function (i, error) {
                      // 物件リクエストのエラーはここには出さない
                      if(i != "mustPublishRequestContact") {

                        var ckboxId = null;
                        if(i.indexOf('pageid_') === 0) {
                          ckboxId = 'page_' + i.replace('pageid_', '') + '_update';
                          tablePublish.find('#' + ckboxId).closest('tr').find('.errors').append(error);
                        } else if(i.indexOf('specialid_') === 0) {
                          ckboxId = 'special_' + i.replace('specialid_', '') + '_update';
                          tablePublish.find('#' + ckboxId).closest('tr').find('.errors').append(error);
                        } else if (i.indexOf('_article') === 0) {
                            ckboxId = 'page_' + i.replace('parentNone_', '') + '_update';
                            tablePublish.find('#' + ckboxId).closest('tr').find('.errors').append(error);
                        } else {
                          // 同じエラーメッセージは出さない（不動産お役立ち情報対応）
                          if($errors[0].innerHTML.indexOf(error[0]) == -1) {
                            $errors.append($('<p/>').html(error[0]));
                          }
                        }
                      }
                      // 物件リクエストのエラーは上部を消して下部を出すようにする
                      else {
                        $(".request_top_error").closest('div').hide();
                        $(".request_bottom_error").show();
                      }
                    });
                  }
                });
                if ($form.find('.errors-article p').length) {
                    $form.find('.errors-article').closest('tr').find('.errors').empty();
                }

                app.modal.alert('エラー', '設定を確認してください。', function () {
                  var $errorInput = $('.is-error:not(:hidden)');
                  var $error = $('.errors p');
                  var $target;
                  if ($errorInput.length) {
                    $target = $errorInput.eq(0);
                  }
                  else if ($error.length) {
                    $target = $error.eq(0);
                  }
                  else {
                    return;
                  }

                  table.showAllPages();
                  app.scrollTo($target.offset().top - 50);
                });
              }
              else {
                if (onSuccess) {
                  onSuccess(res);
                }
                else {
                  app.modal.alert('', 'エラー');
                }
              }
            })
            .always(function () {
              $form.removeClass('is-loading');
              if ($trigger) {
                $trigger.removeClass('is-disable');
              }
            });
        });

        if ($trigger) {
          $trigger.on('click', function (e) {
            e.preventDefault();

            $trigger.addClass('is-disable');

            publish.submit.before(e);
            $form.submit();
          });
        }
      }
    },

    progress: {

      /**
       * プログレスバー
       */
      init: function () {

        var hpUrl,
          error_msg,
          currentPercent = '0';

        var $contens = $(publish.progress.html),
        // $progressBar = $contens.find('.progress-bar'),
        // $progressContainer = $contens.find('.progress-container'),
          $progressDone = $contens.find('.progress-done'),
        // $progressMax = $contens.find('.progress-max'),
          $progressCurrent = $contens.find('.progress-current');

        var modal = app.modal.popup({
          title: '公開処理中…',
          contents: $contens,
          //modalBodyInnerClass: 'align-top',
          closeButton: false
        });
        modal.$el.find('.modal-btns').remove();

        var $iframe = $('<iframe src="/publish/progress" class="progress-bar"></iframe>');
        $iframe.appendTo('body');

    	$(window).on( 'beforeunload', function ( event ){
    		event.preventDefault();
			var str = "公開処理が中断しますが、よろしいですか？\n" ;
			return event.returnValue  = str;
    	});
      
        modal.show();

        // update
        window.progressUpdate = function (res) {

          var text = res.text;
          if (!text) {
            text = '';
          }

          if (text == 'error') {
            modal.close();
            publish.progress.error(error_msg);
            return false;
          }
          else if (text.indexOf('[error_msg]') === 0) {

            var str = '[error_msg]';
            error_msg = text.substr(str.length);
          }
          else if (text.indexOf('http://') === 0) {
            hpUrl = text;
          }

          currentPercent = String(res.percent.toFixed(1));
          $progressCurrent.text(currentPercent);
          // $progressMax.text('100');
          $progressDone.css('width', currentPercent + '%');
        };

        // finish
        window.progressFinish = function () {

          modal.close();
          modal = app.modal.popup({
            title: '完了',
            contents: '公開/更新が完了しました',
            modalBodyInnerClass: 'align-top',
            closeButton: false,
            ok: 'サイトを確認する',
            cancel: 'ホームへ戻る'
          });

          modal.onClose = function (ret) {

            if (!ret) {
              $(location).attr('href', '/');
              return false;
            }

            window.open(hpUrl);
            return false;
          };

          $(window).off( 'beforeunload' )	;

          modal.show();

          $('.btn-t-blue:last').addClass('btn-t-blue-wide');
        };

        // オフラインチェック
        var olCheckTimer = setInterval(function () {
          if (!navigator.onLine) {
            clearInterval(olCheckTimer);
            publish.progress.error('オフラインになりました');
          }
        }, 3000);
      },

      /**
       * プログレスバー：エラー
       */
      error: function (msg) {

        app.modal.alert('エラー', 'システムエラー：' + msg, function () {
          $(location).attr('href', urlSimple);
          return false;
        });
      },

      /**
       * プログレスバー：HTML
       * @type {string}
       */
      html: '<div class="progress">' +
      '<div class="modal-message">' +
      '<strong>公開処理中です。しばらくお待ち下さい。</strong>' +
      '<div class="progress-count-wrap">' +
      '（<span class="progress-current">0</span>%）' +
      '</div>' +
      '</div>' +
      '<div class="progress-container">' +
      '<div class="progress-done"></div>' +
      '</div>' +
      '</div>'

    },

    /**
     * 削除ボタン押下時
     */
    siteDelete: {
      btn: $('.site-delete'),
      /**
       * プログレスバー：エラー
       */
      error: function (msg) {
        app.modal.alert('エラー', 'システムエラー：' + msg, function () {
          $(location).attr('href', urlSimple);
          return false;
        });
      },
      /**
       * プログレスバー：HTML
       * @type {string}
       */
      html: '<div class="progress">' +
      '<div class="modal-message">' +
      '<strong>削除中です。しばらくお待ち下さい。</strong>' +
      '<div class="progress-count-wrap">' +
      '（<span class="progress-current">0</span>%）' +
      '</div>' +
      '</div>' +
      '<div class="progress-container">' +
      '<div class="progress-done"></div>' +
      '</div>' +
      '</div>',
      show: function () {
        app.modal.confirm("非公開設定", '本番サイトを非公開にします。よろしいですか？\n※公開・停止の予約設定がされていた場合、設定をすべて解除します。', this.fandler).show();
      },
      fandler: function (let) {
        if (let == true) {
          var $contens = $(publish.siteDelete.html),
            $progressDone = $contens.find('.progress-done'),
            $progressCurrent = $contens.find('.progress-current');

          var modal = app.modal.popup({
            title: '公開情報削除中…',
            contents: $contens,
            closeButton: false
          });
          modal.$el.find('.modal-btns').remove();
          $('<iframe src="' + $('.site-delete').attr('data-api-action') + '?_token=' + $('#publish-form input[name=_token]').val() + '" class="progress-bar"></iframe>').appendTo('body');
          modal.show();

          // update
          window.progressUpdate = function (res) {
            var text = res.text;
            if (!text) text = '';

            if (text == 'error') {
              modal.close();
              publish.siteDelete.error(error_msg);
              return false;

            } else if (text.indexOf('[error_msg]') === 0) {

              var str = '[error_msg]';
              error_msg = text.substr(str.length);
            } else if (text.indexOf('http://') === 0) {
              hpUrl = text;
            }

            currentPercent = String(res.percent.toFixed(1));
            $progressCurrent.text(currentPercent);
            $progressDone.css('width', currentPercent + '%');
          };

          // finish
          window.progressFinish = function () {

            modal.close();
            modal = app.modal.popup({
              title: '完了',
              contents: '削除が完了いたしました。',
              modalBodyInnerClass: 'align-top',
              closeButton: false,
              ok: 'ホームへ戻る',
              cancel: ''
            });
            modal.onClose = function (ret) {
              $(location).attr('href', '/');
              return false;
            }
            modal.show();
            $('.btn-t-blue:last').addClass('btn-t-blue-wide');
          };
        }
      }
    },
    setArticleUpdate: {
        btn: $('.update-setting-article-btn'),
        show: function (self) {
            if($(self).hasClass("is-disable")) return;
            publish.setArticleUpdate.setData();
            var contents = publish.setArticleUpdate.setHTmlContents();
            var modal = app.modal.popup({
                title: '公開設定(詳細設定) - 不動産お役立ち情報',
                contents: contents,
                modalBodyInnerClass: 'align-top',
                cancel: false,
                ok: false
            });
            modal.show();

            var $form = modal.$el.find('#setting-article-form');

            var listCategoryArticle = modal.$el.find('.list-category-article');
            modal.$el.find('li.item-large:not(.not-used)').eq(0).addClass('active-a');
            modal.$el.on('click', 'li.item-large', function() {
                
                $(this).closest('ul').find('li').removeClass('active-a').removeClass('active-span');
                listCategoryArticle.find('tbody:not(.body-article-top)').toggleClass('is-hide', true);
                if ($(this).find('a').length) {
                    $(this).addClass('active-a');
                    var id = $(this).find('a').attr('data-id');
                    listCategoryArticle.find('.large-'+id).toggleClass('is-hide', false);
                } else {
                    $(this).addClass('active-span');
                    listCategoryArticle.find('.large-no-plan').toggleClass('is-hide', false);
                }
                var $table = $(this).closest('.content-publish').find('table');
                var $tbody = $table.find('tbody:not(.is-hide)');
                if ($tbody.find('input:checkbox').length) {
                    var $tr = $tbody.find('tr').eq(0);
                    table.toggleCheckAllBtn($tr);
                } else {
                    $table.find('.all-check').prop('checked', false);
                }
            });

            modal.$el.on('change', 'input[name="publish_option"]', function() {
                var val = $(this).val();
                switch (val) {
                    case 'release':
                        modal.$el.find('.new_release_flg').val('1');
                        modal.$el.find('.new_close_flg').val('0');
                        break;
                    case 'close':
                        modal.$el.find('.new_release_flg').val('0');
                        modal.$el.find('.new_close_flg').val('1');
                        break;
                    default:
                        break;
                }
            })

            modal.$el.on('click', '.btn-ok', function() {
                modal.$el.find('.modal-content-publish > .alert-strong').toggleClass('is-hide', true);
                modal.$el.find('.errors').empty();
                if (!publish.setArticleUpdate.setValueCheckbox(modal.$el)) {
                    return;
                }
                $form.submit();
            });

            modal.$el.on('click', '.btn-cancel', function() {
                modal.close();
            });

            publish.setArticleUpdate.initChecked(modal);

            publish.setArticleUpdate.reInitTable(modal.$el.find('.publish-modal tbody tr'), modal.$el.find('.publish-modal tr :checkbox'), true);
            
            publish.setArticleUpdate.apiForm($form, modal.$el, function(res) {
                var $row = $('.row-article');
                if (!$row.find('input:checkbox').prop('checked')) {
                    $row.addClass('bg');
                    $row.find('input:checkbox').prop('disabled', false).prop('checked', true);
                }
                $row.find('.errors-article').empty();
                $row.find('.errors').empty();
                saveData.option = modal.$el.find('input[name="publish_option"]:checked').val();
                saveData.pages = [];
                $form.find('.update_flg').each(function() {
                    var pageId = $(this).attr('id').replace('page_', '').replace('_update', '');
                    saveData.pages[pageId] = $(this).prop('checked');
                });
                for(var i in saveData.pages) {
                    var row = $('.publish-detail').find('#page_'+i+'_update');
                    if (!row.closest('tr').hasClass('row-article')) {
                        row.prop('checked', saveData.pages[i]).change();
                    }
                    $('.publish-detail').find('input[name="page['+i+'][update]"]').val(Number(saveData.pages[i]));
                    if (saveData.pages[i]) {
                        switch (saveData.option) {
                            case 'release':
                                $('.publish-detail').find('#page_'+i+'_new_release_flg').val('1');
                                $('.publish-detail').find('#page_'+i+'_new_close_flg').val('0');
                                break;
                            case 'close':
                                $('.publish-detail').find('#page_'+i+'_new_release_flg').val('0');
                                $('.publish-detail').find('#page_'+i+'_new_close_flg').val('1');
                                break;
                            default:
                                break;
                        }
                    } else {
                        $('.publish-detail').find('#page_'+i+'_new_release_flg').val('0');
                        $('.publish-detail').find('#page_'+i+'_new_close_flg').val('0');
                    }
                }
                modal.close();
            });
            modal.onClose = function() {
                publish.setArticleUpdate.reInitTable($('.publish-easy tbody tr, .publish-detail tbody tr'), $('.publish-easy tr :checkbox, .publish-detail tr :checkbox'), false);
                return;
            }
        },
        setHTmlContents: function() {
            var html = '';
            var contents = $('#template');
            var categoryLarge = contents.find('.list-large-catagory a');
            categoryLarge.each(function(i, element) {
                var id = parseInt($(this).attr('data-id'));
                if (id != 0) {
                    var page = data[id];
                    var isHide = i != 0 ? ' is-hide' : '';
                    var classTbody = 'class="large-'+id+isHide+'"';
                    html += '<tbody '+classTbody+'>';
                    html += '<tr class="large-page">';
                    html += '<td>'+publish.setArticleUpdate.getCheckboxItem(page)+'</td>';
                    html += '<td>'+publish.setArticleUpdate.getLabelCategory(page)+'</td>';
                    html += '<td>'+publish.setArticleUpdate.getPageName(page)+'</td>';
                    html += '<td class="alC">'+publish.setArticleUpdate.getNewEditName(page)+'</td>';
                    html += publish.setArticleUpdate.getStatusName(page);
                    html += '</tr>';
                    html += publish.setArticleUpdate.renderLinkArticle(page, false, 'large');
                    html += publish.setArticleUpdate.renderListChid(data, page['id'], false);
                    // html += '<tr class="errors-exist text-errors is-hide"><td colspan="5"></td></tr>'
                    html += '</tbody>';
                }
            });
            html += '<tbody class="large-0 is-hide"><tr><td colspan="5"><p>ページが作成されていません。</p></td></tr></tbody>';
            html += '<tbody class="large-no-plan is-hide"><tr ><td colspan="5"><p>プラン対象外です。</p></td></tr></tbody>';
            contents.find('.list-category-article tbody:not(.body-article-top)').remove();
            contents.find('.list-category-article thead').eq(1).before(html);
            return contents.html();

        },
        initChecked: function(modal) {
            var disableCloseOption = false;
            if (publish2all) {
                modal.$el.find('input[value="release"]').prop('checked', true).change();
                modal.$el.find('.update_flg').prop('checked', true).change();
            } else if (publicLoad) {
                modal.$el.find('input[value="release"]').prop('checked', true).change();
            }
            modal.$el.find('input[value="'+saveData.option+'"]').prop('checked', true).change();
            if (saveData.pages) {
                for(var i in saveData.pages) {
                    modal.$el.find('#page_'+i+'_update').prop('checked', saveData.pages[i]).change();
                }
            }
            if (publicLoad) {
                modal.$el.find('.status-public').each(function () { 
                    var tr = $(this).closest('tr');
                    tr.addClass('auto-publish');
                    tr.find('.update_flg').prop('checked', true).change();
                    disableCloseOption = true;
                })
            }

            modal.$el.find('input[name="publish_option"][value="close"]').prop('disabled', disableCloseOption)
            modal.$el.find('li.item-large').eq(0).trigger('click');
        },
        getPageByType: function(type) {
            $.each(pages, function(i, page) {
                if (page['page_type_code'] == type) {
                    return page;
                }
            })
            return null;
        },
        getPageById: function(id) {
            var result = null;
            pages.forEach(function(page) {
                if (page['id'] == id) {
                    result = page;
                }
            });
            return result;
        },
        renderListChid: function(pages, parentId, char) {
            var html = '';
            $.each(pages, function(i, page) {
                if (page['parent_page_id'] == parentId) {
                    html += '<tr class="'+ (char ? 'article-page' : 'small-page') +'">';
                    html += '<td>'+publish.setArticleUpdate.getCheckboxItem(page)+'</td>';
                    html += '<td>'+publish.setArticleUpdate.getLabelCategory(page)+'</td>';
                    html += '<td>'+publish.setArticleUpdate.getPageName(page, char)+'</td>';
                    html += '<td class="alC">'+publish.setArticleUpdate.getNewEditName(page)+'</td>';
                    html += publish.setArticleUpdate.getStatusName(page);
                    html += '</tr>';
                    html += publish.setArticleUpdate.renderLinkArticle(page, char, char ? 'article' : 'small');
                    html += publish.setArticleUpdate.renderListChid(pages, page['id'], true);
                }
            })
            return html;
        },
        renderLinkArticle: function(page, char, typeLink) {
            var html= '';
            $.each(pages, function(i, p) {
                if(page['link_id'] == p['link_page_id']) {
                    html += '<tr>';
                    html += '<td>'+publish.setArticleUpdate.getCheckboxItem(p)+'</td>';
                    html += '<td>'+publish.setArticleUpdate.getLabelCategory(p , typeLink)+'</td>';
                    html += '<td>'+publish.setArticleUpdate.getPageName(p, char)+'</td>';
                    html += '<td class="alC">'+publish.setArticleUpdate.getNewEditName(p)+'</td>';
                    html += publish.setArticleUpdate.getStatusName(p);
                    html += '</tr>';
                }
            });
            return html;
        },
        setDataIdMenuLargeCategory: function() {

        },
        setData: function() {
            for (var i in pages) {
                data[pages[i].id] = pages[i];
            }
        },
        getCheckboxItem: function(page) {
            var html = '';
            html += '<input type="checkbox" id="page_'+page['id']+'_update" class="update_flg" >';
            html += '<input type="hidden"  name="page['+page['id']+'][update]" class="update-hidden">';
            html += '<div class="hidden-params-area is-hide">';
            html += '<input type="hidden" class="new_release_flg" name="page['+page['id']+'][new_release_flg]" value="0">';
            html += '<input type="hidden" class="new_release_at" name="page['+page['id']+'][new_release_at]" value="0">';
            html += '<input type="hidden" class="new_close_flg" name="page['+page['id']+'][new_close_flg]" value="0">';
            html += '<input type="hidden" class="new_close_at" name="page['+page['id']+'][new_close_at]" value="0">';
            html += '</div>'
            return html;
        },
        getLabelCategory: function(page, typeLink) {
            var html = '';
            var largeLabel = '<div class="label-large-category"></div>';
            var smallLabel = '<div class="label-small-category"></div>';
            var articleLabel = '<div class="label-article-category"></div>';
            switch (parseInt(page['page_category_code'])) {
                case categories.CATEGORY_LARGE:
                    html = largeLabel;
                    break;
                case categories.CATEGORY_SMALL:
                    html = smallLabel;
                    break;
                case categories.CATEGORY_ARTICLE:
                    html = articleLabel;
                    break;
                default:
                    break;
            }
            if (typeof typeLink != 'undefined') {
                switch (typeLink) {
                    case 'large':
                        html = largeLabel;
                        break;
                    case 'small':
                        html = smallLabel;
                        break;
                    case 'article':
                        html = articleLabel;
                        break;
                    default:
                        break;
                }
            }
            return html;
        },
        getPageName: function (page, char) {
            var spanClass = '';
            if (typeof char != 'undefined' && char) {
                spanClass = "span-child";
            }
            var filename = page['filename'];
            if (page['page_category_code'] == 15) {
                filename = 'リンク';
                spanClass += " article-link"
            }
            return '<span class="'+spanClass+'">' + page['title'] + '（' + filename + '）' + '</span><div class="errors"></div>';
        },
        getStatusName: function(page) {
            var textStatus = page['public_flg'] ? '公開':'下書き';
            var classStatus = page['public_flg'] ? ' class="status-public"':' class="status-draf"';
            return '<td'+classStatus+'>'+textStatus+'</td>';
        },
        getNewEditName: function(page) {
            var icon = '';
            switch (page['label']) {
                case 'new':
                    icon = '<i class="i-l-new">新規</i>';
                    break;
                case 'update':
                    icon = '<i class="i-l-update">修正</i>';
                    break;
                default:
                    break;
            }
            return icon;
        },
        reInitTable: function(rows, checkbox, run) {
            table.$rows = rows;
            table.$checkbox = checkbox;
            if (run) {
                table.$rows.click(function (event) {

                    table.clickableRow(this, event);
                    table.rowBgColor();
                    table.toggleCheckAllBtn(this);
                });
                
                table.$checkbox.change(function (e) {
                    table.rowBgColor();
                    if (!$(e.target).hasClass('all-check')) {
                        table.toggleCheckAllBtn(this);
                    }
                });

                $('.all-check').change(function() {
                    $(this).closest('table').find('.all-check').prop('checked', $(this).prop('checked'));
                    table.toggleAllCheck(this);
                    table.rowBgColor();
                });
                table.$checkbox.click(function () {
                    var $this = $(this);
                    if ($this.closest('tr').hasClass('auto-publish')) {
                        $this.prop('checked', true);
                    }
                });
            }
        },
        setValueCheckbox: function(element) {
            var checked = table.$rows.find(':checkbox').filter(':checked');
            if (!checked.length) {
                app.modal.alert('エラー', 'ページを選択してください。', function () {
                    element.find('.modal-content-publish > .alert-strong').html('ページを選択してください。').toggleClass('is-hide', false);
                });
                return false;
            }
            var notChecked = table.$rows.find(':checkbox').not(checked);
            checked.each(function() {
                var rows = $(this).closest('tr');
                rows.find('.update-hidden').val('1');
            });
            notChecked.each(function() {
                var rows = $(this).closest('tr');
                rows.find('.update-hidden').val('0');
            });
            return true;
        },
        apiForm: function ($form, $element, onSuccess) {

            var url = $form.attr('data-api-action');
            var btn = $element.find('.btn-ok');
    
            $form.on('submit', function (e) {
              e.preventDefault();
              $form.addClass('is-loading');
              btn.toggleClass('is-disable', true);
              app.api(url, $('#publish-form').serialize() + "&" + $form.serialize(), function (res) {
                  btn.toggleClass('is-disable', false);
                  $element.find('.is-error').removeClass('is-error');
                  $element.find('.errors').empty();
                  $('#template .errors').empty();
                  if (res.errors) {
                    if (typeof res.errors.article_error != 'undefined') {
                        var list = res.errors.article_error.pages[0];
                        var pageChecks = res.errors.article_error.pages_check[0];
                        var renderPageName = function() {
                            var  ul = '<ul>';
                            var checks = [];
                            for(var i = 0; i <= list.length - 1; i++) {
                                if (!list[i]['checked']) {
                                    if (!($.inArray(list[i]['id'], checks) > -1)) {
                                        ul += '<li level="'+list[i]['level']+'">'+publish.setArticleUpdate.getPageName(data[list[i]['id']])+'</li>';
                                        checks.push(list[i]['id']);
                                    }
                                }
                            };
                            ul += '</ul>';
                            return ul;
                        }
                        var contents = '<div class="error-article js-scroll-container" data-scroll-container-max-height="550">'+
                                        '<p>'+res.errors.article_error.message[0]+'</p>'+
                                        renderPageName()+
                                    '</div>';
                        var modal = app.modal.popup({
                            title: false,
                            contents: contents,
                            modalBodyInnerClass: 'align-top',
                            closeButton: false

                        }).show();
                        modal.onClose = function(ret, modal) {
                            var idLarge = false;
                            for(var i=0; i< list.length; i++) {
                                if (!list[i]['checked']) {
                                    if (ret) {
                                        $form.find('#page_'+list[i]['id']+'_update').prop('checked', true).change();
                                    }
                                    if (!idLarge && !list[i]['checked']) {
                                        var tbody = $form.find('#page_'+list[i]['id']+'_update').closest('tbody');
                                        if (typeof tbody.attr('class') != 'undefined') {
                                            idLarge = parseInt(tbody.attr('class').replace('large-', '').replace(' is-hide', ''));
                                        } 
                                    }  
                                }
                            }
                            if (idLarge) {
                                $form.find('.list-large-catagory .item-large a[data-id="'+idLarge+'"]').closest('li').click();
                            }
                            if (!ret) {
                                var $class;
                                switch ($('input[name="publish_option"]:checked').val()) {
                                    case 'release':
                                        $class = 'draf';
                                        break;
                                    case 'close':
                                        $class = 'public';
                                        break;
                                }
                                for(var i=0; i< pageChecks.length; i++) {
                                    var tr = $form.find('#page_'+pageChecks[i]+'_update').closest('tr');
                                    var tdStatus = tr.find('.status-'+ $class);
                                    if (tdStatus.length > 0) {
                                        tr.find('.errors').html('<p>上層のページと下層のページは合わせて公開もしくは非公開（下書き）にしてください。<br>どちらかのページのみ公開もしくは非公開（下書き）にすることはできません。</p>');
                                    }
                                }
                            }
                            return;
                        }
                    } else {
                        $.each(res.errors, function (id, errors) {
                            var target = $form.find('#' + id).addClass('is-error');
                            var $container = target.closest('tr');
                            var $errors = $container.find('.errors');
                            if ($errors.length) {
                                $errors = $errors.eq(0);
                                
                                $.each(errors, function (i, error) {
                                    $errors.append($('<p/>').html(error));
                                });
                            }
                        });
                        app.modal.alertPublishArticle('エラー', '設定を確認してください。', function () {
                            var $errors = $form.find('.errors p');
                            if ($errors.length) {
                                $errors = $errors.eq(0);
                                var tbody = $errors.closest('tbody');
                                if (typeof tbody.attr('class') != "undefined") {
                                    var id = tbody.attr('class').replace('large-', '').replace(' is-hide', '');
                                    $form.find('.item-large a[data-id*="'+id+'"]').closest('.item-large').trigger('click');
                                }
                            }
                        });
                    }
                    
                  } else {
                    if (onSuccess) {
                        onSuccess(res);
                      }
                  }
                })
            });
        }
    }
  };
  /*
   siteDelete: {
   btn: $('.site-delete'),
   show: function () {
   app.modal.confirm("非公開設定",'本番サイトを非公開にします。よろしいですか？',this.fandler).show();
   },
   fandler : function (let) {
   if(let == true) {
   var alert = app.modal.alert('', '削除中です。しばらくお待ち下さい。');
   alert.$el.find('.modal-btns').remove();

   app.api($('.site-delete').attr('data-api-action'), $('#publish-form').serialize(), function (res) {
   alert.close();
   if (res.errors) {
   app.modal.alert('エラー', 'システムエラー');
   } else {
   modal = app.modal.popup({
   title: '完了',
   contents: '削除が完了いたしました。',
   modalBodyInnerClass: 'align-top',
   closeButton: false,
   ok: 'ホームへ戻る',
   cancel: ''
   });
   modal.onClose = function (ret) {
   $(location).attr('href', '/');
   return false;
   };
   modal.show();
   }
   }).error(function() {
   alert.close();
   });
   }
   }
   }
   */
  var table = {

    $rows: $('.publish-easy tbody tr, .publish-detail tbody tr'),

    $checkbox: $('.publish-easy tr :checkbox, .publish-detail tr :checkbox'),
    // ,

    $diffTabBtn: $('.page-diff-tab'),

    $allTabBtn: $('.page-all-tab'),

    init: function () {

      var showAll = true;

      var showItem = table.$rows;
      for (var i = 0; i < showItem.length; i++) {
        if (!(showItem.eq(i).hasClass('no-diff'))) {
          showAll = false;
        }
      }

      if (showAll) {
        table.showAllPages();
      }

    },

    /**
     * 行クリック時にチェック
     * @param self
     * @param event
     */
    clickableRow: function (self, event) {

      var $eventTarget = $(event.target);

      var tag = $eventTarget.prop('tagName').toLowerCase();
      if (tag === 'input' || tag === 'a') {
        return;
      }

      if ($eventTarget.closest('tr').hasClass('auto-publish')) {
        return;
      }

      var checkbox = $(self).find(':checkbox.update_flg');
      if (!checkbox.prop('disabled')) {
        var isChecked = checkbox.prop('checked');
        checkbox.prop('checked', !isChecked);
      }

      //ボタンの制御
      var btn = $(self).find('.update-setting-btn, .update-setting-article-btn');
      if(btn.hasClass("is-disable")) {
         btn.removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");
      }else{
        btn.attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");
      }
      
      //table.rowBgColor();
    },

    $hasDiffMsg: $('.has-diff-msg').closest('div'),

    /**
     * すべてのページ一覧を表示
     */
    showAllPages: function () {

      table.$diffTabBtn.removeClass('is-active');
      table.$allTabBtn.addClass('is-active');
      $('.publish-detail tr').removeClass('is-hide');

      $(".all2publish").parent().show();

      table.$hasDiffMsg.addClass('is-hide');
      // var $alertArea = table.$hasDiffMsg.closest('.alert-strong');
      // $alertArea.hide();
    },

    /**
     * 差分ページ一覧を表示
     */
    showDiffPages: function () {

      table.$allTabBtn.removeClass('is-active');
      table.$diffTabBtn.addClass('is-active');
      $('.publish-detail tr.no-diff').addClass('is-hide');
      table.$hasDiffMsg.removeClass('is-hide');

      $(".all2publish").parent().hide();
      // var $alertArea = table.$hasDiffMsg;
      // $alertArea.show();
    },

    /**
     * 選択行の背景色
     */
    rowBgColor: function () {
      var checked = table.$rows.find(':checkbox').filter(':checked');
      var notChecked = table.$rows.find(':checkbox').not(checked);
      notChecked.closest('tr').removeClass(rowBgClass);
      checked.closest('tr').addClass(rowBgClass);
    },

    /**
     * すべてチェック
     * @param self
     */
    toggleAllCheck: function (self) {

      var $self, $table, selector, $checkbox, $mustPublish;

      selector = location.href.indexOf(urlSimple) != -1 ? ':visible' : '';

      $self = $(self);
      $table = $self.closest('table');
      $checkbox = $table.find('tbody:not(.is-hide) :checkbox:not(:disabled)' + selector);
      $mustPublish = $table.find('tbody:not(.is-hide) .auto-publish :checkbox');
      $checkbox.not($mustPublish).prop('checked', !!$self.prop('checked')).change();

      //ボタンの制御
      $('.update-setting-btn, .update-setting-article-btn').each(function(num, obj) {
        if($(obj).parents('tr').eq(0).find('[type=checkbox]').prop('checked')) {
           $(obj).removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");
        }else{
          $(obj).attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");
        }
      });
    },

    toggleCheckAllBtn: function (self) {

      var selector, $table, $checkbox, $checked;
      selector = location.href.indexOf(urlSimple) != -1 ? ':visible' : '';
      $table = $(self).closest('.tb-basic');
      $checkbox = $table.find('tbody:not(.is-hide) :checkbox' + selector);
      $checked = $table.find('tbody:not(.is-hide) :checkbox' + selector + ':checked');

      $table.find('thead :checkbox').prop('checked', $checkbox.length === $checked.length);
    },
    toggCheckArticle: function(self, event) {
        var $table, $tr, $checked, $row;
        if (typeof event != 'undefined') {
            var $eventTarget = $(event.target);
            $tr = $(self);
            var tag = $eventTarget.prop('tagName').toLowerCase();
            if (tag === 'input' || tag === 'a') {
                $tr = $(self).closest('tr');
            }
        } else {
            $tr = $('.row-article');
        }
        
        if ($tr.hasClass('row-article')) {
            $checked = $tr.find('.update_flg').prop('checked');
            $table = $tr.closest('.tb-basic');
            $row = $table.find('.no-display-article:not(.no-diff-simple)');
            if (location.href.indexOf(urlSimple) != -1) {
                if ($row.hasClass('not-uncheck')) {
                    $row.find('.td-check input').val(1);
                } else {
                    $row.find('.td-check input').val(Number($checked));
                }
                $row.find('.new_release_flg').val(1);
                $row.find('.new_release_at').val(0);
                $row.find('.new_close_flg').val(0);
                $row.find('.new_close_at').val(0);
            } else {
                if (!$checked) {
                    saveData = {
                        option: 'release',
                        pages: [],
                    };
                    publish2all = false;
                    $row.find('.td-check input').val(0);
                }
            }
        }
    }
  };

  table.$rows.click(function (event) {

    table.clickableRow(this, event);
    table.rowBgColor();
    table.toggleCheckAllBtn(this);
    table.toggCheckArticle(this, event);
  });

  table.$checkbox.change(function (e) {
    table.rowBgColor();
    if (!$(e.target).hasClass('all-check')) {
      table.toggleCheckAllBtn(this);
      table.toggCheckArticle(this, e);
    }
  });

  table.$checkbox.click(function () {
    var $this = $(this);
    if ($this.closest('tr').hasClass('auto-publish')) {
      $this.prop('checked', true);
    }
  });

  /**
   * 変更をクリック時にページ名を返却し、どの行がクリックされているかを把握す
   * @return string tableRowTitle ページ名
   */
  $('.update-setting-btn').click(function () {
    var closestTableRow = $(this).closest("tr");
    var tableRowTitle = closestTableRow.find("td").eq(1).text();
    tableRowPageTitle = tableRowTitle;
  });

  $('.all-check').change(function () {
    table.toggleAllCheck(this);
    table.rowBgColor();
  });

  $contents.find('.page-all-tab a').click(function () {
    table.showAllPages();
  });

  $contents.find('.all2publish').click(function() {
    var msg = '<div style="font-size:17px;text-align:left">公開・停止の予約はすべて解除されます。<br/>すべてのページを公開・更新する設定に変更しますか？<br/><br/><p style="font-size:85%">設定変更後は「本番サイトの公開/更新」または「テストサイトの更新処理に進む」へ進んでください。</p></div>';

    var modal = app.modal.popup({
      title: '確認',
      contents: msg,
      modalBodyInnerClass: 'align-top',
      ok: '設定を変更する'
    });
    modal.show();

    modal.onClose = function (ret) {
      if (!ret) {
        return;
      }

      publish2all = true;
      saveData.pages = [];
      $(tablePublish).find('.row-article .errors').empty();
      $('#template .errors').empty();
      // 全ページをチェック状態にするためにテーブルタイトル部のチェックボックス選択
      if($(tablePublish).find(".all-check").filter(":checked").length == 0) {
        $(tablePublish).find(".all-check").first().prop("checked",true).trigger("change");
      }

      // 即時公開にする
      $(tablePublish).find('input[type=checkbox]:checked').each(function() {
        // ヘッダーは無視する
        if($(this).parent().prop("tagName") == 'TH') return true;

        var $r = $(this).closest('tr');

        // 現在のステータス(公開 or 下書き)
        var $current = $r.find('.current-status');
        var currentStatus = "";
        if($current.hasClass('status-public')) {
          currentStatus = '公開';
        } else if($current.hasClass('status-draft')) {
          currentStatus = '下書き';
        } else {
          return true;
        }

        var hiddenStatus = $r.find(".hidden-params-area").eq(0);
        switch(currentStatus) {
          case '下書き':
            newStatus = "公開";

            // 下書きにいは公開終了が無いはずだが念のためリセット 
            $(hiddenStatus).find(".new_close_flg").eq(0).val(0);
            $(hiddenStatus).find(".new_close_at").eq(0).val(0);
            break;
          case '公開':
            newStatus = "公開（更新）";

            // 公開終了予約も削除する
            $(hiddenStatus).find(".new_close_flg").eq(0).val(0);
            $(hiddenStatus).find(".new_close_at").eq(0).val(0);
            break;
          default:
            return true;
            break;
        }
        // 公開設定&公開予約日削除
        $(hiddenStatus).find(".new_release_flg").eq(0).val(1);
        $(hiddenStatus).find(".new_release_at").eq(0).val(0);

        $(hiddenStatus).closest('td').find("span").remove();
        $(hiddenStatus).before('<span>' + newStatus + '<span>');

        // 現在のステータスが下書き時は次ページ
        if(currentStatus == '下書き') return true;

        // 更新後ステータスが公開停止でないなら次ページ 
        if($(hiddenStatus).find(".new_close_flg").eq(0).val() != 1) {
          // 一応リセット
          $(hiddenStatus).find(".new_close_flg").eq(0).val(0);
          $(hiddenStatus).find(".new_close_at").eq(0).val(0);
          return true;
        }

        // 更新後ステータスが公開停止の場合
        // -即時停止 -> 停止関連リセット
        // -停止予約 -> 停止予約は残す？
        if($(hiddenStatus).find(".new_close_at").eq(0).val() == "" || $(hiddenStatus).find(".new_close_at").eq(0).val() == 0) {
          // 一応リセット
          $(hiddenStatus).find(".new_close_flg").eq(0).val(0);
          $(hiddenStatus).find(".new_close_at").eq(0).val(0);
          return true;
        }

        // 更新ステータスに公開予約も表示(終了予約フラグ&時間は保持)
        $(hiddenStatus).after('<span class="watch">公開終了<span>' + $(hiddenStatus).find(".new_close_at").eq(0).val() + '</span><span>');
      });
      $(tablePublish).find('.no-display-article').each(function() {
        $(this).find('.update_flg').val('1');
        $(this).find(".new_release_flg").eq(0).val("1");
        $(this).find(".new_release_at").eq(0).val("");
        $(this).find(".new_close_flg").eq(0).val("");
        $(this).find(".new_close_at").eq(0).val(0);
      });
    }
  });

  $contents.find('.page-diff-tab a').click(function () {
    table.showDiffPages();
  });

  $contents.find('.publish h1 a').click(function () {

    var msg = 'ページの設定は引き継がれません。移動してよろしいですか？';
    app.modal.confirm('注意', msg, function (res) {

      if (!res) {
        return;
      }

      var url = location.href;

      if (url.indexOf(urlDetail) != -1) {
        $(location).attr('href', urlSimple);
      }
      if (url.indexOf(urlSimple) != -1) {
        $(location).attr('href', urlDetail);
      }
      return false;
    })

  });

  /**
   * テーブル並び替え
   */
  tablePublish.tablesorter({
    headers: {
      0: {sorter: false},
      5: {sorter: false}
    }
  });

  /**
   * 並び替え後の処理
   */
  /*
   $('#table-publish').bind('sortEnd', function () {

   });
   */

  /**
   * 公開設定モーダル
   */
  publish.setUpdate.btn.click(function () {
    publish.setUpdate.show(this);
  });

  /**
   * submit
   */
  publish.submit.apiForm($('#publish-form'), publish.setUpdate.submit, function (res) {

    // preview
    if (res.url) {
      $(location).attr('href', res.url);
      return;
    }

    // publish
    if (res.publish) {

      var msg = '設定した内容で公開/更新します。よろしいですか？\n公開処理中にブラウザを閉じると公開処理が停止し、再度公開処理が必要になります'	;
      app.modal.confirm('確認', msg, function (res) {

        if (!res) {
          return;
        }
        publish.progress.init();
      })
    }
  });

  /**
   * 削除ボタン押下
   */
  publish.siteDelete.btn.click(function () {
    publish.siteDelete.show();
  });

  /**
   * 削除ボタン押下
   */
  publish.setArticleUpdate.btn.click(function () {
    publish.setArticleUpdate.show(this);
  });


  //publish.setUpdate.submit.click(function (e) {
  //  publish.submit.before(e);
  //  $('#publish-form').submit();
  //});

  init.onLoad();
})

