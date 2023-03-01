$(document).ready(function(){

	//会員Noを使用して、会員情報を取得する
	$("#add_company").click(function() {

		if($("#member_no").val() == "") return;
		getCompany('type-add',this);
	});

	//会員Noを使用して、会員情報を取得する
	$(".ref_button").click(function() {
		getCompany('type-ref',this);
	});

	$(".del_button").click(function() {
		getCompany('type-del',this);
	});

	/**
	 * 会員情報を取得する
	 */
	function getCompany(type, obj) {

		if(type != 'type-add' && type != 'type-ref' && type != 'type-del' ){
			return;
		}
		var memberNo;
		var apiUrl;
		var popupFunc;
		if(type == 'type-add'){
			memberNo = $("#member_no").val();
			apiUrl   = "/admin/api/get-estate-group-sub-companies-by-member-no-for-add";
			popupFunc = popupAdd;	

		}else if (type == 'type-ref'){
			memberNo = $(obj).val();
			apiUrl   = "/admin/api/get-estate-group-sub-companies-by-member-no";
			popupFunc = popupRef;	

		}else if (type == 'type-del'){
			memberNo = $(obj).val();
			apiUrl   = "/admin/api/get-estate-group-sub-companies-by-member-no";
			popupFunc = popupDel;	
		}

		//パラメータの設定
		var paramObj = {
			'member_no' : memberNo,
			'company_id' : $("#company_id").val()
		}
		var param = $.param(paramObj);
		$.ajax({
			type: "POST",
			dataType: "json",
			url : apiUrl,
			cache: false,
			data: param,
			success: function(json, textStatus) {
				if(json.success == false) {
					adminApp.modal.alert("エラー", "システムエラーが発生しました。");
				}else{
					if(json.data.error != "") {
						adminApp.modal.alert("エラー", json.data.error);
						return;
					}
					// ポップアップを表示する
					popupFunc(json);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				adminApp.modal.alert("接続エラー", "通信に失敗しました。");
			}
		});

	}

	/**
	 * グループ追加確認ポップアップ（APIから取得した内容にて）
	 */
	function popupAdd(json) {

		var mem_no   = $("#parent_company_no").text();

		if(json.data.company.contract_type == 2) {
			adminApp.modal.alert("エラー", "「評価・分析のみ契約」の会員は設定できません。");
			return;
		}
		//親の名前取得
		var mem_no   = $("#parent_company_no").text();
		var com_name = $("#parent_member_name").text();

		//APIから取得した内容を設定
		$("#area_comment").text("次の会員を" + com_name + "("+ mem_no +")のグループ会社に設定します。よろしいですか？");
		$("#add_member_no").val(json.data.company.member_no);
		$("#area_member_no").text(json.data.company.member_no);
		$("#area_inte_code").text(json.data.company.link_no);
		$("#area_company_name").text(json.data.company.company_name);
		$("#area_member_name").text(json.data.company.member_name);
		$("#area_location").text(json.data.company.location);

		var html = '<table class="form-basic" style="width:500px;" id="add_area">';
		html += $("#add_area").html();
		html += '</table>';
		adminApp.modal.popup({
			"title" : "物件グループ設定",
			"contents" : html,
			modalBodyInnerClass: 'align-top',
			"onClose" : addGroupCompany
		}).show();

	}

	/**
	 * グループ参照確認ポップアップ（APIから取得した内容にて）
	 */
	function popupRef(json) {
		var mem_no   = $("#parent_company_no").text();

		//親の名前取得
		var mem_no   = $("#parent_company_no").text();
		var com_name = $("#parent_member_name").text();

		//APIから取得した内容を設定
		$("#area_comment").text(com_name + "("+ mem_no +")のグループ会社として設定されています");
		$("#add_member_no").val(json.data.company.member_no);
		$("#area_member_no").text(json.data.company.member_no);
		$("#area_inte_code").text(json.data.company.link_no);
		$("#area_company_name").text(json.data.company.company_name);
		$("#area_member_name").text(json.data.company.member_name);
		$("#area_location").text(json.data.company.location);

		var html = '<table class="form-basic" style="width:500px;" id="add_area">';
		html += $("#add_area").html();
		html += '</table>';
		adminApp.modal.popup({
			"title" : "物件グループ設定",
			"contents" : html,
		}).show();

	}

	/**
	 * グループ削除確認ポップアップ（APIから取得した内容にて）
	 */
	function popupDel(json) {
		var mem_no = $("#parent_company_no").text();
		var com_name = $("#parent_member_name").text();

		//APIから取得した内容を設定
		$("#area_comment").text("次の会員を" + com_name + "("+ mem_no +")のグループ会社から削除します。よろしいですか？");
		$("#del_member_no").val(json.data.company.member_no);
		$("#del_associate_id").val(json.data.company.associate_id);
		$("#area_member_no").text(json.data.company.member_no);
		$("#area_inte_code").text(json.data.company.link_no);
		$("#area_company_name").text(json.data.company.company_name);
		$("#area_member_name").text(json.data.company.member_name);
		$("#area_location").text(json.data.company.location);

		var html = '<table class="form-basic" style="width:500px;" id="add_area">';
		html += $("#add_area").html();
		html += '</table>';
		adminApp.modal.popup({
			"title" : "グループ会社設定",
			"contents" : html,
			modalBodyInnerClass: 'align-top',
			"onClose" : delGroupCompany
		}).show();
	}

	/**
	 * グループを追加させる
	 */
	function addGroupCompany(ret) {
		if(ret == true) {
			document.group_form.submit();
		}
	}


	/**
	 * グループ削除する
	 */
	function delGroupCompany(ret) {
		if(ret == true) {
			document.group_del_form.submit();
		}
	}
});
