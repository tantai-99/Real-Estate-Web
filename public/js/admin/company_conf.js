$(document).ready( function(){

	// 登録＆コピーボタン押下時
	$("#regist_copy").click( function() {
		adminApp.modal.popup({
			"title"		: "デモ会員のコピーを開始します。よろしいですか？",
			"onClose"	: registCopyHandler
		}).show() ;
	});

	// NHP-5612 公開処理中の場合は更新できないようにする
	$(".company-regist").click(function() {
		var isUpdateCompanyInformation = true;
		postPublishStatus().done(function(result) {
			var result = JSON.stringify(result);
			result = JSON.parse(result);
			if(!result.LOCK_RES) {
				isUpdateCompanyInformation = false;
				adminApp.modal.alert('エラー', '公開処理実行中のため会員情報を更新できません。')
			}
		});
		if (!isUpdateCompanyInformation) {
			return false;
		}
	});

	function postPublishStatus() {
		return $.ajax({
			type: 'POST',
			url: '/admin/company/publish-lock',
			data: { company_id: $('input[name="basic[id]"]').val() },
			dataType: 'json',
			async : false
		})
	}
	// 登録＆コピーする
	function registCopyHandler( let ) {
        var modal = app.modal.popup({
            title		: 'コピー中'			,
            contents	: '暫くお待ち下さい'	,
            closeButton	: false
        });
		if( let == true ) {
	        modal.$el.find('.modal-btns').remove() ;
	        modal.show() ;
	        var target = document.getElementById("form01") ;
	        target.method = "post"	;
	        target.submit()	;
		}
	}

    // finish
    window.progressFinish = function () {
      modal.close();
    };
});
