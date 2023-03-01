$(document).ready(function(){

	// 「このデモ会員をコピー」押下時
	$("#company_copy").click(function() {
		location.href = "/admin/company/edit/?copy=true&id="+ $("#company_id").val() ;
    });

	//削除ボタン押下時
	$("#company_delete").click(function() {
    adminApp.modal.popup({
			"title" : "契約者情報を削除します。よろしいですか？",
			"onClose" : deleteCompanyHandler
		}).show();
	});
	//削除する
	function deleteCompanyHandler(let) {
		if(let == true) {
			location.href = "/admin/company/delete/?company_id="+ $("#company_id").val();
		}
	}
});
