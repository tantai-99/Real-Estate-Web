$(document).ready(function(){

	//検索エリアの出し入れトグル
	$("#search_title").click(function() {
//		$("#search_area").toggle();
	});


	$('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
//		showOn: 'both',
		showOn: 'button',
		buttonImage: '/images/common/icon_date.png',
		buttonImageOnly: true,
		buttonText: 'Select date'
    });
});
