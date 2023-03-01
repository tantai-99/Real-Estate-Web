// design function (add 201710 by fuku)
$(function(){
	// wrap form table =====================================================
	if($('body').find('.form-table').length){
		$('.form-table').wrap('<div class="form-table-wrap"></div>');
	}
	
	// footer nav
	if($('.gnav2'.length)){
		$('.gnav2 li:empty').append('<span class="empty-node"></span>')
	}
	
	//add background to heading
	if($('.element.element-recommend').length){
		$('.element.element-recommend').prev('.heading-lv1').addClass('headding-recommend')
	}
	
	// half tone
		var elms = $('.area-profile,.list-definition dd,.quote,.element-login,.element-comment,.element-error,.element-qa dt,.element-firstletter .inner');
		$(elms).each(function(){
			var pdgTop = $(this).css('padding-top');
			var pdgLeft = $(this).css('padding-left');
			$(this).prepend('<div class="halftone"></div>');
			//$(this).prepend('<div class="halftone" style="top:-' + pdgTop  + '; left:-' + pdgLeft + '"></div>')
			console.log(pdgTop,pdgLeft);	
		});
});
