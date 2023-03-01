$(document).ready(function(){

	//削除ボタン押下時
	$("#del").click(function() {
		app.modal.popup({
			"title" : "ホームページ情報を削除します。よろしいですか？",
			"onClose" : deleteCompanyHandler
		}).show();
	});
	//削除する
	function deleteCompanyHandler(let) {
		if(let == true) {
			document.form.submit();
		}
	}
});
