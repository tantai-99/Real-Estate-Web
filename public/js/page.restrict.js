
var $before_form = "";	// 編集前のフォームの値(トップページOnly)

$(function(){
	/** 
     * TOPページ対応
     */
	if(typeof is_top_page != 'undefined' && is_top_page == '1') {
		if(typeof has_reserve != 'undefined' && has_reserve == '1') {
			// 予約あり時は、サイドを編集不可にする
			$("#section-side").css('pointer-events', 'none');

			// 各要素を編集不可にする
			$("#section-side").find("select").addClass('is-disable');

			$("#section-side").find("a").addClass('is-disable');
			$("#section-side").find("a").attr('disabled', true);

			$("#section-side").find("input").addClass('is-disable');
			$("#section-side").find("input").attr('readonly', true);

			$("#section-side").on('click change', ':checkbox[readonly]',function(e){
				e.preventDefault();
				return false;
			});

			$("#section-side").find("textarea").addClass('is-disable');
			$("#section-side").find("textarea").attr('readonly', true);

			// CKEditorを readonlyで起動するために、textareaをdisableに
			$("#section-side").find("textarea").attr('disabled', true);

			// textareaがdisabledだと送信されないので、3秒後にdisabledを外す
			setTimeout(function() {
				$("#section-side").find("textarea").attr('disabled', false);
			}, 3000);

			$("#section-side").find("button").addClass('is-disable');

			// CKEエディタのボタン
			$("#section-side").find("span").off('click');
			
			hideMsgDiv = $('<div class="hide-msg-div"></div>');
			$(hideMsgDiv).css('width', '590px');
			$(hideMsgDiv).css('height', $("#section-side").height());
			$(hideMsgDiv).css('min-height', '200px');
			$(hideMsgDiv).css('position', 'absolute');
			$(hideMsgDiv).css('top', $("#section-side").find('h2').eq(0).outerHeight());
			$(hideMsgDiv).css('left', 0);
			$(hideMsgDiv).css('z-index', 100);
			$(hideMsgDiv).css('font-size', '140%');
			$(hideMsgDiv).css('color', '#fff');
			$(hideMsgDiv).css('background', 'rgba(51,51,51,0.8)');
			$(hideMsgDiv).css('padding', '60px 20px');
			$(hideMsgDiv).css('text-align', 'center');
			$(hideMsgDiv).append('「サイトの公開/更新」画面にて予約設定がされています。<br/>設定解除後に編集を行うことができます。');
			$("#section-side").append(hideMsgDiv);
		} else {
			// 予約なし時は、現在のフォームのcloneを取得
			$before_form = $("form").clone(true);
            $($before_form).find('.search-house-method').each(function() {
                $(this).find('.search-method').eq(0).prop('checked', true)
            });
            $($before_form).find('.use-image-link').each(function() {
                if (!$(this).prop('checked')) {
                    $(this).closest('.input-img-link').find('.ml-link-target-blank').prop('checked', true);
                }
            })
		}
	}
});