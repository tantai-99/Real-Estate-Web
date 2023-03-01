// design function (add 201710 by fuku)
$(function(){
	// footer nav
	if($('.gnav2').length){
		$('.gnav2 li:empty').append('<span class="empty-node"></span>');
	}
	// nav position
	if($('.slide-map-cover').length){
		var hh = $('.page-header').height();
		$('.gnav').css('top',hh + 'px');
	}
	
	//add background to heading
	if($('.element.element-recommend').length){
		$('.element.element-recommend').prev('.heading-lv1').addClass('headding-recommend');
	}
	// button design
	if($('input.btn-lv1').length){
		$('input.btn-lv1').each(function(){
			var margin = $(this).css('margin');
			$(this).wrap('<span class="btn-wrap lv1" style="margin:' + margin +'">');
		});
	}
	if($('input.btn-lv2').length){
		$('input.btn-lv2').each(function(){
			var margin = $(this).css('margin');
			$(this).wrap('<span class="btn-wrap lv2" style="margin:' + margin +'">');
		});
	}
	if($('input.btn-lv3').length){
		$('input.btn-lv3').each(function(){
			var margin = $(this).css('margin');
			$(this).wrap('<span class="btn-wrap lv3" style="margin:' + margin +'">');
		});
	}
	if($('input.btn-lv4').length){
		$('input.btn-lv4').each(function(){
			var margin = $(this).css('margin');
			$(this).wrap('<span class="btn-wrap lv4" style="margin:' + margin +'">');
		});
	}
});
