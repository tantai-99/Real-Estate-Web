// Freeword用
$(function(){
	$(".freeword-table").each(function() {
		// 現在のソート順をもとに並べ替える
		if($(this).find('tr').length <= 1) {
			// 上下移動ボタンを非表示にする
			$(this).find('.updown').hide();
		}
		var tr_array = { '1': [], '2': [], '3': [], '4': [], '0': [] };

		$(this).find('tr').each(function() {
			selval = $(this).find('option:selected').val();
			switch(selval) {
				case '1':
				case '2':
				case '3':
				case '4':
					// 要表示
					$(this).find('.fw-display').eq(0).prop('checked', true);
					tr_array[ selval ].push( $(this) );
					break;
				default:
					tr_array[ '0' ].push( $(this) );
					break;
			}
		});
		$(this).empty();
		$(this).append(tr_array['1'], tr_array['2'], tr_array['3'], tr_array['4'], tr_array['0']);

		if(typeof $(this).closest("div .page-element").attr('data-parts-id') === 'undefined') {
			// デフォルトフォームは全チェック
			$(this).find('.fw-display').prop('checked', true);
		}

		var fwObj = new FreeWord();
		fwObj.reNumbering($(this));
		fwObj.ctlArrow($(this));
	});
});

// checkbox:fw-display にイベント
$(document).on('change', ".freeword-table .fw-display", function() {
	var fwObj = new FreeWord();
	fwObj.reNumbering($(this).closest('table'));
});

// 下ボタンにイベントを付与
$(document).on('click', ".freeword-table .fw-down-btn", function() {
	if($(this).hasClass('is-disable')) {
		return;
	}
	var selfTr = $(this).closest('tr');
	var nextTr = $(selfTr).next();
	if(nextTr.length != 0) {
		selfTr.insertAfter(nextTr);

		var fwObj = new FreeWord();
		fwObj.reNumbering($(this).closest('table'));
		fwObj.ctlArrow($(this).closest('table'));
	}
});

// 上ボタンにイベントを付与
$(document).on('click', ".freeword-table .fw-up-btn", function() {
	if($(this).hasClass('is-disable')) {
		return;
	}
	var selfTr = $(this).closest('tr');
	var prevTr = $(selfTr).prev();
	if(prevTr.length != 0) {
		selfTr.insertBefore(prevTr);

		var fwObj = new FreeWord();
		fwObj.reNumbering($(this).closest('table'));
		fwObj.ctlArrow($(this).closest('table'));
	}
});

// FreeWord Class定義 (IE11 class利用不可)
function FreeWord() {}
FreeWord.prototype.reNumbering = function(tblObj) {
		var cno = 1;
		var anyCheck = false;
		$(tblObj).find('.fw-display').each(function() {
			if($(this).prop('checked')) {
				$(this).closest('tr').find("option[value='" + cno + "']").attr('selected', 'selected');
				cno++;
				anyCheck = true;
			} else {
				$(this).closest('tr').find('select').val('');
			}
		});

		if(anyCheck == true) {
			$(tblObj).find('.display_any_cb').eq(0).prop('checked', true);
		} else {
			$(tblObj).find('.display_any_cb').eq(0).prop('checked', false);
		}
};
FreeWord.prototype.ctlArrow = function(tblObj) {
		// 一番上のUPをdisabled
		$(tblObj).find(".fw-up-btn").removeClass('is-disable');
		$(tblObj).find(".fw-up-btn").eq(0).addClass('is-disable');

		// 一番下のDOWNをdisabled
		$(tblObj).find(".fw-down-btn").removeClass('is-disable');
		$($(tblObj).find(".fw-down-btn").get().reverse()).eq(0).addClass('is-disable');
};