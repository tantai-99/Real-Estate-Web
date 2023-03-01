(function (app) {
	
	'use strict';
	
	var DISABLE_WYSIWYG = false;
	
	app.page = {};
	
	app.page.info = {};
	
	app.page._changed = false;
	/*
	$(window).on('beforeunload', function (e) {
		if (app.page._changed) {
			return '保存されていない変更は破棄されます。';
		}
    });
    */
	$(function () {
		$('body').on('change', 'input,textarea,select', function (e) {
			if (e.originalEvent) {
				app.page._changed = true;
			}
		});
		// 画像（不動産お役立ち情報も含む）
		$('body').on('click', '.item-list .input-img-link .search-btn a', function () {
				if (!$(this).hasClass('is-disable')) {
						app.page.freewordSearchModal(
							$(this).closest('.item-list .input-img-link'),
							$(this).closest('.item-list .input-img-link').find('.select-page select'),
							'.page-name'
						);
				}
		});
		// リンク
		$('body').on('click', '.modal-body .input-link .search-btn a', function () {
			if (!$(this).hasClass('is-disable')) {
				app.page.freewordSearchModal(
					$(this).closest('.modal-body .input-link'),
					$(this).closest('.modal-body .input-link').find('.select-page select'),
                    '.page-name',
                    $(this).closest('.modal-set')
				);
			}
		});
	});

	app.page.freewordSearchModal = function (module, select, pageNameClass, oldModal) {
		var contents =
				'<div class="modal-sitemap js-scroll-container modal-list-search-page" data-scroll-container-max-height="500" style="overflow-y:auto;">' +
					'<div class="edit-modal-sitemap">' +
						'<div class="w80per mr10">' +
							'<input type="text" name="page-title" placeholder="ページタイトルまたは特集名を入力（部分一致）" value=""> ' +
							'<ul>' +
								'<li>' +
									'<label>' +
										'<input type="radio" name="page-type" value="all" checked>' +
										'すべて' +
									'</label>' +
								'</li>' +
								'<li>' +
									'<label>' +
										'<input type="radio" name="page-type" value="special">' +
										'物件特集' +
									'</label>' +
								'</li>' + app.page.ToolTipSearchSpecialLabel +
								'<li>' +
									'<label>' +
										'<input type="radio" name="page-type" value="real-estate">' +
										'不動産お役立ち情報' +
									'</label>' +
								'</li>' +
								'<li>' +
									'<label>' +
										'<input type="radio" name="page-type" value="blog">' +
										'ブログ' +
									'</label>' +
								'</li>' +
							'</ul>' +
						'</div>' +
						'<div class="w20per">' +
							'<a class="btn-t-blue hl-50" id="search-narrow-down" href="javascript:;">絞り込む</a>' +
						'</div>' +
					'</div>' +
					'<div class="page-list">' +
						'<h3>ページ一覧' + app.page.ToolTipTitle + '</h3>' +
						'<table class="tb-basic">' +
							'<thead>' +
								'<tr>' +
									'<th>ページ名</th>' +
									'<th>更新日' + app.page.ToolTipUpdateDate + '</th>' +
									'<th>公開状況</th>' +
								'</tr>' +
							'</thead>' +
							'<tbody>' +
							'</tbody>' +
						'</table>' +
						'<div class="no-page">対象のページがありません。<br>絞り込み条件を変更するか、ページの作成状況を再度確認してください。</div>' +
					'</div>' +
				'</div>';
        var editModal = app.modal.popup({
            title: '追加したいリンク先を選択してください。',
            contents: contents,
            modalBodyInnerClass: 'align-top',
            ok: '追加',
            autoRemove: true,
            closeButton: false
        });
        editModal.oldModal = oldModal;
        if (typeof editModal.oldModal != 'undefined') {
            editModal.oldModal.toggleClass('is-hide', true);
        }
        var pages = sortUpdateDate(setAblePages());
        var canAliasPages = createPageList(pages);
        createPagination($('.page-list .tb-basic'), $('.page-list .tb-basic tbody tr'));
        // editModal.$el.find('.modal-contents').first().css('min-height', '560px');
        editModal.show();
        $('.edit-modal-sitemap #search-narrow-down').on('click', function () {
                searchNarrowDown($('.page-list .tb-basic'), $('.page-list .tb-basic tbody tr'), canAliasPages);
                createPagination($('.page-list .tb-basic'), $('.page-list .tb-basic tbody tr'));
                $('.doing-search').remove();
        });
        editModal.onClose = function (ret, modal) {
            if (typeof editModal.oldModal != 'undefined') {
                editModal.oldModal.toggleClass('is-hide', false);
            }
            var selectPage = modal.$el.find('input[name="select-page"]:checked');
            if (!ret) {
                return;
            }
            if (selectPage.val() == undefined) {
                app.modal.alert('', 'ページを選択してください。');
                return false;
            }
            module.find(pageNameClass).text('選択中ページ：' + selectPage.data('title'));
			module.find('.link-wrapper .errors').text('');
            select.val(selectPage.val());
        }
    }

    function setAblePages () {
        var selectAblePages = [];
        if (!selectAblePages.length) {
            if (app.page.siteMapData) {
                app.page.siteMapData.forEach(function (page) {
                    for (var key in app.page.selectData) {
                        if (page.link_id == key && page.link_id != 0) {
                            page.titleWithFilename = app.page.selectData[key];
                            selectAblePages.push(page);
                        }
                    }
                });
            }
            if (app.page.siteMapIndexData) {
                app.page.siteMapIndexData.forEach(function (page) {
                    for (var key in app.page.selectData) {
                        if (page.link_id == key) {
                            page.titleWithFilename = app.page.selectData[key];
                            selectAblePages.push(page);
                        }
                    }
                });
            }
            if (app.page.estateSiteMapData) {
                if (app.page.estateSiteMapData.top && app.page.estateSiteMapData.top.estate_page_type) {
                    app.page.estateSiteMapData.top.titleWithFilename = app.page.estateSiteMapData.top.title + '（shumoku）';
                    selectAblePages.push(app.page.estateSiteMapData.top);
                }
                if (app.page.estateSiteMapData.baibai_top && app.page.estateSiteMapData.baibai_top.estate_page_type) {
                    app.page.estateSiteMapData.baibai_top.titleWithFilename = app.page.estateSiteMapData.baibai_top.title + '（purchase）';
                    selectAblePages.push(app.page.estateSiteMapData.baibai_top);
                }
                if (app.page.estateSiteMapData.chintai_top && app.page.estateSiteMapData.chintai_top.estate_page_type) {
                    app.page.estateSiteMapData.chintai_top.titleWithFilename = app.page.estateSiteMapData.chintai_top.title + '（rent）';
                    selectAblePages.push(app.page.estateSiteMapData.chintai_top);
                }
                app.page.estateSiteMapData.estateTypes.forEach(function (page) {
                    for (var key in app.page.selectData) {
                        if (page.link_id == key) {
                            page.titleWithFilename = app.page.selectData[key];
                            selectAblePages.push(page);
                        }
                    }
                });
                app.page.estateSiteMapData.specials.forEach(function (page) {
                    for (var key in app.page.selectData) {
                        if (page.link_id == key) {
                            page.titleWithFilename = app.page.selectData[key];
                            selectAblePages.push(page);
                        }
                    }
                });
            }
        }
        return selectAblePages;
    }

    function createPageList(pages) {
        if (pages.length > 0) {
            $('.page-list .tb-basic').removeClass('is-hide');
            $('.page-list .no-page').addClass('is-hide');
        } else {
            $('.page-list .tb-basic').addClass('is-hide');
            $('.page-list .no-page').removeClass('is-hide');
        }
        $.each(pages, function (id, page) {
            var dataPageTitle = page.titleWithFilename;
            if (page.titleWithFilename.length <= 15) {
                var pageTitle = page.titleWithFilename;
            } else {
                var pageTitle = page.titleWithFilename.substr(0, (15)) + '...'
            }
            var publishStatus = page.public_flg ? '公開' : '下書き';
            var appendContent =
                '<tr>' +
                    '<td>' +
                        '<label for="' + page.link_id + '">' +
                            '<input type="radio" name="select-page" data-title="' + dataPageTitle + '" id="' + page.link_id + '" value="' + page.link_id + '">' + pageTitle +
                        '</label>' +
                    '</td>' +
                    '<td>' + page.update_date.substr(0, 10).replace(/-/g, '/') + '</td>' +
                    '<td>' + publishStatus + '</td>' +
                '</tr>';
            $('.page-list tbody').append(appendContent);
        });
        return pages;
    }

    function createPagination(
        element,
        itemElement
    ) {
        var options = {
			itemElement: itemElement,
			perPage: 10,
			onePageOnlyDisplay: true,
			firstEndPageBtnMode: true,
            ellipsisMode: true,
            ellipsisMaxPageNumber: 6,
            pageNumberDisplayNumber : 5,
			prevBtnText: '<',
			nextBtnText: '>',
			firstPageBtnText: '|<',
			endPageBtnText: '>|',
			activeClass: 'active',
            paginationClassName: 'pagination',
            isInputSelect : true
        }
		element.pagination(options);
        if (element.find('input[name="select-page"]').length <= options.perPage) {
            element.closest('.page-list').find('.pagination').remove();
        }
    }

    function searchNarrowDown(
        element,
        itemElement,
        canAliasPages
    ) {
        // 1回目に表示されているデータとページ送りを削除する
        element.next('.pagination').remove();
        itemElement.remove();
        // 検索値を取得する
        var pageType = $('.modal-set').not('.is-hide').find('input[name="page-type"]:checked').val();
        var pageTitle = app.hankanaToZenkana($('.modal-set').not('.is-hide').find('input[name="page-title"]').val().toLowerCase());
        // 検索結果を表示する
        var searchPages = [];
        // ページタイプで絞る
        for (var i = 0; i < canAliasPages.length; i++) {
            switch (pageType) {
                case 'all':
                    searchPages = canAliasPages;
                    break;
                case 'special':
                    if (canAliasPages[i].estate_page_type == 'estate_special') {
                        searchPages.push(canAliasPages[i]);
                    }
                    break;
                case 'real-estate':
                    // TODO 不動産お役立ち情報が追加されたら実装するpage_type_code
                    if ($.inArray(canAliasPages[i].page_category_code, app.page.articleCategories) > -1) {
                        searchPages.push(canAliasPages[i]);
                    }
                    break;
                case 'blog':
                    var blogIndexTypeCode = 14;
                    var blogDetailTypeCode = 15;
                    if (
                        canAliasPages[i].page_type_code == blogIndexTypeCode ||
                        canAliasPages[i].page_type_code == blogDetailTypeCode
                    ) {
                        searchPages.push(canAliasPages[i]);
                    }
                    break;
                default:
                    break;
            }
        }
        // ページ名で絞る
        for (var j = 0; j < searchPages.length; j++) {
            if (pageTitle == '') {
                ;　//そのままページを返す
            } else {
                var filterPages = [];
                searchPages.filter(function (page) {
                    if (page.titleWithFilename.toLowerCase().indexOf(pageTitle) != -1) {
                        filterPages.push(page);
                    }
                });
                return createPageList(filterPages);
            }
        }
        createPageList(searchPages);
    }

    function sortUpdateDate(pages) {
        pages.sort(function(a, b) {
            if (a.update_date > b.update_date) {
                return -1;
            } else {
                return 1;
            }
        });
        return pages;
    }
	
	app.page.sampleModal = function (sample, onClose) {
		return app.modal.popup({
			title: '雛形設定',
			contents: sample,
			modalBodyInnerClass: 'align-top',
			onClose: onClose
		});
	};

    app.page.autoLinkModal = function (onClose) {
        var contents = '<div class="modal-message">'+
            '<p>ひな形（定型例文）のご利用について</p>' +
            '<p>（１）ひな形を使用したことによって生じたトラブル、いかなる損害に対しても当社は一切責任を負いませんので記載されている内容は必ず確認していただき、適宜編集してご利用ください。</p>' +
            '<p>（２）作成・編集済みのページにひな形を再度適用させる場合、現在入力されている内容は破棄されます。</p>' +
        '</div>';
        return app.modal.popup({
            title: '',
            contents: contents,
            modalBodyInnerClass: 'align-top modal-auto-link',
            ok: 'ひな形を適用させる',
            cancel: 'キャンセル',
            onClose: onClose
        });
    };

	app.page.sideCommonOtherLinkNotifyModal = function () {
		var modal = app.modal.popup({
			modalContentsClass: 'modal_announce',
			autoRemove: false,
			ok: false,
			cancel: false,
			contents: '<p>「その他のサイドリンク一覧」のブロックは、<br>ホームページでいうと右図の<span class="txt-attention">赤枠の部分</span>になります。<br><br>「見出し」は<span class="txt-attention_orange">オレンジ枠の部分</span>になります。<br>※全ページ共通で表示されます。<br><br>TOPページ以外の場合、<span class="txt-attention_sub">青枠の部分</span>のように<br>該当ページの下層ページへのリンクも表示されます。</p>'
				+ '<p>例：店舗一覧ページの場合、<br>各店舗ページへのリンクが表示されます。</p>'
		});
		modal.$el.find('.tit-none').removeClass('tit-none');
		return modal;
	};
	
    app.page.addNewsDetailModal = function(href) {
        var contents = '<div class="modal-add-news-detail">' +
                        '<div class="note alert-normal">一度作成したお知らせをもう一方のフォーマットへ変更することはできません。</div>' +
                        '<dl class="modal-add-page">' +
                            '<dt>' +
                                '<div class="label-custom-radio"><div class="custom-radio check" data-type="1"></div>お知らせ（詳細ページ追加）</div>' +
                                '<p>一覧からのリンク先となるページを作成する場合に利用します。</p>' +
                            '</dt>' +
                        '</dl>' +
                        '<dl class="modal-only-add-list">' +
                            '<dt>' +
                                '<div class="label-custom-radio"><div class="custom-radio" data-type="2"></div>お知らせ（一覧のみ追加）</div>' +
                                '<p>一覧にテキストとリンクのみを設置する場合に利用します。</p>' +
                            '</dt>' +
                        '</dl>' +
                    '</div>';
        var modal = app.modal.popup({
            title: '作成したいフォーマットを選択してください。',
            contents: contents,
            modalBodyInnerClass: 'align-top',
            ok: '作成に進む',
            autoRemove: false
        });

        var moduleAddNewsDetail = modal.$el.find('.modal-add-news-detail');
        var btnOk = modal.$el.find('.modal-btns .btn-t-blue')

        moduleAddNewsDetail.on('click', '.label-custom-radio', function() {
            $('.custom-radio').removeClass('check');
            $(this).find('.custom-radio').addClass('check');
            
        })

        modal.onClose = function (ret, modal) {
			if (!ret) {
				return;
            }
            var type = moduleAddNewsDetail.find('.custom-radio.check').data('type');
            var url = [href, 'type=' + type].join('&');
            if (app.page._changed) {
				var str = "別画面へ移動します。よろしいですか？\n\n編集中の内容は保存されません。\n保存する場合は、「保存」を押下してください\n";
                if(confirm(str)) {
                    $(window).off('beforeunload');
                    location.href = url;
                }
                return false;
            }
            location.href = url;
        }
		return modal;
    }
	
	app.page.terminologyModal = function () {
		if (arguments.length === 1) {
			onClose = data;
			data = null;
		}
		var modal = app.modal.popup({
			title: '用語の追加',
			contents: $('#templates #terminologyModal').clone().children(),
			onClose: function (ret) {
				if (ret) {
					return false;
				}
			},
			autoRemove: false,
			modalBodyInnerClass: 'align-top',
			modalContentsClass: 'size-l'
		});
		
		app.page.initWysiwyg(modal.$el);
		var wysi;
		if (modal.$el.find('.has-wysihtml5').length) {
			wysi = modal.$el.find('.has-wysihtml5').data('wysihtml5Instance');
		}
		
		modal.setTitle = function (title) {
			modal.$el.find('.modal-header h2').text(title);
		};
		
		function resetFormData() {
			modal.$el.find('select')[0].selectedIndex = 0;
			modal.$el.find('input,textarea').val('').change();
			modal.$el.find('.select-image a.i-e-delete').remove();
			modal.$el.find('.select-image a').html('<span>画像の追加</span>');
			modal.$el.find('.errors').empty();
			if (wysi) {
				wysi.clear();
			}
		}
		
		modal.reset = function () {
			modal.setTitle('用語の追加');
			resetFormData();
			return this;
		};
		
		modal.setData = function (data) {
			modal.setTitle('用語の編集');
			resetFormData();
			
			$.each(data, function (name, value) {
				var $input = modal.$el.find('[data-name="' + name + '"]');
				$input.val(value).change();
				
				if (name === 'image' && value) {
					modal.$el.find('.select-image a')
						.html('<img src="/image/hp-image?image_id='+value+'">')
						.before('<a class="i-e-delete" href="javascript:;"></a>');
					modal.$el.find('.select-image__tx_annotation').html("「画像」をクリックして画像フォルダから変更してください。");
				}
			});
			
			return this;
		};
		
		var _onClose;
		modal.onClose = function (ret) {
			if (ret) {
				return false;
			}
			_onClose = null;
			if (modal.$el.find('.has-ckeditor')) {
				CKEDITOR.instances[modal.$el.find('.has-ckeditor').attr('id')].destroy();
			}
			this.remove();
		};
		var _show = modal.show;
		modal.show = function (onClose) {
			_onClose = onClose;
			return _show.call(this);
		};
		
		modal.$el.find('.modal-btns .btn-t-blue').on('click', function () {
			app.page.updateAllCkeditorElements();
		});

		app.initApiForm(modal.$el.find('form'), modal.$el.find('.modal-btns .btn-t-blue'), function (data) {
			_onClose && _onClose(modal.$el.find('form').serializeArray());
			_onClose = null;
			modal.close();
		});
		
		return modal;
	};
	
	app.page._terminologyModalInstance = null;
	app.page.getTerminologyModalInstance = function () {
		return app.page.terminologyModal();
	};
	
	app.page.datePicker = function () {
		$('.datepicker:not(.hasDatepicker)').datepicker({
			showOn: "both",
			buttonImage: "/images/common/icon_date.png",
			buttonImageOnly: true
		});
	};
	
    app.page.btnDeleteBusinessUpdate = function ($container) {
        var $listSubParts = $container.find('.sub-parts');
        $container.find('.page-element-body .item-set-header .delete-btn').toggleClass('is-disable', $listSubParts.length <= 1);
        $listSubParts.each(function(){
            $(this).find('.sub-elements .delete-btn').toggleClass('is-disable', $(this).find('.sub-elements .added-item').length <= 1);
        });
}

	app.page.sortUpdate = function ($container) {
		var $sortableItems;
		if ($container.hasClass('page-element-body') && !$container.hasClass('js-side-common')) {
			$sortableItems = $container.children('.added-item');
		}
		else {
			$sortableItems = $container.children('.sortable-item,.unsortable-item');
		}
		$sortableItems.each(function (i) {
			var $item = $(this);
			$item.find('.sort-value').eq(0).val(i);
			
			var $upBtn;
			var $downBtn;
			if ($item.hasClass('page-area') && $item.find('.col').length) {
				$upBtn = $item.find('.col-action .up-btn');
				$downBtn = $item.find('.col-action .down-btn');
			}
			else {
				$upBtn = $item.find('.up-btn').eq(0);
				$downBtn = $item.find('.down-btn').eq(0);
			}
			$upBtn.toggleClass('is-disable', i == 0);
			$downBtn.toggleClass('is-disable', i == $sortableItems.length - 1 || $item.next().hasClass('unsortable-item'));
			
			if ($item.hasClass('page-area') && $item.hasClass('column1')) {
				// 1カラムエリアには1パーツ
				$item.find('.page-element-header .sort-value').val(0);
			}
		});
	};
	
	app.page.toggleFile2Title = function ( $container, isRequired ) {
        // リンクモーダルのファイル名は読み取り専用なのでis-requireつけない
        $container.each(function() {
            if ($(this).hasClass('input-img-title')) {
                $(this).toggleClass(	'is-require'	,		 !$(this).find('input[disabled]').length && isRequired ) ;
                $(this).find(		'.i-l-require'			).toggleClass(	'is-hide'	, !isRequired	) ;
                $(this).find(		'input[type="text"].not-edit'	).prop(			'disabled'	, true	) ;
            }
            if ($(this).hasClass('select-file2-title')) {
                $(this).remove();
            }
        })
	};
	
	app.page.initFile2Title = function ( $container ) {
		$container.find( '.select-file2 a > span' ).each( function () {
			var $a = $(this).parent() ;
			
			app.page.toggleFile2Title( $(this).closest('.select-file2').parent().find('.select-file2-title,.input-img-title'), false ) ;
		});
	};
	
	app.page.toggleImageTitle = function ($container, isRequired) {
		$container.toggleClass('is-require', isRequired);
		$container.find('.i-l-require').toggleClass('is-hide', !isRequired);
		$container.find('input[type="text"]').prop('disabled', !isRequired);
	};
	
	app.page.initImageTitle = function ($container) {
		$container.find('.select-image a > span').each(function () {
			var $a = $(this).parent();
			
			app.page.toggleImageTitle($(this).closest('.select-image').parent().find('.select-image-title,.input-img-title'), false);
		});
	};

	app.page.initUseLinkLoad = function ($elements, isMainImage = false) {
		// ページをリロード時に「リンクを利用する」の初期化処理
		$elements.each(function () {
			var $this = $(this);
			var linkHouse = $this.find('.input-img-link .input-img-wrap .link-house-module input[type="hidden"]').val();
			$this.find('.use-image-link').prop('checked',
				(
					$this.find('.input-img-link .input-img-wrap .select-file2 input[type="hidden"]' ).val() > 0 ||
					$this.find('.input-img-link .input-img-wrap select').val() ||
					$this.find('.input-img-link .input-img-wrap input[type="text"]').val() ||
					linkHouse
				)
			);
	
			$this.find('.input-img-link .input-img-wrap').toggle($this.find('.use-image-link').prop('checked'));
            if (!$this.find('.use-image-link').prop('checked')) {
                $this.find('.ml-link-target-blank').prop('checked', true);
            }
			$this.find('input[type="radio"]:eq(0)').change();
	
			if ($this.find('.input-img-link .input-img-wrap .select-file2 input[type="hidden"]' ).val() > 0) {
					$this.find('.input-img-width input.not-edit').val($this.find('.input-img-width input.not-edit').next().val());
			}
			
			if (isMainImage) {
				app.page.initImageTitle($this);
			}
		});
	}

	app.page.initUseLinkForm = function ($container) {
		// ページ保存時に「リンクを利用する」でチェックが入っていないものは初期化する
		$container.each(function() {
			var useImageLinkCheck = $(this).find('.use-image-link').prop('checked') ? true : false;
			var selectPageCheck = $(this).find('input:radio').eq(0).prop('checked');
			var urlCheck = $(this).find('input:radio').eq(1).prop('checked');
			var fileCheck = $(this).find('input:radio').eq(2).prop('checked');
			var bukkenCheck = $(this).find('input:radio').eq(3).prop('checked');

			var isLinkElement = false;
			var dataType = $(this).closest('.item-list').data('type');
			if (dataType !== undefined && dataType === 'link') {
				isLinkElement = true;
			}

			function resetExistingPage(element) {
				element.find('.page-name').text('');
				element.find('.select-page select').val('');
			}
			function resetUrl(element) {
				element.find('.link-wrapper:eq(1) input:text').val('');
				element.find('.link-wrapper:eq(1) .watch-input-count').trigger("change");
			}
			function resetFile(element) {
				element.find('.select-file2-title').text('');
				element.find('.select-file2 input').val('');
				element.find('.link-wrapper:eq(2) input:text').val('');
				app.page.initCountInput(element.find('.link-wrapper:eq(2) .watch-input-count'));
			}
			function resetHouse(element) {
				element.find('.house-title label').text('');
				element.find('.house-title input').val('');
				element.find('.house-title a').removeAttr('data-href');
				element.find('.member-no-info label').text('');
				element.find('.search-house-method input:radio').eq(0).prop('checked', true).change();
				element.find('.link-house-module .display-house-title').css('display', 'none');
				element.find('.link-house-module .member-no-info').addClass('is-hide');
				element.find('.link-wrapper:eq(3) input:text').val('');
				app.page.initCountInput(element.find('.link-wrapper:eq(3) .watch-input-count'));
			}

			if (!useImageLinkCheck && !isLinkElement) {
				// チェックをリセット
				$(this).find('.ml-link-target-blank').prop('checked', true);
				$(this).find('.select-page-radio input').prop('checked', true).change();

				resetExistingPage($(this));
				resetUrl($(this));
				resetFile($(this));
				resetHouse($(this));
			}

			if (selectPageCheck) {
				resetUrl($(this));
				resetFile($(this));
				resetHouse($(this));
			}
			if (urlCheck) {
				resetExistingPage($(this));
				resetFile($(this));
				resetHouse($(this));
			}
			if (fileCheck) {
				resetExistingPage($(this));
				resetUrl($(this));
				resetHouse($(this));
			}
			if (bukkenCheck) {
				resetExistingPage($(this));
				resetUrl($(this));
				resetFile($(this));
			}
		})
	}
    app.page.initCountInput = function(input) {
        input.each(function() {
            var inputCount = $(this).parent().find('.input-count');
            if (inputCount.length > 0) {
                inputCount.text('0/'+inputCount.text().split('/')[1]);
            }
        })
    }
	
	function _updateWysiwygElement(e) {
		if (e.editor.checkDirty()) {
			e.editor.updateElement();
			e.editor.resetDirty();
		}
	}
	
	function _createUpdateWysiwygElement(editor) {
		return function (e) {
            if (typeof e != 'undefined') {
                e.editor = editor;
                _updateTitleDetailList(editor);
                _updateWysiwygElement(e);
                _updateCountWysiwygElement(editor);
            }
		}
    }

    function _updateCountWysiwygElement(editor) {
        var inputCount = $('#' + editor.element.getId()).parent().find('.input-count');
        if (inputCount.length) {
            var str = editor.getData().replace(/<[^>]*>/g, '').replace(/&nbsp;/gi,' ');
            var max = editor.element.data('maxlength');
            var count = str.length
            var countStr =  count; 
            if (!isNaN(max)) {
                countStr += '/' + max;
            }
            inputCount.toggleClass('is-over', !isNaN(max) && count > max);
            inputCount.text(countStr);
        }
    }
    
    function _updateTitleDetailList(editor) {
        if (editor.element.hasClass('has-detail-list')) {
            var text = editor.getData().replace(/<[^>]*>/g, '').replace(/&nbsp;/gi,'');
            if (text.length > 20) {
                text = text.substring(0, 19) + '...';
            }
            $('input[name="tdk[title]"]').val(text).parent().find('span').html(text);
        }
    }
	
	app.page._initWysiwyg = function (element, config) {
		var editor = CKEDITOR.replace(element, config || {});
		editor.on('instanceReady', function () {
			editor.on('blur', _updateWysiwygElement);
			editor.on('saveSnapshot', _updateWysiwygElement);
            editor.on('afterCommandExec', _updateWysiwygElement);
			editor.document.on('keyup', _createUpdateWysiwygElement(editor));
			editor.document.on('keypress', _createUpdateWysiwygElement(editor));
            editor.document.on('click', _createUpdateWysiwygElement(editor));
            editor.document.on('focus', _createUpdateWysiwygElement(editor) );
			// var data = editor.getData();
			// editor.setData(data);
		});
	};
	
	app.page.updateAllCkeditorElements = function () {
		if (!window.CKEDITOR) {
			return;
		}
		
		$.each(CKEDITOR.instances, function (id, editor) {
			if (editor.checkDirty()) {
				editor.updateElement();
				editor.resetDirty();
			}
		});
	};
	
	app.page.initWysiwyg = function ($containers) {
		if (DISABLE_WYSIWYG) {
			return;
		}
		
		$containers.each(function () {
			var $container = $(this);
			var $toolbar = $container.find('.element-text-util');
			var $textarea = $container.find('textarea');
			
			if ($textarea.hasClass('has-ckeditor')) {
				return;
			}
			
			$toolbar.hide();
			
			app.page._initWysiwyg($textarea[0], {
				height: $textarea.height()
			});
			$textarea.addClass('has-ckeditor');
		});
	};
	
	app.page.initLinkWysiwyg = function ($containers) {
		if (DISABLE_WYSIWYG) {
			return;
		}
		
		$containers.each(function () {
			var $container = $(this);
			var $toolbar = $container.find('dd:eq(0)');
			var $textarea = $container.find('textarea');
			
			if ($textarea.hasClass('has-ckeditor')) {
				return;
			}
			
			$toolbar.hide();
			
			app.page._initWysiwyg($textarea[0], {
				toolbar: 'Link',
				height: 50
			});
			$textarea.addClass('has-ckeditor');
		});
	};
	
    app.page.initWysiwygListTitle = function ($containers, $toolbar) {
		if (DISABLE_WYSIWYG) {
			return;
		}
		
		$containers.each(function () {
			var $container = $(this);
			var $textarea = $container.find('textarea');
			
			if ($textarea.hasClass('has-ckeditor')) {
				return;
			}
			
			app.page._initWysiwyg($textarea[0], {
				toolbar : $toolbar
			});
			$textarea.addClass('has-ckeditor');
		});
	};
	
	app.page.destroyWysiwyg = function ($container) {
		$container.find('.has-ckeditor').each(function () {
			var $this = $(this);
			var id = $this.attr('id');
			$this.removeClass('has-ckeditor');
			if (CKEDITOR.instances[id]) {
				CKEDITOR.instances[id].destroy();
			}
		});
	};
	
	app.page.initLinkEnable = function ($container) {
		$container.find('.input-img-wrap').each(function () {
			$(this).find('input[type="radio"]:eq(0)').change();
		});
	};
	
	var template = app.page.template = function () {
		if (!(this instanceof app.page.template)) {
			return app.page.template.getInstance();
		}
		
		this.main = {};
		this.side = {};
		
		this.init();
	};
	template.prototype = {
		init: function () {
			var self = this;
            var temp = '';
			$.each(['main', 'side'], function (i, section) {
				$('#templates #'+section+'-parts .page-element').each(function () {
					var $this = $(this);
					var type = $this.attr('data-type');
                    temp = $this.attr('data-template');
					var info = {
						type:     type,
						typeName: $this.attr('data-type-name'),
						title:    $this.find('> .page-element-header h3 > span').text(),
						isUnique: !!$this.attr('data-is-unique'),
						hasElement: !!$this.attr('data-has-element'),
						maxElementCount: 0,
						isSide:   section === 'side',
						$elem:    $this
					};

					$this.find('input[checked]').attr('data-checked', true);
					
					if (info.hasElement) {
						var maxElementCount = parseInt($this.attr('data-max-element-count'));
						if (!isNaN(maxElementCount)) {
							info.maxElementCount = maxElementCount;
						}
						
						info.elements = {};
						info.elementTypes = [];
						if (info.type == '110' || info.type == '112' || temp == 'origin') {
							var template = $this.find('.page-element-body > .added-item');
                            var typeActicle = template.attr('data-template');
                            var articleElm = [];
                            template.each(function () {
                                var data = $(this).find('.item-set-list2 .added-item');
                                articleElm.push([data.eq(0), data.eq(1), data.eq(2)])
                            });
                            if (typeActicle == 'origin') {
                                template.eq(0).find('.item-set-list2 .added-item').remove();
                                // template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                                // template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                                // template.eq(1).find('.item-set-list2 .added-item').eq(0).remove();
                                // template.eq(1).find('.item-set-list2 .added-item').eq(0).attr('data-name', 0);
                                // template.eq(1).find('.item-set-list2 .added-item').eq(1).remove();
                                // template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                                // template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();

                            }
                            // if (typeActicle == 'origin_a') {
                            //     var elmText = articleElm[0][0].clone();
                            //     elmText.attr('data-name', 2);
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).attr('data-name', 1);
                            //     template.eq(0).find('.item-set-list2').append(elmText);
                            //     template.eq(0).find('.item-set-list2').append(template.eq(0).find('.item-set-list2 .item-add'));
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).remove();
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).attr('data-name', 0);
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                            // }
                            // if (typeActicle == 'origin_b') {
                            //     var elmText = articleElm[1][0].clone();
                            //     elmText.attr('data-name', 2);
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).remove();
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).attr('data-name', 0);
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(1).attr('data-name', 1);
                            //     template.eq(1).find('.item-set-list2').append(elmText);
                            //     template.eq(1).find('.item-set-list2').append(template.eq(1).find('.item-set-list2 .item-add'));
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                            // }
                            // if (typeActicle == 'origin_c') {
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).attr('data-name', 1);
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).find('input[type="radio"]:eq(0)').attr('name', 'link_type_');
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).remove();
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).attr('data-name', 0);
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(1).attr('data-name', 1);
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                            // }
                            // if (typeActicle == 'origin_d') {
                            //     var elmText = articleElm[2][0].clone();
                            //     elmText.attr('data-name', 2);
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(0).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).remove();
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(0).attr('data-name', 0);
                            //     template.eq(1).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).remove();
                            //     template.eq(2).find('.item-set-list2 .added-item').eq(1).attr('data-name', 1);
                            //     template.eq(2).find('.item-set-list2').append(elmText);
                            //     template.eq(2).find('.item-set-list2').append(template.eq(2).find('.item-set-list2 .item-add'));
                            // }
						}
						$this.find('.page-element-body > .added-item').each(function () {
							var $this = $(this);
							var type = $this.attr('data-type');
							var isPreset = !!$this.attr('data-is-preset');
							if (info.type == '110' || info.type == '112' || temp == 'origin') {
								info.elements[type] = {
									type: type,
									title: $this.attr('data-title'),
									isUnique: !!$this.attr('data-is-unique'),
									isPreset: isPreset,
									hasElement: $this.hasClass('sub-parts'),
									$elem: isPreset ? template.clone() : $this.remove(),
                                    $articleElm: articleElm[$this.attr('data-name')] ? articleElm[$this.attr('data-name')] : null
								};
							} else {
								info.elements[type] = {
									type: type,
									title: $this.attr('data-title'),
									isUnique: !!$this.attr('data-is-unique'),
									isPreset: isPreset,
									hasElement: $this.hasClass('sub-parts'),
									$elem: isPreset ? $this.clone() : $this.remove()
								};
							}
							
							info.elementTypes.push(type);
							
							// subParts
							if (info.elements[type].hasElement) {
								info.elements[type].elements = {};
								info.elements[type].elementTypes = [];
                                if (info.type == '110' || info.type == '112' || temp == 'origin') {
                                    $.each(info.elements[type].$articleElm, function( i, l ){
                                        var $this = $(this);
                                        var subType = $this.attr('data-type');
                                        var isPreset = !!$this.attr('data-is-preset');
                                        info.elements[type].elements[subType] = {
                                            type: subType,
                                            title: $this.attr('data-title'),
                                            isUnique: !!$this.attr('data-is-unique'),
                                            isPreset: isPreset,
                                            $elem: isPreset ? $this.clone() : $this.remove()
                                        };
                                        
                                        info.elements[type].elementTypes.push(subType);
                                    });
                                } else {
    								info.elements[type].$elem.find('.sub-elements > .added-item').each(function () {
    									var $this = $(this);
    									var subType = $this.attr('data-type');
    									var isPreset = !!$this.attr('data-is-preset');
    									info.elements[type].elements[subType] = {
    										type: subType,
    										title: $this.attr('data-title'),
    										isUnique: !!$this.attr('data-is-unique'),
    										isPreset: isPreset,
    										$elem: isPreset ? $this.clone() : $this.remove()
    									};
    									
    									info.elements[type].elementTypes.push(subType);
    								});
                                }
								
								$.unique(info.elements[type].elementTypes);
							}
						});
						
						$.unique(info.elementTypes);
					}
					
					self[section][type] = info;
				});
			});
			
		}
	};
	
	template.instance = null;
	template.getInstance = function () {
		if (!template.instance) {
			template.instance = new template();
		}
		return template.instance;
	};
	
	app.page.initialize = function ($container, info) {
		
		var $section  = $container.closest('.section');
		var section = $section.attr('data-section');
		
		var $area = $container.closest('.page-area');
		var areaNo = $area.attr('data-name');
		
		var partsNo = $container.attr('data-name');
		var newName = section + ($area.length?'[' + areaNo + '][parts]':'') + '[' + partsNo + ']';
		var newId   = section + '-' + ($area.length?areaNo + '-parts-':'') + partsNo;
		
		$container.attr({
			'data-parts-name': newName,
			'data-parts-id': newId
		});
		
		$container.find('input[data-checked]').prop('checked', true);
		
		var colNo = 1;
		if ($container.parent().hasClass('col')) {
			colNo = ( $container.closest('.page-area').find('.col').index($container.parent()) ) + 1;
		}
		
		$container.find('.page-element-header input[name="parts_type_code"]').val(info.type);
		$container.find('.page-element-header input[name="column_sort"]').val(colNo);
		$container.find('.page-element-header input[name="display_flg"]').val(1);
		$container.find('.page-element-header input[name="sort"]').val(0);
		
		$container.find('input,select,textarea').each(function () {
			var _name = $(this).attr('name');
			if (!_name || $(this).closest('.added-item').length) {
				return;
			}
			
			var suffix = '';
			if (_name.match(/\[\]$/)) {
				suffix = '[]';
				_name = _name.replace(/\[\]$/, '');
			}
			
			_name = _name.replace(/\]$/, '').split('[').pop();
			
			$(this).attr({
				id: newId + '-' + _name,
				name: newName + '[' + _name + ']' + suffix
			});
		});
		
		app.page.sortUpdate($container.closest('.sortable-item-container'));
		
        if ( info.typeName === 'businesscontent' )
        {
            app.page.btnDeleteBusinessUpdate($container)
        }
            
		app.page.datePicker();
		
		$container.find('[placeholder]').ahPlaceholder();
		
		// リンク初期化
		if (info.typeName === 'image') {
			$container.find('.input-img-link .input-img-wrap').toggle($container.find('.use-image-link').prop('checked'));
			$container.find('input[type="radio"]:eq(0)').change();
		}
		
		app.page.initLinkEnable($container);
        app.page.initImageTitle($container);
		
		if (info.typeName === 'map') {
			app.page.parts.map($container, info);
		}
		else if (info.typeName === 'terminology') {
			app.page.parts.terminology($container, info);
		}
		else if (info.hasElement) {
			app.page.parts.hasElement($container, info);
        }
        
        if (info.typeName ==='articletemplate' || info.typeName ==='originaltemplate') {
			$container.find('.use-image-link').each(function () {
				if ($(this).is(':checked')) {
                    var inputWrap = $(this).parent().parent();
                    inputWrap.find('.input-img-wrap').show();
                    if (inputWrap.find('.select-file2-title').length) {
                        inputWrap.find('input[type="radio"]').eq(2).prop('checked', true).change();
                    }
				}
			});
		}
		
		// wysiwyg初期化
		var $textUtil = $container.find('.element-text-util');
		if ($textUtil.length) {
			app.page.initWysiwyg($textUtil.closest('.element-text-utilcontainer,.page-element-body'));
		}
		if (info.typeName === 'lists') {
			app.page.initLinkWysiwyg($container.find('.added-item'));
		}
	};
	
	app.page.parts = {};
	
	app.page.parts.hasElement = function ($container, info) {
		var $itemAdd = $container.find('.page-element-body > .item-add');
		var $itemSelect = $itemAdd.find('select');
		var $itemAddBtn = $itemAdd.find('a');
		var canSelect = !!$itemSelect.length;
		
		var partsId   = $container.attr('data-parts-id');
		var partsName = $container.attr('data-parts-name');
		
		function initSubParts($subParts) {
			// subParts用
			$subParts.find('.sub-elements').each(function () {
				var subPartsNo = $subParts.attr('data-name');
				// if ($subParts.attr('data-type') == 'articles') {
				// 	subPartsNo = $(this).attr('data-name');
				// }
				$(this).attr({
					'data-parts-name': partsName + '[elements][' + subPartsNo + ']',
					'data-parts-id': partsId + '-elements-' + subPartsNo
				});
			});
		}
		
		// 属性初期化
		function initSubAttrs($element) {
			$element.find('input,select,textarea').each(function () {
				var _name = $(this).attr('name');
				if (!_name || $(this).hasClass('ignore-attrs')) {
					return;
				}
				
				var $parts = $(this).closest('[data-parts-id]');
				var no = $(this).closest('[data-name]').attr('data-name');
				
				_name = _name.replace(/\]$/, '').split('[').pop();
				$(this).attr({
					id: $parts.attr('data-parts-id') + '-elements-' + no + '-' + _name,
					name: $parts.attr('data-parts-name') + '[elements][' + no + '][' + _name + ']'
				});
			});
		}
		function initAttrs ($element) {
			if ($element.hasClass('sub-parts')) {
				initSubAttrs($element);
			}
			else {
				var no = $element.attr('data-name');
				
				$element.find('input,select,textarea').each(function () {
					var _name = $(this).attr('name');
					if (!_name || $(this).hasClass('ignore-attrs')) {
						return;
					}
					
					_name = _name.replace(/\]$/, '').split('[').pop();
					$(this).attr({
						id: partsId + '-elements-' + no + '-' + _name,
						name: partsName + '[elements][' + no + '][' + _name + ']'
					});
				});
			}
		}
		
		// 選択肢
		function updateSelect () {
			if (info.maxElementCount) {
				$itemAddBtn.toggleClass('is-disable', info.maxElementCount <= $container.find('.page-element-body > .added-item').length);
			}
			
			
			if (!canSelect) {
				return;
			}
			
			var options = '<option value="">選択して下さい</option>';
			
			$.each(info.elementTypes, function (i, type) {
				if (info.elements[type].isUnique && $container.find('.added-item[data-type="'+type+'"]').length) {
					return;
				}
				
				options += '<option value="'+type+'">'+info.elements[type].title+'</option>';
			});
			
			$itemSelect.html(options);
		}
		
		function updateSubSelect($element) {
			var type = $element.attr('data-type');
			var elemInfo = info.elements[type];
			
			if (!elemInfo) {
				return;
			}
			
			var $add = $element.find('.item-add');
			var $select = $add.find('select');
			
			var options = '<option value="">選択して下さい</option>';

			$.each(elemInfo.elementTypes, function (i, subType) {
				if (elemInfo.elements[subType].isUnique && $element.find('.added-item[data-type="'+subType+'"]').length) {
					return;
				}
				
				options += '<option value="'+subType+'">'+elemInfo.elements[subType].title+'</option>';
			});
			
			$select.html(options);
		}
		
		// 追加
		$itemAddBtn.on('click', function () {
			
			if ($itemAddBtn.hasClass('is-disable')) {
				return;
			}
			
			var type;
			if (canSelect) {
				type = $itemSelect.val();
			}
			else {
				type = info.elementTypes[0];
			}
			
			if (!type || !info.elements[type]) {
				return;
			}
			
			var elemInfo = info.elements[type];
			var $elem = elemInfo.$elem.clone();
			
			var $title = $elem.find('dt input:text');
			if (elemInfo.isUnique) {
				$title.val($title.attr('placeholder'));
			}
			else {
				$title.val('');
			}
			
			var newElementNo = 0;
			$container.find('.page-element-body > .added-item').each(function () {
				var elementNo = parseInt($(this).attr('data-name'));
				if (newElementNo <= elementNo) {
					newElementNo = elementNo + 1;
				}
			});
			if (type == 'articles') {
				for (var i = 0; i < $elem.length; i++) {
					$elem[i].setAttribute('data-name', newElementNo + i);
					$elem.find('.sub-elements')[i].setAttribute('data-name', newElementNo + i);
				}
			} else {
				$elem.attr('data-name', newElementNo);
			}
			$itemAdd.before($elem);
			
			if (elemInfo.hasElement) {
				initSubParts($elem);
			}
			
			initAttrs($elem);
			
			updateSelect();
			app.page.sortUpdate($itemAdd.parent());
			
			app.page.datePicker();
			
			$title.change();
			$elem.find('input[data-checked]').prop('checked', true);
			$elem.find('[placeholder]').ahPlaceholder();
			
			if (elemInfo.hasElement) {
				updateSubSelect($elem);
				app.page.sortUpdate($elem.find('.sortable-item-container'));
			}
			
			app.page.initLinkEnable($container);
			app.page.initImageTitle($container);
			// wysiwyg初期化
			var $textUtil = $container.find('.element-text-util');
			if ($textUtil.length) {
				app.page.initWysiwyg($textUtil.closest('.element-text-utilcontainer,.page-element-body'));
			}
			if (info.typeName === 'lists') {
				app.page.initLinkWysiwyg($container.find('.added-item'));
			}
			
			if (info.typeName === 'fordownloadapplication') {
				$elem.find('.item-set-list').initUpload();
			}
			
			if (info.typeName === 'sellingcasedetail' || info.typeName === 'eventdetail' || info.typeName === 'columndetail' )
			{
				$container.find('.page-element-body .delete-btn').toggleClass('is-disable', $container.find('.page-element-body > .added-item').length <= 1);
			}
			if ( info.typeName === 'businesscontent' )
			{
				app.page.btnDeleteBusinessUpdate($container)
			}
            $elem.find('.search-house-method input:radio').eq(0).prop('checked', true).change();
		});
		
		$container.on('click', '.sub-parts .item-add a', function () {
			var $set = $(this).closest('.sub-parts');
			var $list = $set.find('.sub-elements');
			var $itemAdd = $(this).closest('.item-add');
			
			var type = $set.attr('data-type');
			var elemInfo = info.elements[type];
			
			if (!elemInfo) {
				return;
			}
			
			var $select = $itemAdd.find('select');
			var subType;
			
			if ($select.length) {
				subType = $select.val();
			}
			else {
				subType = elemInfo.elementTypes[0];
			}
			
			if (!subType || !elemInfo.elements[subType]) {
				return;
			}
			
			var subInfo = elemInfo.elements[subType];
			var $elem = subInfo.$elem.clone();
			
			var $title = $elem.find('dt input');
			if (elemInfo.isUnique) {
				$title.val($title.attr('placeholder'));
			}
			else {
				$title.val('');
			}
			
			var newElementNo = 0;
			$set.find('.added-item').each(function () {
				var elementNo = parseInt($(this).attr('data-name'));
				if (newElementNo <= elementNo) {
					newElementNo = elementNo + 1;
				}
			});
			$elem.attr('data-name', newElementNo);
			
			var tryScrollToElement = false;
			if (info.typeName === 'recruit') {
				if ($list.find('.fix-bottom-item').length) {
					$list.find('.fix-bottom-item:first').before($elem);
					tryScrollToElement = true;
				}
				else {
					$list.append($elem);
				}
			}
			else if ($list.find('.item-add').length) {
				$list.find('.item-add').before($elem);
			}
			else {
				$list.append($elem);
			}
			if (subType == 'image') {
				$('#link_type-1').val(1);
				$('#link_type-2').val(2);
				$('#link_type-3').val(3);
                $('#link_type-4').val(4);
				$('#link_type-1').prop("checked", true).change();
			}
			if (subType == 'image_text') {
				$('#art_link_type-1').val(1);
				$('#art_link_type-2').val(2);
				$('#art_link_type-3').val(3);
                $('#art_link_type-4').val(4);
                $('#art_link_type-1').prop("checked", true).change();
			}
			initSubAttrs($elem);
			if ( info.typeName === 'businesscontent' )
			{
				app.page.btnDeleteBusinessUpdate($container);
			}
			
			app.page.sortUpdate($list);
			updateSubSelect($set);
			app.page.initImageTitle($elem);
			// wysiwyg初期化
			var $textUtil = $elem.find('.element-text-util');
			if ($textUtil.length) {
				app.page.initWysiwyg($textUtil.closest('.element-text-utilcontainer,.page-element-body'));
			}
			
			if (tryScrollToElement) {
				setTimeout(function () {
					
					app.scrolltoElementIfOutOfscreen($elem, -50);
					
				}, 100);
            }
            $elem.find('.search-house-method input:radio').eq(0).prop('checked', true).change();
		});
		
		$container.on('destroy-element', function (e, $parent) {
			if ($parent.hasClass('sub-elements')) {
				updateSubSelect($parent.closest('.item-set'));
			}
			else {
				updateSelect();
			}
			
			if ( info.typeName === 'sellingcasedetail' || info.typeName === 'eventdetail' || info.typeName === 'columndetail' )
			{
				$container.find('.page-element-body .delete-btn').toggleClass('is-disable', $container.find('.page-element-body > .added-item').length <= 1);
			}
			if ( info.typeName === 'businesscontent' )
			{
				app.page.btnDeleteBusinessUpdate($container);
			}
		});
		
		$container.on('destroy', function () {
			$itemAddBtn.off();
			$container.off();
		});
		
		// 初期化
		$container.find('.sub-parts').each(function () {
			initSubParts($(this));
			updateSubSelect($(this));
		});
		$container.find('.page-element-body > .added-item').each(function (i) {
			initAttrs($(this));
		});
		updateSelect();
		
		$container.find('.sortable-item-container').each(function () {
			app.page.sortUpdate($(this));
		});
		
		if (info.typeName === 'fordownloadapplication') {
			$container.find('.item-set-list').initUpload();
		}
		
		if ( info.typeName === 'sellingcasedetail' || info.typeName === 'eventdetail' || info.typeName === 'columndetail' )
		{
			$container.find('.page-element-body .delete-btn').toggleClass('is-disable', $container.find('.page-element-body > .added-item').length <= 1);
		}
		if ( info.typeName === 'businesscontent' )
		{
			app.page.btnDeleteBusinessUpdate($container);
		}
	};
	
	app.page.parts.terminology = function ($container, info) {
		
		var $itemAddBtn = $container.find('.page-element-body > .btn-right a');
		
		function _searchBefore($elements, kana) {
			var $element;
			for (var i=$elements.length; i--;) {
				$element = $elements.eq(i);
				if ($element.attr('data-kana') <= kana) {
					return $element;
				}
			}
		}
		
		var partsId   = $container.attr('data-parts-id');
		var partsName = $container.attr('data-parts-name');
		
		function initAttrs($element) {
			var no = $element.attr('data-name');
			
			$element.find('input,select,textarea').each(function () {
				var _name = $(this).attr('name');
				if (!_name || $(this).hasClass('ignore-attrs')) {
					return;
				}
				
				_name = _name.replace(/\]$/, '').split('[').pop();
				$(this).attr({
					id: partsId + '-elements-' + no + '-' + _name,
					name: partsName + '[elements][' + no + '][' + _name + ']'
				});
			});
		}
		
		function setData($elem, data) {
			$.each(data, function (i, val) {
				var $input = $elem.find('[data-name="' + val.name + '"]');
				$input.val(val.value || '');
				
				if (val.name === 'kana') {
					$elem.attr('data-kana', val.value);
				}
				
				var _html;
				if (!val.value) {
					_html = '';
				}
				else if (val.name === 'image') {
					_html = '<img src="/image/hp-image?image_id=' + val.value + '">';
				}
				else if (val.name === 'description') {
					_html = val.value;
				}
				else {
					_html = app.h(val.value).replace(/\r?\n/g, '<br>');
				}
				
				$input.prev().html(_html);
			});
		}
		
		function updateElem($elem, data) {
			var kana = data[1].value;
			
			setData($elem, data);
			
			var $header = _searchBefore($container.find('.page-element-body .page-element-header'), kana);
			if (!$header) {
				return;
			}
			
			var kanaHeader = $header.attr('data-kana');
			$elem.attr('data-kana-header', kanaHeader);
			
			var $before = _searchBefore($header.nextAll('[data-kana-header="'+kanaHeader+'"]'), kana);
			if (!$before) {
				$before = $header;
			}
			$before.after($elem);
		}

		var type = info.elementTypes[0];
		if (!type || !info.elements[type]) {
			return;
		}
		var elemInfo = info.elements[type];
		
		// 追加
		$itemAddBtn.on('click', function () {
			
			app.page.getTerminologyModalInstance().reset().show(function (data) {
				var $elem = elemInfo.$elem.clone();
				
				updateElem($elem, data);
				
				var newElementNo = 0;
				$container.find('.page-element-body > .added-item').each(function () {
					var elementNo = parseInt($(this).attr('data-name'));
					if (newElementNo <= elementNo) {
						newElementNo = elementNo + 1;
					}
				});
				$elem.attr('data-name', newElementNo);
				
				initAttrs($elem);
				
				$elem.find('.sort-value').val(0);
				
			});
		});
		
		// 編集
		$container.on('click', '.word-edit-btn', function () {
			
			var $elem = $(this).closest('.item-set');
			var editData = {};
			$elem.find('[data-name]').each(function () {
				var $this = $(this);
				editData[$this.attr('data-name')] = $this.val();
			});
			
			
			app.page.getTerminologyModalInstance().setData(editData).show(function (data) {
				
				updateElem($elem, data);
			});
		});
		
		
		$container.on('destroy', function () {
			$itemAddBtn.off();
			$container.off();
		});
	};
	
	app.page.parts.map = function ($container) {
		
		var $centerLat = $container.find('.center_lat');
		var $centerLng = $container.find('.center_lng');
		var $pinLat = $container.find('.pin_lat');
		var $pinLng = $container.find('.pin_lng');
		var $zoom = $container.find('.zoom');
		
		var mapOptions = {
			disableDefaultUI: true,
			center: new google.maps.LatLng(parseFloat($centerLat.val()), parseFloat($centerLng.val())),
			zoom: parseInt($zoom.val()),
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.DEFAULT,
				position: google.maps.ControlPosition.RIGHT_BOTTOM
			},
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			mapTypeControl: false,
			panControl: false,
			scaleControl: false,
			streetViewControl: false,
			overviewMapControl: false,
			scrollwheel: false
		};
		
		var map = new google.maps.Map($container.find('.google-map')[0], mapOptions);
		
		var markerOptions = {
			position: new google.maps.LatLng(parseFloat($pinLat.val()), parseFloat($pinLng.val())),
			map: map,
			draggable: true,
			title: '中心位置'
		};
		
		var marker = new google.maps.Marker(markerOptions);
		
		google.maps.event.addListener(map, 'zoom_changed', function () {
			$zoom.val(map.getZoom());
		});
		
		google.maps.event.addListener(map, 'center_changed', function () {
			var latLng = map.getCenter();
			$centerLat.val(latLng.lat());
			$centerLng.val(latLng.lng());
		});
		
		google.maps.event.addListener(marker, 'position_changed', function () {
			var latLng = marker.getPosition();
			$pinLat.val(latLng.lat());
			$pinLng.val(latLng.lng());
		});
		
		google.maps.event.addListener(map, 'click', function (e) {
			marker.setPosition(e.latLng);
			map.setCenter(e.latLng);
		});
		
		google.maps.event.addListener(marker, 'dragend', function (e) {
			map.setCenter(e.latLng);
		});
		
		var $query = $container.find('.item-add input');
		var $searchBtn = $container.find('.item-add a');
		
	    // search
	    var service = new google.maps.places.PlacesService(map);

	    var callback = function (results, status) {

	      if (status !== google.maps.places.PlacesServiceStatus.OK) {
	        return;
	      }

	      var place = results[0].geometry.location;
	      marker.setPosition(place);
	      map.setCenter(place);
	      map.setZoom(15);
	    }
	    
		$searchBtn.on('click', function () {
			var val = $query.val();
			if (!val) {
				return false;
			}
			
			var request = {
					query: val,
					location: marker.getPosition(),
					radius: '30000'
			};
			
			service.textSearch(request, callback);
			
			return false;
		});
		
		$container.on('destroy', function () {
			google.maps.event.clearInstanceListeners(map);
			google.maps.event.clearInstanceListeners(marker);
			$searchBtn.off();
		});
	};
	
	
	// 全部削除
	app.page.removeAllParts = function ($elem) {
		var $sections = $('#section-main');
		var $items = $elem.find('.unsortable-item,.sortable-item');
		$items.each(function () {
			var $item = $(this);
			var section = $item.closest('.section').attr('data-section');
			
			var $sortContainer = $item.parent();
			
			// エリア削除の場合
			if ($item.hasClass('page-area')) {
				$item.find('.page-element').trigger('destroy');
			}
			else {
				// 1エリア1パーツの場合（1カラムエリアの場合）
				if (section === 'main' && $item.hasClass('page-element') && !$item.parent().hasClass('col')) {
					$item.trigger('destroy');
				}
				// パーツエレメントの場合
				else if ($item.hasClass('added-item')) {
					var $parts = $item.closest('.page-element');
					var $parent = $item.parent();
					// パーツエレメント削除を通知
					$parts.trigger('destroy-element', [$parent]);
					$item.attr('data-after-destory',1);
				}
				else {
					$item.trigger('destroy');
				}
			}
		});
		
		app.page.destroyWysiwyg($elem);
		
		// 要素削除
		$items.remove().filter('[data-after-destroy]').trigger('destroy');
		$elem.trigger('destroy');
	};

	// 旧雛形反映
	app.page.replaceWithOldSample = function (type, $elem) {
		var tpl = app.page.template.getInstance().main[type];
		var data = app.page.sample[type];

		if (!tpl || !data) {
			return;
		}

		// 全パーツ削除
		app.page.removeAllParts($elem);

		// パーツエレメント作成
		var $addButton = $elem.find('> .page-element-body > .item-add');
		var childInfo;
		var subChildInfo;
		var $child;

		// console.log(tpl.elements);
		$.each(tpl.elements, function (name, info) {
			childInfo = info;
			return false;
		});

		if (childInfo.hasElement) {
			$.each(childInfo.elements, function (name, info) {
				subChildInfo = info;
				return false;
			});
		}

		//なぜ−１しているのだろう？。。。。。
//		for (var i=0,l=data.elements.length - 1;i<l;i++) {
		for (var i=0,l=data.elements.length;i<l;i++) {
			$child = childInfo.$elem.clone().attr('data-name', i+1);
			$addButton.before($child);
		}

		//CMSテンプレートパターンの追加
		$elem.find('.item-header #main-0-parts-0-title').val(data.title);
		$elem.find('.item-header #main-0-parts-0-image_title').val(data.image_title);
		$elem.find('.item-header #main-0-parts-0-image_title').prop('disabled', false);
		$elem.find('.item-header textarea').val(data.description);
		_setImage($elem.find('.item-header'), _getImageId(data.image));
		$elem.find('.item-header #main-0-parts-0-image').val( _getImageId(data.image));

		//CMSテンプレートパターンの追加

		function _getImageId(value) {
			if (value && app.page.sampleImageMap[ value ]) {
				return app.page.sampleImageMap[ value ];
			}
			return '';
		}

		function _getImageTitle(value, data) {
			if (!data.image || !app.page.sampleImageMap[ data.image ]) {
				return;
			}

			if (value) {
				return value;
			}

			return data.image;
		}

		function _setImage($elem, id) {
			var $selectImage = $elem.find('.select-image a');
			$selectImage.html('<img src="/image/hp-image?image_id='+id+'" alt=""/>');
			// 削除ボタンを追加
			$selectImage.before($('<a href="javascript:;" class="i-e-delete"></a>'));
		}

		$elem.find('> .page-element-body > .added-item').each(function (i) {
			if (!data.elements[i]) {
				return;
			}

			var $item = $(this);
			$.each(data.elements[i], function (name, value) {
				if (name === 'image' && value != "") {
					value = _getImageId(value);
				}
				if (name === 'image_title') {
					value = _getImageTitle(value, data.elements[i]);
				}

				if (name === 'elements') {
					if (!subChildInfo) {
						return;
					}

					var $addButton = $item.find('.item-add');
					for (var x=0,y=value.length - 1;x<y;x++) {
						$child = subChildInfo.$elem.clone().attr('data-name', x+1);
						$addButton.before($child);
					}

					$item.find('.added-item').each(function (z) {
						var $subItem = $(this);
						$.each(value[z], function (name, subValue) {
							if (name === 'image') {
								subValue = _getImageId(subValue);
							}
							if (name === 'image_title') {
								subValue = _getImageTitle(subValue, value[z]);
							}

							$subItem.find('[name="'+name+'"]').val(subValue);

							if (name === 'image' && subValue) {
								_setImage($subItem, subValue);
							}
						});
					});

				}
				else {
					$item.find('[name="'+name+'"]').val(value);

					if (name === 'image' && value) {
						_setImage($item, value);
					}
				}
			});
		});

		app.page.initialize($elem, tpl);
		$elem.find('input[type="text"]').change();
	};

	// 雛形反映
	app.page.replaceWithSample = function (type, $elem, pageType) {
		var tpl = app.page.template.getInstance().main[type];
        var data = app.page.sample[type];
        if (typeof pageType != 'undefined') {
            data = app.page.sampleArticle[pageType];
            if (!data) {
                data = app.page.sampleArticle['patern1'];
            }
        }
		
		if (!tpl || !data) {
			return;
		}
		
		// 全パーツ削除
		app.page.removeAllParts($elem);
		
		// パーツエレメント作成
		var $addButton = $elem.find('> .page-element-body > .item-add');
		var childInfo;
		var subChildInfo;
		var $child;

		// console.log(tpl.elements);
		$.each(tpl.elements, function (name, info) {
			childInfo = info;
			return false;
		});

		if (childInfo.hasElement) {
			$.each(childInfo.elements, function (name, info) {
				subChildInfo = info;
				return false;
			});
		}

		//なぜ−１しているのだろう？。。。。。
//		for (var i=0,l=data.elements.length - 1;i<l;i++) {
		for (var i=0,l=data.elements.length;i<l;i++) {
			$child = childInfo.$elem.clone().attr('data-name', i+1);
            if (typeof pageType != 'undefined') {
                $child.find('.added-item').remove();
            }
			$addButton.before($child);
		}

		//CMSテンプレートパターンの追加
		$elem.find('.item-header #main-0-parts-0-title').val(data.title);
		$elem.find('.item-header .select-image-title input').val(data.image_title);
		$elem.find('.item-header .select-image-title input').prop('disabled', false);
		$elem.find('.item-header textarea').val(data.description);
		_setImage($elem.find('.item-header'), _getImageId(data.image));
		$elem.find('.item-header .select-image > input').val( _getImageId(data.image));
        
        app.page.toggleImageTitle($elem.find('.item-header .select-image-title'), true);

		//CMSテンプレートパターンの追加

		function _getImageId(value) {
			if (value && app.page.sampleImageMap[ value ]) {
				return app.page.sampleImageMap[ value ];
			}
			return '';
		}
		
		function _getImageTitle(value, data) {
			if (!data.image || !app.page.sampleImageMap[ data.image ]) {
				return;
			}
			
			if (value) {
				return value;
			}
			
			return data.image;
		}
		
		function _setImage($elem, id) {
			var $selectImage = $elem.find('.select-image a');
            if ($selectImage.length > 1) {
			    $selectImage.eq(1).html('<img src="/image/hp-image?image_id='+id+'" alt=""/>');
            } else {
                $selectImage.html('<img src="/image/hp-image?image_id='+id+'" alt=""/>');
    			// 削除ボタンを追加
    			$selectImage.before($('<a href="javascript:;" class="i-e-delete"></a>'));
            }
        }

        function _getFile2sId(value) {
            if (value && app.page.sampleFile2sMap[ value ][0]) {
				return app.page.sampleFile2sMap[ value ][0];
			}
			return '';
        }

        function _getFile2sExtension(value) {
            if (value && app.page.sampleFile2sMap[ value ][1]) {
				return app.page.sampleFile2sMap[ value ][1];
			}
			return '';
        }
        
        function _setFile2s($elem, value) {
            var extension = _getFile2sExtension(value);
            $elem.find('.select-file2').append('<p class="select-file2-title">選択中ファイル：'+value+'.'+extension+'</p>');
            $elem.find('.use-image-link').prop('checked', true);
		}
		
		$elem.find('> .page-element-body > .added-item').each(function (i) {
			if (!data.elements[i]) {
				return;
			}
			
			var $item = $(this);
			$.each(data.elements[i], function (name, value) {
				if (name === 'image' && value != "") {
					value = _getImageId(value);
				}
				if (name === 'image_title') {
					value = _getImageTitle(value, data.elements[i]);
				}

                var $addButton = $item.find('.item-add');
                if (name == 'element') {
                    var arr = [];
                    $.each(value, function(i, element) {
                    	if (i === 'text 2') {
                    		element['text_2'] = true;
						}
                        i = i.split(' ')[0];
                        $addButton.before(childInfo.elements[i].$elem.clone());
                        arr.push(element);
                    });
                    $item.find('.added-item').each(function (z) {
                        var $subItem = $(this);
						$.each(arr[z], function (name, subValue) {
							if (name === 'image') {
								subValue = _getImageId(subValue);
							}
							if (name === 'image_title') {
								subValue = _getImageTitle(subValue, arr[z]);
                            }
                            if (name === 'file2') {
                                _setFile2s($subItem, subValue);
                                subValue = _getFile2sId(subValue);
                            }

                            if (name === 'image' && i == 0) {
                                $subItem.find('.select-page-radio input').attr('name', 'link_type');
                            }
                            if (arr[z]['text_2']) {
                            	var oldName = parseInt($subItem.data('name'), 10);
                            	var text2PlusNo = 2;
                            	$subItem.attr('data-name', oldName + text2PlusNo);
							}
							$subItem.find('[name="'+name+'"]').val(subValue);
							
							if (name === 'image' && subValue) {
								_setImage($subItem, subValue);
							}
						});
					});
                } else if (name === 'elements') {
					if (!subChildInfo) {
						return;
					}
					
					for (var x=0,y=value.length - 1;x<y;x++) {
						$child = subChildInfo.$elem.clone().attr('data-name', x+1);
						$addButton.before($child);
					}
					
					$item.find('.added-item').each(function (z) {
						var $subItem = $(this);
						$.each(value[z], function (name, subValue) {
							if (name === 'image') {
								subValue = _getImageId(subValue);
							}
							if (name === 'image_title') {
								subValue = _getImageTitle(subValue, value[z]);
							}
							
							$subItem.find('[name="'+name+'"]').val(subValue);
							
							if (name === 'image' && subValue) {
								_setImage($subItem, subValue);
							}
						});
					});
					
				}
				else {
                    if ($item.attr('data-type') == 'articles') {
                        name = 'article_elem_title';
                    }
					$item.find('[name="'+name+'"]').val(value);
					
					if (name === 'image' && value) {
						_setImage($item, value);
					}
				}
			});
		});
		
		app.page.initialize($elem, tpl);
		$elem.find('input[type="text"]').change();
	};
	

})(app);

$(function () {
	
	'use strict';
	
	var $mainContainer = $('.main-contents-body');
	
	// ステータス
	var $status = $mainContainer.find('#page-edit-side .page-edit-status span');
	
	// 削除
	var $deleteBtn = $('.page-delete-btn a');
	$deleteBtn.on('click', function () {
        var $this = $(this);
		if ($this.hasClass('is-ban-page-delete')) {
			app.modal.alertBanDeletePage('このページは「公開中」のため、削除ができません。', '「サイトの公開/更新」の「公開設定（詳細設定）」より<br>公開停止を行ってください。<br>公開停止後、「削除」することができます。<br>※詳しくはマニュアル内「ページごとに下書きにする」をご確認ください。<br>');
			return;
		}
		if ($(this).hasClass('is-ban-sched-page-delete')) {
			app.modal.alertBanDeletePage('このページは公開予約設定中のため、削除ができません。', '「サイトの公開/更新」の「公開設定（詳細設定）」より公開予約の解除を行ってください。<br>公開予約の解除後、「削除」することができます。');
			return;
		}
		
		app.modal.confirm('', 'ページを削除します。\nよろしいですか？', function (ret) {
			if (!ret) {
				return;
            }
			app.api('/page/api-delete', {_token: app.token, id: app.page.info.id}, function (data) {
                $deleteBtn.removeClass('is-disable');
				if (data.error) {
                    if (typeof data.errorArticle != 'undefined' && data.errorArticle) {
                        var links = [
                            {title: '編集ページへ戻る', url: '/page/edit'},
                            {title: 'ページの作成/更新（不動産お役立ち情報）へ移動する', url: '/site-map/article'}
                          ];
                        var link = app.modal.message({
                            message: data.error.replace('<br>', '\n'),
                            links: links,
                            ok: false,
                            cancel: false,
                            onClose: function (){
                                if (app.polling) app.polling.start();
                            }
                        });
                        link.$el.on('click', '.i-s-link', function(e) {
                            e.preventDefault();
                            var href = $(this).attr('href');
                            if (href == '/page/edit') {
                                link.close();
                            } else {
                                window.location = href;
                            }
                        })
                    } else {
                        app.modal.alert('', data.error);
                    }
					return;
				}
				
				$deleteBtn.parent().addClass('is-hide');
				
				location.href = data.url;
			});
		});
	});
	
	var $form = $('form[name="pageform"]');
	var $formTrigger = $('#page-edit-side .page-edit-save a');
	if (!$formTrigger.length) {
		$formTrigger = $form.find('input[type="submit"]');
	}
	
	// プレビュー
	var previewWindowPrefix = 'pageeditpreview';
	$('.btn-preview').on('click', function () {
		
		app.page.updateAllCkeditorElements();
		
		var id = $form.attr('data-id');
		var parentId = $form.attr('data-parent-id');
		var device = $(this).attr('data-type');
		
		$form.trigger('pre-submit');
		
		var previewWindowName = previewWindowPrefix + (new Date()).getTime();
		var previewWindow = window.open('', previewWindowName);
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
				
				previewWindow.close();
				window.focus();
				return;
			}
			
			// フォームの書き換え(NHP-5188)
			var fmArray = $form.serializeArray();
			var useImageLink = {};
			var orgImageFile2 = {};

			// 画像パーツの『リンクを利用する』の設定取得
			$(fmArray).each(function() {
				if(this.name.match(/\[use_image_link\]$/)) {
					var partsTypeCode = $form.find("[name='" + this.name.replace('use_image_link','parts_type_code') + "']").val();
					if( (this.name.match(/^main/) && partsTypeCode == 5)
					 || (this.name.match(/^side/) && partsTypeCode == 2) ) {
						useImageLink[ this.name ] = this.value;
					}
				}
			});
			// console.log(useImageLink);
			for(var ulKey in useImageLink) {
				// 『リンクを利用する』未チェック時はfile2を消す
				if(useImageLink[ ulKey ] == 0) {
					var file2Name = ulKey.replace('use_image_link', 'file2');
					var file2Val = $form.find("[name='" + file2Name + "']").val();
					if(file2Val != "") {
						orgImageFile2[ file2Name ] = file2Val;
						$form.find("[name='" + file2Name + "']").val('');
					}
				}
			}
			// console.log(orgImageFile2);
			
			$form.attr('target', previewWindowName);
			if(id == '') {
				id = 0;
			}
			if (parentId == '') {
				parentId = 0;
			}
			$form.attr('action', '/publish/preview-page/id/' + id + '/parent_id/' + parentId  + '/device/' + device);
			$form.submit();
			
			// フォームの戻し(NHP-5188)
			for(var ofKey in orgImageFile2) {
				// console.log(ofKey + " = " + orgImageFile2[ofKey]);
				$form.find("[name='" + ofKey + "']").val( orgImageFile2[ofKey] );
			}
			
			previewWindow.focus();
		});
		
		return false;
	});
	
	app._isPageSave = false;
	$formTrigger.on('click', function () {
		if (app.polling) app.polling.stop();
		app.page.updateAllCkeditorElements();
        checkDelLinkFile($('.input-link_file'));

		// リンク未使用時の画像削除(NHP-5188)
		checkDelImageLinkFile();
		// 問い合わせ系ページでテキストボックスを選択している場合は入力欄の値を空にする(NHP-5529)
		checkResetContactInput();
		
		app._isPageSave = true;
		$form.attr('target', '');
	});
	
	// 保存
	app.initApiForm($form, $formTrigger, function (data) {
		app.page._changed = false;
		
		if (data.redirectTo) {
			location.href = data.redirectTo;
			return;
		}

		// ページ情報更新
		var query = 'id=' + data.info.id;
		if (data.info.isDetail) {
			query += '&parent_id=' + data.info.parentId;
            window.history.pushState({}, null, window.location.href.replace(/\?.*$/, '') + '?' + query);
		}

		$form.attr('data-api-action', $form.attr('data-api-action').replace(/\?.*$/, '') + '?' + query);
		$form.attr('data-id', data.info.id);
		$form.attr('data-parent-id', data.info.parentId || '');
		$deleteBtn.toggleClass('is-ban-page-disable', !data.info.canDelete).parent().removeClass('is-hide');
		$status.text(data.info.isPublic ? '公開' : '下書き');
		$status.toggleClass('is-draft', !data.info.isPublic);

		app.page.info = data.info;

    var links = [
      {title: '公開設定へ', url: '/publish/simple'},
      {title: 'ホームへ', url: '/'}
    ];

    if ($('.i-m-deputize').parent('li').find('a:contains("代行作成テストサイト確認")').hasClass('is-disable')){
      links.shift();
    }

		app.modal.message({
			message: '設定を保存しました。',
			links: links,
			ok: '閉じる',
			cancel: false,
			onClose: function (){
				if (app.polling) app.polling.start();
			}
		});
	});
	
	// -----------------------------------------------------------------
	// エリア挿入
	var $selectColumn = $mainContainer.find('.page-element-add');
	var $selectParts  = $('#templates .select-element').clone();
	var $area         = $('#templates .page-area').clone();
	var $colAction    = $('#templates .col-action').clone();
	
	var template = app.page.template.getInstance();
	
	function createSelectPartsSelectOptions(section, cols) {
		var options = '<option value="">選択してください</option>';
		
		$.each(template[section], function (i, info) {
			if (info.isUnique) {
				
				if (
					cols > 1 ||
					$mainContainer.find('#section-'+section+' .page-element[data-type="'+info.type+'"]').length
				) {
					return;
				}
			}
			
			options += '<option value="'+info.type+'">'+info.title+'</option>';
		});
		
		return options;
	}
	
	function updateSelectPartsSelectOptions(section) {
		var $selectParts = $mainContainer.find('#section-'+section+' .select-element');
		var cache = {};
		$selectParts.each(function () {
			var cols = parseInt($(this).closest('.page-area').find('.column-type-code').val());
			if (!cache[cols]) {
				cache[cols] = createSelectPartsSelectOptions(section, cols);
			}
			
			$(this).find('select').html(cache[cols]);
		});

		//物件コマ削除
		$('.sortable-item.column2, .sortable-item.column3').each(function () {
			$(this).find('option[value=44]').remove();
			// N02 Delete element search Koma 
			$(this).find('option[value=75]').remove();
            // N02 Delete element search ER freeword main parts
            $(this).find('option[value=80]').remove();
            // No.2 Delete element search freeword main parts
            $(this).find('option[value=81]').remove();
		});
        if ($("#displayFreeword").val()) {
            $('#section-main select').each(function () {
                // No.2-3365 Delete element search freeword main parts
                $(this).find('option[value=81]').remove();
            });
            $('#section-side select').each(function () {
                // No.2-3365 Delete element search freeword side parts
                $(this).find('option[value=10]').remove();
            });
        }
        $('.sortable-item.column1').each(function(){
        	if($(this).find('option[value=81]')){
        		$(this).find('option[value=80]').before($(this).find('option[value=81]'));
        	}
        })
	}
	
	function createSelectParts(section, cols) {
		var $newSelectParts = $selectParts.clone();
		$newSelectParts.find('select').html( createSelectPartsSelectOptions(section, cols) );
		return $newSelectParts;
	}
	
	// カラム選択
	$mainContainer.on('click', '.page-element-add .select-column li', function () {
		$(this).addClass('is-selected').siblings().removeClass('is-selected');
	});
	// 要素挿入へ切り替え エリア挿入
	$mainContainer.on('click', '.page-element-add .btn-area a', function () {
		var cols    = parseInt($selectColumn.find('.is-selected').attr('data-column-type-code'));
		var $section = $(this).closest('.section');
		var section = $section.attr('data-section');
		
		var $newArea = $area.clone();
		$newArea.addClass('column' + cols);
		
		var newAreaNo = 0;
		$mainContainer.find('.page-area').each(function () {
			var areaNo = parseInt($(this).attr('data-name'));
			if (newAreaNo <= areaNo) {
				newAreaNo = areaNo + 1;
			}
		});
		
		var newName = section + '[' + newAreaNo + ']';
		var newId   = section + '-' + newAreaNo;
		
		$newArea.attr('data-name', newAreaNo);
		$newArea.find('.column-type-code').attr({
			id: newId + '-column_type_code',
			name: newName + '[column_type_code]'
		}).val(cols);
		
		$newArea.find('.sort-value').attr({
			id: newId + '-sort',
			name: newName + '[sort]'
        });
		
		var $newSelectParts = createSelectParts(section, cols);
		
		if (cols > 1) {
			for (var i=1;i<=cols;i++) {
				$('<div class="col sortable-item-container"></div>').append( $newSelectParts.clone() ).appendTo($newArea);
			}
			$newArea.append($colAction.clone());
			//物件コマ削除
			$newArea.find('.sortable-item-container select').each(function () {
				$(this).find('option[value=44]').remove();
				// N02 Delete element search Koma FreeWord
				$(this).find('option[value=75]').remove();
                // N02 Delete element search ER freeword main parts
                $(this).find('option[value=80]').remove();
                // No.2 Delete element search freeword main parts
                $(this).find('option[value=81]').remove();
			});
		}
		else {
			$newArea.append( $newSelectParts );
            if ($("#displayFreeword").val()) {
                $newArea.find('select').each(function () {
                    // No.2-3365 Delete element search freeword main parts
                    $(this).find('option[value=81]').remove();
                });
            }
		}
		if(cols == 1){
			if($newArea.find('option[value=81]')){
        		$newArea.find('option[value=80]').before($newArea.find('option[value=81]'));
        	}
		}
		
		$selectColumn.before($newArea);
		
		app.page.sortUpdate($section);
	});
	
	// パーツ挿入
	$mainContainer.on('click', '.select-element a', function () {
        app.page._changed = true;
		
		var $selectParts = $(this).closest('.select-element');
		var section = $(this).closest('.section').attr('data-section');
		
		var type = $selectParts.find('select').val();
		if (!type) {
			return;
		}
		
		if (!template[section][type]) {
			return;
		}
		
		var newPartsNo = 0;
		var $siblings;
		if ($selectParts.parent().hasClass('col')) {
			$siblings = $selectParts.closest('.page-area').find('.col > .page-element');
		}
		else {
			$siblings = $selectParts.siblings('.page-element');
		}
		$siblings.each(function () {
			var partsNo = parseInt($(this).attr('data-name'));
			if (newPartsNo <= partsNo) {
				newPartsNo = partsNo + 1;
			}
		});
		
		var info = template[section][type];
		var $elem = info.$elem.clone();
		$elem.attr('data-name', newPartsNo);
		
		// 1カラムの場合
		if ($selectParts.parent().hasClass('page-area')) {
			$selectParts.replaceWith($elem);
		}
		// 複数カラムの場合
		else {
			// 見出し削除
			$elem.find('.item-header select').replaceWith('見出し');
			
			$selectParts.before($elem);
		}

        $elem.find('.search-house-method input:radio').eq(0).prop('checked', true).change();
		app.page.initialize($elem, info);
		
		updateSelectPartsSelectOptions(section);
        
        var selector = document.querySelector('div[data-parts-id="'+$elem.attr('data-parts-id')+'"]');
        app.setInputFilter(selector.getElementsByClassName('input-house-no'), function(value) {
            return /^[0-9０-９]*$/.test(value); 
        });
	});
	
	// 初期化
	$mainContainer.find('.page-area .col').each(function () {
		$(this).append($selectParts.clone());
	});
	updateSelectPartsSelectOptions('main');
	updateSelectPartsSelectOptions('side');
	
	// -----------------------------------------------------------------
	// 表示・非表示
	$mainContainer.on('click', '.close-btn', function () {
		var $page = $(this).closest('.page-element');
		var isHide = $page.find('.page-element-body').toggleClass('is-hide-parts').hasClass('is-hide-parts');
		$(this).html('<i class="i-e-close"></i>' + (isHide ? '表示' : '非表示'));
		$page.find('.display-isDisplayItem').val(isHide ? 0 : 1);
	});
	
	// 初期化
	$mainContainer.find('.display-isDisplayItem[value="0"]').closest('.page-element').find('.close-btn').click();
	
	
	// -----------------------------------------------------------------
	// サイド共通 表示・非表示
	$mainContainer.on('click', '.js-side-common-display-btn', function () {
		var $element = $(this).closest('.page-element');
		var isHide = $element.toggleClass('is-hide-side-common').hasClass('is-hide-side-common');
		$(this).html('<i class="i-e-close"></i>' + (isHide ? '表示する' : '表示しない'));
		$element.find('.js-side-common-display-value').val(isHide ? 0 : 1);
		$element.find('.js-side-common-display-item').toggleClass('is-hide', !isHide);
	});
	
	// 初期化
	$mainContainer.find('.js-side-common-display-value[value="0"]').closest('.page-element').find('.js-side-common-display-btn').click();
	
	
	
	// -----------------------------------------------------------------
	// 削除
	$mainContainer.on('click', '.delete-btn:not(.is-disable)', function () {
		var $item = $(this).closest('.unsortable-item,.sortable-item');
		var section = $item.closest('.section').attr('data-section');
		
		var $sortContainer = $item.parent();
		
		// エリア削除の場合
		if ($item.hasClass('page-area')) {
			app.modal.confirm('', '削除します。よろしいですか？', function (ret) {
				if (!ret) {
					return;
				}
				$item.find('.page-element').trigger('destroy');
				
				app.page.destroyWysiwyg($item);
				$item.remove();
				
                app.page._changed = true;
				
				// 追加パーツ選択更新
				updateSelectPartsSelectOptions( section );

				// ソート順
				app.page.sortUpdate($sortContainer);
			});
			return;
		}
		else {
			// 1エリア1パーツの場合（1カラムエリアの場合）
			if (section === 'main' && $item.hasClass('page-element') && !$item.parent().hasClass('col')) {
				app.modal.confirm('', '削除します。よろしいですか？', function (ret) {
					if (!ret) {
						return;
					}
					// エリアごと削除
					$sortContainer = $sortContainer.parent();
					
					app.page.destroyWysiwyg($item.parent());
					$item.parent().remove();
					$item.trigger('destroy');
					
                    app.page._changed = true;
					
					// 追加パーツ選択更新
					updateSelectPartsSelectOptions( section );
					// ソート順
					app.page.sortUpdate($sortContainer);
				});
				return;
			}
			// パーツエレメントの場合
			else if ($item.hasClass('added-item')) {
				var $parts = $item.closest('.page-element');
				var $parent = $item.parent();
				app.page.destroyWysiwyg($item);
				$item.remove();
				// パーツエレメント削除を通知
				$parts.trigger('destroy-element', [$parent]);
			}
			else {
				app.modal.confirm('', '削除します。よろしいですか？', function (ret) {
					if (!ret) {
						return;
					}
					app.page.destroyWysiwyg($item);
					$item.remove();
					$item.trigger('destroy');
					
                    app.page._changed = true;
					
					// 追加パーツ選択更新
					updateSelectPartsSelectOptions( section );
					// ソート順
					app.page.sortUpdate($sortContainer);
				});
				return;
			}
			
			$item.trigger('destroy');
			
            app.page._changed = true;
		}
		
		// 追加パーツ選択更新
		updateSelectPartsSelectOptions( section );
		// ソート順
		app.page.sortUpdate($sortContainer);
	});
	
	
	
	
	// -----------------------------------------------------------------
	// sort
	$mainContainer.on('click', '.up-btn:not(.is-disable),.down-btn:not(.is-disable)', function () {
		if ($(this).closest('.f-file-upload').length) {
			return;
		}
		
		var $item = $(this).closest('.sortable-item');

		if (!$item.parent().hasClass('sortable-item-container')) {
			$item = $item.parents('.sortable-item');
		}
		
		var top = $item.offset().top - $(window).scrollTop();
		
		var reInitializeCKEditors = [];
		function destroyEditor() {
			var editor = CKEDITOR.instances[ this.id ];
			if (!editor) {
				return;
			}
			reInitializeCKEditors.push([this, editor.config]);
			editor.destroy();
		}
		
		if ($(this).hasClass('up-btn')) {
			if ($item.prev('.sortable-item').find('.has-ckeditor').length) {
				$item.prev('.sortable-item').find('.has-ckeditor').each(destroyEditor);
			}
			$item.after($item.prev('.sortable-item'));
		}
		else {
			if ($item.next('.sortable-item').find('.has-ckeditor').length) {
				$item.next('.sortable-item').find('.has-ckeditor').each(destroyEditor);
			}
			$item.before($item.next('.sortable-item'));
		}
		
		for (var i=0,l=reInitializeCKEditors.length;i<l;i++) {
			app.page._initWysiwyg.apply(app.page, reInitializeCKEditors[i]);
		}
		
		app.page.sortUpdate($item.parent());
		
		setTimeout(function () {
			
			app.scrolltoElementIfOutOfscreen($item, -top);
			
		}, 100);
	});
	
	
	
	// -----------------------------------------------------------------
	// 画像選択
	var onHpImageSelected;
	var hpImageModal = app.modal.hpImage(app.token, function (image) {
		if (image && onHpImageSelected) {
			onHpImageSelected(image);
		}
		
		onHpImageSelected = null;
	});
	$('body').on('click', '.select-image a.i-e-delete', function () {
		var $this = $(this);
		var $img = $(this).next();
		$img.html('<span>画像の追加</span>');
		$img.next().val('');
		var $title = $this.closest('.select-image').parent().find('.select-image-title input,.input-img-title input');
		if ($title.length) {
			$title.val('').change(); 
		}
		
		//画像注意文言の変更
		$this.siblings(".select-image__tx_annotation").html("「画像の追加」をクリックして画像フォルダから追加してください。");
		$this.closest('.select-image').next(".select-image__tx_annotation").html("「画像の追加」をクリックして画像フォルダから追加してください。"); //用語集用
		
		// タイトルを非表示
		app.page.toggleImageTitle($(this).closest('.select-image').parent().find('.select-image-title,.input-img-title'), false);
		
		$this.remove();
		return false;
	});
	$('body').on('click', '.select-image a:not(.i-e-delete)', function () {
		var $this = $(this);
		onHpImageSelected = function (image) {
			$this.html('<img src="'+image.url+'" alt=""/>');
			
			$this.next().val(image.id);
			
			var $container = $this.closest('.select-image').parent();
            var $title = $container.find('.select-image-title input,.input-img-title:not(".input-img-width") input');

			if ($title.length) {
				$title.val(image.title).change(); 
			}
			
			// 削除ボタンを追加
			if (!$this.parent().hasClass('main-image') && !$this.siblings('.i-e-delete').length) {
				$this.before($('<a href="javascript:;" class="i-e-delete"></a>'));
			}
			
			// タイトルを表示
			app.page.toggleImageTitle($container.find('.select-image-title,.input-img-title:not(".input-img-width")'), true);
			
			// 値変更を通知
			$this.trigger('app-page-image-selected');

			//画像注意文言の変更
			$this.siblings(".select-image__tx_annotation").html("「画像」をクリックして画像フォルダから変更してください。");
			$this.closest('.select-image').next(".select-image__tx_annotation").html("「画像」をクリックして画像フォルダから変更してください。"); //用語集用

		};
		hpImageModal.show(1);
	});
	
	$('.select-image a > img').each(function () {
		var $a = $(this).parent();
		if ($a.parent().hasClass('main-image')) {
			return;
		}
		
		$a.before($('<a href="javascript:;" class="i-e-delete"></a>'));
	});
	
	// 画像のリンク使用
	$('body').on('change', '.use-image-link', function () {
		var $link = $(this).closest('.input-img-link').find('.input-img-wrap');
		if ($(this).prop('checked')) {
            $link.show();
            $link.find('input[name="radio-search-house"]').eq(0).prop('checked', true).change();
		}
		else {
			$link.hide();
		}
	});
	// 画像のリンクタイプ活性・非活性
	$('body').on('change', '.input-img-wrap input[type="radio"]', function () {
        var $linkHouseModule = $(this).closest('.link-house-module');
        if ($(this).closest('.search-house-method').length <= 0) {
            $(this).closest('.input-img-wrap').find('.not-edit')
            .toggleClass('is-disable', true)
            .prop('disabled', true);
            var $radio = $(this).closest('.input-img-wrap').find('input[type="radio"]:not(.search-method)');
            $radio.each(function () {
                var $this = $(this);
                var isDisabled = !$this.prop('checked');
                var elements = null;
                if ($this.closest('dl').find('.select-image').length > 0) {
                    elements = $this.closest('.input-img-link').find('input[type="text"]:not(.not-edit), .search-method,a,select,li');
                } else if ($this.closest('.search-btn').length > 0) {
                    elements = $this.closest('.search-btn').find('a,li');
                } else {
                    elements = $this.closest('dl').find('input[type="text"]:not(.not-edit), .search-method,a,select,li');
                }
                elements.toggleClass('is-disable', isDisabled).prop('disabled', isDisabled);
				elements.closest('.link-wrapper').toggleClass('selected', !isDisabled);
            });
        } else {
            var isHide;
            isHide = !$linkHouseModule.find('input:radio').eq(0).prop('checked');
            $linkHouseModule.find('li.content-search-method div:nth-child(1)').toggleClass('is-hide', isHide);
            isHide = !$linkHouseModule.find('input:radio').eq(1).prop('checked');
            $linkHouseModule.find('li.content-search-method div:nth-child(2)').toggleClass('is-hide', isHide);
        }

        if (!app.page.hasSearchSetting) {
            var element = $(this).closest('.input-img-wrap').find('.link-house-module');
            if (element.length) {
                var linkHouseWrapper = element.closest('dl');
                linkHouseWrapper.toggleClass('is-disable', !app.page.hasSearchSetting);
                linkHouseWrapper.find('label').toggleClass('is-disable', !app.page.hasSearchSetting);
                linkHouseWrapper.find('input').prop('disabled', !app.page.hasSearchSetting);
                linkHouseWrapper.find('input:text').val('');
            }
        }
	});

	// -----------------------------------------------------------------
	// ファイル
	var onHpFile2Selected ;
	var hpFile2Modal = app.modal.hpFile2( app.token, function ( file2 ) {
		if ( file2 && onHpFile2Selected ) {
			onHpFile2Selected( file2 ) ;
		}
		
		onHpFile2Selected = null ;
	});
	var onLinkedFileDelete = function (deleteBtn) {
		var $this	= $(deleteBtn)			;
		var $img	= $(deleteBtn).next()	;
		$img.html('<span>ファイルの追加</span>');
		$img.next().val('');
        $this.closest('.select-file2').find('input').val('');
		// var $title = $this.closest('.select-file2').parent().find('.select-file2-title input,.input-img-title input');
		// if ($title.length) {
		// 	$title.val('').change(); 
		// }
		
		//ファイル注意文言の変更
		$this.siblings(".select-file2__tx_annotation").html("「ファイルの追加」をクリックしてファイル管理から追加してください。");
		$this.closest('.select-file2').next(".select-file2__tx_annotation").html("「ファイルの追加」をクリックしてファイル管理から追加してください。"); //用語集用
		
		// タイトルを非表示
		app.page.toggleFile2Title( $(deleteBtn).closest('.select-file2').parent().find('.select-file2-title,.input-img-title'), true ) ;
		
		$this.remove() ;
		return false ;
	};
    var checkDelLinkFile = function (link) {
        if (link.length) {
            var linkFile = link.parent().parent().parent().parent();
            if (!link.is(':checked') || !linkFile.parent().find('.use-image-link').is(':checked')) {
                onLinkedFileDelete( linkFile.find( '.i-e-delete' ) ) ;
            }
        }
    };
	var checkDelImageLinkFile = function() {
		// リンク未使用時の画像削除(NHP-5188)
		var fmArray = $form.serializeArray();
		var useImageLink = {};
		$(fmArray).each(function() {
			if(this.name.match(/\[use_image_link\]$/)) {
				var partsTypeCode = $form.find("[name='" + this.name.replace('use_image_link','parts_type_code') + "']").val();
				if( (this.name.match(/^main/) && partsTypeCode == 5)
				 || (this.name.match(/^side/) && partsTypeCode == 2) ) {
					useImageLink[ this.name ] = this.value;
                }
                var type = $form.find("[name='" + this.name.replace('use_image_link','type') + "']").val();
                if (this.name.match(/^main/) && (type == 'image_text' || type == 'image')) {
                    useImageLink[ this.name ] = this.value;
                }
			}
		});
		for(var ulKey in useImageLink) {
			if(useImageLink[ ulKey ] == 0) {
				var file2Name = ulKey.replace('use_image_link', 'file2');
				onLinkedFileDelete($form.find("[name='" + file2Name + "']").closest('div').find(".i-e-delete").eq(0));
			}
		}
	};
	var checkResetContactInput = function() {
		$(".option-type").each(function () {
			var type = $(this).find('input:checked').val();
			if (type === 'text' || type === 'textarea') {
				var contactInput = $(this).next('.choices-contact').find('input');
				contactInput.each(function () {
					// 値を空にする
					$(this)[0].value = '';
					// inputタグの文字数カウントと色をデフォルトに戻す
					$(this).next('.input-count').text('0/100');
				});
			}
		});
	}
    $('body').on('click', '.select-file2 a.i-e-delete', function () {
    	onLinkedFileDelete(this);
    });
	$('body').on( 'click', '.select-file2 a:not(.i-e-delete)', function () {
		var $this = $(this);
		onHpFile2Selected = function ( file2 ) {
            $this.closest('.select-file2').find('p.select-file2-title').remove();
			$this.closest('.select-file2').append('<p class="select-file2-title">選択中ファイル：' +  file2.title + '.'+ file2.extension + '</p>');

			$this.next().val( file2.id ) ;
			
			var $container = $this.closest( '.select-file2' ).parent() ;
			var $title = $container.find( '.input-img-title.hogehoge input' ) ;
			if ( $title.length ) {
				$title.val( file2.title ) ; 
			}
			
			// タイトルを表示
			// app.page.toggleFile2Title( $container.find( '.select-file2-title,.input-img-title' ), true ) ;
			
			// 値変更を通知
			$this.trigger( 'app-page-file2-selected' ) ;

			//ファイル注意文言の変更
			$this.siblings(".select-file2__tx_annotation").html("「ファイル」をクリックしてファイル管理から変更してください。");
			$this.closest( '.select-file2').next(".select-file2__tx_annotation").html("「ファイル」をクリックしてファイル管理から変更してください。"); //用語集用

		};
		hpFile2Modal.show( 1 ) ;
	});
	
	$('.select-file2 a > img').each(function () {
		var $a = $(this).parent();
		if ($a.parent().hasClass('main-file2')) {
			return;
		}
		
		$a.before($('<a href="javascript:;" class="i-e-delete"></a>'));
	});
	
	// ファイルのリンク使用
	$('body').on('change', '.use-file2-link', function () {
		var $link = $(this).closest('.input-img-link').find('.input-img-wrap');
		if ($(this).prop('checked')) {
			$link.show();
		}
		else {
			$link.hide();
			$link.find('select')[0].selectedIndex = 0;
			$link.find('input[type="text"]').val('');
		}
	});

	// // ファイルのリンクタイプ活性・非活性
	// $('body').on('change', '.input-img-wrap input[type="radio"]', function () {
    //     $(this).closest('.input-img-wrap').find('.not-edit')
    //     .toggleClass('is-disable', true)
	// 	.prop('disabled', true);
	// 	var $radio = $(this).closest('.input-img-wrap').find('input[type="radio"]');
	// 	$radio.each(function () {
	// 		var $this = $(this);
	// 		var isDisabled = !$this.prop('checked');
	// 		$this.closest('dl').find('input[type="text"]:not(.not-edit),select')
	// 			.toggleClass('is-disable', isDisabled)
	// 			.prop('disabled', isDisabled);
	// 	});
	// });
	
	// -----------------------------------------------------------------
	// リンク
    var $linkModal = $('#templates #side-parts .page-element[data-type-name="link"] .input-img-wrap').clone();
    $linkModal.addClass('js-scroll-container').attr('data-scroll-container-max-height', 500).attr('style', 'overflow-y:auto');
	$linkModal.find('input[name="elements[0][link_label]"]').nextAll('div,span').remove().end().remove();
    // ファイル名部分を削除する
    $linkModal.find('.input-img-title').removeClass('is-require').addClass('hogehoge is-hide')
        .find('.watch-input-count').removeClass('watch-input-count').prop('disabled', true).addClass('not-edit').attr('placeholder', 'ファイルのタイトル')
        .next('.input-count').remove();
	var css = {
		"position": "relative",
		"left": "23px",
		"white-space": "nowrap"
	}
	$linkModal.find('.input-img-title').next('.errors').css(css);
	$linkModal.find('.input-img-title').parent().addClass('link-file-dd');
    $linkModal.find('.link-house-module li.member-no-info').next('li').addClass('hogehoge is-hide');
	$linkModal.children().wrapAll('<div class="input-link"></div>');
	$linkModal.find('.input-link dl:eq(0)').addClass('pr10 pl10');
	$linkModal.find('.link-house-module .member-no-info').addClass('is-hide');
	$linkModal.find('.link-house-module .member-no-info').addClass('is-hide');
	$linkModal.find('.link-house-module').parent('dd').addClass('table-row');
	var linkModal = app.page.linkModal = app.modal.popup({
		title: 'リンクの挿入',
        contents: $linkModal,
        modalBodyInnerClass: 'align-top',
		autoRemove: false,
		ok: 'リンクを挿入'
	});
	var linkModalShow = linkModal.show;
	linkModal.show = function () {
		linkModal.$el.find('.page-name').text('');
		linkModal.$el.find('.select-page select').val('');
		linkModal.$el.find('input:text').val('');
		linkModal.$el.find('input:radio:eq(0)').prop('checked', true).change();
		linkModal.$el.find('input:checkbox').prop('checked', true);
		linkModal.$el.find('.watch-input-count').trigger("change");
        linkModal.$el.find('dd .errors').html('');
        linkModal.$el.find('dd .error').html('');
        linkModal.$el.find('.house-title label').text('');
        linkModal.$el.find('.house-title input').val('');
        linkModal.$el.find('.house-title a').removeAttr('data-href');
        linkModal.$el.find('.member-no-info label').text('');
        linkModal.$el.find('.search-house-method input:radio').eq(0).prop('checked', true).change();
		linkModal.$el.find('.link-house-module .display-house-title').css('display', 'none');
		linkModal.$el.find('.link-house-module .member-no-info').addClass('is-hide');
		linkModal.$el.find('.select-file2-title').text('');
		linkModal.$el.find('#file2').val('');

        onLinkedFileDelete( linkModal.$el.find('.select-file2 .i-e-delete')[0] );

		return linkModalShow.apply(linkModal, arguments);
	};
	
	$('body').on('click', '.insert-link-btn', function () {
		var type = $(this).closest('.page-element').attr('data-type-name');
		var $target;

		if (type === 'list') {
			$target = $(this).closest('.add-item').find('textarea');
		}
		else {
			$target = $(this).closest('.element-text-utilcontainer,.page-element-body').find('textarea');
		}

		if ($target.hasClass('has-wysihtml5')) {
			if ($target.data('wysihtml5Instance').composer.commands.state("createLink")) {
				$target.data('wysihtml5Instance').composer.commands.exec("createLink");
				return;
			}
			var bookmark = $target.data('wysihtml5Instance').composer.selection.getBookmark();
		}

		linkModal.onClose = function (ret, modal) {

			if (!ret) {
				return;
			}

			var $checked = modal.$el.find('input[type="radio"]:checked');
			if (!$checked) {
				return;
			}

			var $urlElem = $checked.closest('dl').find('dd select,dd input:text');
			var val = $urlElem.val();

			if (!val) {
				return false;
			}

			var url;
			if ($urlElem.attr('name') === 'link_page_id') {
				url = '###link_page_id:' + val + '###';
			}
			else {
				url = val;
			}

			var isBlank = modal.$el.find('input[type="checkbox"]').prop('checked');

			if ($target.hasClass('has-wysihtml5')) {
				var opt = {href: url};
				if (isBlank) {
					opt.target = '_blank';
				}
				$target.data('wysihtml5Instance').composer.selection.setBookmark(bookmark);
				$target.data('wysihtml5Instance').composer.commands.exec("createLink", opt);
			}
			else {
				$target
					.selection('insert', {text: '<a href="' + url + '" '+ (isBlank?' target="_blank"':'') +'>', mode: 'before'})
					.selection('insert', {text: '</a>', mode: 'after'}).change();
			}

			linkModal.onClose = null;
		};

		linkModal.show();
		linkModal.$el.focus();
	});
	
	
	
	
	// -----------------------------------------------------------------
	// 雛形選択
	$mainContainer.on('click', 'a.policy-sample', function () {
		
		var $target = $(this).closest('.page-element-body').find('textarea');
		var $sample = $(this).next().children().clone();
		app.page.sampleModal($sample, function (ret) {
			if (ret) {
				if ($target.hasClass('has-ckeditor')) {
					CKEDITOR.instances[ $target.attr('id') ].setData($sample.filter('.modal-policy').text().replace(/\r?\n/g, '<br>'));
				}
				else if ($target.hasClass('has-wysihtml5')) {
					$target.data('wysihtml5Instance').setValue($sample.filter('.modal-policy').text().replace(/\r?\n/g, '<br>'), true);
				}
				else {
					$target.val($sample.filter('.modal-policy').text()).change();
				}
			}
		}).show();
	});
	$('body').on('change', '.terminology-sample', function () {
		var $this = $(this);
		var $selected = $this.find(':selected');
		if (!$selected.length || $selected.val() === '') {
			return;
		}
		
		app.modal.confirmSample('', '雛形「'+$selected.text()+'」を使用します。\n現在入力されている内容は上書きされます。', function (ret) {
			$this[0].selectedIndex = 0;
			
			if (!ret) {
				return;
			}
			
			var $target = $this.closest('.modal-body');
			$target.find('[data-name="word"]').val($selected.text()).change();
			$target.find('[data-name="kana"]').val($selected.attr('data-kana')).change();
			if ($target.find('[data-name="description"]').hasClass('has-ckeditor')) {
				CKEDITOR.instances[ $target.find('[data-name="description"]').attr('id') ].setData($selected.attr('data-content'));
			}
			else if ($target.find('[data-name="description"]').hasClass('has-wysihtml5')) {
				$target.find('[data-name="description"]').data('wysihtml5Instance').setValue($selected.attr('data-content'), true);
			}
			else {
				$target.find('[data-name="description"]').val($selected.attr('data-content')).change();
			}
			
			$target.find('.errors').empty();
		});
	});
	$('body').on('change', '.qa-sample', function () {
		var $this = $(this);
		var $selected = $this.find(':selected');
		if (!$selected.length || $selected.val() === '') {
			return;
		}
		
		app.modal.confirmSample('', '雛形「'+$selected.text()+'」を使用します。\n現在入力されている内容は上書きされます。', function (ret) {
			$this[0].selectedIndex = 0;
			
			if (!ret) {
				return;
			}
			
			var $target = $this.closest('.item');
			$target.find('input:text:first').val($selected.text()).change();
			if ($target.find('textarea').hasClass('has-ckeditor')) {
				CKEDITOR.instances[ $target.find('textarea').attr('id') ].setData($selected.attr('data-a'));
			}
			else if ($target.find('textarea').hasClass('has-wysihtml5')) {
				$target.find('textarea').data('wysihtml5Instance').setValue($selected.attr('data-a'), true);
			}
			else {
				$target.find('textarea').val($selected.attr('data-a')).change();
			}
		});
	});
	
	$('body').on('click', '#section-main .page-sample', function () {
		var type = $(this).attr('data-type');
		var $elem = $(this).closest('.page-element');
		app.modal.confirmSample('', '入力内容を雛形で置き換えます。\n現在入力されている内容は破棄されます。', function (ret) {
			if (!ret) {
				return;
			}
			app.page.replaceWithOldSample(parseInt(type), $elem);
		});
	});
	//CMSテンプレートパターンの追加
	$('body').on('click', '#section-main .model_sample', function () {
		var type = $(this).attr('data-type');
        var $elem = $(this).closest('.page-element');
        var pageType = $(this).closest('#section-main').attr('data-page-type');
		app.modal.confirmSample('', '雛形「' + $('.main-contents h1').text() + '」を使用します。', function (ret) {
			if (!ret) {
				return;
			}
			app.page.replaceWithOldSample(parseInt(type), $elem, parseInt(pageType));
		});
    });
    $('body').on('click', '#section-main .model_sample_article', function () {
		var type = $(this).attr('data-type');
        var $elem = $(this).closest('.page-element');
        var pageType = $(this).closest('#section-main').attr('data-page-type');
		app.page.autoLinkModal(function (ret) {
			if (!ret) {
				return;
            }
			app.page.replaceWithSample(parseInt(type), $elem, parseInt(pageType));
		}).show();
	});
	$('body').on('click', '#section-main .auto_link_sample', function () {
		var type = $(this).attr('data-type');
		var $target = $(this).closest('.page-element-body').find('textarea');
		var data = app.page.sampleArticle[parseInt(type)].lead;
        app.page.autoLinkModal(function (ret) {
            if (ret) {
                if ($target.hasClass('has-ckeditor')) {
                CKEDITOR.instances[ $target.attr('id') ].setData(data.replace(/\r?\n/g, '<br>'));
                }
                else if ($target.hasClass('has-wysihtml5')) {
                    $target.data('wysihtml5Instance').setValue(data.replace(/\r?\n/g, '<br>'), true);
                }
                else {
                    $target.val(data).change();
                }
            }
        }).show();
		// app.modal.confirmSample('', '雛形「' + $('.main-contents h1').text() + '」を使用します。', function (ret) {
		// 	if (!ret) {
		// 		return;
		// 	}
		// 	if ($target.hasClass('has-ckeditor')) {
		// 		CKEDITOR.instances[ $target.attr('id') ].setData(data.replace(/\r?\n/g, '<br>'));
		// 	}
		// 	else if ($target.hasClass('has-wysihtml5')) {
		// 		$target.data('wysihtml5Instance').setValue(data.replace(/\r?\n/g, '<br>'), true);
		// 	}
		// 	else {
		// 		$target.val(data).change();
		// 	}
		// });
	});
	//CMSテンプレートパターンの追加
	
	// wysiwyg-previewのリンクキャンセル
	$('body').on('click', '.wysiwyg-preview a', function () {
		return false;
	});

	// -----------------------------------------------------------------
	//TDKについて、入力値を設定してあげる
	$mainContainer.on("keydown keyup blur", "input,textarea", function() {
		if($(this).attr("id") != null && $(this).attr("id").indexOf("tdk-keyword") >= 0) return;
		var element = $(this).closest(".inner").nextAll(".real").children(".real-body").children("span");
		element.html(jQuery('<span/>').text($(this).val()).html());
		if(element.html() == "" || element.html() == "/") element.html("<入力内容が入ります>");
		if($(this).attr("id") == "tdk-filename") element.html("/"+ element.html() +"/");
	});

	//KeyWordのTDK設定
	$mainContainer.on("focus", "input[id^='tdk-keyword']", function() {
		var value = $(this).val();
		if(value == "") $("#view_"+ $(this).attr("id")).html("<入力内容が入ります>");
		$("#view_"+ $(this).attr("id")).show();

	}).on("keyup blur", "input[id^='tdk-keyword']", function() {

		if($(this).val() == "") {
			$("#view_"+ $(this).attr("id")).html("<入力内容が入ります>");
		}else{
			$("#view_"+ $(this).attr("id")).show();
			$("#view_"+ $(this).attr("id")).html($(this).val());
		}
    });

	// -----------------------------------------------------------------
	// サイド共通：その他のサイドリンクの表示の注意
	var sideCommonOtherLinkNotifyModal = app.page.sideCommonOtherLinkNotifyModal();
	$mainContainer.find('.js-side-common-otherlink-notify-btn').click(function () {
		sideCommonOtherLinkNotifyModal.show();
	});

	// -----------------------------------------------------------------
	// 初期化
	app.page.datePicker();
	
	$mainContainer.find('.section[data-section]').each(function () {
		var section = $(this).attr('data-section');
		
		app.page.sortUpdate($(this));
		
		$(this).find('.page-element').each(function () {
			var $this = $(this);
			var type = $this.attr('data-type');
			
			if (template[section][type]) {
				app.page.initialize($this, template[section][type]);
			}
		});
	});

	var $input = $('input[name="sidelayout[3][title]"]');
	var $output = $('#side-title');
	$output.attr('value', $input.val());
	
	$input.on('input', function() {
		$output.attr('value',$input.val());
    });
    
    var $inputArticle = $('input[name="sidelayout[5][title]"]');
	var $outputArticle = $('#side-article_title');
	$outputArticle.attr('value', $inputArticle.val());
	
	$inputArticle.on('input', function() {
		$outputArticle.attr('value',$inputArticle.val());
	});
    var $eleListTitle = $mainContainer.find('.element-list-title');
    if ($eleListTitle.length > 0) {
        var toolbar = 'ListTitle1';
        if ($eleListTitle.hasClass('has-copy')) {
            toolbar = 'ListTitle2';
        }
        app.page.initWysiwygListTitle($eleListTitle, toolbar);
    }
    $('.add-news-detail').on('click', function(e) {
        e.preventDefault();
        var href = $(this).data('url');
        var addNewsDetailModal = app.page.addNewsDetailModal(href);
        addNewsDetailModal.show();

    })

    $("div[data-type-name=school]").on('keydown', '.cancelEnter', function (e) {
        if (e.key == 'Enter') {
            return false;
        }
    });
    $("div[data-type-name=school]").on('input', '.cancelEnter', function (e) {
        var txtVal = $(this).val();
        $(this).val( txtVal.replace(/\r?\n/g,""));
    });

    var windowSize = {
        w: window.outerWidth,
        iw: window.innerWidth
    };

    window.addEventListener("resize", function() {
        let zoom = (( window.outerWidth - 10 ) / window.innerWidth);
        if (window.innerWidth + window.innerWidth * .05 < windowSize.iw || window.innerWidth - window.innerWidth * .05 > windowSize.iw) {
            if (windowSize.w == window.innerWidth) {
                var widthMain = windowSize.w - 520;
                var widthTxt = windowSize.w - 669;
                var widthImgTxt = windowSize.w - 569;
                var widthImgTxtOrinal = windowSize.w - 546;
                $('.page-element.element-articles .mb20').css({"width" : widthMain + 'px'});
                $('.page-element.element-articles .item-set-list2 .mb20').css({"width" : widthTxt + 'px'});
                $('.page-element.element-articles .item-set-list2 .image-text').css({"width" : widthImgTxt + 'px'});
                $('.page-element.element-articles .item-set-list .image-text').css({"width" : widthImgTxtOrinal + 'px'});
            } else {
                if (zoom < 1) {
                    var width = window.innerWidth*.7;
                    var widthMain = width;
                    var widthTxt = width - 149;
                    var widthImgTxt = width - 49;
                    $('.page-element.element-articles .mb20').css({"width" : widthMain + 'px'});
                    $('.page-element.element-articles .item-set-list2 .mb20').css({"width" : widthTxt + 'px'});
                    $('.page-element.element-articles .item-set-list2 .image-text').css({"width" : widthImgTxt + 'px'});
                    $('.page-element.element-articles .item-set-list .image-text').css({"width" : widthImgTxtOrinal + 'px'});
                } else {
                    $('.page-element.element-articles .mb20').css({"width" : '100%'});
                }
            }
            windowSize.iw = window.innerWidth;
        }
    }, false);
	document.addEventListener('readystatechange', () => {
        if ($('.main-contents-image .input-img-link').length) {
            app.page.initUseLinkLoad($('.main-contents-image .input-img-link'));
        }
        if ($('.side-contents-image .input-img-link').length) {
            app.page.initUseLinkLoad($('.side-contents-image .input-img-link'));
        }
    });	
});
