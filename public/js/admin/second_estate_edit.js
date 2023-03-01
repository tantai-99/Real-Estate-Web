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
					copy_key = "secondEstate-member_no";
				}else if($(this).closest("td").children("input[type='text']").attr('id') == "cms-password" ) {
					copy_key = "cp-cp_password";
				}else if($(this).closest("td").children("input[type='text']").attr('id') == "ftp-ftp_directory" ) {
					copy_key = "secondEstate-domain";
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
	$('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: 'button',
		buttonImage: '/images/common/icon_date.png',
		buttonImageOnly: true,
		buttonText: 'Select date'
    });

	//初期設定系を行う
	//会員Noの参照ボタンを押させるために契約店名は入力出来ないようにする
	$("#secondEstate-member_name").attr("disabled", "disabled").addClass("is-disable").addClass("is-lock");
	//契約担当者名＆契約担当者部署もAPI経由で登録のため入力させない
	$("#secondEstate-contract_staff_name").attr("disabled", "disabled").addClass("is-disable").addClass("is-lock");
	$("#secondEstate-contract_staff_department").attr("disabled", "disabled").addClass("is-disable").addClass("is-lock");
	//解約担当者名＆解約担当者部署もAPI経由で登録のため入力させない
	$("#secondEstate-cancel_staff_name").attr("disabled", "disabled").addClass("is-lock");
	$("#secondEstate-cancel_staff_department").attr("disabled", "disabled").addClass("is-lock");

	//初期はロック
	$("#cp-cp_url").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_server_name").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_server_port").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_password").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_password").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_pasv_flg-0").attr("disabled", "disabled").addClass("is-lock");
	$("#ftp-ftp_pasv_flg-1").attr("disabled", "disabled").addClass("is-lock");

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
	 * 担当者IDを使用して、担当者情報を取得する
	 */
	$(".search_staff").click(function() {
		var search_name = $(this).val();

		if($("#secondEstate-"+ search_name + "_id").val() == "") {
			alert("担当者IDを入力してください。");
			return;
		}

		var asd = $(this);

		var staff_id = $("#secondEstate-"+ search_name + "_id").val();

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

				//担当者名
                if(data != "" && Object.keys(data.data).length != 0 ) {
                    $("#secondEstate-"+ search_name + "_name").val(data.data.tantoName);
					$("#secondEstate-"+ search_name + "_department").val(data.data.shozokuName);
					$("#search_"+ search_name).parent("td").children("p").text("");

				}else{
					adminApp.modal.alert("エラー", "担当者が存在しません。担当者ＩＤをお確かめください。");
				}

			},
			error: function(jqXHR, textStatus, errorThrown){
				adminApp.modal.alert("エラー", "担当者APIに接続できません。しばらく経ってから再度お試しください。");
			},
		});
	});

	//契約担当の入力内容が変わったら担当者名を消しちゃう
	$('#secondEstate-contract_staff_id').change(function() {
		$("#secondEstate-contract_staff_name").val("");
		$("#secondEstate-contract_staff_department").val("");
	});

	//解約担当の入力内容が変わったら担当者名を消しちゃう
	$('#secondEstate-cancel_staff_id').change(function() {
		$("#secondEstate-cancel_staff_name").val("");
		$("#secondEstate-cancel_staff_department").val("");
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

	function changelEments(key) {

		//デモだったら
		if(key == 1) {
			//会社名を入力可
			$("#secondEstate-member_name").removeAttr("disabled").removeClass("is-lock is-disable").css("margin-top", '5px');
			$("#secondEstate-location").val("東京都千代田区内幸町1-3-2 内幸町東急ビル");
			//ボタンを押せないようにする
			$("#search_member_no").attr("disabled", "disabled").addClass("is-lock").addClass("is-disable");

		//デモ以外
		}else{
			//会社名を入力不可
			$("#secondEstate-member_name").attr("disabled", "disabled").addClass("is-lock is-disable").css("margin-top", '');
			//ボタンを押せるようにする
			$("#search_member_no").removeAttr("disabled").removeClass("is-disable");

 			if(pre_contract_type == 1) {
				$("#secondEstate-member_name").val("");
				$("#secondEstate-location").val("");
			}
		}

		//現状の契約を取っておく
		pre_contract_type = key;

		//設定用情報
		var param_names = [
				"secondEstate-company_name",
				"secondEstate-domain",
				"secondEstate-applied_start_date",
				"secondEstate-start_date",
				"secondEstate-contract_staff_id",
				"search_contract_staff",
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
/*
			if($("#secondEstate-id").val() == "" & key == 2) {
				$("#ftp-ftp_pasv_flg-0").attr("checked", false);
				$("#ftp-ftp_pasv_flg-1").attr("checked", false);
			}
*/
		}

		//その他の表示非表示

		//データピッカーの出し入れ
		$('.datepicker').each(function(i) {

			if(key == 2 && (i == 0 || i == 1)) {
				$(this).next().hide();
			}else if(i == 0 || i == 1) {
				$(this).next().show();
			}
		});

		if(key == 2) {
			//パスワードコピーボタン
			$("#password_copy").attr("disabled", "disabled").addClass("is-disable");
			//パスワード生成ボタン
			$("#password_create").removeAttr("disabled").removeClass("is-disable");
			//ロックボタン
			$("#lock").hide();

			//新規の場合で「評価・分析のみ契約」の場合は中身を消す
			//⇒　新規じゃなくても隠すに変更
//			if($("#secondEstate-id").val() == "" || $("#secondEstate-id").val() == "0" ) {
				for(var name in non_disablied_name) {
//					$("#"+ non_disablied_name[name]).val("");
					$("#"+ non_disablied_name[name]).hide();
				}
//			}

			$("span").each(function() {
				if($(this).html() == "※yyyy-mm-dd") $(this).hide();
			});

		}else{
			//パスワードコピーボタン
			$("#password_copy").removeAttr("disabled").removeClass("is-disable");
			//パスワード生成ボタン
			$("#password_create").attr("disabled", "disabled").addClass("is-disable");
			//ロックボタン
			$("#lock").show();

			$("span").each(function() {
				if($(this).html() == "※yyyy-mm-dd") $(this).show();
			});

			//新規の場合で「評価・分析のみ契約」以外の場合は中身を戻す
			//⇒　新規じゃなくても隠すに変更
			for(var name in non_disablied_name) {
				$("#"+ non_disablied_name[name]).show();
				if($("#"+ non_disablied_name[name]).val() == "") $("#"+ non_disablied_name[name]).val($("#default-"+ non_disablied_name[name]).val());
			}
		}

		//契約がデモの場合は会員名を設定させて、参照ボタンを押さなくても登録出来るようにする
		if(key == 1) {
			$("#secondEstate-member_name").removeAttr("disabled").removeClass("is-lock is-disable").css("margin-top", '5px');
			$("#secondEstate-location").val("東京都千代田区内幸町1-3-2 内幸町東急ビル");
		}else{
			$("#secondEstate-member_name").attr("disabled", "disabled").addClass("is-lock is-disable").css("margin-top", '');
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
		$("#cms-login_id").val($("#secondEstate-member_no").val());

	});



});
