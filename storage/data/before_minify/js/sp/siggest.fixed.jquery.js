function detecedDevice(){
    var isSafari = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/);
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    return isSafari || iOS;
}
var isFixed = detecedDevice();
var tagOption = 'span';
var inputSelector = 'input[name="search_filter[fulltext_fields]"]';
var classSuggest = '.suggesteds';
var cloneSuggest = true;
var historytouch = '';
(function() {
    $.fn.replaceSuggest = function() {
        if (isFixed){
            var innerHTML= this[0].innerHTML;
            innerHTML = innerHTML.replace(/option/g, tagOption);
            if (this.attr('data-isBlur') != "true"){
                $(document).on('click', function(event) {
                    if (!(event.target).closest([classSuggest,inputSelector].join(','))) {
                        $(classSuggest).css({'display':'none'});
                        $('.contents-main>.inner').removeAttr('style');
                    } else {
                        if (event.target.closest(inputSelector)) {
                            $(classSuggest).not('#'+$(event.target).attr('list')).css({'display':'none'});
                            $('.contents-main>.inner').removeAttr('style');
                        }
                    }
                });
                $(document).on('touchend', function(event) {
                    if (!(event.target).closest([classSuggest,inputSelector].join(','))) {
                        $(inputSelector).blur();
                    } else {
                        if ($(event.target).attr('name') == 'search_filter[fulltext_fields]') {
                            $(event.target).data('plugin_fulltextSuggest').getSuggests();
                            $('.contents-main>.inner').css({'overflow':'unset'});
                        }
                    }
                });
                $(inputSelector).unbind('click');
                $(document).on('touchstart touchend touchmove',['#' + $(this).get(0).id + this.selector, tagOption].join(' '),function(event){
                    if (event.type=='touchmove') {
                        historytouch=event.type;
                    }
                    if (event.type=='touchend') {
                        if (historytouch != '') {
                            historytouch = '';
                        } else {
                            event.preventDefault();
                            $(this).parent().parent().find(inputSelector).val(this.innerText);
                            $(this).parent().css({'display':'none'});
                            $('.contents-main>.inner').removeAttr('style');
                            historytouch = '';
                        }
                    }

                });
                $(document).on('blur focusout', inputSelector, function () {
                    $(classSuggest).css({'display' : 'none'});
                });
                var display = 'none';
                if ($(this).parent().parent().find(inputSelector).val() != '') {
                    display = 'block';
                }
                this.replaceWith('<div id="'+this.attr('id')+'" class="'+this.attr('class')+'" data-isBlur="true" style="display:'+display+'" >'+innerHTML+'</div>');
                $('.contents-main > .inner').css({'overflow':'unset'});
            }
            this[0].innerHTML = innerHTML;
            if (innerHTML != '') {
                this.css({'display' : 'block'});
            }
        }
        return this;
    }

    $.fn.cloneToSuggest = function($element) {
        for (index in $element) {
            $element[0].innerHTML = this[0].innerHTML;
            $($element[0]).replaceSuggest();
        }
    }
}( jQuery ));