$(document).ready(function(){
	'use strict';


	$('.up-btn input').click(function() {
		$("#submit").attr("disabled", "disabled").addClass("is-disable");
	});


	//ファイルアップ
	$('.up-btn input').each(function() {

		$(this).parent(".file_name").val('');

		var instance;
		
		$(this).fileupload({
			dropZone : null,
			url : "/admin/api/" + $(this).closest(".up-btn").attr('upload-url') +"/company_id/"+ $("#company_id").val(),
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

				$(this).closest(".f-img-upload").next("#file_errors").html('');

				if(data.result.success == true && !data.result.errors) {
					$("#uv").val(data.result.data.file.file_name);
					$("#google-file_name").val(data.result.data.file.file_name);
				}else{

					$(this).closest(".f-img-upload").next("#file_errors").html('アップロードに失敗しました。');

				}

				$("#submit").removeAttr("disabled").removeClass("is-disable");

			},
			fail: function () {
				$(this).closest(".f-img-upload").next("#file_errors").html('アップロードに失敗しました。');
				$("#submit").removeAttr("disabled").removeClass("is-disable");
			}
		});
		
		instance = $(this).data('blueimpFileupload');
	});
});

