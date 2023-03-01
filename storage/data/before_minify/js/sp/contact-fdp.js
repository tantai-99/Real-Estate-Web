var contactFdp = {};
$(function(){
    var $modal;
    
    app.modal = function (contents, onClose, autoRemove) {
        if (!(this instanceof app.modal)) {
            return new app.modal(contents, onClose, autoRemove);
        }
        
        this.$el = $('<div class="modal-set"><div class="modal-contents-wrap"></div></div>');
        this.$el.find('.modal-contents-wrap').append(contents);
        
        if (!$modal) {
            $modal = $('<div id="modal" class="is-hide"></div>');
            $modal.appendTo('body');
        }
        this.$el.addClass('is-hide');
        $modal.append(this.$el);
        
        this.onClose = onClose;
        this.autoRemove = autoRemove;
        
        var self = this;
        this.show = self.show();
    };
    app.modal.prototype = {
        show: function () {
            this.$el.appendTo($modal).removeClass('is-hide');
            $modal.removeClass('is-hide');
            return this;
        },
        hide: function () {
          this.$el.appendTo($modal).addClass('is-hide');
          $modal.addClass('is-hide');
          return this;
        },
        close: function (value) {
            var ret;
            if (this.onClose) {
                ret = this.onClose(value, this);
            }
            
            if (ret !== false) {
                this.$el.addClass('is-hide');
                if (!$modal.find('.modal-set:not(.is-hide)').length) {
                    $modal.addClass('is-hide');
                }
            }
            
            return this;
        },
    };
    
    app.modal.popup = function (options) {

        var $elem = $('<div class="modal-contents"><div class="modal-body"><div class="modal-body-inner"></div></div></div>');
        
        if (options.modalContentsClass) {
            $elem.addClass(options.modalContentsClass);
        }
        
        var $modalBodyInner = $elem.find('.modal-body-inner');
        $modalBodyInner.append(options.contents);
        if (options.modalBodyInnerClass) {
            $modalBodyInner.addClass(options.modalBodyInnerClass);
        }
        
        if (options.tabs) {
            var $tabs = $('<div class="modal-tab"></div>');
            $.each(options.tabs, function (i, tab) {
                $tabs.append($('<a href="javascript:;"></a>').html(tab));
            });
            
            $modalHeader.after($tabs);
            
            var tabSelector = options.tabSelector || '> *';
            $tabs.on('click', 'a', function () {
                if ($(this).hasClass('is-active')) {
                    return;
                }
                var index = $tabs.find('a').removeClass('is-active').index(this);
                $(this).addClass('is-active');
                $modalBodyInner.find(tabSelector).addClass('is-hide').eq(index).removeClass('is-hide');
                
                options.onTabChange && options.onTabChange(index);
            });
            
            if (options.tabInitialIndex) {
                $tabs.find('a').eq(options.tabInitialIndex).click();
            }
        }
        
        var modal = app.modal($elem, options.onClose, options.autoRemove);
        
        if (options.closeButton !== false) {
            // 13012 fix area link click
            $('.modal-fdp-cancel-btns > a').on('click', function(e){
                e.preventDefault();
                $("#peripheral").prop('checked', false);
                // 13001 Fix block scroll
                $('html, body').removeClass('is-not-scroll');
                modal.close(false);
                startBodyScrolling()
            });
            // 13012 fix area link click
            $('.modal-fdp-btns > a').on('click', function(e){
                e.preventDefault();
                $("#peripheral").prop('checked', true);
                // 13001 Fix block scroll
                $('html, body').removeClass('is-not-scroll');
                modal.close(false);
                startBodyScrolling()
            });
            
        }
        
        return modal;
    };
    contactFdp = function() {
        this.modal = null;

        this.getForm = function(){
            var closeButton = '<div class="close-button-modal-square"><a class="modal-close-button"><i class="i-e-delete"></i></a></div>';
            var $contents = $('<div class="section file-upload">' +
            '<div class="contact-scroll"><div class="area"><div class="left"><div class="text">ご提供する「エリア情報」</div>' +
            '<div class="contact-info">物件をお探しの際「この物件の近所はどういった場所なんだろうか・・・？」といった不安はありませんか？<br>' +
            '当社では「エリア情報の提供を希望する」にチェックをつけていただき物件へのお問い合わせをいただくことで、不動産会社ならではの近隣情報をお問い合わせとあわせてご用意しています。' +
            '<br><p>ぜひとも一緒にご覧いただき、物件選びの参考にしてください。</p></div></div>' +
            '<div class="right"><img class="contact-img" src="/sp/imgs/fdp/contact-fdp.png"><div class="contact-text">「エリア情報」のサンプル</div></div></div>' +
            '<div class="modal-fdp-btns">' +
                '<a class="btn-t-gray btn-redirect">エリア情報も希望する</a>' +
            '</div>' + '<div class="modal-fdp-cancel-btns">' +
                '<a href="javascript:;" class="btn-t-blue save-btn save">ウィンドウを閉じる</a>' +
            '</div></div>' +
            '</div>');
 
            var modal = app.modal.popup({
                modalContentsClass: 'size-xl',
                modalBodyInnerClass: 'align-top',
                autoRemove: true,
                ok: false,
                cancel: false,
                header: false,
                contents: $contents
            });

            var header = modal.$el.find('.section.file-upload');
            header.prepend(closeButton);
            $('.modal-close-button').on('click', function() {
                $('html, body').removeClass('is-not-scroll');
                modal.close(false);
                startBodyScrolling()
            });
        }
    };

    function freezeScroll(e) {
        e.preventDefault();
    }

    function stopBodyScrolling () {
        $('body').css({
            position: "fixed",
            top: '-' + window.scrollY + 'px',
            left: 0,
            right: 0
        });
    }

    function startBodyScrolling() {
        var scrollY = document.body.style.top;
        document.body.style.position = '';
        document.body.style.top = '';
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
    }

    $('a.js-fdp-modal').on('click', function (e) {
        e.preventDefault();
        // 13001 Fix block scroll
        $('html, body').addClass('is-not-scroll');
        var up = new contactFdp();
        up.getForm();
        stopBodyScrolling();
    });
});
