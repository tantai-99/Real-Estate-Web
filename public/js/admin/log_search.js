$(document).ready(function(){
	'use strict';

	$('#datetime_s.datepicker').datepicker({
		dateFormat: 'yy-mm-dd 00:00',
		showOn: 'button',
		buttonImage: '/images/common/icon_date.png',
		buttonImageOnly: true,
		buttonText: 'Select date'
    });

	$('#datetime_e.datepicker').datepicker({
		dateFormat: 'yy-mm-dd 23:59',
		showOn: 'button',
		buttonImage: '/images/common/icon_date.png',
		buttonImageOnly: true,
		buttonText: 'Select date'
    });

	// ATHOME_HP_DEV-5622 ログ管理の仕様を変更する
	$("#search_area input[type=submit]").click(function() {
		var isEnable = true;
		postOutPutCsv().done(function(result) {
			if(result === false) {
				isEnable = false;
				adminApp.modal.alert('エラー', '操作日時の期間を短くしてください。')
			}
		});
		if (!isEnable) {
			return false;
		}
	});

	function postOutPutCsv() {
		return $.ajax({
			type: 'POST',
			url: '/admin/log/enable-output',
			data: {
				submit: $('input[name="submit"]').val(),
				log_type: $('select[name="log_type"] option:selected').val(),
				athome_staff_id: $('input[name="athome_staff_id"]').val(),
				member_no: $('input[name="member_no"]').val(),
				company_name: $('input[name="company_name"]').val(),
				datetime_s: $('input[name="datetime_s"]').val(),
				datetime_e: $('input[name="datetime_e"]').val(),
			},
			dataType: 'json',
			async : false
		})
	}
});

