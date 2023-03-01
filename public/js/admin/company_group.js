$(document).ready(function(){

	//会員Noを使用して、会員情報を取得する
	$("#get_company").click(function() {

		if($("#member_no").val() == "") return;

		//パラメータの設定
		var obj = {
			'member_no' : $("#member_no").val(),
			'company_id' : $("#company_id").val()
		}
		var param = $.param(obj);

		$.ajax({
			type: "POST",
			dataType: "json",
			url : "/admin/api/get-company-for-memberno-check",
			cache: false,
			data: param,
			success: function(json, textStatus) {

				if(json.success == false) {
					app.modal.alert("エラー", "システムエラーが発生しました。");
				}else{
					if(json.data.error != "") {
						app.modal.alert("エラー", json.data.error);
						return;
					}

					if(json.data.company.contract_type == 2) {
						app.modal.alert("エラー", "「評価・分析のみ契約」の会員は設定できません。");
						return;
					}

					//親の名前取得
					var mem_no   = $("#parent_company_no").text();
					var com_name = $("#parent_company_name").text();

					//APIから取得した内容を設定
					$("#area_comment").text("次の会員を" + com_name + "("+ mem_no +")のグループ会社に設定します。よろしいですか？");
					$("#add_company_id").val(json.data.company.id);
					$("#area_member_no").text(json.data.company.member_no);
					$("#area_company_name").text(json.data.company.company_name);
					$("#area_member_name").text(json.data.company.member_name);
					$("#area_location").text(json.data.company.location);

					var html = '<table class="form-basic" style="width:500px;" id="add_area">';
					html += $("#add_area").html();
					html += '</table>';
					app.modal.popup({
						"title" : "グループ会社設定",
						"contents" : html,
						modalBodyInnerClass: 'align-top',
						"onClose" : addGroupCompany
					}).show();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				app.modal.alert("接続エラー", "通信に失敗しました。");
			}
		});
	});

	/**
	 * グループを追加させる
	 */
	function addGroupCompany(ret) {
		if(ret == true) {
			document.group_form.submit();
		}
	}

	//会員Noを使用して、会員情報を取得する
	$(".del_button").click(function() {

		//パラメータの設定
		var obj = {
			'member_no' : $(this).val(),
			'company_id' : $("#company_id").val()
		}
		var param = $.param(obj);

		$.ajax({
			type: "POST",
			dataType: "json",
			url : "/admin/api/get-company-for-memberno",
			cache: false,
			data: param,
			success: function(json, textStatus) {

				if(json.success == false) {
					app.modal.alert("エラー", "システムエラーが発生しました。");
				}else{

					if(json.data.error != "") {
						app.modal.alert("エラー", json.data.error);
						return;
					}

					var mem_no = $("#parent_company_no").text();
					var com_name = $("#parent_company_name").text();

					//APIから取得した内容を設定
					$("#area_comment").text("次の会員を" + com_name + "("+ mem_no +")のグループ会社から削除します。よろしいですか？");
					$("#del_company_id").val(json.data.company.subsidiary_company_id);
					$("#del_id").val(json.data.company.associated_company_id);
					$("#area_member_no").text(json.data.company.member_no);
					$("#area_company_name").text(json.data.company.company_name);
					$("#area_member_name").text(json.data.company.member_name);
					$("#area_location").text(json.data.company.location);

					var html = '<table class="form-basic" style="width:500px;" id="add_area">';
					html += $("#add_area").html();
					html += '</table>';
					app.modal.popup({
						"title" : "グループ会社設定",
						"contents" : html,
						modalBodyInnerClass: 'align-top',
						"onClose" : delGroupCompany
					}).show();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				app.modal.alert("接続エラー", "通信に失敗しました。");
			}
		});
	});

	/**
	 * グループ削除する
	 */
	function delGroupCompany(ret) {
		if(ret == true) {
			document.group_del_form.submit();
		}
	}
});
