function detecedDevice(){
	var isSafari = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/);
	var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
	return isSafari || iOS;
}
var isFixed=detecedDevice();
var tagOption='span';
var inputSelector='input[name="search_filter[fulltext_fields]"]';
var classSuggest='.suggesteds';
var cloneSuggest=true;
(function() {
	$.fn.replaceSuggest = function(){
		if(isFixed){
			var innerHTML= this[0].innerHTML;
			innerHTML=innerHTML.replace(/option/g, tagOption);
			if(this.attr('data-isBlur')!="true"){
				$(document).on('click',function(event){
					if(!(event.target).closest([classSuggest,inputSelector].join(','))){
						$(classSuggest).css({'display':'none'});
						$('.contents-main>.inner').removeAttr('style');
					}
					else{
						if(event.target.closest(inputSelector)){
							$(classSuggest).not('#'+$(event.target).attr('list')).css({'display':'none'});
							$('.contents-main>.inner').removeAttr('style');
						}
					}
				});
				$(document).on('click',inputSelector,function(event){
					$(this).data('plugin_fulltextSuggest').getSuggests();
					$('#'+$(this).attr('list')).css({'display':'block'});
					$('.contents-main>.inner').css({'overflow':'unset'});
				});
				$(inputSelector).unbind('click');
				$(document).on('click',[this.selector,tagOption].join(' '),function(event){
					$(inputSelector).val(this.innerText) ;
					$(this).parent().css({'display':'none'});
					$('.contents-main>.inner').removeAttr('style');

				});
				this.replaceWith('<div id="'+this.attr('id')+'" class="'+this.attr('class')+'" data-isBlur="true" style="display:block" >'+innerHTML+'</div>');
				$('.contents-main > .inner').css({'overflow':'unset'});
			}
			this[0].innerHTML=innerHTML;
		}
		return this;
	}

	$.fn.cloneToSuggest = function($element){
		for(index in $element){
			$element[0].innerHTML=this[0].innerHTML;
			$($element[0]).replaceSuggest();
		}
	}
}( jQuery ));