$(function(){
	'use strict';

	$('#back').on('click', function () {
		app.modal.message({
			"title"   :"前の画面に戻ります。よろしいですか？",
			"message" : '編集中の内容は保存されません。 \r\n 保存する場合は、「保存して次へ」を押下してください。',
			"onClose" : _backClose
		});
	});
	function _backClose(res) {
		if(res == true) {
			$(window).off('beforeunload');
			location.href = $('#back').attr('data-link');
		}
	}
});
