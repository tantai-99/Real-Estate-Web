$(function () {

	if(app === undefined) var app= {page:{}};
	app.page._changed = false;

	$(window).on('beforeunload', function (e) {
		if (app.page._changed) {
			var str = "編集中の内容は保存されませんが、よろしいですか？\n";

			//initializeかpageで出しわける
			if(window.location.href.indexOf("initialize") != -1) {
				str += "保存する場合は、「保存して次へ」を押下してください。\n";
			}else{
				str += "保存する場合は、「保存」を押下してください。\n";
			}
			return e.returnValue  = str;
		}
	});

	$(function () {
		$('body').on('change', 'input,textarea,select', function (e) {
			if (e.originalEvent || e.target.id.indexOf('image_title')!=-1
             || e.target.id.indexOf('tdk-date')!=-1 || e.target.id.indexOf('file2_title')!=-1) {
				app.page._changed = true;
			}
		});
	});


	//logout時
	$("#logout").on('click', function (event) {

		if (app.page._changed) {
			event.preventDefault();
			var str = "ログアウトします。よろしいですか？\n\n編集中の内容は保存されません。\n";
			//initializeかpageで出しわける
			if(window.location.href.indexOf("initialize") != -1) {
				str += "保存する場合は、「保存して次へ」を押下してください。\n";
			}else{
				str += "保存する場合は、「保存」を押下してください。\n";
			}
			if(confirm(str)) {
				$(window).off('beforeunload');
				location.href = $(this).attr("href");
			}
			return false;
		}
	});

	//IE<11のみaタグでbeforeunloadが効いてしまうので対応
	$("a").on('click', function (e) {
		//ログアウトは対応しない
		if($(this).attr("id") == "logout") return;
		//別画面に行く場合は対応しない
		if($(this).attr("target") && $(this).attr("target").indexOf("blank") != false) return;
		if($(this).attr("href") === undefined) return;
		if($(this).attr("href").indexOf("#") != -1) return;

		if ($(this).attr("href") && $(this).attr("href").indexOf("javascript") === 0) {
            // ATHOME_HP_DEV-5976 【CMS】【ページの作成/更新（不動産お役立ち情報）】【表示系】ページ保存をし別画面に遷移するとき「別画面へ移動しますか」モーダルが表示される
			// var _changed = app.page._changed;
		    // app.page._changed = false;
		    // setTimeout(function() { app.page._changed = _changed; }, 150);

		}else{

			if (app.page._changed) {
				var str = "別画面へ移動します。よろしいですか？\n\n編集中の内容は保存されません。\n";
				//initializeかpageで出しわける
				if(window.location.href.indexOf("initialize") != -1) {
					str += "保存する場合は、「保存して次へ」を押下してください。\n";
				}else{
					str += "保存する場合は、「保存」を押下してください。\n";
				}
				if(confirm(str)) {
					$(window).off('beforeunload');
					location.href = $(this).attr("href");
				}
				return false;
			}
		}
	});

	var $mainContainer = $('.main-contents-body');
	$mainContainer.on('click', '.select-element a', function () {
		var _changed = app.page._changed;
	    app.page._changed = false;
	    setTimeout(function() { app.page._changed = _changed; }, 150);
	});


//	$(".select-element .select-element-body .btn-area a").on('click', function (event) {
//		app.page._changed = true;
//	});

	//サブミット時は何もしない
	$("form").submit(function(){
		app.page._changed = false;
	});

});
