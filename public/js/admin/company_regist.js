$(document).ready(function(){
	//ロック解除にフォーカスさせない
	$(window).keyup(function(e){
		if($(':focus').attr('id') == "lock" ) {
			if(e.shiftKey == false && e.keyCode == 9){	
				$("#back").focus();
				return false;
			}else if(e.shiftKey == true  && e.keyCode == 9){
				$("#other-remarks").focus();
				return false;
			}
		}
	});

	//ボタン制御
	$('button').click(function() {

		var msg = "上書きしてよろしいですか？";

		switch($(this).attr('id')) {
			case "memberno_copy" :
			case "password_copy" :
			case "ftp_directory_copy" :

				var copy_key = "";
				if($(this).closest("td").children("input[type='text']").attr('id') == "cms-login_id" ) {
					copy_key = "basic-member_no";
				}else if($(this).closest("td").children("input[type='text']").attr('id') == "cms-password" ) {
					copy_key = "cp-cp_password";
				}else if($(this).closest("td").children("input[type='text']").attr('id') == "ftp-ftp_directory" ) {
					copy_key = "basic-domain";
				}

				if(($(this).closest("td").children("input").val() != "" && window.confirm(msg)) || $(this).closest("td").children("input").val() == "") {
					$(this).closest("td").children("input").val($('#' + copy_key).val());
				}
				break;

			case "password_create" :
				if(($(this).closest("td").children("input").val() != "" && window.confirm(msg)) || $(this).closest("td").children("input").val() == "") {
					createPasswordForRandm("cms-password");
				}
				break;
		}
    });

	//データピッカー
    var format = '';
    if (window.location.href.indexOf("map-option") != -1) {
        format = 'yy-mm-dd';
    } else {
        format = 'yy/mm/dd';
    }
	$('.datepicker').datepicker({
		dateFormat: format,
//		showOn: 'both',
		showOn: 'button',
		buttonImage: '/images/common/icon_date.png',
		buttonImageOnly: true,
		buttonText: 'Select date'
    });

	//初期設定系を行う
	//会員Noの参照ボタンを押させるために契約店名は入力出来ないようにする
	$("#basic-member_name").attr("disabled", "disabled").addClass("is-disable").addClass("is-lock");
	$("#basic-member_linkno").attr("disabled", "disabled").addClass("is-disable").addClass("is-lock");
	// 契約担当者名＆契約担当者部署もAPI経由で登録のため入力させない
	$("#reserve-reserve_contract_staff_name"		).attr("disabled", "disabled").addClass("is-disable").addClass("is-lock")	;
	$("#reserve-reserve_contract_staff_department"	).attr("disabled", "disabled").addClass("is-disable").addClass("is-lock")	;
	$("#map-map_contract_staff_name"		).attr("disabled", "disabled").addClass("is-disable").addClass("is-lock")	;
	$("#map-map_contract_staff_department"	).attr("disabled", "disabled").addClass("is-disable").addClass("is-lock")	;
	// 解約担当者名＆解約担当者部署もAPI経由で登録のため入力させない
	$("#cancel-cancel_staff_name"			).attr("disabled", "disabled").addClass("is-lock")	;
	$("#cancel-cancel_staff_department"		).attr("disabled", "disabled").addClass("is-lock")	;
	$("#map-map_cancel_staff_name"			).attr("disabled", "disabled").addClass("is-lock")	;
	$("#map-map_cancel_staff_department"	).attr("disabled", "disabled").addClass("is-lock")	;

	//初期はロック
	$("#cp-cp_url").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_server_name").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_server_port").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_password").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_password").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_pasv_flg-0").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_pasv_flg-1").attr("disabled", "disabled").addClass("is-lock");

	
	/**
	 * 会員Noを使用して、会員情報を取得する
	 */
	searchMemberNo();

	/**
	 * ロックを解除する
	 */
	$("#lock").click(function() {
		if($('input[name="basic[contract_type]"]:checked').val() != 2) {
			$("#cp-cp_url").removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");;
			$("#ftp-ftp_server_name").removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");;
			$("#ftp-ftp_server_port").removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");;
			$("#ftp-ftp_password").removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");;
			$("#ftp-ftp_pasv_flg-0").removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");;
			$("#ftp-ftp_pasv_flg-1").removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");;
		}
	});

	/**
	 * 会員Noを使用して、会員情報を取得する
	 */
	$("#search_member_no").click(function() {

		if($("#basic-member_no").val() == "") {
			alert("会員Noを入力してください。");
			return;
		}

		searchMemberNo("click_search_member_no");
	});

	/**
	 * 利用ドメイン
	 */
	$("#basic-domain").keyup( function() {
		if( document.getElementById( "basic-domain" ).value.match(/.+\..+/) == null ) {
			//ボタンを押せないようにする
			$("#make_domain").attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");
		}else{
			//ボタンを押せるようにする
			if( $('input[name="basic[contract_type]"]:checked').val() == 1 ) {
				$("#make_domain").removeAttr("disabled").removeClass("is-disable");
			}
		}
	});
	$("#basic-domain").focusin( function() {
		if( $('input[name="basic[contract_type]"]:checked').val() == 1 ) {
			if( $("#basic-domain").val() == "" ) {
				$("#basic-domain").val( $('input[name="ftp[demo_domain]"]').val() )	;
			}
		}
	});

	/**
	 * デモ用ドメイン（サイト）作成する
	 */
	$("#make_domain").click( function() {
		var useDomain	= document.getElementById( "basic-domain"		).value		;
		var pass		= document.getElementById( "ftp-ftp_password"	).value		;
		var domain		= useDomain.substr( useDomain.indexOf( '.' ) + 1 )			;
		var user		= useDomain.substr( 0, useDomain.indexOf( '.' ) )			;
		var uri			= '/admin/api/make-demo-add-user?domain=' + domain + '&user=' + user + '&pass=' + pass ;
		const request	= new XMLHttpRequest();
		request.responseType	= 'json'	;
		request.open( "GET", uri ) ;
		request.addEventListener( "load", function(event) {
			console.log( event.target.status	)	;
			var domain		= event.target.response.data.result.domain		;
			var dir			= event.target.response.data.result.dir			;
			var user		= event.target.response.data.result.user		;
			var result		= event.target.response.data.result.result		;
			if ( result == 'already' ) {
		   		alert( "既に作成されています" ) ;
			} else {
				document.getElementById( "ftp-ftp_server_name"	).value = 'ftp.' + domain		;
				document.getElementById( "ftp-ftp_user_id"		).value = user					;
				document.getElementById( "ftp-ftp_directory"	).value = dir					;
		   		alert( "作成されました" ) ;
			}
		});
		request.addEventListener( "error", function() {
			alert( "デモ用サーバーとの通信に失敗しました!" ) ;
		});
		request.send()	;
		return			;
	});

	//会員Noの入力内容が変わったら会員名を消しちゃう
	$('#basic-member_no').change(function() {
		$("#basic-member_name").val("");
		$("#basic-member_linkno").val("");
	});

	/**
	 * 担当者IDを使用して、担当者情報を取得する
	 */

	$(".search_staff").click(function() {

		var search_name = $(this).val();
		if($( "#"+ search_name + "_id" ).val() == "" ) {
			alert("担当者IDを入力してください。");
			return;
		}

		var staff_id = $( "#"+ search_name + "_id" ).val() ;

		var obj = {
			'cd' : staff_id
		}
		var param = $.param(obj);

		$.ajax({
			type: "POST",
			dataType: "json",
			cache: false,
			url: $("#staff_api_url").val(),
			data: param,
			success: function(data){

				//会員名の設定
				if(data != "" && Object.keys(data.data).length != 0 ) {
					$( "#"+ search_name + "_name"       ).val( data.data.tantoName   ) ;
					$( "#"+ search_name + "_department" ).val( data.data.shozokuName ) ;
					$("#search_"+ search_name).parent("td").children("p").text("");

				}else{
					app.modal.alert("エラー", "担当者が存在しません。担当者ＩＤをお確かめください。");
				}

			},
			error: function(jqXHR, textStatus, errorThrown){
				app.modal.alert("エラー", "担当者APIに接続できません。しばらく経ってから再度お試しください。");
			},
		});
	});

	// 現在の契約担当の入力内容が変わったら担当者名を消しちゃう
	$('#status-contract_staff_id').change( function() {
		$("#status-contract_staff_name"      ).val( "" ) ;
		$("#status-contract_staff_department").val( "" ) ;
	});

	// 予約契約担当の入力内容が変わったら担当者名を消しちゃう
	$('#reserve-reserve_contract_staff_id').change( function() {
		$("#reserve-reserve_contract_staff_name"      ).val( "" ) ;
		$("#reserve-reserve_contract_staff_department").val( "" ) ;
	});

	//解約担当の入力内容が変わったら担当者名を消しちゃう
	$('#cancel-cancel_staff_id').change( function() {
		$("#cancel-cancel_staff_name"      ).val( "" ) ;
		$("#cancel-cancel_staff_department").val( "" ) ;
	});

	// 地図オプション契約担当の入力内容が変わったら担当者名を消しちゃう
	$('#map-map_contract_staff_id').change( function() {
		$("#map-map_contract_staff_name"      ).val( "" ) ;
		$("#map-map_contract_staff_department").val( "" ) ;
	});

	// 地図オプション解約担当の入力内容が変わったら担当者名を消しちゃう
	$('#map-map_cancel_staff_id').change( function() {
		$("#map-map_cancel_staff_name"      ).val( "" ) ;
		$("#map-map_cancel_staff_department").val( "" ) ;
	});

	/**
	 * 契約情報が変更されるたびにいろいろする
	 */
	var pre_contract_type = '';
	$('input[name="basic[contract_type]"]:radio').change(function() {
		changelEments($(this).val());
	});

	/**
	 * 入力系の制御
	 */
	changelEments($('input[name="basic[contract_type]"]:checked').val());


	/**
	 * 会員Noを使用して、会員情報を取得する
	 */
	function searchMemberNo(type) {

		if($("#basic-member_no").val() == "") {
			return;
		}

		var obj = {
			'no' : $("#basic-member_no").val()
		}
		var param = $.param(obj);

		$.ajax({
			type: "POST",
			dataType: "json",
			cache: false,
			url: $("#member_api_url").val(),
			data: param,
			success: function(data){

                if(data != "" && Object.keys(data.data).length != 0 ) {
					//会員名の設定
					$("#basic-member_name").val(data.data.seikiShogoName);
					$("#basic-member_linkno").val(data.data.kaiinLinkNo);

					//住所の設定
					address = data.data.location;

					$("#basic-location").val(address);

					// 会員番号参照ボタンを押下した場合
					if( type == "click_search_member_no"){
						//インターネットコードの設定
						if(!data.data.kaiinLinkNo){
							app.modal.alert("エラー", "インターネットコードが設定されていません。");
						}
						// エラーをクリア
						$("#search_member_no").parent("td").children("p").text("")

					}
				}else{
					// 会員番号参照ボタンを押下した場合
					if( type == "click_search_member_no"){
						app.modal.alert("エラー", "会員情報が存在しません。会員Noをお確かめください。");
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				app.modal.alert("エラー", "会員情報APIに接続できません。しばらく経ってから再度お試しください。");
			},
		});
	}



	function changelEments(key) {

		//デモだったら
		if(key == 1) {
			//会社名を入力可
			$("#basic-member_name").removeAttr("disabled").removeClass("is-lock is-disable").css("margin-top", '5px');
			$("#basic-location").val("東京都千代田区内幸町1-3-2 内幸町東急ビル");
			//ボタンを押せないようにする
			$("#search_member_no").attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");
			//ボタンを表示
			$("#make_domain").show() ;

		//デモ以外
		}else{
			//会社名を入力不可
			$("#basic-member_name").attr("disabled", "disabled").addClass("is-lock is-disable").css("margin-top", '');
			//ボタンを押せるようにする
			$("#search_member_no").removeAttr("disabled").removeClass("is-disable");
			//ボタンを非表示
			$("#make_domain").hide() ;

 			if(pre_contract_type == 1) {
				$("#basic-member_name").val("");
				$("#basic-location").val("");
			}
		}

		//現状の契約を取っておく
		pre_contract_type = key;

		//設定用情報
		var param_names = [
				"basic-company_name",
				"basic-domain",
				"cp-cp_url",
				"cp-cp_user_id",
				"cp-cp_password",
				"ftp-ftp_server_name",
				"ftp-ftp_server_port",
				"ftp-ftp_user_id",
				"ftp-ftp_password",
				"ftp-ftp_directory",
				"ftp_directory_copy",
				"switch",
				"ftp-ftp_pasv_flg-0",
				"ftp-ftp_pasv_flg-1"
		];

		//disabliedしない項目
		var non_disablied_name = [
				"reserve-reserve_cms_plan"	,
				"cp-cp_url",
				"ftp-ftp_server_name",
				"ftp-ftp_server_port",
				"ftp-ftp_password",
				"switch",
				"ftp-ftp_pasv_flg-0",
				"ftp-ftp_pasv_flg-1"
		];

		if(key == 1) {
			$("#search_member_no").attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");
		}else{
			$("#search_member_no").removeAttr("disabled").removeClass("is-disable");
		}

		for(var name in param_names) {
			if(key == 2) {
				$("#"+ param_names[name]).attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");
				$("#"+ param_names[name]).parents("tr").removeClass("is-require");
				if(param_names[name] == "ftp_directory_copy") {
					$("#"+ param_names[name]).css("visibility", "hidden");
				}else if(param_names[name] == "search_contract_staff") {
					$("#"+ param_names[name]).css("visibility", "hidden");
				}else if(param_names[name] == "switch") {
					$("#"+ param_names[name]).css("visibility", "hidden");
				}
			}else{

				if($.inArray(param_names[name], non_disablied_name) < 0) $("#"+ param_names[name]).removeAttr("disabled").removeClass("is-lock").removeClass("is-disable");
				$("#"+ param_names[name]).parents("tr").addClass("is-require");
				if(param_names[name] == "ftp_directory_copy") {
					$("#"+ param_names[name]).css("visibility", "visible");
				}else if(param_names[name] == "search_contract_staff") {
					$("#"+ param_names[name]).css("visibility", "visible");
				}else if(param_names[name] == "switch") {
					$("#"+ param_names[name]).css("visibility", "visible");
				}
			}
		}

		//その他の表示非表示

		if(key == 2) {
			//パスワードコピーボタン
			$("#password_copy").attr("disabled", "disabled").addClass("is-disable");
			//パスワード生成ボタン
			$("#password_create").removeAttr("disabled").removeClass("is-disable");
			//ロックボタン
			$("#lock").hide();

			//「評価・分析のみ契約」の場合は中身を消す
			for(var name in non_disablied_name) {
				$("#"+ non_disablied_name[name]).hide();
			}
		}else{
			//パスワードコピーボタン
			$("#password_copy").removeAttr("disabled").removeClass("is-disable");
			//パスワード生成ボタン
			$("#password_create").attr("disabled", "disabled").addClass("is-disable");
			//ロックボタン
			$("#lock").show();

			//「評価・分析のみ契約」以外の場合は中身を戻す
			for(var name in non_disablied_name) {
				$("#"+ non_disablied_name[name]).show();
				if($("#"+ non_disablied_name[name]).val() == "") $("#"+ non_disablied_name[name]).val($("#default-"+ non_disablied_name[name]).val());
			}
		}

		//契約がデモの場合は会員名を設定させて、参照ボタンを押さなくても登録出来るようにする
		if(key == 1) {
			$("#basic-member_name").removeAttr("disabled").removeClass("is-lock is-disable").css("margin-top", '5px');
			$("#basic-location").val("東京都千代田区内幸町1-3-2 内幸町東急ビル");
		}else{
			$("#basic-member_name").attr("disabled", "disabled").addClass("is-lock is-disable").css("margin-top", '');
		}
	}

	/**
	 * 確認画面へ
	 */
	$("#sub_edit").click(function() {
		$("input[type=text]").removeAttr("disabled").removeClass("is-disable");
		$("input[type=radio]").removeAttr("disabled").removeClass("is-disable");
		document.form.submit();
	});


	$("#copy_id").click(function() {
		$("#cms-login_id").val($("#basic-member_no").val());

	});

	//サーバーコンパネのパスワードを必須をつけたり外したり
	changeCpPass($('#cp-cp_password_used_flg').prop('checked'));
	$('#cp-cp_password_used_flg').change(function() {
		changeCpPass($(this).prop('checked'));
	});
	function changeCpPass(checked) {
		if(checked) {
			$("#cp-cp_password").val("#######").addClass("is-lock").addClass("is-disable");
			$('#cp-cp_password_used_flg').parent().find("input[type='hidden']").val(1);
		}else{
			$("#cp-cp_password").removeClass("is-lock").removeClass("is-disable");
			$('#cp-cp_password_used_flg').parent().find("input[type='hidden']").val(0);
		}
	}


	/**
	 * パスワードをランダムに生成する
	 */
	function createPasswordForRandm(element_name, length) {

		if(length == undefined || length == '') length = 8;
		var password = Math.random().toString(36).slice(-length);

		//#$&をつけるかチェック
		var check = Math.floor( Math.random() * 10 ); 
		if(check < 3) {
			var str = ["#","$","&"];
			//何個目を変えるか
			var change = Math.floor( Math.random() * 7 );
			//どれと変えるか
			var replace = Math.floor( Math.random() * 2 );
			//置換
			password = password.replace(password[change], str[replace]);
		}

		if(element_name != undefined && element_name != '') {
			document.getElementById(element_name).value = password;
		}else{
			return password;
		}
	}

});
