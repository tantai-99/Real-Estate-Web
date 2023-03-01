$(document).ready(function(){
	'use strict';

	$('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: 'button',
		buttonImage: '/images/common/icon_date.png',
		buttonImageOnly: true,
		buttonText: 'Select date'
    });

	//出しわけ処理
	//初期設定
	$('#form_detail').hide();
	$('#form_url').hide();
	$('#form_file').hide();

	//ファイル系の初期文言設定
	$(".up_file_name").each(function() {
		if($(this).val() != "") $(this).closest(".up-img").children(".file_name").text($(this).val());
	});


	//初期表示時の入れ替え
	changelEments($('input[name="basic[display_type_code]"]:checked').val());
	$('input[name="basic[display_type_code]"]:radio').change(function() {
		changelEments($(this).val());
	});

	function changelEments(key) {

		$('#form_url').hide();
		$('#form_detail').hide();
		$('#form_file').hide();

		if(key == 1) {
			$('#form_url').show();
		}else if(key == 2) {
			$('#form_detail').show();
		}else if(key == 3) {
			$('#form_file').show();
		}
	}

	//ファイルアップ
	$('.up-btn input').each(function() {

		var instance;

		var dropZone = $(this).parent().next();
		dropZone.hide();
		if ('draggable' in dropZone[0]) {
			dropZone.show();
		}else{
			dropZone = null;
		}
		$(this).fileupload({
			dropZone : dropZone,
			url : "/admin/api/" + $(this).closest(".up-btn").attr('upload-url'),
	        dataType: 'json',
            add: function (e, data) {
                if (e.isDefaultPrevented()) {
                    return false;
                }
                if (data.autoUpload || (data.autoUpload !== false &&
                        instance.options.autoUpload)) {
                    data.process().done(function () {
                        data.submit();
                    });
                }
            },

	        done: function (e, data) {

				$(this).closest(".up-img").children(".file_name").text('');
				$(this).closest(".up-img").children(".error_file").text('');

				if(data.result.success == true && !data.result.errors) {
					$(this).closest(".up-img").children(".tmp_file").val(data.result.data.file.file_name);
					$(this).closest(".up-img").children(".up_file_name").val(data.result.data.file.file_name);
					$(this).closest(".up-img").children(".file_name").text(data.result.data.file.moto_file_name);

				}else{
					$(this).closest(".up-img").children(".error_file").text('アップロードに失敗しました。ファイルをお確かめください。');
				}
			},
			fail: function () {
				$(this).closest(".up-img").children(".error_file").text('アップロードに失敗しました。');
	        }
	    });
		instance = $(this).data('blueimpFileupload');

    });
});

