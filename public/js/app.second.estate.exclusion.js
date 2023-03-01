$(function () {
  'use strict';

	$("#delete_company").prop("disabled", true);
	$("#delete_company").css("color", "white");

	//一覧
	$('.delete_comapny_check').change( function () {
		if($(this).prop('checked')) {
			var hidden = $('<input>').attr({
			    type: 'hidden',
			    name: $(this).attr("name"),
			    id: $(this).attr("id"),
			    value: $(this).val()
			});
			hidden.appendTo('#targetId');
		}else{
			$('#targetId #' + $(this).attr("id")).remove();	
		}

		if( $("#targetId input:hidden").length == 0) {
			$("#delete_company").removeClass("btn-t-blue");
			$("#delete_company").addClass("btn-t-gray");
			$("#delete_company").prop("disabled", true);

		} else {
			$("#delete_company").removeClass("btn-t-gray");
			$("#delete_company").addClass("btn-t-blue");
			$("#delete_company").prop("disabled", false);
		}

	});

	$('#delete_company').click(function() {
		if( $("#targetId input:hidden").length > 0) {
		    var msg = '設定を解除いたします。よろしいでしょうか？';
		    app.modal.confirm('確認', msg, function (res) {
			    if (!res) {
			    	return;
			    }
				$('#delete_form').submit();
				return false;
		    });
		}
	});

	//検索一覧系
	$("#detail_btn").prop("disabled", true);
	$("#detail_btn").css("color", "white");

	$('.add_no').change( function () {
		if($(this).prop('checked')) {
			var hidden = $('<input>').attr({
			    type: 'hidden',
			    name: $(this).attr("name"),
			    id: $(this).attr("id"),
			    value: $(this).val()
			});
			hidden.appendTo('#targetId');
		}else{
			$('#targetId #' + $(this).attr("id")).remove();	
		}

		if( $("#targetId input:hidden").length == 0) {
			$("#detail_btn").removeClass("btn-t-blue");
			$("#detail_btn").addClass("btn-t-gray");
			$("#detail_btn").prop("disabled", true);
		} else {
			$("#detail_btn").removeClass("btn-t-gray");
			$("#detail_btn").addClass("btn-t-blue");
			$("#detail_btn").prop("disabled", false);
		}
	});

	if( $("#targetId input:hidden").length == 0) {
		$("#detail_btn").removeClass("btn-t-blue");
		$("#detail_btn").addClass("btn-t-gray");
		$("#detail_btn").prop("disabled", true);
	} else {
		$("#detail_btn").removeClass("btn-t-gray");
		$("#detail_btn").addClass("btn-t-blue");
		$("#detail_btn").prop("disabled", false);
	}

	$(".update-setting-btn").click(function () {
		if($(this).attr("data") == "type1") {
			$("#submit_name").val($(this).attr("data"));
		}else if($(this).attr("data") == "type2") {
			$("#submit_phone").val($(this).attr("data"));
		}
		$('#search_form').submit();
		return false;
	});

	$('.section .paging a').each(function() {
		$(this).click(function () {
            // ATHOME_HP_DEV-5173 URLパラメータに重要情報を含めないよう改修する
            var data = {};
            $("#targetId > input").each( function() {
                if (typeof data['kaiin_no'] == 'undefined') {
                    data['kaiin_no'] = [];
                }
                data['kaiin_no'][$(this).val()] = $(this).val();
            });
            if ($('#input_name').length > 0) {
                data['BukkenShogo'] = $('#input_name').val();
            }
            if ($('#input_phone').length > 0) {
                data['DaihyoTel'] = $('#input_phone').val();
            }
            data['_token'] = $('meta[name="csrf-token"]').attr('content');
            var url = $(this).attr("href");
            postForm(url, data);
            return false;
		});
	});

	$('#detail_btn').click(function() {
		if( $("#targetId input:hidden").length > 0) {
			$('#form').submit();
			return false;
		}
	});

	$(".btn-modal").click(
		function(){
			$("#modal").toggleClass("is-hide");
		}
	);

    /**
     * ATHOME_HP_DEV-5173 URLパラメータに重要情報を含めないよう改修する
     * post
     *
     * @param url string
     * @param data object
     * 
     */
    var postForm = function(url, data) {
        var $form, params;
        params = {action: url, method: 'post'};
        $form = $('<form/>', params);
        $.each(data, function (i, v) {
            if ($.isArray(v)) {
              for (var j in v) {
                  $form.append($('<input/>', {'type': 'hidden', 'name': i +'[' + j + ']', 'value': v[j]}));
              }
            } else {
              $form.append($('<input/>', {'type': 'hidden', 'name': i, 'value': v}));
            }
        });
        $form.appendTo($('body')).submit().remove();
    }
});
