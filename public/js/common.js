$(function(){

    /**
     * scroll
     */
    $('a[href^=#]').click(function(){
        var target;
        
        target = $( $(this).attr('href') );
        if (target.length == 0) {
            return;
        }
        $('html, body').animate({scrollTop: target.offset().top}, {duration: "fast"});
        return false;
    });

    /**
     * ラベル削除
     */
    $('#g-header .h-mark').on('click', function () {
        $(this).hide();
    });

    /**
     * pageedit-sidefix
     */
    var tab = $('#page-edit-side'),
        offset;
    if (tab.length) {
        offset = tab.offset();
 
        $(window).scroll(function () {
            if($(window).scrollTop() > offset.top) {
                tab.addClass('fixed');
            } else {
                tab.removeClass('fixed');
            }
        });
    }


    /**
     * pageedit-sidefix
     */
    var tab2 = $('#side'),
        offset2;
    if (tab2.length) {
        offset2 = tab2.offset();
 
        $(window).scroll(function () {
            if($(window).scrollTop() > offset2.top) {
                tab2.addClass('fixed');
            } else {
                tab2.removeClass('fixed');
            }
        });
    }
    
    $.fn.findInputCount = function () {
		return this.data('_inputCounte') || (function ($this) {
			
			var $current = $this;
			
			do {
				if ($current.next('.input-count').length) {
					var $inputCount = $current.next('.input-count');
					var max = parseInt($this.attr('maxlength') || $this.attr('data-maxlength'));
					if ($this.is('textarea,input')) {
						$this.removeAttr('maxlength');
						if (max) {
							$this.attr('data-maxlength', max);
						}
						else {
							$this.removeAttr('data-maxlength');
						}
					}
					$this.data('_inputCount', $inputCount);
					$this.data('_inputCountMax', max);
					return $inputCount;
				}
				$current = $current.parent();
			}
			while ($current.length);
			
			return $();
			
		})(this);
		
    };
    
    $('body').on('keyup change', '.watch-input-count', function () {
    	var $this = $(this);
    	var $inputCount = $this.findInputCount();
    	var max   = $this.data('_inputCountMax');
    	
    	var count = $this.val().replace(/<[^>]*>/g, '').replace(/&nbsp;/gi,'').length;
    	count += parseInt($this.attr('data-initial-count')) || 0;
    	var countStr = count;
    	if (!isNaN(max)) {
    		countStr += '/' + max;
    	}
    	$inputCount.toggleClass('is-over', !isNaN(max) && count > max);
    	$inputCount.text(countStr);
    })
    .find('.watch-input-count').trigger('change');



    /**
     * login form placeholder ie8 ie9
     */
    $.ahPlaceholderDefaults({
        placeholderColor : '#a2adba',
        placeholderAttr  : 'placeholder',
        likeApple        : false
    });
    $('[placeholder]').ahPlaceholder();


    /**
     * theme-slider
     */
    // Can also be used with $(document).ready()
    $(window).load(function() {
      $('.flexslider').flexslider({
        animation: "slide",
        slideshow: false,
        // animationLoop: false,
        prevText:"←",
        nextText:"→",
        controlNav:false
      });
    });


    /// URLの書式チェック - URLならtrue、違うならfalseを返す。
    $.extend({
        checkUrlFormat: function checkUrlFormat(inputUrl) {
            inputUrl = inputUrl || "";

            return inputUrl.match(/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/);
        }
    });


  $('.copy-icon').on('click',function(e){
    var url = $(this).data('url');
    var parent = $(this).parent();
    var input = parent.find('input[type=hidden]');
    if(input){
      var value = input.val();
      if ("undefined" !== typeof  window.clipboardData){
        window.clipboardData.setData('text', value);  
      } else {
        ClipboardHelper.copyText(value);
      }
      
      showPopup(value);
    }
  });

  var ClipboardHelper = {
    copyText:function(text) // Linebreaks with \n
    {
      var $tempInput =  $("<textarea>");
      $("body").append($tempInput);
      $tempInput.val(text).select();
      document.execCommand("copy");
      $tempInput.remove();
    }
  };

  function showPopup(text) {
    $("body").find('#popupContainer').remove();
    var html = '<div id="popupContainer" class="modal">\n' +
      '    <span class="modal-content" id="myPopup"> コピーしました: '+ jQuery('<span/>').text(text).html() +'</span>\n' +
      '</div>';
    $("body").append(html);
    var popup = document.getElementById("popupContainer");
    popup.classList.toggle("show");
    $(popup).delay(1000)
      .fadeOut(100);
  }

    /**
     * CMSの最大容量と現在の容量を表示する
     */
    $("#confirm-capacity").on("click", function () {
		$.ajax({
			url: "/index/confirm-capacity",
			type: "GET",
            success: function (data) {
                app.modal.alert("容量", data);
			},
			error: function () {
                app.modal.alert("エラー", "システムエラー");
			}
		});
	});

    /**
     * 予約がある場合は、初期設定/デザイン設定への移動の制御
     */
    if(typeof has_reserve != 'undefined' && has_reserve == '1') {
		switch(location.pathname) {
			case '/site-setting':
			case '/site-setting/':
			case '/site-setting/design':
			case '/site-setting/design/':
				app.modal.hasResever('「サイトの公開/更新」画面にて公開・停止の予約設定がされています。予約設定解除後にこのページを「保存」できます。');
				break;
		}
    } 

    $('.content-button-open-close button[class*="btn-t-"]').on('click', function(e) {
        e.preventDefault();
        var section = $(this).closest('.section');
        section.find('.content-button-open-close button').toggleClass('is-hide', false);
        $(this).toggleClass('is-hide', true);
        var open = section.find('.btn-t-open-all').hasClass('is-hide')
        section.find('.page-area').toggleClass('is-hide', !open);
        section.find('.page-area').toggleClass('down', open);
        section.find('.page-creation-title').toggleClass('down', open);
    });
    $('.page-creation-title').on('click', function() {
        var isDown = $(this).hasClass('down');
        $(this).toggleClass('down', !isDown);
        var pageAreaElement = $(this).next();
        pageAreaElement.toggleClass('is-hide', isDown);
        pageAreaElement.toggleClass('down', !isDown);
        var section = $(this).closest('.section');
        if (section.find('.page-area:not(.is-hide)').length == 0) {
            section.find('.btn-t-open-all').toggleClass('is-hide', false);
            section.find('.btn-t-close-all').toggleClass('is-hide', true);
        }
        if (section.find('.page-area.is-hide').length == 0) {
            section.find('.btn-t-open-all').toggleClass('is-hide', true);
            section.find('.btn-t-close-all').toggleClass('is-hide', false);
        }
    });
});

if (!Element.prototype.matches) {
  Element.prototype.matches = Element.prototype.msMatchesSelector ||
                              Element.prototype.webkitMatchesSelector;
}

if (!Element.prototype.closest) {
  Element.prototype.closest = function(s) {
    var el = this;

    do {
      if (Element.prototype.matches.call(el, s)) return el;
      el = el.parentElement || el.parentNode;
    } while (el !== null && el.nodeType === 1);
    return null;
  };
}
