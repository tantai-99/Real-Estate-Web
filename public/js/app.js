(function () {
	'use strict';
	
	$(function () {
		function raf(cb) {
			if (window.requestAnimationFrame) {
				window.requestAnimationFrame(cb);
			}
			else {
				setTimeout(cb, 100);
			}
		}
		function af() {
			$('#modal .js-scroll-container').each(function () {
				var $this = $(this);
				if ($this.closest('.modal-set.is-hide').length != 0) {
					return;
				}

				var $window = $this.closest('.modal-contents');
				var maxModalHeight = $(window).height() - 40;
				var otherHeight = $window.outerHeight() - $this.outerHeight();
				var maxContentHeight = Math.max(Math.round(maxModalHeight - otherHeight), 100);
				var stepContent = $this[0].querySelector('.step-contents:not(.is-hide)');
				var contentStyle = null;
				var contentHeight = 0;
				if (stepContent === null) {
					var max = parseInt($this.attr('data-scroll-container-max-height'));
					if(!isNaN(max)) {
						if ($this[0].classList.contains('error-article')) {
							var listCount = $this[0].querySelectorAll('li').length;
							$this.height(Math.min((listCount * 30) + 100, max, Math.round(maxModalHeight - 120)));
						} else {
							if (otherHeight + $this.innerHeight() >= maxModalHeight) {
								 var maxheight = maxModalHeight - 120;
                                if ($window.find('.sample-select').length) {
                                    maxheight = maxModalHeight - 140 - $window.find('.sample-select').outerHeight();
                                }
								if ($window.find('.img-folder-list').length) {
                                    maxheight = maxModalHeight - 160 - $window.find('.img-category-list').outerHeight();
								}
                                $this.height(Math.min(max, Math.round(maxheight)));
                            } else {
								if ($this.height() >= max) {
									$this.height(max)
								} else {
									$this.height('auto');
								}
							}
						}
					}
				} else if (stepContent.classList.contains('add-step0')) {
					var listCount = stepContent.querySelectorAll('.item-normal > .item-page label').length;
					$this.height(listCount == 1 ? 100 : Math.min((listCount * 27) + 130, maxContentHeight, 500));
					contentStyle = stepContent.querySelector('.item-normal').style;
					contentHeight = $this.height() - 90;
				} else if (stepContent.classList.contains('add-step1')) {
					$this.height(listCount == 0 ? 100 : Math.min(stepContent.querySelector('.tb-basic').clientHeight + 100, maxContentHeight, 500));
					contentStyle = stepContent.querySelector('.item-content').style;
					contentHeight = $this.height() - 60;
				} else if (stepContent.classList.contains('add-step2')) {
					var listCount = stepContent.querySelectorAll('.item-page > .item-normal label').length;
					$this.height(listCount == 0 ? 100 : Math.min((listCount * 25) + 110, maxContentHeight, 500));
					contentStyle = stepContent.querySelector('.item-normal').style;
					contentHeight = $this.height() - 90;
				} else if (stepContent.classList.contains('add-step3')) {
					var listCount = stepContent.querySelectorAll('.item-page label').length;
					var headCount = stepContent.querySelectorAll('.heading-area').length;
					$this.height(Math.min((listCount * 25) + (headCount * 65) + 85, maxContentHeight, 500));
					contentStyle = stepContent.querySelector('.item-content').style;
					contentHeight = $this.height() - 60;
				} else if (stepContent.classList.contains('add-step4')) {
					var max = parseInt($this.attr('data-scroll-container-max-height'));
					$this.height(Math.min(max, Math.round(maxModalHeight - 120)));
					contentStyle = stepContent.querySelector('.text-content').style;
					contentHeight = $this.height() - 70;
				}

				if (contentStyle !== null) {
					contentStyle.height = contentHeight + 'px';
					contentStyle.overflowY = 'auto';
				}
			});
			raf(af);
		}
		af();
		
		$('body').on('click', '.js-confirm-leave-edit', function () {
			var ref = $(this).attr('href');
			app.modal.confirm('注意', '設定内容は保存されません。\n移動してよろしいですか？', function (ret) {
				if (ret) {
					location.href = ref;
				}
			});
			return false;
		});
	});
	
	var app = window.app = {};

	var _hDiv = $('<div/>');
	app.h = function (str) {
		return _hDiv.text(str).html();
	};
  
  app.getToken = function () {
    return $('meta[name="csrf-token"]').attr('content');
  };
  
  app.getParameter = function(name, url) {
    if (!url) url = window.location.href;
      name = name.replace(/[\[\]]/g, '\\$&');
      var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
          results = regex.exec(url);
      if (!results) return null;
      if (!results[2]) return '';
      return decodeURIComponent(results[2].replace(/\+/g, ' '));
  };

	var $modal;
	
	/**
	 * @param {string|HTMLElement|jQuery} contents
	 * @param {function=} onClose
	 * @param {boolean=} autoRemove
	 * @returns {app.modal}
	 */
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
		this.$el.on('app-modal-closeall', function () {
			self.close(false);
		});
	};
	app.modal.prototype = {
		show: function () {
			this.$el.appendTo($modal).removeClass('is-hide');
			$modal.removeClass('is-hide');
			$('body').addClass('show-modal');
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
					$('body').removeClass('show-modal');
				}
				
				if (this.autoRemove !== false) {
					this.remove();
				}
			}
			
			return this;
		},
		
		remove: function () {
			this.$el.trigger('app-modal-remove').off().remove();
			return this;
		}
	};
	
	app.modal.closeAll = function () {
		if ($modal) {
			$modal.trigger('app-modal-closeall');
		}
	};
	
	/**
	 * @param {object} options
	 *   {string} title
	 *   {string|HTMLELement|jQuery} contents
	 *   {boolean=} closeButton
	 *   {function=} onClose
	 *   {string=} modalBodyInnerClass
	 *   {string=} modalContentsClass
	 *   {Array.<string>=} tabs
	 *   {string=} tabSelector
	 *   {function=} onTabChange
	 *   {boolean|string=} ok default OK
	 *   {boolean|string=} cancel default キャンセル
	 *   {boolean=} autoRemove default true
	 * @returns {app.modal}
	 */
	app.modal.popup = function (options) {

		var $elem = $('<div class="modal-contents"><div class="modal-header"></div><div class="modal-body"><div class="modal-body-inner"></div></div></div>');
		
		if (options.modalContentsClass) {
			$elem.addClass(options.modalContentsClass);
		}

		var showModalHeader = true;

	    if(options.hasOwnProperty('header')){
	      showModalHeader = options.header;
	    }

		var $modalHeader = $elem.find('.modal-header');

		if(showModalHeader){
	      if (options.title) {
	        $modalHeader.append($('<h2/>').text(options.title));
	      }
	      else {
	        $modalHeader.addClass('tit-none');
	      }
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
				if ($modalBodyInner.find('.img-folder-list .img-list').outerHeight() > 0) {
					$modalBodyInner.find('.img-folder-list .img-list').height($modalBodyInner.find('.img-folder-list .img-list').outerHeight());
				}
				
				options.onTabChange && options.onTabChange(index);
			});
			
			if (options.tabInitialIndex) {
				$tabs.find('a').eq(options.tabInitialIndex).click();
			}
		}
		
		var modal = app.modal($elem, options.onClose, options.autoRemove);
		
		var $close;
		if (options.closeButton !== false) {
			if(showModalHeader){
				var $closeContainer = $('<div class="modal-close"></div>');
				$close = $('<a class="btn-modal" href="javascript:;"><i class="i-e-delete"></i>');
				$closeContainer.append($close);
				$modalHeader.append($closeContainer);
				
				$close.on('click', function() {
					modal.close(false);
				});
			}

			$('body').on('click', '.modal-close-button', function(e){
				e.preventDefault();
				modal.close(false);
			});
		}
		
		if (options.ok === undefined) {
			options.ok = 'OK';
		}
		if (options.cancel === undefined) {
			options.cancel = 'キャンセル';
		}
		
		var $ok;
		var $cancel;
		if (options.ok || options.cancel) {
			var $modalBtns = $('<div class="modal-btns"></div>').appendTo(modal.$el.find('.modal-body-inner'));
			if (options.cancel) {
				$cancel = $('<a class="btn-t-gray" href="javascript:;"></a>').text(options.cancel);
				$cancel.on('click', function () {
					modal.close(false);
				});
				$modalBtns.append($cancel);
			}
			if (options.ok) {
				$ok = $('<a class="btn-t-blue" href="javascript:;"></a>').text(options.ok);
				$ok.on('click', function () {
					modal.close(true);
				});
				$modalBtns.append($ok);
			}
		}
		
		modal.$el.on('app-modal-remove', function () {
			$ok && $ok.off();
			$cancel && $cancel.off();
			$close && $close.off();
		});
		
		return modal;
	};
	
	/**
	 * @param {object} options
	 *   {string} title
	 *   {string} message
	 *   {Array.<object>} links
	 *     {string} title
	 *     {string} url
	 *     {string=} target
	 *   {boolean|string=} ok default OK
	 *   {boolean|string=} cancel default キャンセル
	 *   {boolean=} closeButton
	 *   {function=} onClose
	 *   {boolean=} autoRemove default true
	 * @returns {app.modal}
	 */
	app.modal.message = function (options) {

		if(!options.type){
			options.type = '';
		}

		options.contents = $('<div class="modal-message"><strong>'+ app.h(options.message).replace(/\r?\n/, '<br>') +'</strong></div>');
		
		var modal = app.modal.popup(options);
		
		if (options.links) {
			var $message = modal.$el.find('.modal-message');
			$.each(options.links, function (i, link) {
				var $a = $('<a class="i-s-link"></a>').text(link.title || link.url).attr('href', link.url);
				if (link.target) {
					$a.attr('target', link.target);
				}
				$message.append($('<p/>').append($a));
			});
		}
		
		return modal.show();
	};
	
	app.modal.confirm = function (title, message, onClose, closeButton) {
        var options = {
			title: title,
			message: message,
			onClose: onClose
        };
        if (typeof closeButton != 'undefined') {
            options.closeButton = closeButton;
        }
		return app.modal.message(options);
	};
	
	app.modal.confirmSample = function (title, message, onClose) {
		var modal = app.modal.message({
			title: title,
			message: message,
			onClose: onClose
		});
		
		modal.$el.find('strong').after($('<p/>').html(
			'雛形（定型文例）のご利用について'
		).add($('<p/>').css({'font-size':'12px'}).html(
			'雛形を利用する際は貴社独自のコンテンツページとなるように編集することを推奨いたします。<br><br>'+
			'雛形を使用したことによって生じたトラブル、いかなる損害に対して当社は一切責任を負いませんので記載されている内容は必ず確認していただき、適宜編集してご利用ください。'
		)));
		
		return modal;
	};

  app.modal.success = function (title, message, onClose) {
    return app.modal.message({
      title: title,
      message: message,
      cancel: false,
      onClose: onClose,
      type: 'success'
    });
  };

  app.modal.error = function (title, message, onClose) {
    return app.modal.message({
      title: title,
      message: message,
      cancel: false,
      onClose: onClose,
      type: 'error'
    });
  };

	app.modal.alert = function (title, message, onClose) {
		return app.modal.message({
			title: title,
			message: message,
			cancel: false,
			onClose: onClose
		});
    };
    
    app.modal.alertPublishArticle = function (title, message, onClose) {
		return app.modal.message({
			title: title,
			message: message,
            cancel: false,
            closeButton: false,
			onClose: onClose
		});
	};
	
	app.modal.closeButtomAlert = function ( title, message, closeButton, onClose ) {
		return app.modal.message({
			title: title,
			message: message,
			cancel: false,
			closeButton: closeButton,
			onClose: onClose,
			ok: '閉じる'
		});
	};

    app.modal.alertForReload = function (title, message, onClose) {
        return app.modal.message({
            title: title,
            message: message,
            cancel: false,
            onClose: onClose,
            ok:'再読み込み'
        });
	};
	
	app.modal.alertBanDeletePage = function (message, description, onClose) {
		var modal = app.modal.message({
			message: message,
			onClose: onClose,
			cancel: false,
		});
		
		modal.$el.find('strong').after($('<p/>').html(description).add($('<p/>')));
		
		return modal;
	};

	//-------------------------------------------------------------------------------------------
	app.modal.categoryEdit = function (token, onUpdate) {
		var options = {
			title: 'カテゴリ編集',
			contents: '<dl class="add-category"><dt>カテゴリ追加</dt><dd><div><input class="watch-input-count" type="text" maxlength="20" placeholder="カテゴリ名を入れてください"><span class="input-count"></span></div><div><a class="btn-t-blue size-s add">追加</a></div></dd></dl><dl class="edit-category"><dt>カテゴリ一覧</dt><dd class="js-scroll-container" data-scroll-container-max-height="400"><ul class="edit-category-list"></ul></dd></dl><div class="errors"></div>',
			autoRemove: false,
			modalBodyInnerClass: 'align-top',
			modalContentsClass: 'size-l',
			ok: '登録'
		};
		
		var modal = app.modal.popup(options);
		
		var $addCategory = modal.$el.find('.add-category');
		var $addCategoryInput = $addCategory.find('input').change();
		var $categoriesContainer = modal.$el.find('.edit-category dd');
		var $categories = $categoriesContainer.find('ul');
		
		$categories.on('click', '.i-e-delete', function () {
			$(this).parents('ul.action').parent().remove();
		});
		$categories.on('click', '.i-e-up', function () {
			var $li = $(this).parents('ul.action').parent();
			if ($li.prev().length) {
				$li.prev().before($li);
			}
		});
		$categories.on('click', '.i-e-down', function () {
			var $li = $(this).parents('ul.action').parent();
			if ($li.next().length) {
				$li.next().after($li);
			}
		});
		
		function _addCategory(val, id) {
			var $input = $('<input type="text" value="" maxlength="20" class="watch-input-count" />');
			if (id) {
				$input.attr('data-id', id);
			}
			var $div = $('<div/>');
			$div.append($input);
			$div.append('<span class="input-count"></span>');
			
			
			var $li = $('<li/>')
					.append($div)
					.append('<ul class="action"><li><a href="javascript:;"><i class="i-e-up"></i></a></li><li><a href="javascript:;"><i class="i-e-down"></i></a></li><li><a href="javascript:;"><i class="i-e-delete"></i></a></li></ul>');
			$categories.append($li);
			
			_markFirstLast();
			
			$input.val(val).change();
		}
		
		function _markFirstLast() {
			var $lis = $categories.find('>li');
			$lis.removeClass('first last');
			$lis.filter(':first').addClass('first');
			$lis.filter(':last').addClass('last');
		}
		
		$addCategory.find('.add').on('click', function () {
			var val = $addCategoryInput.val();
			if (!val) {
				return;
			}
			
			_addCategory(val);
			
			$addCategoryInput.val('').change();
		});
		
		modal.categories = [];
		modal.setCategories = function (categories) {
			modal.categories = categories;
			$categories.empty();
			$.each(categories, function (i, category) {
				_addCategory(category.name, category.id);
			});
		};
		modal.resetCategories = function () {
			modal.setCategories(modal.categories);
		};
		
		var show = modal.show;
		modal.show = function () {
			modal.resetCategories();
			modal.$el.find('.errors').empty();
			show.call(modal);
		};
		
		var $ok = modal.$el.find('.ok');
		var $errors = modal.$el.find('.errors');
		modal.onClose = function (ret) {
			if ($ok.hasClass('is-disable')) {
				return false;
			}
			
			if (!ret) {
				return;
			}
			
			var categories = [];
			
			var sort = 0;
			$categories.find('input').each(function(i, elem) {
				var $this = $(this);
				if (!$this.attr('data-id') && ($this.val() === '' || $this.val() === undefined)) {
					$this.parent().parent().remove();
					return;
				}
				
				categories.push({
					id: $this.attr('data-id'),
					name: $this.val(),
					sort: sort++
				});
			});
			
			$ok.addClass('is-disable');
			$errors.empty();
			$categories.find('.is-error').removeClass('is-error');
			
			app.api('/site-setting/api-save-image-category', {'_token': token, categories: categories}, function (res) {
				if (res.errors) {
					if (typeof res.errors == 'object') {
						$.each(res.errors, function (index, errors) {
							$categories.find('input:eq('+index+')').addClass('is-error');
						});
						
						$errors.append('<p>入力に誤りがあります。</p>');
					} else {
						app.modal.alert('', res.errors);
					}
				}
				else {
					app.modal.alert('', '設定を保存しました。', function () {
						modal.setCategories(res.categories);
						onUpdate && onUpdate(res);
						modal.close();
					});
				}
			})
			.always(function () {
				$ok.removeClass('is-disable');
			});
			
			return false;
		};
		
		return modal;
	};
	
	//-------------------------------------------------------------------------------------------
	app.modal.categoryFile2Edit = function ( token, onUpdate ) {
		var options = {
			title				: 'ファイルカテゴリ編集'	,
			contents			: '<dl class="add-category"><dt>ファイルカテゴリ追加</dt><dd><div><input class="watch-input-count" type="text" maxlength="20" placeholder="ファイルカテゴリ名を入れてください"><span class="input-count"></span></div><div><a class="btn-t-blue size-s add">追加</a></div></dd></dl><dl class="edit-category"><dt>ファイルカテゴリ一覧</dt><dd class="js-scroll-container" data-scroll-container-max-height="400"><ul class="edit-category-list"></ul></dd></dl><div class="errors"></div>',
			autoRemove			: false						,
			modalBodyInnerClass	: 'align-top'				,
			modalContentsClass	: 'size-l'					,
			ok					: '登録'
		};
		
		var modal = app.modal.popup( options) ;
		
		var $addCategory			= modal.$el.find( '.add-category' )		;
		var $addCategoryInput		= $addCategory.find( 'input' ).change()	;
		var $categoriesContainer	= modal.$el.find( '.edit-category dd' )	;
		var $categories				= $categoriesContainer.find( 'ul' )		;
		
		$categories.on( 'click', '.i-e-delete', function () {
			$(this).parents( 'ul.action' ).parent().remove() ;
		});
		$categories.on( 'click', '.i-e-up', function () {
			var $li = $(this).parents( 'ul.action' ).parent() ;
			if ( $li.prev().length ) {
				$li.prev().before( $li ) ;
			}
		});
		$categories.on( 'click', '.i-e-down', function () {
			var $li = $(this).parents( 'ul.action' ).parent() ;
			if ( $li.next().length ) {
				$li.next().after( $li ) ;
			}
		});
		
		function _addCategory( val, id ) {
			var $input = $('<input type="text" value="" maxlength="20" class="watch-input-count" />') ;
			if ( id ) {
				$input.attr( 'data-id', id ) ;
			}
			var $div = $('<div/>') ;
			$div.append( $input ) ;
			$div.append( '<span class="input-count"></span>' ) ;
			
			var $li = $('<li/>')
					.append( $div )
					.append('<ul class="action"><li><a href="javascript:;"><i class="i-e-up"></i></a></li><li><a href="javascript:;"><i class="i-e-down"></i></a></li><li><a href="javascript:;"><i class="i-e-delete"></i></a></li></ul>') ;
			$categories.append( $li ) ;
			
			_markFirstLast() ;
			
			$input.val( val ).change() ;
		}
		
		function _markFirstLast()
		{
			var $lis = $categories.find( '>li' ) ;
			$lis.removeClass( 'first last' ) ;
			$lis.filter( ':first' ).addClass( 'first' ) ;
			$lis.filter( ':last'  ).addClass( 'last'  ) ;
		}
		
		$addCategory.find( '.add' ).on( 'click', function () {
			var val = $addCategoryInput.val() ;
			if ( !val ) {
				return ;
			}
			
			_addCategory( val ) ;
			
			$addCategoryInput.val( '' ).change() ;
		});
		
		modal.categories = [] ;
		modal.setCategories	= function ( categories ) {
			modal.categories = categories ;
			$categories.empty() ;
			$.each( categories, function ( i, category ) {
				_addCategory( category.name, category.id ) ;
			});
		};
		modal.resetCategories = function () {
			modal.setCategories( modal.categories ) ;
		};
		
		var show = modal.show ;
		modal.show = function () {
			modal.resetCategories() ;
			modal.$el.find( '.errors' ).empty() ;
			show.call( modal ) ;
		};
		
		var $ok		= modal.$el.find( '.ok'		) ;
		var $errors = modal.$el.find( '.errors'	) ;
		modal.onClose = function ( ret ) {
			if ( $ok.hasClass( 'is-disable' ) ) {
				return false ;
			}
			
			if ( !ret ) {
				return ;
			}
			
			var categories = [] ;
			
			var sort = 0 ;
			$categories.find( 'input' ).each( function( i, elem ) {
				var $this = $(this) ;
				if ( !$this.attr( 'data-id' ) && ( $this.val() === '' || $this.val() === undefined ) ) {
					$this.parent().parent().remove() ;
					return ;
				}
				
				categories.push({
					id	: $this.attr('data-id')	,
					name: $this.val()			,
					sort: sort++
				});
			});
			
			$ok.addClass( 'is-disable' ) ;
			$errors.empty() ;
			$categories.find( '.is-error' ).removeClass( 'is-error' ) ;
			
			app.api( '/site-setting/api-save-file2-category', {'_token': token, categories: categories}, function ( res ) {
				if ( res.errors ) {
					$.each( res.errors, function ( index, errors ) {
						$categories.find( 'input:eq(' + index + ')' ).addClass( 'is-error' ) ;
					});
					
					$errors.append( '<p>入力に誤りがあります。</p>' ) ;
				}
				else {
					app.modal.alert( '', '設定を保存しました。', function () {
						modal.setCategories( res.categories ) ;
						onUpdate && onUpdate( res ) ;
						modal.close() ;
					});
				}
			})
			.always( function () {
				$ok.removeClass( 'is-disable' ) ;
			});
			
			return false ;
		};
		
		return modal ;
	};
	
	//-------------------------------------------------------------------------------------------
	app.modal.hpFile2 = function ( token, onClose ) {
		var $contents = $('<div class="section img-folder">' +
				'<form data-api-action="/site-setting/api-save-file2">' +
				'<input type="hidden" name="_token" value="'+app.h(token)+'">' +
				'<div class="f-file-upload">' + 
					'<input type="hidden" id="hp_file2_content_id" data-file-type="file2" data-view="/file/hp-file2" data-upload-to="/api-upload/hp-file2" class="upload-file-id" value="" name="hp_file2_content_id">' +
					'<div class="up-img">' +
						'<div class="up-btn">' +
							'<input type="file" name="file" />' +
						'</div>' +
						
						'<div class="up-area is-hide">' +
							'または、ファイルをドロップしてください。' +
						'</div>' +
		
						'<small>pdf,xls,xlsx,doc,docx,ppt,pptx（5MBまで）</small><br>' +
						'<small>※著作権又はその他の知的財産権で保護されているファイルを使用する場合は、当該権利者の許諾を得るものとし、当該権利者の許諾を得ていないものは使用しないでください。</small>' +
					'</div>' +
					
					'<div class="up-preview">' +
						'<a href="javascript:;" class="i-e-delete is-hide"></a>' +
						'<p></p>' +
					'</div>' +
				'</div>' +
		
				'<div class="img-up-info">' +
					'<dl>' +
						'<dt>ファイルタイトル<i class="i-l-require">必須</i></dt>' +
						'<dd>' +
							'<div class="is-require input-img-title">' +
								'<input id="title" name="title" class="watch-input-count" type="text" maxlength="30">' +
								'<span class="input-count"></span>' +
							'</div>' +
						'</dd>' +
					'</dl>' +
		
					'<dl>' +
						'<dt>ファイルカテゴリ<a href="javascript:;" class="i-s-link edit-category-btn">カテゴリ編集</a></dt>' +
						'<dd>' +
							'<select name="category_id"><option value="0">選択してください</option></select>' +
						'</dd>' +
					'</dl>' +
				'</div>' +
				'<div class="errors"></div>' +
				'<div class="modal-btns">' +
					'<a href="javascript:;" class="btn-t-gray close-btn">キャンセル</a>' +
					'<a href="javascript:;" class="btn-t-blue save">登録</a>' +
				'</div>' +
				'</form>' +
			'</div>' +
			'<div class="section img-folder">' +
				'<div class="img-category-list">' +
					'<h3>ファイルカテゴリ</h3>' +
					'<a href="javascript:;">全て</a>' +
					'<ul>' +
					'</ul>' +
				'</div>' +
		
				'<div class="img-folder-list js-scroll-container" data-scroll-container-max-height="300">' +
					'<ul class="img-list">' +
					'</ul>' +
					'<ul class="paging" data-page-size="24">' +
					'</ul>' +
				'</div>' +
				'<div class="modal-btns">' +
					'<a href="javascript:;" class="btn-t-gray close-btn">キャンセル</a>' +
					'<a href="javascript:;" class="btn-t-blue select-btn">決定</a>' +
				'</div>' +
			'</div>'
		) ;
		
		var modal = app.modal.popup({
			title				: 'ファイル'							,
			modalContentsClass	: 'size-l'								,
			modalBodyInnerClass	: 'align-top'							,
			tabs				: ['ファイルを登録', 'ファイルを選択']	,
			tabInitialIndex		: 1										,
			autoRemove			: false									,
			ok					: false									,
			cancel				: false									,
			contents			: $contents								,
			onClose				: onClose
		});
		
		var $hpFile2	= $contents.eq( 1 )	;
		var hpFile2		= app.hpFile2( $hpFile2 ) ;
		
		var $hpFile2List = $hpFile2.find('.img-list');
		
		// ok cancel 処理
		var $selectBtn = $hpFile2.find('.select-btn');
		$selectBtn.on( 'click', function () {
			if ( $(this).hasClass( 'is-disable' ) ) {
				return false ;
			}
			
			var isMemberOnly	= app.page.info[ 'isMemberOnly' ]								;		// 現在のページ
			var $selected		= $( $hpFile2List.find( '.is-active:eq(0)' )[ 0 ] )	;
			var alertMessage	= ''															;
			if ( $selected.hasClass( 'in-use' ) )
			{
				if ( $selected.hasClass( 'is-member-only' ) )
				{
					alertMessage	= isMemberOnly ? '' : "会員さま専用ページ配下に設置しているページで使用中です。\会員さま専用ページ配下以外で利用する場合は、別途ファイルを登録し直し、ご利用ください。"	;
				}
				else
				{
					alertMessage	= isMemberOnly ? "通常のページに設置しているファイルは会員さま専用ページ配下には設置できません。\会員さま専用ページ配下で利用する場合は、別途ファイルを登録し直し、ご利用ください。" : ''	;
				}
			}
			if ( alertMessage != '' )
			{
				app.modal.closeButtomAlert( '', alertMessage, true, function( ret ){} ) ;
				return	false	;
			}
			
			modal.close( hpFile2.getFile2ById( $selected.attr( 'data-id' ) ) );
		});
		
		$hpFile2List.on( 'click', '.img-list-thumb', function () {
			var $li = $(this).parent() ;
			$li.addClass( 'is-active' ).siblings().removeClass( 'is-active' ) ;
			$selectBtn.removeClass( 'is-disable' ) ;
			return false ;
		}) ;
		hpFile2.onRender = function () {
			$selectBtn.toggleClass( 'is-disable', !$hpFile2List.find( '.is-active' ).length ) ;
		} ;
		
		$contents.find( '.close-btn' ).on( 'click', function () {
			modal.close( false ) ;
		}) ;
		
		var $hpFile2Upload = $contents.eq( 0 ) ;
		$hpFile2Upload.initUploadHPFile2( function ( data ) {
			hpFile2.addFile2( data.item ).filter().render() ;
		}) ;
		
		var $categorySelect = $hpFile2Upload.find( 'select[name="category_id"]' ) ;
		
		function _updateCategories( categories ) {
			var $option = $categorySelect.find( 'option:eq(0)' ) ;
			$categorySelect.empty().append( $option ) ;
			$.each( categories, function ( i, category ) {
				$categorySelect.append( $('<option/>').val( category.id ).text( category.name ) ) ;
			}) ;
			
			hpFile2.setCategories( categories ) ;
		}
		var categoryModal = app.modal.categoryFile2Edit( token, function ( data ) {
			_updateCategories( data.categories ) ;
		}) ;
		
		$hpFile2Upload.find( '.edit-category-btn' ).on( 'click', function () {
			categoryModal.show() ;
		}) ;
		
		var xhr ;
		var show = modal.show ;
		modal.show = function ( tabIndex ) {
			xhr = null ;
			if ( !xhr ) {
				xhr = app.api( '/site-setting/api-get-file2', null, function ( data ) {
					_updateCategories( data.categories ) ;
					categoryModal.setCategories( data.categories ) ;
					hpFile2.setFile2s( data.file2s ).filter().render() ;
				});
			}
			
			if ( tabIndex !== undefined ) {
				modal.$el.find( '.modal-tab a' ).eq( tabIndex ).click() ;
			}
			
			// エラーメッセージをクリア
			$('.modal-set').find('.errors').empty() ;
			
			show.call( modal ) ;
			return modal ;
		};
		
		return modal;
	};

    app.modal.hasResever = function(message) {
				var modal = app.modal.popup({
					title: '',
					contents: $('<div class="modal-message"><strong>'+ app.h(message).replace(/\r?\n/, '<br>') +'</strong></div>'),
					header: false,
					cancel: false,
					ok: 'OK'
				});
		modal.show();
    }

    app.modal.confirmFirstAddPageArticle = function() {
        var contents = '' +
                    '<div style="margin: 40px 8px;">' +
                        '<p style="margin-bottom: 0;">すべてのページに反映が必要となる内容の追加・修正があります。</p>' +
                        '<p style="margin-bottom: 0;">この内容を保存すると、次回公開処理時に現在修正中のページ（※）が自動的に更新されます。</p>' +
                        '<p style="margin-bottom: 25px;">※新規ページを除く本番サイトへ未反映の内容があるページ</p>' +
                        '<p>内容を保存してよろしいですか？</p>' +
                    '</div>';
        var modal = app.modal.popup({
            contents: contents,
            autoRemove: false
        }).show();

        return modal;
    }
	
	app.hpFile2 = function( $container, isEdit ) {
		if ( !( this instanceof app.hpFile2 ) ) {
			return new app.hpFile2( $container, isEdit ) ;
		}
		
		this.$container         = $container											  ;
		this.$categoryContainer = $container.find(				'.img-category-list'	) ;
		this.$categories        = this.$categoryContainer.find(	'ul'					) ;
		this.$file2Container    = $container.find(				'.img-folder-list'		) ;
		this.$file2s            = this.$file2Container.find(	'ul.img-list'			) ;
		this.$paging            = this.$file2Container.find(	'ul.paging'				) ;
		
		this.isEdit			= isEdit	;
		
		this.categories		= []		;
		this.categoryIds	= []		;
		
		this.file2s			= []		;
		this.filtered		= []		;
		this.pageSize		= 0			;
		this.pagingLinks	= 6			;
		this.page			= 1			;
		
		var self			= this		;
		
		if ( this.$paging.length )
		{
			this.pageSize = parseInt( this.$paging.attr( 'data-page-size' ) ) ;
			this.$paging.on( 'click', 'a', function () {
				var $page = $(this).parent() ;
				if ( $page.hasClass( 'is-invisible' ) || $page.hasClass( 'is-active' ) )
				{
					return ;
				}
				self.render( $page.attr( 'data-page' ) ) ;
			}) ;
		}
		
		this.$categoryContainer.on( 'click', 'a', function () {
			self.$categories.find( '.is-active' ).removeClass( 'is-active' ) ;
			var $category = $(this).parent() ;
			if ( $category.attr( 'data-category-id' ) )
			{
				$category.toggleClass( 'is-active' ) ;
			}
			self.filter().render() ;
		});
	};
	
	app.hpFile2.prototype = {
		setCategories: function( categories ) {
			var self = this;
			
			var categoryIds = [] ;
			var $tmp = $( '<ul/>' ) ;
			
			function _addCategory( category ) {
				categoryIds.push( parseInt( category.id ) ) ;
				
				var $li = $( '<li/>' ) ;
				$li.append($(		'<a href="javascript:;"></a>').text( category.name ) ) ;
				$li.toggleClass(	'is-active', !!self.$categories.find( '.is-active[data-category-id="'+category.id+'"]' ).length ) ;
				$li.attr(			'data-category-id', category.id ) ;
				$tmp.append( $li ) ;
			}
			
			$.each( categories, function ( i, category ) {
				_addCategory( category ) ;
			});
			
			// 未登録
			_addCategory({
				id		: 0			,
				name	: '未登録'
			});
			
			this.categories		= categories	;
			this.categoryIds	= categoryIds	;
			this.$categories.html( $tmp.html() ) ;
			
			return this ;
		},
		
		getCategories: function () {
			return this.categories ;
		},
		
		setFile2s: function ( file2s ) {
			this.file2s = file2s ;
			return this ;
		},
		
		addFile2: function ( file2 ) {
			this.file2s.push( file2 ) ;
			return this ;
		},
		
		updateFile2Data: function ( file2Id, data ) {
			file2Id = parseInt( file2Id ) ;
			$.each( this.file2s, function ( i, file2 ) {
				if ( file2Id === parseInt( file2.id ) ) {
					$.extend( file2, data ) ;
					return false ;
				}
			}) ;
			return this ;
		},
		
		getFile2ById: function ( file2Id ) {
			file2Id = parseInt( file2Id ) ;
			
			var ret ;
			$.each( this.file2s, function ( i, file2 ) {
				if ( parseInt( file2.id ) === file2Id ) {
					ret = $.extend( {}, file2 ) ;
					return false ;
				}
			}) ;
			return ret ;
		},
		
		removeFile2ById: function ( file2Id ) {
			var idx ;
			$.each( this.file2s, function ( i, file2 ) {
				if ( parseInt( file2.id ) === parseInt( file2Id ) ) {
					idx = i ;
					return false ;
				}
			}) ;
			
			if (idx === undefined) {
				return this;
			}
			
			this.file2s.splice( idx, 1) ;
			return this ;
		},
		
		filter: function () {
			var self = this;
			
			var actives = [] ;
			this.$categories.find( '.is-active' ).each( function () {
				actives.push( parseInt( $(this).attr( 'data-category-id' ) ) ) ;
			}) ;
			if ( !actives.length ) {
				this.filtered = this.file2s ;
				return this ;
			}
			
			var isCheckedNoCategory = app.arrayIndexOf( 0, actives ) >= 0 ;
			
			var filtered = [] ;
			$.each( this.file2s, function ( i, data ) {
				var categoryId = parseInt( data.category_id ) ;
				if (
					( app.arrayIndexOf( categoryId, actives				) >= 0 ) ||
					( app.arrayIndexOf( categoryId, self.categoryIds	) <  0   && isCheckedNoCategory )
				) {
					filtered.push( data ) ;
				}
			});
			
			this.filtered = filtered ;
			
			return this ;
		},
		
		buildPaging: function () {
			if ( !this.pageSize ) {
				return this ;
			}
			
			var pages = this.getPages() ;
			var start = this.page - Math.floor(	( this.pagingLinks - 1 ) / 2 ) ;
			var end   = this.page + Math.ceil(	( this.pagingLinks - 1 ) / 2 ) ;
			if ( start < 1 ) {
				end  += 1 - start ;
				start = 1 ;
			}
			else if ( end > pages ) {
				start -= end - pages
				end = pages ;
			}
			start = Math.max(	  1, start	) ;
			end   = Math.min( pages, end	) ;
			
			var _html = '' ;
			// prev
			_html += '<li class="prev'+(this.page > 1?'':' is-invisible')+'" data-page="'+(this.page - 1)+'"><a href="javascript:;"></a></li>';
			
			for (var i = start; i <= end; i++) {
				_html += '<li class="'+(this.page === i?'is-active':'')+'" data-page="'+i+'"><a href="javascript:;">'+i+'</a></li>';
			}
			
			// next
			_html += '<li class="next'+(this.page < pages?'':' is-invisible')+'" data-page="'+(this.page + 1)+'"><a href="javascript:;"></a></li>';
			
			this.$paging.html( _html ) ;
			
			return this ;
		},
		
		getPages: function () {
			if ( !this.pageSize || !this.filtered.length ) {
				return 1 ;
			}
			
			return Math.ceil( this.filtered.length / this.pageSize ) ;
		},
		
		render: function ( page ) {
			// id 降順にソート
			this.filtered.sort(function (a, b) {
				if (a.id < b.id) return 1;
				if (a.id > b.id) return -1;
				return 0;
			});
			
			this.page = Math.max( 1, Math.min( parseInt( page || this.page ) || 1, this.getPages() ) ) ;
			var offset	;
			var count	;
			if ( this.pageSize ) {
				offset = ( this.page - 1 ) * this.pageSize ;
				count  = this.pageSize ;
			}
			else {
				offset = 0;
				count  = this.filtered.length;
			}
			
			var data		;
			var isActive	;
			var _html = ''	;
			var _editHtml	;
			for ( var i = offset, l = Math.min( offset + count, this.filtered.length ) ; i < l ; i++ ) {
				data = this.filtered[ i ] ;
				
				if ( this.isEdit ) {
					_editHtml = '<div class="pull action-menu"><a href="javascript:void(0);"><i class="i-e-set">操作</i></a><ul><li><a href="javascript:void(0);"><i class="i-e-list"></i>使用ページ</a></li><li><a href="/site-setting/download-file2?id='+data.id+'"><i class="i-e-dl"></i>ダウンロード</a></li><li><a href="javascript:void(0);"><i class="i-e-move"></i>カテゴリー移動</a></li><li><a href="javascript:void(0);"><i class="i-e-delete"></i>削除</a></li></ul></div>';
				} else {
					_editHtml = '' ;
				}
				isActive = !!this.$file2s.find('.is-active[data-id="'+data.id+'"]').length ;
				var imageIconTag	= this.getImageIconTag( data )	;
				var isMemberOnly	= data.member					;
				var inUse			= data.in_use					;
				var dataTitleTag	= ''							;
				if ( isMemberOnly ) {
					dataTitleTag	= '<span class="clearfix"><span class="i-padlock"><img src="/images/icon/padlock.png" width="20" height="20" /><span class="i-padlock-note">会員さま専用ページで使用しております。会員さま専用ページ以外では使用ができません</span></span><span class="i-text"><p>' + app.h(data.title) + '</p></span></span>'
				} else {
					dataTitleTag	= '<span><p>' + app.h(data.title) + '</p></span>'
				}
				_html += '<li data-id="'+data.id+'" data-category-id="'+data.category_id+'" class="'+(inUse?'in-use':'')+(isMemberOnly?' is-member-only':'')+(isActive?' is-active':'')+'"><a href="javascript:;" class="img-list-thumb">' + imageIconTag + '</a><div class="img-list-info">' + dataTitleTag + _editHtml+'</div></li>';
			}
			
			this.$file2s.html(	_html )			;
			this.$file2s.height('auto');
			if (this.$file2s.outerHeight() > 0) {
				this.$file2s.height(this.$file2s.outerHeight() - 5);
			}
			this.buildPaging()					;
			this.onRender && this.onRender()	;
			
			return this ;
		},
		
		getImageIconTag :function( data )
		{
			var icon = '/images/icon/' + data.extension + '.png' ;
			var tag = '<img src="'+ icon + '" alt="' + app.h( data.title ) + '">' ;
			
			return tag ;
		}
	};

	//-------------------------------------------------------------------------------------------
	app.modal.hpImage = function (token, onClose) {
		var $contents = $('<div class="section img-folder">' +
			'<form data-api-action="/site-setting/api-save-image">' +
			'<input type="hidden" name="_token" value="'+app.h(token)+'">' +
			'<div class="f-img-upload">' + 
				'<input type="hidden" id="hp_image_content_id" data-view="/image/hp-image" data-upload-to="/api-upload/hp-image" class="upload-file-id" value="" name="hp_image_content_id">' +
				'<div class="up-img">' +
					'<div class="up-btn">' +
						'<input type="file" name="file" />' +
					'</div>' +
					
					'<div class="up-area is-hide">' +
						'または、ファイルをドロップしてください。' +
					'</div>' +
					
					'<small>（１）jpg,jpeg,png (容量 10MB、サイズ 縦：960px　横：1280px まで。サイズ超過時は範囲内に収まるように自動縮小されます。）</small><br>' +
					'<small>（２）gif（容量 2MB、サイズ 縦：960px　横：1280px まで。）</small><br>' +
					'<small>※著作権又はその他の知的財産権で保護されている画像を使用する場合は、当該権利者の許諾を得るものとし、当該権利者の許諾を得ていないものは使用しないでください。</small>' +
				'</div>' +
	
				'<div class="up-preview">' +
					'<a href="javascript:;" class="i-e-delete is-hide"></a>' +
				'</div>' +
			'</div>' +
	
			'<div class="img-up-info">' +
				'<dl>' +
					'<dt>画像タイトル<i class="i-l-require">必須</i></dt>' +
					'<dd>' +
						'<div class="is-require input-img-title">' +
							'<input name="title" class="watch-input-count" type="text" maxlength="30">' +
							'<span class="input-count"></span>' +
						'</div>' +
					'</dd>' +
				'</dl>' +
	
				'<dl>' +
					'<dt>画像カテゴリ<a href="javascript:;" class="i-s-link edit-category-btn">カテゴリ編集</a></dt>' +
					'<dd>' +
						'<select name="category_id"><option value="0">選択してください</option></select>' +
					'</dd>' +
				'</dl>' +
			'</div>' +
			'<div class="errors"></div>' +
			'<div class="modal-btns">' +
				'<a href="javascript:;" class="btn-t-gray close-btn">キャンセル</a>' +
				'<a href="javascript:;" class="btn-t-blue save">登録</a>' +
			'</div>' +
			'</form>' +
		'</div>' +
		'<div class="section img-folder">' +
			'<div class="img-category-list">' +
				'<h3>画像カテゴリ</h3>' +
				'<a href="javascript:;">全て</a>' +
				'<ul>' +
				'</ul>' +
			'</div>' +
	
			'<div class="img-folder-list js-scroll-container" data-scroll-container-max-height="300">' +
				'<ul class="img-list">' +
				'</ul>' +
				'<ul class="paging" data-page-size="24">' +
				'</ul>' +
			'</div>' +
			'<div class="modal-btns">' +
				'<a href="javascript:;" class="btn-t-gray close-btn">キャンセル</a>' +
				'<a href="javascript:;" class="btn-t-blue select-btn">決定</a>' +
			'</div>' +
		'</div>');
		
		var modal = app.modal.popup({
			title: '画像',
			modalContentsClass: 'size-l',
			modalBodyInnerClass: 'align-top',
			tabs: ['画像を登録', '画像を選択'],
			tabInitialIndex: 1,
			autoRemove: false,
			ok: false,
			cancel: false,
			contents: $contents,
			onClose: onClose
		});
		
		var $hpImage = $contents.eq(1);
		var hpImage = app.hpImage($hpImage);
		
		var $hpImageList = $hpImage.find('.img-list');
		
		// ok cancel 処理
		var $selectBtn = $hpImage.find('.select-btn');
		$selectBtn.on('click', function () {
			if ($(this).hasClass('is-disable')) {
				return false;
			}
			
			modal.close( hpImage.getImageById($hpImageList.find('.is-active:eq(0)').attr('data-id')) );
		});
		
		$hpImageList.on('click', '.img-list-thumb', function () {
			var $li = $(this).parent();
			$li.addClass('is-active').siblings().removeClass('is-active');
			$selectBtn.removeClass('is-disable');
			return false;
		});
		hpImage.onRender = function () {
			$selectBtn.toggleClass('is-disable', !$hpImageList.find('.is-active').length);
		};
		
		$contents.find('.close-btn').on('click', function () {
			modal.close(false);
		});
		
		var $hpImageUpload = $contents.eq(0);
		$hpImageUpload.initUploadHPImage(function (data) {
			hpImage.addImage(data.item).filter().render();
		});
		
		var $categorySelect = $hpImageUpload.find('select[name="category_id"]');
		
		function _updateCategories(categories) {
			var $option = $categorySelect.find('option:eq(0)');
			$categorySelect.empty().append($option);
			$.each(categories, function (i, category) {
				$categorySelect.append($('<option/>').val(category.id).text(category.name));
			});
			
			hpImage.setCategories(categories);
		}
		var categoryModal = app.modal.categoryEdit(token, function (data) {
			_updateCategories(data.categories);
		});
		
		$hpImageUpload.find('.edit-category-btn').on('click', function () {
			categoryModal.show();
		});
		
		var xhr;
		var show = modal.show;
		modal.show = function (tabIndex) {
			if (!xhr) {
				xhr = app.api('/site-setting/api-get-images', null, function (data) {
					_updateCategories(data.categories);
					categoryModal.setCategories(data.categories);
					hpImage.setImages(data.images).filter().render();
				});
			}
			
			if (tabIndex !== undefined) {
				modal.$el.find('.modal-tab a').eq(tabIndex).click();
			}
			
			// エラーメッセージをクリア
			$('.modal-set').find('.errors').empty() ;
			
			show.call(modal);
			return modal;
		};
		
		return modal;
	};
	
  app.modal.editFileName = function (token, kwargs) {
    var $contents = $('<div class="section file-upload">' +
			'<form id="form-upload" data-api-action="'+ kwargs['api_action'] + '">' +
			'<div class="file-up-info">' +
				'<dl>' +
					'<dt>' + kwargs['name'] +
					'<dd>' +
						'<div class="input-file-name">' +
							'<input id="file-name" name="file-name" pattern="^[\\w,\\s-]+$" class="watch-input-change" type="text">' +
						'</div>' +
					'</dd>' +
				'</dl>' +
			'</div>' +
			'<div class="errors"></div>' +
			'<div class="modal-btns">' +
				'<a href="javascript:;" class="btn-t-gray close-btn">キャンセル</a>' +
				'<a href="javascript:;" class="btn-t-blue save-btn save">登録</a>' +
			'</div>' +
			'</form>' +
		'</div>');
    
    var modal = app.modal.popup({
        title: 'ファイル名を編集する',
        modalContentsClass: 'modal-upload',
        autoRemove: true,
        closeButton: false,
        ok: false,
        cancel: false,
        contents: $contents
    });
    $contents.keypress(function(event) {
        if (event.which == '13') {
            event.preventDefault();
        }
    });
    $contents.find('.save-btn').on('click', function () {
        var names = kwargs['name'].split('.');
        var extension = names.pop();
        
        if (names.join('.') != $('#file-name').val() && '' !== $('#file-name').val()) {
            var $form = $('form#form-upload');
            app.api($form.attr('data-api-action'), {
                '_token': token,
                company_id: kwargs['company_id'],
                sub_dir: kwargs['sub_dir'],
                original_file: kwargs['name'],
                change_name: $('#file-name').val() + '.' + extension
            }, function (data) {
              if (data.error) {
              	// app.modal.alert('', data.error);
              	app.modal.alertUpload('', data.error);
              } else {              
	              window.location.reload();
	          }
            });
        }

        modal.close(false);
    });
    
    $contents.find('.watch-input-change').on('change keyup paste', function () {
        var $this = $(this);
        var regx = $this.attr('pattern') || null;

        if (null == regx)  return false;
        
        regx = new RegExp(regx);
        if (false == regx.test($this.val()) && '' != $this.val()) {
            $this.val($this.attr('data-fnm') || '');
        } else {
            $this.attr('data-fnm', $this.val());
        }
    });
    
    $contents.find('.close-btn').on('click', function () {
        modal.close(false);
    });
    
    var show = modal.show ;
		modal.show = function () {
			modal.$el.find( '.errors' ).empty() ;
			show.call( modal ) ;
		};
    
    return modal ;
  }
  
  app.modal.alertUpload = function (title, message, onClose) {
		var modal = app.modal.popup({
        title				: title,
        modalContentsClass	: 'modal-upload size-lg',
        modalBodyInnerClass	: '',
        autoRemove			: true,
        ok					: '削除',
        cancel				: false,
        closeButton         : false,
        contents			: "<p class='message'>" + message + '</p>',
        onClose				: onClose
    });
    
    return modal.show();
  };
  app.modal.confirmUpload = function(title, message, onClose, ok) {
      var ok = ok || 'OK';
      var modal = app.modal.popup({
        title				: title,
        modalContentsClass	: 'modal-upload',
        modalBodyInnerClass	: '',
        autoRemove			: true,
        ok					: ok,
        cancel				: '閉じる',
        closeButton         : false,
        contents			: "<p class='message'>" + message + '</p>',
        onClose				: onClose
    });
    
    return modal.show();
  }
  app.modal.revertFileContent = function (token, kwargs) {
    var onClose = function(ret) {
    
        if (ret) {
            app.api(kwargs['api_action'], {
              '_token': token,
              company_id: kwargs['company_id'],
              sub_dir: kwargs['sub_dir'],
              revert_content: kwargs['name']
            }, function (data) {
              // console.log(data);
              location.reload();
            });
        }
    }
    
    app.modal.confirmUpload('初期状態に戻す', '初期状態に戻します。よろしいですか？', onClose);
  }
  
  app.modal.removeFile = function (token, kwargs) {
    var onClose = function(ret) {
      
      if (ret) {
        app.api(kwargs['api_action'], {
            '_token': token,
            company_id: kwargs['company_id'],
            sub_dir: kwargs['sub_dir'],
            remove_content: kwargs['name']
        }, function (data) {
          // console.log(data);
          location.reload();
        });
      }
    }

    app.modal.confirmUpload('ファイル削除', kwargs['name'] + ' を削除していいですか？', onClose, '削除');
    // var message = kwargs['name'] + ' を削除していいですか？';
    // var content = '<div class=""><div class="text-left pt10 pb10">'+message+'</div></div>';
    // var modal = app.modal.popup({
      // title: 'ファイル削除',
      // modalContentsClass: 'size-s',
      // modalBodyInnerClass: 'text-left',
      // autoRemove: true,
      // contents: content,
			// header: true,
			// ok: '削除',
			// cancel: '閉じる',
			// onClose: onClose
    // });

    // modal.show();

  }
  
  app.modal.editFileContent = function (token, kwargs) {
    var $contents = $('<div class="section file-upload">' +
			'<form id="form-upload" data-api-action="'+kwargs['api_action']+'">' +
			'<div class="file-up-info">' +
				'<dl>' +
					'<dd>' +
						'<div class="input-file-content">' +
							'<textarea id="file-content" rows="25" name="file-content" class="watch-input-change" type="text"></textarea>' +
						'</div>' +
					'</dd>' +
				'</dl>' +
			'</div>' +
			'<div class="errors"></div>' +
			'<div class="modal-btns">' +
				'<a href="javascript:;" class="btn-t-gray close-btn">キャンセル</a>' +
				'<a href="javascript:;" class="btn-t-blue save-btn save">登録</a>' +
			'</div>' +
			'</form>' +
		'</div>');
    
    var modal = app.modal.popup({
        title: 'ファイル名を編集する',
        modalContentsClass: 'size-xxl',
        modalBodyInnerClass: 'align-top',
        autoRemove: true,
        ok: false,
        cancel: false,
        contents: $contents
    });
    
    $contents.find('.save-btn').on('click', function () {
        if (kwargs['name'] != $('#file-name').val() && '' !== $('#file-name').val()) {
            var $form = $('form#form-upload');
            app.api($form.attr('data-api-action'), {
                '_token': token,
                company_id: kwargs['company_id'],
                sub_dir: kwargs['sub_dir'],
                original_file: kwargs['name'],
                change_content: $('#file-content').val()
            }, function (data) {
              console.log(data);
            });
        }
      
        modal.close(false);
    });
    
    $contents.find('.close-btn').on('click', function () {
        modal.close(false);
    });
    
    var show = modal.show ;
		modal.show = function () {
			modal.$el.find( '.errors' ).empty() ;
      
      var xhr = null ;
			if ( !xhr ) {
                xhr = app.api(kwargs['api_get_file'], {
                    '_token': app.h(token),
                    company_id:  kwargs['company_id'],
                    sub_dir:  kwargs['sub_dir'],
                    original_file: kwargs['name'],
                }, function ( data ) {
                    $('#file-content').text(data);
				});
			}
      
			show.call( modal ) ;
		};
    
    return modal ;
  }
    
	var _loadingTemplate = '<div class="modal-set" style="z-index:9999;"><div class="all-loading"><p><img alt="" src="/images/common/loading.gif"></p></div></div>';
	var $loading;
	var closers = [];
	app.loading = function () {
		if (!$loading) {
			$loading = $(_loadingTemplate).appendTo('body');
		}
		
		var closer = function () {
			var i=0,l=closers.length;
			for (;i<l;i++) {
				if (closers[i] == closer) {
					closers.splice(i, 1);
					if (!closers.length) {
						$loading.remove();
						$loading = null;
					}
					return;
				}
			}
		};
		closers.push(closer);
		return closer;
	};
	
	app.hpImage = function($container, isEdit) {
		if (!(this instanceof app.hpImage)) {
			return new app.hpImage($container, isEdit);
		}

		this.$container         = $container;
		this.$categoryContainer = $container.find('.img-category-list');
		this.$categories        = this.$categoryContainer.find('ul');
		this.$imageContainer    = $container.find('.img-folder-list');
		this.$images            = this.$imageContainer.find('ul.img-list');
		this.$paging            = this.$imageContainer.find('ul.paging');
		
		this.isEdit = isEdit;

		this.categories = [];
		this.categoryIds = [];
		
		this.images = [];
		this.filtered = [];
		this.pageSize = 0;
		this.pagingLinks = 6;
		this.page = 1;
		
		var self = this;
		
		if (this.$paging.length) {
			this.pageSize = parseInt(this.$paging.attr('data-page-size'));
			this.$paging.on('click', 'a', function () {
				var $page = $(this).parent();
				if ($page.hasClass('is-invisible') || $page.hasClass('is-active')) {
					return;
				}
				
				self.render($page.attr('data-page'));
			});
		}
		
		this.$categoryContainer.on('click', 'a', function () {
			self.$categories.find('.is-active').removeClass('is-active');
			var $category = $(this).parent();
			if ($category.attr('data-category-id')) {
				$category.toggleClass('is-active');
			}
			
			self.filter().render();

		});
	};
	
	app.hpImage.prototype = {
		setCategories: function(categories) {
			var self = this;
			
			var categoryIds = [];
			var $tmp = $('<ul/>');
			
			function _addCategory(category) {
				categoryIds.push(parseInt(category.id));
				
				var $li = $('<li/>');
				$li.append($('<a href="javascript:;"></a>').text(category.name));
				$li.toggleClass('is-active', !!self.$categories.find('.is-active[data-category-id="'+category.id+'"]').length);
				$li.attr('data-category-id', category.id);
				$tmp.append($li);
			}
			
			$.each(categories, function (i, category) {
				_addCategory(category);
			});
			
			// 未登録
			_addCategory({
				id: 0,
				name: '未登録'
			});
			
			this.categories = categories;
			this.categoryIds = categoryIds;
			this.$categories.html($tmp.html());
			
			return this;
		},
		
		getCategories: function () {
			return this.categories;
		},
		
		setImages: function (images) {
			this.images = images;
			return this;
		},
		
		addImage: function (image) {
			this.images.push(image);
			return this;
		},
		
		updateImageData: function (imageId, data) {
			imageId = parseInt(imageId);
			$.each(this.images, function (i, image) {
				if (imageId === parseInt(image.id)) {
					$.extend(image, data);
					return false;
				}
			});
			return this;
		},
		
		getImageById: function (imageId) {
			imageId = parseInt(imageId);
			
			var ret;
			$.each(this.images, function (i, image) {
				if (parseInt(image.id) === imageId) {
					ret = $.extend({}, image);
					return false;
				}
			});
			return ret;
		},
		
		removeImageById: function (imageId) {
			var idx;
			$.each(this.images, function (i, image) {
				if (parseInt(image.id) === parseInt(imageId)) {
					idx = i;
					return false;
				}
			});
			
			if (idx === undefined) {
				return this;
			}
			
			this.images.splice(idx, 1);
			return this;
		},
		
		filter: function () {
			var self = this;
			
			var actives = [];
			this.$categories.find('.is-active').each(function () {
				actives.push(parseInt($(this).attr('data-category-id')));
			});
			if (!actives.length) {
				this.filtered = this.images;
				return this;
			}
			
			var isCheckedNoCategory = app.arrayIndexOf(0, actives) >= 0;
			
			var filtered = [];
			$.each(this.images, function (i, data) {
				var categoryId = parseInt(data.category_id);
				if (
					(app.arrayIndexOf(categoryId, actives) >= 0) ||
					(app.arrayIndexOf(categoryId, self.categoryIds) < 0 && isCheckedNoCategory)
				) {
					filtered.push(data);
				}
			});
			
			this.filtered = filtered;
			
			return this;
		},
		
		buildPaging: function () {
			if (!this.pageSize) {
				return this;
			}
			
			var pages = this.getPages();
			var start = this.page - Math.floor((this.pagingLinks - 1) / 2);
			var end   = this.page + Math.ceil((this.pagingLinks - 1) / 2);
			if (start < 1) {
				end += 1 - start;
				start = 1;
			}
			else if (end > pages) {
				start -= end - pages
				end = pages;
			}
			start = Math.max(1, start);
			end   = Math.min(pages, end);
			
			var _html = '';
			// prev
			_html += '<li class="prev'+(this.page > 1?'':' is-invisible')+'" data-page="'+(this.page - 1)+'"><a href="javascript:;"></a></li>';
			
			for (var i = start; i <= end; i++) {
				_html += '<li class="'+(this.page === i?'is-active':'')+'" data-page="'+i+'"><a href="javascript:;">'+i+'</a></li>';
			}
			
			// next
			_html += '<li class="next'+(this.page < pages?'':' is-invisible')+'" data-page="'+(this.page + 1)+'"><a href="javascript:;"></a></li>';
			
			this.$paging.html(_html);
			
			return this;
		},
		
		getPages: function () {
			if (!this.pageSize || !this.filtered.length) {
				return 1;
			}
			
			return Math.ceil(this.filtered.length / this.pageSize);
		},
		
		render: function (page) {
			// id 降順にソート
			this.filtered.sort(function (a, b) {
				if (a.id < b.id) return 1;
				if (a.id > b.id) return -1;
				return 0;
			});
			
			this.page = Math.max(1, Math.min(parseInt(page || this.page) || 1, this.getPages()));
			var offset;
			var count;
			if (this.pageSize) {
				offset = (this.page - 1) * this.pageSize;
				count  = this.pageSize;
			}
			else {
				offset = 0;
				count  = this.filtered.length;
			}
			
			var data;
			var isActive;
			var _html = '';
			var _editHtml;

			// ATHOME_HP_DEV-5366

			for (var i = offset, l = Math.min(offset + count, this.filtered.length); i < l; i++) {
				data = this.filtered[i];
				
				if (this.isEdit) {
					_editHtml = '<div class="pull action-menu"><a href="javascript:void(0);"><i class="i-e-set">操作</i></a><ul><li><a href="javascript:void(0);"><i class="i-e-list"></i>使用ページ</a></li><li><a href="/site-setting/download-image?id='+data.id+'"><i class="i-e-dl"></i>ダウンロード</a></li><li><a href="javascript:void(0);"><i class="i-e-move"></i>カテゴリー移動</a></li><li><a href="javascript:void(0);"><i class="i-e-delete"></i>削除</a></li></ul></div>';
				}
				else {
					_editHtml = '';
				}
				isActive = !!this.$images.find('.is-active[data-id="'+data.id+'"]').length;
				if (this.isEdit) {
				    _html += '<li data-id="'+data.id+'" data-category-id="'+data.category_id+'" class="'+(isActive?'is-active':'')+'"><a href="javascript:;" class="img-list-thumb"><img src="" data-original="'+data.url+'" alt="'+app.h(data.title)+'"></a><div class="img-list-info"><span>'+app.h(data.title)+'</span>'+_editHtml+'</div></li>';
				}
				else {
					_html += '<li data-id="'+data.id+'" data-category-id="'+data.category_id+'" class="'+(isActive?'is-active':'')+'"><a href="javascript:;" class="img-list-thumb"><img src="" data-src="'+data.url+'" alt="'+app.h(data.title)+'" class="lazyload"></a><div class="img-list-info"><span>'+app.h(data.title)+'</span>'+_editHtml+'</div></li>';
				}
			}

			this.$images.html(_html);
			if (this.isEdit) {
				// 画像フォルダは lazyload
				this.$images.find("img").lazyload({effect: 'fadeIn'});
			} else {
				lazyload(this.$images[0].querySelectorAll(".lazyload"), {
					rootMargin: "0px 0px 300px 0px"
				});
				this.$images.height('auto');
				if (this.$images.outerHeight() > 0) {
					this.$images.height(this.$images.outerHeight() - 10);
				}
				this.$images.find(".view-next").on('click', function() {
					var imgList = $(this).closest('ul').find('li');
					var index = $(imgList).index(this);
					for(var i=index + 1 ; i <= index + perPage + 5; i++) {
						if($(imgList).eq(i).prop('outerHTML') != 'undefined') {
							$(imgList).eq(i).show();
						}
					}
					$(this).empty();
				});
			}
			
			this.buildPaging();
			
			this.onRender && this.onRender();
			
			return this;
		}
	};
	
	app.unload = false;
	$(window).on('beforeunload', function _beforeunloadForAjax() {
		$(window).off('beforeunload', _beforeunloadForAjax);
		app.unload = true;
	});
	
	app.api = function(url, data, fn, msg, options) {

        var popupError = true;
        var onError;

        if(options && options.hasOwnProperty('popupError')){
          popupError = options.popupError;
        }

        if(options && options.hasOwnProperty('onError')){
          onError = options.onError;
        }

		var defer = $.ajax(url, {
			dataType: 'json',
			method: 'POST',
			data: data
		});
		
		defer.success(function (res) {
			if (res.success) {
				fn && fn(res.data);
			}
			else {
				app.modal.alert('', res.error);
			}
		})
		.fail(function (xhr, statusText) {
			if (app.unload) {
				return;
			}
			if (statusText === 'abort') {
				return;
			}
			if (msg) {
				app.modal.alertForReload('', msg, function () {
                  location.reload();
                });
			}else {
			  if(popupError){
		          app.modal.alert('', '通信に失敗しました。');
		      }
		    }
		      if(typeof onError  === "function") {
		        onError(xhr);
			}
		});
		return defer;
	};
	
	app.setErrors = function ($form, errors) {
		$.each(errors, function (id, errors) {
			var target = $form.find('#'+id).addClass('is-error');
			var $container = target.parent();
			var $errors = $('.errors.error-'+id);
			
			if (!$errors.length) {
				while ($container.length) {
					$errors = $container.find('.errors');
					if ($errors.length) {
						break;
					}
					$container = $container.parent();
				}
			}
			
			if ($errors.length) {
				if (id.indexOf('side') != -1 && id.indexOf('link_label') != -1) {
					$errors = $errors.eq(1);
				} else {
					$errors = $errors.eq(0);
				}
				$.each(errors, function (i, error) {
					$errors.append($('<p/>').text(error));
				});
			}
		});
	};
	
	app.initApiForm = function ($form, $trigger, onSuccess) {
		
		$form.on('submit', function (e) {
			
			if ($form.attr('target')) {
				return;
			}
			
			e.preventDefault();
			
			if ($form.hasClass('is-loading')) {
				return false;
			}
			$form.addClass('is-loading');
			
			if ($trigger) {
				$trigger.addClass('is-disable');
			}
			
			$form.trigger('pre-submit');
			app.api($form.attr('data-api-action'), $form.serialize(), function (res) {
				$form.find('.is-error').removeClass('is-error');
				$form.find('.errors, .error').empty();
				if (res.errors) {
					$form.trigger('reset-placeholder');
					
					app.setErrors($form, res.errors);
					
					$form.trigger('app-api-form-error');

					//サイト指定容量を超えているとエラーとする
					if(typeof res.errors.over_capacity !== "undefined") {
						app.modal.alert('', res.errors.over_capacity.data_max);
					}else{
						app.modal.alert('', '入力に誤りがあります。', function () {
							if (app.polling) app.polling.start();
							var $errorInput = $('.is-error:not(:hidden)');
							var $error = $('.errors p');
							var $target;
							if ($errorInput.length) {
								$target = $errorInput.eq(0);
							}
							else if ($error.length) {
								$target = $error.eq(0);
							}
							else {
								return;
							}
							
							if (!$form.closest('#modal').length) {
								app.scrollTo($target.offset().top - 50);
							}
						});
					}
				}
				else {
					if (onSuccess) {
						onSuccess(res);
						// ATHOME_HP_DEV-5759 画像およびファイル登録時は「サイトの公開/更新」に「！」がつかないようにする
						if(isUpdateAlertPublish(res)) {
							app.updateAlertPublish();
						}
					}
					else {
						app.modal.alert('', '設定を保存しました。', function(){
							if (app.polling) app.polling.start();
						});
					}
				}
			})
			.always(function () {
				$form.removeClass('is-loading');
				if ($trigger) {
					$trigger.removeClass('is-disable');
				}
			});
		});

		// ATHOME_HP_DEV-5759 画像およびファイル登録時は「サイトの公開/更新」に「！」がつかないようにする
		var isUpdateAlertPublish = function(res) {
			if (res === undefined || res.item === undefined || res.item.url === undefined) {
				return true;
			}
			var url = res.item.url;
			if (url.indexOf('/image/hp-image') !== -1 || url.indexOf('/file/hp-file2') !== -1) {
				return false;
			}
			return true;
		};
    
		if ($trigger) {

			if($trigger.hasClass('basic-setting') && $trigger.hasClass('is-disable') == false) {
				$trigger.on('click', function (e) {
					var closeFunction = function(ret) {
						if(ret) {
							e.preventDefault();
							if ($trigger.hasClass('is-disable')) {
								return false;
							}
							$trigger.addClass('is-disable');
							$form.submit();
						}
					};

					app.api($form.attr('data-api-action') + '?only_valid=1', $form.serialize(), function (res) {
						$form.find('.is-error').removeClass('is-error');
						$form.find('.errors').empty();
						if (res.errors) {
							$form.trigger('reset-placeholder');

							app.setErrors($form, res.errors);

							$form.trigger('app-api-form-error');

							//サイト指定容量を超えているとエラーとする
							if(typeof res.errors.over_capacity !== "undefined") {
								app.modal.alert('', res.errors.over_capacity.data_max);
							}else{
								app.modal.alert('', '入力に誤りがあります。', function () {
									if (app.polling) app.polling.start();
									var $errorInput = $('.is-error:not(:hidden)');
									var $error = $('.errors p');
									var $target;
									if ($errorInput.length) {
										$target = $errorInput.eq(0);
									}
									else {
										return;
									}

									if (!$form.closest('#modal').length) {
										app.scrollTo($target.offset().top - 50);
									}
								});
							}
						} else {
							var options = {
								'title': '',
								'contents': '<div style="margin: 40px 8px;">'+
												'<p>すべてのページに反映が必要となる内容の追加・修正があります。<br/>この内容を保存すると、次回公開処理時に現在修正中のページ（※）が自動的に更新されます。<br>※新規ページを除く本番サイトへ未反映の内容があるページ</p>' +
												'<p>内容を保存してよろしいですか？</p>' +
											'</div>',
								'onClose': closeFunction
							};
							var modal = app.modal.popup(options);
							modal.show();
							return false;
						}
					});
					return false;
				});
			} else if(typeof is_top_page != 'undefined' && is_top_page == '1' && typeof has_reserve != 'undefined' && has_reserve == '0') {
				// トップページかつ、予約なし
				$trigger.on('click', function (e) {

					// ATHOME_HP_DEV-5273 & ATHOME_HP_DEV-5277
					// ページ保存(api-save)以外の保存は、formに従う 
					var api_save_url = '/page/api-save';
					var api_str = $form.attr('data-api-action');
					if(api_str.substr(0, api_save_url.length) != api_save_url) {
						e.preventDefault();
						if ($trigger.hasClass('is-disable')) {
							return false;
						}
						$trigger.addClass('is-disable');
						$form.submit();
						return;
					}
					var bmax = $before_form[0].length;
					var amax = $form[0].length;
					var fname = '';

					var bvals = [];
					var avals = [];

					var bnames = [];
					var anames = [];
					var value_diff = false;
					for(var bno = 0; bno < bmax; bno++) {
						fname = $($before_form[0][bno]).attr('name');
                        if(typeof fname != "undefined" && fname.indexOf('side', 0) === 0 && fname.indexOf('link_house_type') < 0){
							// nameの順番用
							bnames.push(fname);

							// 初期化
							bvals = [];
							avals = [];
							$($before_form).find("[name='" + fname + "']").each(function() {
								if($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
									if($(this).prop('checked')) {
										bvals.push($(this).val());
									}
								} else {
									bvals.push($(this).val());
								}
							});
							$($form).find("[name='" + fname + "']").each(function() {
								if($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
									if($(this).prop('checked')) {
										avals.push($(this).val());
									}
								} else {
									avals.push($(this).val());
								}
							});
						}
						if(JSON.stringify(bvals) != JSON.stringify(avals)) {
							value_diff = true;
						}
					}
					if(value_diff == false) {
						for(var ano = 0; ano < amax; ano++) {
							fname = $($form[0][ano]).attr('name');
                            if(typeof fname != "undefined" && fname.indexOf('side', 0) === 0 && fname.indexOf('link_house_type') < 0 ){
								anames.push(fname);
							}
						}
						if(JSON.stringify(bnames) != JSON.stringify(anames)) {
							value_diff = true;
						}
					}

					if(value_diff == false) {
						e.preventDefault();
					
						if ($trigger.hasClass('is-disable')) {
							return false;
						}
						$trigger.addClass('is-disable');
						
						if (typeof app._isPageSave != 'undefined' && app._isPageSave) {
							app.validateCheckBeforeSave($form);
						} else {
							$form.submit();
						}
						app._isPageSave = false;

						return false;
					}
					var closeFunction = function(ret) {
						if(ret) {
							e.preventDefault();
							if ($trigger.hasClass('is-disable')) {
								return false;
							}
							$trigger.addClass('is-disable');
							if (typeof app._isPageSave != 'undefined' && app._isPageSave) {
								app.validateCheckBeforeSave($form);
							} else {
								$form.submit();
							}
							app._isPageSave = false;
						}
					};

					// まず、/page/api-validateでチェックのみ実施
					// Preview処理参照
					var id = $form.attr('data-id');
					var parentId = $form.attr('data-parent-id');	// topだからいらない？
					app.api('/page/api-validate?id=' + id + '&parent_id=' + parentId, $form.serialize(), function (res) {
						$form.find('.is-error').removeClass('is-error');
						$form.find('.errors, .error').empty();
						if (res.errors) {
							$form.trigger('reset-placeholder');

							app.setErrors($form, res.errors);

							$form.trigger('app-api-form-error');

							app.modal.alert('', '入力に誤りがあります。', function () {
								var $errorInput = $('.is-error:not(:hidden)');
								var $error = $('.errors p');
								var $target;
								if ($errorInput.length) {
									$target = $errorInput.eq(0);
								}
								else if ($error.length) {
									$target = $error.eq(0);
								}
								else {
									return;
								}

								if (!$form.closest('#modal').length) {
									app.scrollTo($target.offset().top - 50);
								}
							});
							return;
						} else {
							// エラーが無いときのみ保存前警告
							var options = {
								'title': '',
								'contents': '<div style="margin: 40px 8px;">' +
												'<p>すべてのページに反映が必要となる内容の追加・修正があります。<br/>この内容を保存すると、次回公開処理時に現在修正中のページ（※）が自動的に更新されます。<br>※新規ページを除く本番サイトへ未反映の内容があるページ</p>' +
												'<p>内容を保存してよろしいですか？</p>' +
											'</div>',
								'onClose': closeFunction
							};
							var modal = app.modal.popup(options);
							modal.show();
							return false;
						}
					});
					return;
				});
			} else {
				$trigger.on('click', function (e) {
					e.preventDefault();
					
					if ($trigger.hasClass('is-disable')) {
						return false;
					}
					$trigger.addClass('is-disable');

					if (typeof app._isPageSave != 'undefined' && app._isPageSave) {
						app.validateCheckBeforeSave($form);
					} else {
						$form.submit();
					}
					app._isPageSave = false;
				});
			}
		}
	};

	app.validateCheckBeforeSave = function ($form) {
		// リンクを利用するの初期化(NHP-5962)
		var id = $form.attr('data-id');
		var parentId = $form.attr('data-parent-id');
		var type = $form.attr('data-type');
		var url = '/page/api-validate?id=' + id + '&parent_id=' + parentId;
		if (type !== undefined) {
			url += '&type=' + type;
		}
		app.api(url, $form.serialize(), function (res) {
			var isError = res.errors ? true : false;
			if (!isError) {
				app.page.initUseLinkForm($('.input-img-link'));
			}
			$form.submit();
		});
	}
  
    app.initApiProcessForm = function ($form, $trigger, onProgess, onLoaded, onSuccess) {
        var req = new XMLHttpRequest();
        $form.on('submit', function(e){
            $form.trigger('pre-submit');

            var dataForm = $form.attr('is-upload') ? new FormData($form[0]) : $form.serialize();
            var url = $form.attr('data-api-action');
            
            $.ajax({
                url: url,
                type: "POST",
                dataType:"JSON",
                data: dataForm,
                processData: false,
                contentType: false,
                cache:false,
                xhr: function() {
                    var myXhr = $.ajaxSettings.xhr();
                        if(myXhr.upload){
                            myXhr.upload.addEventListener('progress', function(e){
                                if (onProgess) onProgess(myXhr, e);
                            }, false);
                            myXhr.upload.addEventListener('load', function(e){
                                if (onLoaded) onLoaded();
                            }, false);
                        }
                        return myXhr;
                },
                success: function(data){
                    // console.log('here.')
                    if (onSuccess) onSuccess(data);
                },
				error: function (myXhr, textStatus, thrownError) {
					if (onSuccess) onSuccess({
						'message': 'Ok',
                        'errors': [],
						// 'errors': [
							// {'thrownError': thrownError}
						// ]
					});
				}
            });
            
            e.preventDefault();
        });
    
		if ($trigger) {
			$trigger.on('click', function (e) {
				e.preventDefault();
				
				if ($trigger.hasClass('is-disable')) {
					return false;
				}
				$trigger.addClass('is-disable');
				
                $form.submit();
			});
		}
	};
  
    app.notify = function(url, msg, data, fn) {
		var Polling = function() {
			var _me = this;
			
			this.start = function() {
				_me.xhrPoll = $.ajax({
					url: url,
					type: "POST",
					dataType:"JSON",
					data: data,
					xhr: function() {
						var myXhr = $.ajaxSettings.xhr();
						return myXhr;
					},
					success: function(data) {
						if (fn) {
							fn(data);
							return;
						}
						if (data.errors || data.data.errors) {
							// console.log(data.errors.message)
						} else if (data.updated || data.data.updated) {
							app.modal.notify('設定を保存しました', _me.start);
						}  else {
							_me.start();
						}
					},
					fail: function() {
						console.log('fail.')
					}
				});
			};
			
			this.stop = function(){
				if (_me.xhrPoll) {
					_me.xhrPoll.abort();
				}
			}
			
			this.start();
		};
		
		app.polling = new Polling();
    };
    app.modal.notify = function(message, fn, time){
		var delay = time || 2500;
		
        var html = '<div class="modal-message"><strong>' + message + '</strong></div>';
        var modal = app.modal.popup({
            title: '',
            contents: html,
            ok: false,
            cancel:false,
            closeButton:false,
            modalContentsClass: 'size-s notify'
        }).show();

		setTimeout(function() { 
            modal.close();
            fn();
        }, delay);
	};
    
	app.inherits = function (sp, fn, props) {
		fn.super_ = sp;
		var prot;
		if (Object.create) {
			
			prot = Object.create(sp.prototype, {
				constructor: {
					value: fn,
					enumerable: false,
					writeable: true,
					configurable: true
				}
			});
		}
		else {
			var F = function () {};
			F.prototype = sp.prototype;
			prot = new F();
		}
		
		fn.prototype = prot;
		
		if (props) {
			for (var prop in props) {
				fn.prototype[prop] = props[prop];
			}
		}
	
		return fn;
	};
	
	app.arrayIndexOf = function (value, ary) {
		for (var i = 0, l = ary.length; i < l; i++) {
			if (value === ary[i]) {
				return i;
			}
		}
		return -1;
	};
	
	app.scrollToElement = function ($target) {
		if (!$target.length) {
			return;
		}
		app.scrollTo($target.offset().top);
	}
	app.scrollTo = function (top, duration) {
		
		$('html, body').animate({scrollTop: top}, {duration: duration || "fast"});
	};
	
	app.scrolltoElementIfOutOfscreen = function ($elem, offset) {
		var currentTop = $elem.offset().top;
		var st = $(window).scrollTop();
		
		if (currentTop < st || currentTop + $elem.height() > st + $(window).height()) {
			app.scrollTo(currentTop + offset, 500);
		}
	};

	app.apiCustom = function(url,data,options){

    var popupError = false;
    var onError;
    var onSuccess;
    var tag;
    var msg = false;
    var el;

    if(options && options.hasOwnProperty('tag')){
      tag = options.tag;
    }

    if(options && options.hasOwnProperty('el')){
      el = options.el;
      app.clearErrorsCustom(el);
    }

    if(options && options.hasOwnProperty('popupError')){
      popupError = options.popupError;
    }

    if(options && options.hasOwnProperty('onError')){
      onError = options.onError;
    }

    if(options && options.hasOwnProperty('onSuccess')){
      if(typeof options.onSuccess === 'function'){
        onSuccess = options.onSuccess;
      }
    }

    var setOptions = {
      popupError: popupError
    };

    if(typeof onError === 'function'){
      setOptions.onError = function(xhr){
        var res = xhr.responseJSON;
        var data = {};
        var message = null;
        if(res && res.hasOwnProperty('data')) {
        	data = res.data;
        	if(data.hasOwnProperty('message')){
        		if(popupError){
              msg = res.message;
						}
					}
        	if(data.hasOwnProperty('errors')){
            var errors = data.errors;
            app.setErrorsCustom(el,errors,tag);
					}
        }
        onError(xhr);
      };
    }

		app.api(url,data,onSuccess,msg, setOptions);
	};

	app.clearErrorsCustom = function($form){
    $form.find('.errors').empty();
  };

  app.setErrorsCustom = function ($form, errors, tag) {
  	if(!tag){
      tag = 'p';
    }
    Object.keys(errors).forEach(function(key) {
      var value = errors[key];
      var outsideDiv = $form.find('div.' + key + '-error');
      if(outsideDiv && outsideDiv.length === 0){
        outsideDiv = $form.find('span.' + key +'-error');
			}
      outsideDiv.empty();
      Object.keys(value).forEach(function(key) {
        var message = value[key];
        outsideDiv.append('<'+ tag + ' style="color:red;">' + message + '</'+tag+'>');
      });
    });
  };

  app.modal.tempo = function(time,title,message,onClose){
		var html = '<div class="modal-message"><strong>' + message + '</strong></div>';
    var tempoModal = app.modal.popup({
      "contents": html,
      ok: false,
      cancel:false,
			closeButton:false,
      modalContentsClass: 'size-s tempo'
    }).show();

		if(time > 0){
      var t = setTimeout(function(){
        tempoModal.close();
        clearTimeout(t);
        if(onClose){
          onClose();
				}
      },time);
		}

	};

	var $contextMenu;
    app.contextMenu = function($target, pos, fn){
        this.fn = fn;
        this.posX = pos.left || pos.x;
        this.posY = pos.top || pos.y;
        this.opening = false;
        this.menuItems = [];

        if (!(this instanceof app.contextMenu)) {
            return new app.contextMenu($target, fn);
        }

        this.$el = $('<div class="context-menu-set"></div>');
        this.$el.addClass('is-hide');

        if (!$contextMenu) {
            $contextMenu = $('<div id="app-context-menu" class="is-hide"></div>');
            $contextMenu.appendTo('body');
            // $contextMenu.append(this.$el);
        }

        $contextMenu.css({'position': 'absolute', 'left': this.posX, 'top': this.posY});

        var _me = this;

        $target.on('contextmenu',function(e){e.preventDefault();});
        window.addEventListener('click',function(e){if(_me.opening)_me.close();});
    }
  
    app.contextMenu.prototype = {
        addMenus: function(menus) {
            var _me = this;
            var $tmp = $('<ul/>');
          
            $.each(menus, function (i, m) {
            	if (m.hide) return;

                var $li = $('<li/>');
                var $anchor = $('<a/>');

                $anchor.attr('href', 'javascript:;');
                $anchor.attr('dada-id', m.id || i);

                if (m.route) $anchor.attr('href', m.route);
                if (m.icon) $anchor.append($('<i class="'+m.icon+'"></i>'));
                if (m.fileName) $anchor.attr('data-file', m.fileName);
                if (m.className) $anchor.addClass(m.className);
                if (m.onClick) $anchor.on('click', m.onClick);

                $anchor.append($('<span>').text(m.name));
                $li.append($anchor);
                $tmp.append($li);
            });
          
            this.$el.append($tmp);
        },
        
        show: function () {      
            $contextMenu.html('');
            this.$el.appendTo($contextMenu).removeClass('is-hide');
            this.$el.removeClass('is-hide');
            $contextMenu.removeClass('is-hide');
      
            this.opening = true;
        },	
        
        close: function () {
            if (this.opening !== false) {
                this.$el.addClass('is-hide');
                if (!$contextMenu.find('.context-menu-set:not(.is-hide)').length) {
                    $contextMenu.addClass('is-hide');
                }
        
                this.opening = false;
            }
        }
    }

    // Restricts input for the given textbox to the given inputFilter.
    app.setInputFilter = function(textbox, inputFilter) {
        ["click", "blur", "mousedown", "mouseup","select", "drop", "contextmenu", "compositionstart", "compositionend"].forEach(function(event) {
            for (var i = 0; i < textbox.length; i++) {
                textbox[i].addEventListener(event, function(e) {
                    if (inputFilter(this.value)) {
                        this.oldValue = this.value;
                        this.oldSelectionStart = this.selectionStart;
                        this.oldSelectionEnd = this.selectionEnd;
                    } else if (this.hasOwnProperty("oldValue")) {
                        this.value = this.oldValue;
                        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                    }
                    var $this = this;
                    if (event != 'blur') {
                        setTimeout(function() {
                            $this.click();
                        }, 10, $this);
                    }
                    
                });
            }
        });
    }
	app.updateAlertPublish = function() {
        var gnaviPublish = $('.i-m-publish').closest('li');
        if (!gnaviPublish.hasClass('is-alert')) {
            gnaviPublish.addClass('is-alert');
            gnaviPublish.append('<img class="icon-alert" src="/images/home/home_alert.png" alt="">');
        }
    }
    /**
     * 全角 → 半角
     * @param str
     * @returns {string}
     */
    app.hankanaToZenkana = function (str) {
        var kanaMap = {
            'ｶﾞ': 'ガ', 'ｷﾞ': 'ギ', 'ｸﾞ': 'グ', 'ｹﾞ': 'ゲ', 'ｺﾞ': 'ゴ',
            'ｻﾞ': 'ザ', 'ｼﾞ': 'ジ', 'ｽﾞ': 'ズ', 'ｾﾞ': 'ゼ', 'ｿﾞ': 'ゾ',
            'ﾀﾞ': 'ダ', 'ﾁﾞ': 'ヂ', 'ﾂﾞ': 'ヅ', 'ﾃﾞ': 'デ', 'ﾄﾞ': 'ド',
            'ﾊﾞ': 'バ', 'ﾋﾞ': 'ビ', 'ﾌﾞ': 'ブ', 'ﾍﾞ': 'ベ', 'ﾎﾞ': 'ボ',
            'ﾊﾟ': 'パ', 'ﾋﾟ': 'ピ', 'ﾌﾟ': 'プ', 'ﾍﾟ': 'ペ', 'ﾎﾟ': 'ポ',
            'ｳﾞ': 'ヴ', 'ﾜﾞ': 'ヷ', 'ｦﾞ': 'ヺ',
            'ｱ': 'ア', 'ｲ': 'イ', 'ｳ': 'ウ', 'ｴ': 'エ', 'ｵ': 'オ',
            'ｶ': 'カ', 'ｷ': 'キ', 'ｸ': 'ク', 'ｹ': 'ケ', 'ｺ': 'コ',
            'ｻ': 'サ', 'ｼ': 'シ', 'ｽ': 'ス', 'ｾ': 'セ', 'ｿ': 'ソ',
            'ﾀ': 'タ', 'ﾁ': 'チ', 'ﾂ': 'ツ', 'ﾃ': 'テ', 'ﾄ': 'ト',
            'ﾅ': 'ナ', 'ﾆ': 'ニ', 'ﾇ': 'ヌ', 'ﾈ': 'ネ', 'ﾉ': 'ノ',
            'ﾊ': 'ハ', 'ﾋ': 'ヒ', 'ﾌ': 'フ', 'ﾍ': 'ヘ', 'ﾎ': 'ホ',
            'ﾏ': 'マ', 'ﾐ': 'ミ', 'ﾑ': 'ム', 'ﾒ': 'メ', 'ﾓ': 'モ',
            'ﾔ': 'ヤ', 'ﾕ': 'ユ', 'ﾖ': 'ヨ',
            'ﾗ': 'ラ', 'ﾘ': 'リ', 'ﾙ': 'ル', 'ﾚ': 'レ', 'ﾛ': 'ロ',
            'ﾜ': 'ワ', 'ｦ': 'ヲ', 'ﾝ': 'ン',
            'ｧ': 'ァ', 'ｨ': 'ィ', 'ｩ': 'ゥ', 'ｪ': 'ェ', 'ｫ': 'ォ',
            'ｯ': 'ッ', 'ｬ': 'ャ', 'ｭ': 'ュ', 'ｮ': 'ョ',
            '｡': '。', '､': '、', 'ｰ': 'ー', '｢': '「', '｣': '」', '･': '・'
        };

        var reg = new RegExp('(' + Object.keys(kanaMap).join('|') + ')', 'g');
        return str
            .replace(reg, function (match) {
                return kanaMap[match];
            })
            .replace(/ﾞ/g, '゛')
            .replace(/ﾟ/g, '゜');
    };
})();
