(function (app) {
	'use strict';
	
	app.SiteMap = function () {
		this.token = null;
		this.$container = null;
	};
	app.SiteMap.prototype = {
		init: function (token, $container) {
			this.token = token;
			
			this.pages = {};
			this.estatePages = [];
			this.indexPages = {};
			
			this.$container  = $container;
			
			var $editSitemap = $container.find('.edit-sitemap');
			this.global  = new app.SiteMap.MenuArea($editSitemap.eq(0).find('.sitemap-main > ul'));
			this.fixed   = new app.SiteMap.FixedArea($editSitemap.eq(0).find('.sitemap-fix > ul'));
			var $outSitemap = $editSitemap.eq(1).find('.sitemap-fix > ul');
			this.free    = new app.SiteMap.Area($outSitemap.eq(0));
			this.special = new app.SiteMap.Area($outSitemap.eq(1));
			
            if (app.SiteMap.isSiteMapArticle) {
                // var deleteArticleModal = app.SiteMap.deleteArticleModal();
                var setLinkModal = app.SiteMap.setLinkModal();
                var addArticleModal = app.SiteMap.addArticleModal();
                var usePageArticleModal = app.SiteMap.usePageArticleModal();
            } else {
                var modal = app.SiteMap.addModal();
                var editinkModal = app.SiteMap.editLinkModal();
            }
			
			var self = this;
			
            // Resize 
            $(window).on('resize', function () {
                var drops = $('.drop-zone-inside');
                $.each(drops, function () {
                    var drop_height = $(this).parent().height() - $(this).parent().find('> .item').innerHeight();
                    var drop_top = $(this).parent().find('> .item').innerHeight() + 10;
                    $(this).parent().find('.drop-zone-inside').css('top', drop_top);
                    $(this).parent().find('.drop-zone-inside').css('height', drop_height);

                });

                // var drop_items = $('.app-sitemap-page-item[draggable=true]').parent().find('>li').not('.last');
                // if (drop_items.length != 0) {
                //     genBlockOpacity(drop_items);                
                // }

            });

            $('body').on('keyup', function (event) {
                if ($('.drag-zone')) {
                    event.preventDefault();
                    var keyDownEvent = event || window.event,
                    keycode = (keyDownEvent.key) ? keyDownEvent.key : keyDownEvent.keyCode;

                    if (keycode == 'Escape' || keycode == 'Esc') {
                        resetSettingDragDrop();
                    }
                }
            });

			// 追加
			$container.on('click', '.item.add a', function () {
				var $this = $(this).parent().parent();
				var $parent = $this.parent().parent();
                var parent = null;
                
                if (!app.SiteMap.isSiteMapArticle) {
                    // グロナビ
                    if ($parent.hasClass('sitemap-main')) {
                        modal.setTypes(app.SiteMap.GlobalMenuTypes, self.global);
                    }
                    // グロナビ階層内
                    else if ($parent.attr('data-id')) {
                        parent = self.getPage($parent.attr('data-id'));
                        modal.setTypes(parent.getChildTypes(), parent);
                    }
                    // 階層外
                    else {
                        modal.setTypes(app.SiteMap.NotInMenuTypes, self.free);
                    }
                    
                    modal.show();
                } else {
                    if (app.SiteMap.isFirstCreatePageArticle && app.SiteMap.hasReserve) {
                        app.modal.hasResever('「サイトの公開/更新」画面にて公開・停止の予約設定がされています。予約設定解除後に作成を開始できます。');
                    }else {
                        var category;
                        var element = $this.parent();
                        var level = parseInt(element.attr('class').replace('level', ''));
                        var type = $parent.attr('data-type');
                        parent = self.getPage($parent.attr('data-id'));
                        switch (level) {
                            case 1:
                                type = app.SiteMap.Types.TYPE_USEFUL_REAL_ESTATE_INFORMATION;
                                category = app.SiteMap.Categories.CATEGORY_LARGE;
                                break;
                            case 2:
                                category = app.SiteMap.Categories.CATEGORY_LARGE; 
                                parent.level = level;
                                break;
                            case 3:
                                category = app.SiteMap.Categories.CATEGORY_SMALL;
                                parent.level = level;
                                break;
                            case 4:
                                category = app.SiteMap.Categories.CATEGORY_ARTICLE;
                                parent.level = level;
                                break;
                            default:
                                break;
                        }
                        addArticleModal.setTypes(category, type, parent, element);
                        if (addArticleModal.isMaxCategory) {
                            app.modal.closeButtomAlert( '', 'これ以上カテゴリーを作成できません。\n作成可能な上限数に達しています。', false);
                            return false;
                        }
                        addArticleModal.show();
                    }
                }
				return false;
			});
			
			// 編集
			$container.on('click', '.app-sitemap-page-item-edit', function () {
				var $page = $(this).closest('.app-sitemap-page-item');
                var page;
				if (self.isEstatePageElement($page)) {
					page = self.getEstatePage($page.attr('data-type'), $page.attr('data-id'));
					if (page && page.getEditPageUrl()) {
						location.href = page.getEditPageUrl();
					}
					return false;
				}
				
				page = self.getPage($page.attr('data-id'));
				if (!page) {
					return false;
                }
                
                if (self.isSiteMapArticle($page, page)) {
                    location.href = '/site-map/article';
					return false;
                }

				if (!page.isLink()) {
					location.href = '/page/edit?id=' + page.getId();
					return false;
				}
				
				editinkModal.setLink(page);
				editinkModal.show();
				
				return false;
			});
			
            function genPopupRemove() {
                var contents = '<div style="margin: 40px 8px;">'+
                                    '<h2 style="margin-bottom: 40px;">このリンクは「公開中」のため、削除ができません。</h2>' +
                                    '<div style="padding:0px 40px">' +
                                        '<p>「サイトの公開/更新」の「公開設定（詳細設定）」より</p>' +
                                        '<p>公開停止を行ってください。</p>' +
                                        '<p>公開停止後、「メニューから削除」することができます。</p>' +
                                    '</div>' +
                                '</div>';
                var modal = app.modal.popup({
                    contents: contents,
                    modalBodyInnerClass: 'align-top',
                    autoRemove: false
                });
                modal.$el.find('.modal-btns .btn-t-gray').remove();
                modal.$el.find('.modal-contents').first().css('min-height', '360px');
                modal.show();
            }

            function genPopupScheduledRemove() {
                var contents = '<div style="margin: 40px 8px;">'+
                                    '<h2 style="margin-bottom: 40px;">このリンクは公開予約設定中のため、削除ができません。</h2>' +
                                    '<div style="padding:0px 40px">' +
                                        '<p>「サイトの公開/更新」の「公開設定（詳細設定）」より公開予約の解除を行ってください。</p>' +
                                        '<p>公開予約の解除後、「メニューから削除」することができます。</p>' +
                                    '</div>' +
                                '</div>';
                var modal = app.modal.popup({
                    contents: contents,
                    modalBodyInnerClass: 'align-top',
                    autoRemove: false
                });
                modal.$el.find('.modal-btns .btn-t-gray').remove();
                modal.$el.find('.modal-contents').first().css('min-height', '360px');
                modal.show();
            }

            function genPopupScheduledMove() {
                var contents = '<div style="margin: 40px 8px;">'+
                                    '<h2 style="margin-bottom: 40px;">このページは公開予約設定中のため、「階層外へ移動させる」ことができません。</h2>' +
                                    '<div style="padding:0px 40px">' +
                                        '<p>「サイトの公開/更新」の「公開設定（詳細設定）」より公開予約の解除を行ってください。</p>' +
                                        '<p>公開予約の解除後、「階層外へ移動させる」ことができます。</p>' +
                                    '</div>' +
                                '</div>';
                var modal = app.modal.popup({
                    contents: contents,
                    modalBodyInnerClass: 'align-top',
                    autoRemove: false
                });
                modal.$el.find('.modal-btns .btn-t-gray').remove();
                modal.$el.find('.modal-contents').first().css('min-height', '360px');
                modal.show();
            }

            function genPopupChildScheduledMove() {
                var contents = '<div style="margin: 40px 8px;">'+
                                    '<h2 style="margin-bottom: 40px;">配下に設置しているページまたはリンクが公開予約設定中のため、「階層外へ移動させる」ことができません。</h2>' +
                                    '<div style="padding:0px 40px">' +
                                        '<p>「サイトの公開/更新」の「公開設定（詳細設定）」より公開予約の解除を行ってください。</p>' +
                                        '<p>公開予約の解除後、「階層外へ移動させる」ことができます。</p>' +
                                    '</div>' +
                                '</div>';
                var modal = app.modal.popup({
                    contents: contents,
                    modalBodyInnerClass: 'align-top',
                    autoRemove: false
                });
                modal.$el.find('.modal-btns .btn-t-gray').remove();
                modal.$el.find('.modal-contents').first().css('min-height', '360px');
                modal.show();
            }

			// メニューから削除
			$container.on('click', '.app-sitemap-page-item-remove', function () {
				var $page = $(this).parents('.app-sitemap-page-item').eq(0);
				var page = self.getPage($page.attr('data-id'));

				if (page.isLink() && page.isPublic()) {
					genPopupRemove();
                    return false;
                }
                if(page.isLink() && page.data.isScheduled !== undefined && page.data.isScheduled) {
					genPopupScheduledRemove();
                    return false;
                }
				
				if (page) {
					app.api('/site-map/api-remove-from-menu', {id: page.getId(), _token: self.token}, function (res) {
						if (res.error) {
                            if (res.error === '公開予約されています。') {
                                genPopupScheduledMove();
                                return false;
                            } else if (res.error === '配下が公開予約されています。') {
                                genPopupChildScheduledMove();
                                return false;
                            } else {
                                app.modal.alert('', '削除に失敗しました。');
                                return;
                            }
						}
						
						self.setData(res.items);
					});
				}
				
				return false;
			});
			
			// 並び替え
			function switchItem($a, $b, isTop) {
                if (isTop) {
                    $a.before($b);
                } else {
                    $a.after($b);
                }

				if ($a.hasClass('global') !== $b.hasClass('global')) {
					$a.toggleClass('global');
					$b.toggleClass('global');
				}
			}

			function updateSort($a, $b, isTop) {
				$('.ghost').remove();
				switchItem($a, $b, isTop);
				
				var data = [];
				$a.parent().children(':not(.last, .drop-zone-inside)').each(function (){
					data.push($(this).attr('data-id'));
				});

				app.api('/site-map/api-sort', {_token: self.token, sort: data}, function (res) {
					self.setData(res.items);
				}).fail(function () {
					switchItem($b, $a);
				});
			}
			$container.on('click', '.app-sitemap-page-item-up', function () {
				if ($(this).hasClass('is-disable')) {
					return false
				}
				
				var $page = $(this).parents('.app-sitemap-page-item').eq(0);
				var $prev = $page.prev('.app-sitemap-page-item');
				if (app.SiteMap.IsTopOriginal && !app.SiteMap.IsAgency) {
					if ($prev.hasClass('global')) {
						return false;
					}
				}
				if (!$prev.length) {
					return false;
				}
				
				updateSort($page, $prev, false);
				
				return false;
			});
			$container.on('click', '.app-sitemap-page-item-down', function () {
				var $page = $(this).parents('.app-sitemap-page-item').eq(0);
				var $next = $page.next('.app-sitemap-page-item');
				if (!$next.length) {
					return false;
				}
				
				updateSort($next, $page, false);
				
				return false;
			});

			$container.on('click', '.app-sitemap-page-item-add-page', function () {
                var $this = $(this).parent().parent();
                var $current_page = $this.parent().parent().parent().parent();
                var $parent = $current_page.parent().parent();
                var current_page = null;
                var parent = null;

                current_page = self.getPage($current_page.attr('data-id'));
                parent = self.getPage($parent.attr('data-id'));

                if (app.SiteMap.isSiteMapArticle) {
                    var category;
                    var element = $current_page.parent();
                    var level = parseInt(element.attr('class').replace('level', ''));
                    var type = $parent.attr('data-type');
                    parent = self.getPage($parent.attr('data-id'));
                    switch (level) {
                        case 1:
                            type = app.SiteMap.Types.TYPE_USEFUL_REAL_ESTATE_INFORMATION;
                            category = app.SiteMap.Categories.CATEGORY_LARGE;
                            break;
                        case 2:
                            category = app.SiteMap.Categories.CATEGORY_LARGE; 
                            parent.level = level;
                            break;
                        case 3:
                            category = app.SiteMap.Categories.CATEGORY_SMALL;
                            parent.level = level;
                            break;
                        case 4:
                            category = app.SiteMap.Categories.CATEGORY_ARTICLE;
                            parent.level = level;
                            break;
                        default:
                            break;
                    }
                    addArticleModal.setTypes(category, type, parent, element, current_page.data.sort, current_page.data.id);
                    if (addArticleModal.isMaxCategory) {
                        app.modal.closeButtomAlert( '', 'これ以上カテゴリーを作成できません。\n作成可能な上限数に達しています。', false);
                        return false;
                    }
                    addArticleModal.show();
                } else {
                    // グロナビ
                    if ($parent.hasClass('sitemap-main')) {
                        modal.setTypes(app.SiteMap.GlobalMenuTypes, self.global, current_page.data.sort, current_page.data.id);
                    }
                    // グロナビ階層内
                    else if ($parent.attr('data-id')) {
                        modal.setTypes(parent.getChildTypes(), parent, current_page.data.sort, current_page.data.id);
                    }
                    // 階層外
                    else {
                        modal.setTypes(app.SiteMap.NotInMenuTypes, self.free, current_page.data.sort, current_page.data.id);
                    }

                    modal.show();
                }

				
				return false;
			});

			// Drag & Drop
            function roundBrowser(val) {
                if (!!window.MSInputMethodContext && !!document.documentMode) {
                    return val;
                }
                return Math.round(val);
            }

            function resetSettingDragDrop() {
                var pages = $('.app-sitemap-page-item[draggable=true]');
                var lines = $('.drop-zone-inside');
                var items = $('.selected-item');
                var closese = $('.close-drag');

                if (pages.length != 0) {
                    var contents = $('.edit-sitemap');
                    $(pages[0]).removeAttr('style');
                    $(pages[0]).removeClass('drag-zone');
                    $(pages[0]).removeAttr('draggable');
                    setCursor(pages[0], false);
                }

                if (lines != 0) {
                    $.each(lines, function () {
                        $(this).remove();
                    })
                }

                if (items != 0) {
                    $.each(items, function () {
                        $(this).removeClass('selected-item');
                    })
                }

                if (closese.length != 0) {
                    $.each(closese, function () {
                        $(this).remove();
                    })
                }

                controlAction(false);
				$('.drag-button-close').remove();
                $('.ghost').remove();
                $('.block-opacity-all').remove();
                $('.block-child').remove();
                $('.app-sitemap-page-item').removeClass('item-drag-drop');
                removeBlock();

                $('.item.add a').removeAttr('draggable');

                controlLabelCategory();
            }

            function controlLabelCategory() {
                if (app.SiteMap.isSiteMapArticle) {
                    var scrollTop = $(window).scrollTop(),
                    offset = $container.find('.category-label').offset().top;
                    if (scrollTop > offset && $container.find('.drag-zone').length <= 0) {
                        $container.find('.category-label > div').toggleClass('label-fixed', true);
                    } else {
                        $container.find('.category-label > div').toggleClass('label-fixed', false);
                    }
                }
            }

            function grabBrowser(type) {
                if (!!window.MSInputMethodContext && !!document.documentMode) {
                    return "url(images/cursor/" + type + ".cur), pointer";
                }
                return type;
            }

            function setCursor(drag, type) {
                if (type) {
                    $(drag).on('mouseenter mouseover mouseout mouseup', function () {
                        $(this).css({cursor: grabBrowser('grab')});
                    });
                    $(drag).on('mousedown', function () {
                        $(this).css({cursor: grabBrowser('grabbing')});
					});

					var item = $(drag).find('.item').not('.add');
                    var add  = $(drag).find('.item.add');

                    $.each(item, function () {
                        $(this).on('mouseenter mouseover mouseout mouseup', function () {
                            $(this).css({cursor: grabBrowser('grab')});
						});
						$(this).on('mousedown', function () {
							$(this).css({cursor: grabBrowser('grabbing')});
						});

						$(this).find('.app-sitemap-page-item-edit').on('mouseenter mouseover mouseout mouseup', function () {
							$(this).css({cursor: grabBrowser('grab')});
						});
						$(this).find('.app-sitemap-page-item-edit').on('mousedown', function () {
							$(this).css({cursor: grabBrowser('grabbing')});
						});

						$(this).find('.pull > a').on('mouseenter mouseover mouseout mouseup', function () {
							$(this).css({cursor: grabBrowser('grab')});
						});
						$(this).find('.pull > a').on('mousedown', function () {
							$(this).css({cursor: grabBrowser('grabbing')});
                        });
                    });

                    $.each(add, function () {
                        $(this).on('mouseenter mouseover mouseout mouseup', function () {
							$(this).css({cursor: grabBrowser('grab')});
						});
						$(this).on('mousedown', function () {
							$(this).css({cursor: grabBrowser('grabbing')});
                        });
                        $(this).find('a').css({cursor: grabBrowser('grab')});
						$(this).find('a').on('mouseenter mouseover mouseout mouseup', function () {
							$(this).css({cursor: grabBrowser('grab')});
						});
						$(this).find('a').on('mousedown', function () {
							$(this).css({cursor: grabBrowser('grabbing')});
                        });
                    });
                } else {
                    $(drag).unbind('mouseenter mouseover mouseout mouseup mousedown');
                    $(drag).css({cursor: 'default'});

                    var item = $(drag).find('.item').not('.add');
                    var add  = $(drag).find('.item.add');

                    $.each(item, function () {
                        $(this).unbind('mouseenter mouseover mouseout mouseup mousedown');
						$(this).find('.app-sitemap-page-item-edit').unbind('mouseenter mouseover mouseout mouseup mousedown');
                        $(this).find('.pull > a').unbind('mouseenter mouseover mouseout mouseup mousedown');
						$(this).css({cursor: 'default'});
						$(this).find('.app-sitemap-page-item-edit').css({cursor: 'pointer'});
						$(this).find('.pull > a').css({cursor: 'pointer'});
                    });

                    $.each(add, function () {
                        $(this).unbind('mouseenter mouseover mouseout mouseup mousedowns');
                        $(this).css({cursor: 'default'});
                        $(this).find('a').unbind('mouseenter mouseover mouseout mouseup mousedown');
                        $(this).find('a').css({cursor: 'pointer'});
                    });
                }
            }

            function controlAction(type) {
                var cursor = 'pointer';
                if (type) {
                    cursor = 'default';
                }

                if (type) {
                    $('.sitemap-main .level1 > .last').on('click', function (e) {
                        e.preventDefault();
                    });
					$('.app-sitemap-page-item-edit').css({cursor: cursor});
					$('.app-sitemap-page-item-edit').prop('disabled', type);

                    $('.item.add a').css({cursor: cursor});
                    $('.item.add a').prop('disabled', type);

					$('.action > .pull > a').css({cursor: cursor});
                    $('.item').hover(function () {
						$(this).find('ul').css({display: 'none'});
                        $(this).find('a').css({opacity: 1});
                        $(this).find('.action .pull a i').addClass('i-e-set-not');
                        $(this).find('.action .app-sitemap-page-item-edit i.i-e-list').addClass('i-e-list-not');
						$(this).find('.action .app-sitemap-page-item-edit i.i-e-edit').addClass('i-e-edit-not');
					});
				} else {
                    $('.app-sitemap-page-item-edit').css({cursor: cursor});
                    $('.app-sitemap-page-item-edit').prop('disabled', type);

					$('.item.add a').css({cursor: cursor});
					$('.item.add a').prop('disabled', type);

					$('.action > .pull > a').css({cursor: cursor});
                    $('.item').css({userSelect: ''});
					$('.item').hover(function () {
						$(this).find('ul').css({display: ''});
                        $(this).find('a').css({opacity: ''});
						$(this).find('.action .pull a i').removeClass('i-e-set-not');
						$(this).find('.action .app-sitemap-page-item-edit i.i-e-list').removeClass('i-e-list-not');
						$(this).find('.action .app-sitemap-page-item-edit i.i-e-edit').removeClass('i-e-edit-not');
					});
				}
                $('.sitemap-article-contents').find('.btn-use-page-article, .btn-set-link, .btn-delete-article').prop('disabled', type);
            }

            function setStyleDrag(drag, item) {
                controlAction(true);
                setCursor(drag[0], true);
                drag.addClass('drag-zone');
                drag.attr('draggable', true);
                drag.css('padding-bottom', '3px');
                drag.css('overflow', 'hidden');
                item.addClass('selected-item');
                $('.edit-sitemap, .sitemap-bottom').append('<div class="block-opacity-all"></div>');
                if (app.SiteMap.IsTopOriginal && !app.SiteMap.IsAgency && $(drag).parent().hasClass('level1')) {
                    drag.parent().find('>.app-sitemap-page-item:not(.drag-zone, .global)').addClass('item-drag-drop');
                } else {
                    drag.parent().find('>.app-sitemap-page-item:not(.drag-zone)').addClass('item-drag-drop');
                }
                $('.item-drag-drop').find('ul .item').append('<div class="block-child"></div>');

                drag.parent().find('.item.add a').attr('draggable', false);
                drag.parent().find('.item.add').last().on('dragstart', function() {
                    return false;
                });

                var drag_width = '265px';
                if (drag.find('.level2 >.last').not('.is-hide').length > 0) {
                    drag_width = '265px';
                    if (drag.parent().hasClass('level1')) {
                        drag_width = '512px';
                    }
                }

                if (drag.find('.level3 >.last').not('.is-hide').length > 0) {
                    drag_width = '265px';
                    if (drag.parent().hasClass('level1')) {
                        drag_width = '750px';
                    }
                    if (drag.parent().hasClass('level2')) {
                        drag_width = '510px';
                    }
                }

                if (drag.find('.level4 >.last').not('.is-hide').length > 0) {
                    drag_width = '265px';
                    if (drag.parent().hasClass('level1')) {
                        drag_width = '1000px';
                    }
                    if (drag.parent().hasClass('level2')) {
                        drag_width = '750px';
                    }
                    if (drag.parent().hasClass('level3')) {
                        drag_width = '510px';
                    }
                }
                if (self.getPage(drag.attr('data-id')).isTopType()) {
                    drag_width = '290px';
                }
                drag.css('width', drag_width);
            }

            function genBlockOpacity(drops) {
                $('.block-opacity').remove();
                
                var site_map_width          = $('.edit-sitemap').outerWidth();
                var site_map_top            = $('.edit-sitemap').offset().top;
                var drag                    = $('.app-sitemap-page-item[draggable="true"]');  
                var level                   = $(drag).parent().attr('class').split(' ')[0];
                var left                    = 0;
                var width                   = 12;

                if (level === 'level2') {
                    left += 250;
                    width = 240;
                } else if (level === 'level3') {
                    left += 500;
                    width = 485;
                } else if (level === 'level4') {
                    left += 744 ;
                    width = 729;
                }

                // Disable Global Navigation in Top-original
                if (app.SiteMap.IsTopOriginal && $(drag).parent().hasClass('level1')) {
                    $.each(drops, function (j) {
                        var page = self.getPage($(this).attr('data-id'));
                        var next_page = self.getPage($(this).next().attr('data-id'));
                        if (app.SiteMap.globalNav !== null) {
                            if (page.isGlobal(page.getId()) && !next_page.isGlobal(next_page.getId())) {
                                var nav_height = $(drops[j]).offset().top - site_map_top + $(drops[j]).outerHeight() - 52;
                                $('.edit-sitemap').first().prepend('<div class="block-opacity block-top-navigation"></div>');
                                $('.block-top-navigation').css({height: roundBrowser(nav_height)});
                                return false;
                            }
                        }
                    });
                }

                var blockOne = {
                    left: {
                        top: 0,
                        left: 0,
                        width: 0,
                        height: 0,
                    },
                    right: {
                        top: 0,
                        left: 0,
                        width: 0,
                        height: 0,
                    },
                }

                var blockTwo = JSON.parse(JSON.stringify(blockOne));
                var blockThree = JSON.parse(JSON.stringify(blockOne));

                // BLOCK ONE
                blockOne.left.top      = roundBrowser($(drag).parent().children().first().offset().top - site_map_top);
                blockOne.left.left     = 0;
                blockOne.left.width    = width;
                blockOne.left.height   = roundBrowser($(drag).offset().top - $(drag).parent().children().first().offset().top);

                blockOne.right.top      = roundBrowser($(drag).parent().children().first().offset().top - site_map_top);
                blockOne.right.left     = (level === 'level1') ? left + 266 : left + 250;
                blockOne.right.width    = (level === 'level1') ? site_map_width - left - 266 : site_map_width - left - 250;
                blockOne.right.height   = roundBrowser($(drag).offset().top - $(drag).parent().children().first().offset().top);
                // END BLOCK ONE

                // BLOCK TWO
                blockTwo.left.top      = roundBrowser(blockOne.left.top + blockOne.left.height);
                blockTwo.left.left     = 0;
                blockTwo.left.width    = (level === 'level1') ? 0 : width;
                blockTwo.left.height   = roundBrowser($(drag).outerHeight());

                blockTwo.right.top      = roundBrowser(blockOne.left.top + blockOne.left.height);
                blockTwo.right.left     = (level === 'level1') ? $(drag).outerWidth() : width + $(drag).outerWidth();
                blockTwo.right.width    = site_map_width - $(drag).outerWidth() - ((level === 'level1') ? 0 : width);
                blockTwo.right.height   = roundBrowser($(drag).outerHeight());
                // END BLOCK TWO

                // BLOCK THREE
                blockThree.left.top      = roundBrowser(blockTwo.left.top + blockTwo.left.height);
                blockThree.left.left     = 0;
                blockThree.left.width    = width;
                blockThree.left.height   = roundBrowser($(drag).parent().outerHeight() - blockOne.right.height - blockTwo.right.height);

                blockThree.right.top      = roundBrowser(blockTwo.left.top + blockTwo.left.height);
                blockThree.right.left     = blockOne.right.left;
                blockThree.right.width    = blockOne.right.width;
                blockThree.right.height   = roundBrowser($(drag).parent().outerHeight() - blockOne.right.height - blockTwo.right.height);
                // END BLOCK THREE

                // ADD BLOCK
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-bottom"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-three-right"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-three-left"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-two-right"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-two-left"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-one-right"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-one-left"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-top-right"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-top-left"></div>');
                $('.edit-sitemap').first().prepend('<div class="block-opacity block-top"></div>');
                $('.edit-sitemap.free').first().prepend('<div class="block-opacity block-free"></div>');

                // STYLE BLOCK TOP
                if (level !== 'level1') {
                    $('.block-top').css({height: roundBrowser($(drag).parent().offset().top - site_map_top)});
                    $('.block-top-left').remove();
                    $('.block-top-right').remove();
                } else {
                    $('.block-top-left').css({height: roundBrowser($('.sitemap-main h4').first().offset().top - site_map_top - 52 + $('.sitemap-main h4').first().outerHeight())});
                    $('.block-top-right').css({height: roundBrowser($('.sitemap-main h4').first().offset().top - site_map_top - 52 + $('.sitemap-main h4').first().outerHeight())});
                }
                // STYLE BLOCK BOTTOM
                var bottom_height = $('.edit-sitemap').outerHeight() 
                                    - $('.block-top').outerHeight() 
                                    - $('.block-top-right').outerHeight() 
                                    - blockOne.right.height 
                                    - blockTwo.right.height 
                                    - blockThree.right.height
                $('.block-bottom').css({top: blockThree.right.top + blockThree.right.height});
                $('.block-bottom').css({height: bottom_height});

                // STYLE BLOCK ONE
                $('.block-one-left').css({top: blockOne.left.top});
                $('.block-one-left').css({left: blockOne.left.left});
                $('.block-one-left').css({width: blockOne.left.width});
                $('.block-one-left').css({height: blockOne.left.height});
                $('.block-one-right').css({top: blockOne.right.top});
                $('.block-one-right').css({left: blockOne.right.left});
                $('.block-one-right').css({width: blockOne.right.width});
                $('.block-one-right').css({height: blockOne.right.height});
                // STYLE BLOCK TWO
                $('.block-two-left').css({top: blockTwo.left.top});
                $('.block-two-left').css({left: blockTwo.left.left});
                $('.block-two-left').css({width: blockTwo.left.width});
                $('.block-two-left').css({height: blockTwo.left.height});
                $('.block-two-right').css({top: blockTwo.right.top});
                $('.block-two-right').css({left: blockTwo.right.left});
                $('.block-two-right').css({width: blockTwo.right.width});
                $('.block-two-right').css({height: blockTwo.right.height});
                // STYLE BLOCK THREE
                $('.block-three-left').css({top: blockThree.left.top});
                $('.block-three-left').css({left: blockThree.left.left});
                $('.block-three-left').css({width: blockThree.left.width});
                $('.block-three-left').css({height: blockThree.left.height});
                $('.block-three-right').css({top: blockThree.right.top});
                $('.block-three-right').css({left: blockThree.right.left});
                $('.block-three-right').css({width: blockThree.right.width});
                $('.block-three-right').css({height: blockThree.right.height});
                // STYLE BLOCK FREE 
                $('.block-free').css({height: $('.edit-sitemap.free').first().outerHeight()});
                /* 
                 * END OPACITY BLOCK
                 */
            }

            function genDropZone(drops, dataId) {
                // Generate line
                $.each(drops, function (i) {

                    if ($(this).attr('data-id') != dataId && typeof ($(this).attr('data-id')) !== "undefined") {
                        var drop_height = $(this).height() - $(this).find('> .item').innerHeight();
                        var drop_top = $(this).find('> .item').innerHeight() + 10;

                        $(this).append('<div class="drop-zone-inside" data-above="'+$(this).attr('data-id')+'"></div>');
                        $(this).find('.drop-zone-inside').append('<div class="drop-zone-line"></div>');
                        $(this).find('.drop-zone-inside').css('top', drop_top);
                        $(this).find('.drop-zone-inside').css('height', drop_height);
                    }

                    if (i === 0) {
                        $(this).prepend('<div class="drop-zone-inside is-drag-top" data-below="'+$(this).attr('data-id')+'"></div>');
                        $(this).find('.is-drag-top').append('<div class="drop-zone-line"></div>');
                        $(this).find('.is-drag-top').css({'top': '-10px'});
                        $(this).find('.is-drag-top').css({'height': '20px'});
                    }

                    if ($(this).attr('data-id') == dataId) {
                        if ($(this).prev().children().last().attr('class') == 'drop-zone-inside') {
                            $(this).prev().children().last().remove();
                        }
                    }

                });

                // Remove top line
                if ($('.drop-zone-inside.is-drag-top').attr('data-below') == dataId) {
                    $('.drop-zone-inside.is-drag-top').remove();
                }

                // Generate button close
                $('.main-contents').prepend('<button class="drag-button-close">キャンセル</button>').on('click', '.drag-button-close', function () {
                    resetSettingDragDrop();
                });

                /* 
                 * OPACITY BLOCK
                 */
                // genBlockOpacity(drops);
            }

            function removeBlock() {
                $('body').removeClass('disable-select');
                $('.block-opacity').remove();
            }

            function dropped(e) {
                e.preventDefault();

                // Switch and update sort page
                var drag = $('.app-sitemap-page-item[draggable="true"]');
                if ($(this).hasClass('is-drag-top')) {
                    var pos = $('.app-sitemap-page-item[data-id="'+$(this).attr('data-below')+'"]');
                    updateSort(pos, drag, true);
                } else {
                    var pos = $('.app-sitemap-page-item[data-id="'+$(this).attr('data-above')+'"]');
                    updateSort(pos, drag, false);
                }

                // Reset drag style
                resetSettingDragDrop();
                // controlAction(false);
            }

            function genGhostImage(drag) {

                var bg_position = $(drag).find('> .item .status').css('background-position');
                var type        = $(drag).find('> .item .label .type').text();
                var page_name   = $(drag).find('> .item .page-name').text();
                var etc         = '...';
                if($('.ghost').length==0){
                    $('body').append('<div class="ghost"></div>');
                    $('body').append('<div class="hidden-ghost"></div>');
                    $('.ghost').append('<div class="ghost-image"></div>');
                    $('.ghost').append('<div class="ghost-left"></div>');
                    $('.ghost').append('<div class="ghost-right"></div>');
                    $('.ghost').append('<div class="ghost-frame"></div>');
                    $('.ghost-image').append('<div class="ghost-label"></div>');
                    $('.ghost-image').append('<div class="ghost-action"></div>');
                    $('.ghost-label').append('<span class="ghost-status"></span>');
                    $('.ghost-label').append('<span class="ghost-type"></span>');
                    $('.ghost-image').append('<span class="ghost-page-name"></span>');
                    $('.ghost-action').append('<a class="ghost-edit"></a>');
                    $('.ghost-action').append('<div class="ghost-pull"></div>');
                    $('.ghost-edit').append('<i class="ghost-icon-edit"></i>');
                    $('.ghost-pull').append('<i class="ghost-icon-pull"></i>');
                    $('.ghost-frame').append('<div class="ghost-frame-first"></div>');
                    $('.ghost-frame').append('<div class="ghost-frame-second"></div>');
                    $('.ghost-frame').append('<div class="ghost-frame-etc"></div>');
                }
                $('.ghost-status').css('background-position', bg_position);
                $('.ghost-type').text(type);
                $('.ghost-page-name').text(page_name);
                $('.ghost-frame-etc').text(etc);

                return $('.ghost')[0];
            }

            function dragStart(e) {
                //run firefox draggale;
                if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                    e.originalEvent.dataTransfer.setData('text','anything');
                }

                // Generate ghost image
                var image = genGhostImage(this);
                if (typeof e.originalEvent.dataTransfer.setDragImage === 'function')
                {
                    e.originalEvent.dataTransfer.setDragImage(image, 100, 50);
                }
            }

            function drag(e) {
             //  $('.ghost').remove();
            }

            function dragOver(e) {
                e.preventDefault();
                $(this).find('.drop-zone-line').css('border-color', 'red');
            }

            function dragLeave(e) {
                $(this).find('.drop-zone-line').css('border-color', 'transparent');
            }

            $container.on('drop', '.drop-zone-inside', dropped);
            $container.on('dragover', '.drop-zone-inside',dragOver);
            $container.on('dragleave', '.drop-zone-inside',dragLeave);
            $container.on('dragstart', '.app-sitemap-page-item[draggable="true"]', dragStart);
            $container.on('drag', '.app-sitemap-page-item[draggable="true"]', drag);
            $container.on('click', '.app-sitemap-page-item-drag-drop', function (e) {
                var current = $(this).parent().parent().parent().parent().parent().parent();
                var item = current.children('.item');
                var children = current.parent().parent().find('> ul > li');
                var dataId = current.attr('data-id');
                var page = self.getPage(dataId);

                if (typeof ($(current[0]).attr('draggable')) === "undefined") {
                    // Reset style drag zone
                    resetSettingDragDrop();
                    $('body').addClass('disable-select');
                    $(this).parent().parent().css({display: "none"});
                    // Set style drag
                    setStyleDrag(current, item);
                    // Generate drop zone
                    genDropZone(children, dataId);
                    genGhostImage();
                } else {
                    resetSettingDragDrop();
                }
                controlLabelCategory();
            });

            if (app.SiteMap.isSiteMapArticle) {
                function openModeDeleteArticle() {
                    var height = $container.find('.btn-use-page-article').offset().top - $container.find('.alert-normal, .alert-strong').eq(0).offset().top + 25;
                    controlAction(true);
                    $container.prepend('<div class="btn-delete-group"><a href="javascript:;" class="btn-delete-close">キャンセル</a><a href="javascript:;" class="btn-delete-ok">削除する</a></div>');
                    $container.prepend('<div class="block-delete-group"><div class="block-delete-top" style="height: '+height+'px"></div><div class="block-delete-bottom"></div></div>');
                    $container.find('.sitemap-main .item:not(.add)').append('<div class="checkbox-delete"></div>');
                    $container.find('.alert-delete').toggleClass('is-hide', false);
                    $container.find('.block-category').before('<div class="alert-normal alert-delete-article">「下書き」ページを削除できます。公開中のページは「サイトの公開/更新」の「公開設定（詳細設定）」より公開停止を行ってください。公開停止後、「削除」することができます。</div>');
                    setHeightlabelCategory();
                }
    
                function closeModeDeleteArticle() {
                    controlAction(false);
                    $container.find('.block-delete-group, .btn-delete-group, .checkbox-delete, .alert-delete-article, .block-page').remove();
                    setHeightlabelCategory();
                }
    
                function checkCanDelete() {
                    var isDelete = true;
                    $container.find('.checkbox-delete.checked').each(function() {
                        var $page = $(this).closest('.app-sitemap-page-item');
                        var page = app.SiteMap.getInstance().getPage($page.attr('data-id'));
                        if (page.public_flg) {
                            isDelete = false;
                        }
                        if ($page.find('.checkbox-delete:not(.checked)').length) {
                            isDelete = false;
                        }
                        var $parent = $page.parent();
                        if ($parent.children().length <= 2 && !$parent.hasClass('level1')) {
                            if (!$parent.closest('.app-sitemap-page-item').find('>.item .checkbox-delete.checked').length) {
                                isDelete = false;
                            }
                        }
                    })
                    return isDelete;
                }
    
                function checkDelete(self) {
                    var ul, parent, checked, li;
                    var ul = self.closest('ul');
                    var parent = ul.parent();
                    if (parent.hasClass('app-sitemap-page-item')) {
                        li = ul.find('>.app-sitemap-page-item');
                        checked = li.find('>.item .checkbox-delete.checked');
                        parent.find('>.item .checkbox-delete:not(.is-disabled)').toggleClass('checked', li.length == checked.length);
                        checkDelete(parent);
                    }
                }
                function enableBtnOk() {
                    var countCheck = $container.find('.checkbox-delete.checked').length;
                    $container.find('.btn-delete-group .btn-delete-ok').toggleClass('is-disable', countCheck > 0 ? false : true);
                }
    
                $container.on('click', '.btn-delete-article', function() {
                    openModeDeleteArticle();
                    $container.find('.app-sitemap-page-item').each(function() {
                        if ($(this).find('>.item.is-empty, >.item.is-draft').length) {
                            $(this).find('>.item .checkbox-delete').toggleClass('is-disabled', false);
                        } else {
                            $(this).find('>.item .checkbox-delete').toggleClass('is-disabled', true);
                            $(this).find('>.item').append('<div class="block-page"></div>');
                        }
                    });
                    enableBtnOk();
                    return;
                });
    
                $container.on('click', '.checkbox-delete:not(.is-disabled)', function() {
                    $(this).closest('.app-sitemap-page-item').find('.checkbox-delete:not(.is-disabled)').toggleClass('checked', !$(this).hasClass('checked'));
                    checkDelete($(this).closest('.app-sitemap-page-item'));
                    enableBtnOk();
                });
    
                $container.on('click','.btn-delete-ok:not(.is-disable)', function() {
                    if (!checkCanDelete()) {
                        app.modal.popup({
                            contents: '<p style="text-align: center;">上層のページと下層のページは合わせて削除してください。どちらかのページのみ削除することはできません。</p>',
                            ok: 'OK',
                            cancel: false,
                            closeButton: false,
                        }).show();
                        return false;
                    }
        
                    var pages = [];
                    $container.find('.checkbox-delete').each(function() {
                        if ($(this).hasClass('checked')) {
                            pages.push($(this).closest('.app-sitemap-page-item').attr('data-id'));
                        }
                    });
        
                    var url = '/site-map/api-delete-page-article';
                    var sitemap = app.SiteMap.getInstance();
                    var params= {};
                    params._token = sitemap.token;
                    params.pages = pages;
                    app.modal.confirm('確認', 'ページを削除します。よろしいですか？',function(ret, modal) {
                        if(!ret) {
                            return;
                        }
                        closeModeDeleteArticle();
                        var closer = app.loading();
                        app.api(url, params, function (res) {
                            closer();
                            if (res.errors) {
                                app.modal.alert('', '登録内容に誤りがあります。');
                                app.setErrors(modal.$el, res.errors);
                                return;
                            }
                            $.each(pages, function(i, id) {
                                $('.app-sitemap-page-item[data-id="'+id+'"]').remove();
                            });
                            $container.find('ul[class*="level"]').each(function() {
                                if ($(this).find('>.app-sitemap-page-item').length && $(this).data('can-display-add-btn') !== undefined) {
                                    $(this).find('>.app-sitemap-page-item').last().removeClass('not-border-left');
                                    $(this).find('>.last').toggleClass('is-hide', false);
                                }
                            });
                            sitemap.setData([]);
                            var links = [
                                {title: 'ページの作成/更新 （不動産お役立ち情報）へ', url: '/site-map/article'},
                                {title: 'ホームへ', url: '/'}
                            ];
                            var link = app.modal.message({
                                message: 'ページを削除しました。',
                                links: links,
                                ok: false,
                                cancel: false,
                                closeButton: false,
                                onClose: function (){
                                    if (app.polling) app.polling.start();
                                }
                            });
                            link.$el.on('click', '.i-s-link', function(e) {
                                e.preventDefault();
                                var href = $(this).attr('href');
                                if (href == '/site-map/article') {
                                    link.close();
                                } else {
                                    window.location = href;
                                }
                            });
                            setHeightlabelCategory();
                        });
                    }, false);
                });
                $container.on('click','.btn-delete-close', function() {
                    closeModeDeleteArticle();
                });
    
                $container.on('click', '.btn-set-link', function() {
                    var clone = $container.find('.tempale-set-link').clone();
                    setLinkModal.setContents(clone);
                    setLinkModal.show();
                    if (app.SiteMap.hasReserve) {
                        app.modal.hasResever('「サイトの公開/更新」画面にて公開・停止の予約設定がされています。予約設定解除後にこのページを「保存」できます。');
                        setLinkModal.$el.find('.modal-btns .btn-t-blue').addClass('is-disable').unbind('click');
                        var alert = '<div class="alert-strong" style="text-align: left;">「サイトの公開/更新」画面にて公開・停止の予約設定がされています。予約設定解除後にこのページを「保存」できます。</div>';
                        setLinkModal.$el.find('.block-legend-description').before(alert);
                    }
                    return;
                });
    
                $container.on('click', '.btn-use-page-article', function() {
                    var clone = $container.find('.template-use-page').html();
                    usePageArticleModal.setContents(clone);
                    usePageArticleModal.show();
                    $('.modal-set .content-article-use-page').scrollTop(0);
                    function setStyleModalUseArticle() {
                        var element = $('.modal-set .content-article-use-page');
                        element.width($(window).width() - 100);
                        element.height($(window).height() - 150)
                        element.find('>.tb-basic').width(element.find('.table-body').width() - 2);
                    }
                    setStyleModalUseArticle();
                    $(window).resize(function() {
                        setStyleModalUseArticle();
                    });
                    return;
                });
                $(window).scroll(function(e) {
                    controlLabelCategory();
                    var scrollTop = $(window).scrollTop(),
                    offset = $container.find('.category-label').offset().top,
                    scrollUp = $('.btn-scroll .scroll-up'),
                    scrollDown = $('.btn-scroll .scroll-down'),
                    winHeight = $(window).height(),
                    docHeight = $(document).height(),
                    offsetTop = $container.find('.category-label').offset().top,
                    scrollBottom = scrollTop + winHeight,
                    rate = (winHeight - 60)/docHeight,
                    top = rate*(scrollTop - 130);
                    if (scrollTop <= 105) {
                        top = 105;
                        scrollUp.toggleClass('is-disable', true);
                    } else {
                        scrollUp.toggleClass('is-disable', false);
                    }
                    if (scrollBottom > docHeight - 74 ) {
                        top = winHeight - 164;
                        scrollDown.toggleClass('is-disable', true);
                    } else {
                        scrollDown.toggleClass('is-disable', false);
                    } 
                    $('.btn-scroll').removeAttr('style').css('top', top );
                });
                $(document).on('click', '.scroll-up, .scroll-down', function(e) {
                    var current = $(e.currentTarget);
                    if (current.hasClass('is-disable')) {
                        return;
                    }
                    var top;
                    if (current.hasClass('scroll-up')) {
                        top = 0;
                    }
                    if (current.hasClass('scroll-down')) {
                        top = $(document).height() - 164;
                    }
                    $('body, html').animate({scrollTop: top}, 400, 'swing');
                })

            }
			
			return this;
		},
		
		clear: function () {
			this.pages = {};
			this.estatePages = [];
			
			this.global.empty();
			this.fixed.empty();
			this.free.empty();
		},
		
		setData: function (data, type, above) {
			var i;
			for (i in data) {
				if (data[i].deleted) {
					if (this.pages[data[i].id]) {
						this.pages[data[i].id].remove();
						delete this.pages[data[i].id];
					}
				}
				else {
					if (this.pages[data[i].id]) {
						this.pages[data[i].id].updateData(data[i]);
					}
					else {
						this.pages[data[i].id] = new app.SiteMap.Page(data[i]);
					}
				}
			}

			var page;
			var addedArea = {};
			for (i in data) {
				page = this.pages[data[i].id];
				if (page) {
                    page.render();
                    if (page.isArticleTop(true)) {
                        this.global.addChild(page, above);
                        addedArea.global = false;
                    }
                    else if (page.isArticleTop(false)) {
                        this.fixed.addChild(page);
						addedArea.fixed = true;
                    }
                    else if (app.SiteMap.isSiteMapArticle == false && page.isRealEstateSiteMap()) {
                        continue;
                    }
					else if (page.data.parent_page_id === 0) {
						this.global.addChild(page, above);
						addedArea.global = true;
					}
					else if (page.data.parent_page_id !== null && this.pages[page.data.parent_page_id]) {
						this.pages[page.data.parent_page_id].addChild(page, above);
					}
					else if (page.isFixedType()) {
						this.fixed.addChild(page);
						addedArea.fixed = true;
					}
					else {
						this.free.addChild(page);
						addedArea.free = true;
					}
				}
			}
			
			var self = this;
			$.each(addedArea, function (area) {
				self[area].sortChildren();
			});
			if (!addedArea.global) {
				this.global.updateGlobal();
            }
            if (app.SiteMap.isSiteMapArticle) {
                this.global.removeAddButton();
            }

            this.fixed.changePositionRealEstate();

            setHeightlabelCategory();
            setTypeNameLabel();

			return this;
		},

		setIndexData: function (data) {
			var i;
			for (i in data) {
				if (data[i].deleted) {
					if (this.indexPages[data[i].id]) {
						this.indexPages[data[i].id].remove();
						delete this.indexPages[data[i].id];
					}
				}
				else {
					this.indexPages[data[i].id] = new app.SiteMap.Page(data[i]);
				}
			}
			return this;
		},
		
		setEstateData: function (data) {
			this.estatePages.top = new app.SiteMap.EstatePage(data.top);
			this.estatePages.chintai_top = new app.SiteMap.EstatePage(data.chintai_top);
			this.estatePages.baibai_top = new app.SiteMap.EstatePage(data.baibai_top);
			this.estatePages.push(this.estatePages.top);

			this.estatePages.estateTypes = [];
			var i,l,estateType, estateTypePage;
			var estateTopDisplay = false;
			for (i=0,l=data.estateTypes.length;i<l;i++) {
				estateType = data.estateTypes[i];
				if (estateType.estate_class == 1 || estateType.estate_class == 2 || estateType.estate_class == 3 || estateType.estate_class == 4) {
					estateTopDisplay = true;
					if (estateType.estate_class == 1 || estateType.estate_class == 2) {
						if (!this.estatePages['chintai_top_pushed']) {
							this.estatePages.push(this.estatePages.chintai_top);
							this.estatePages['chintai_top_pushed'] = true;
						}
					}else if (estateType.estate_class == 3 || estateType.estate_class == 4) {
						if (!this.estatePages['baibai_top_pushed']) {
							this.estatePages.push(this.estatePages.baibai_top);
							this.estatePages['baibai_top_pushed'] = true;
						}
					}
				}
				estateTypePage = new app.SiteMap.EstatePage(estateType);
				this.estatePages.top.addChild(estateTypePage);
				this.estatePages.estateTypes.push(estateTypePage);
				this.estatePages.push(estateTypePage);
			}
			this.estatePages.top.render(true);
			this.estatePages.chintai_top.render(true);
			this.estatePages.baibai_top.render(true);
			if (estateTopDisplay) {
				this.fixed.addChild(this.estatePages.top);
			}
			if (this.estatePages['chintai_top_pushed'] == true) {
				this.fixed.addChild(this.estatePages.chintai_top);
			}
			if (this.estatePages['baibai_top_pushed'] == true) {
				this.fixed.addChild(this.estatePages.baibai_top);
			}
			this.fixed.sortChildren();
			
			var special, specialPage;
			this.estatePages.specials = [];
			// 2019/02/18 don't hide special anymore
			// var isTopOriginal = app.SiteMap.IsTopOriginal;
			// var housingBlocksHidden = [];
			// if(isTopOriginal){
			// 	housingBlocksHidden = app.SiteMap.housingBlocksHidden;
			// }
			for (i=0,l=data.specials.length;i<l;i++) {
				special = data.specials[i];
				// if(isTopOriginal){
				//   var special_id = special.origin_id.toString();
				//   // hidden
				//   if(housingBlocksHidden.indexOf(special_id) >= 0 )
				//   {
				// 	continue;
				//   }
				// }
				specialPage = new app.SiteMap.EstatePage(special);
				specialPage.render();
				this.estatePages.specials.push(specialPage);
				this.estatePages.push(specialPage);
				this.special.addChild(specialPage);
			}
		},
		
		getPage: function (id) {
			return this.pages[id];
		},
		
		getPageByLinkId: function (linkId) {
			for (var i in this.pages) {
				if (this.pages[i].getLinkId() === linkId) {
					return this.pages[i];
				}
			}
			return;
		},

		getIndexPageByLinkId: function (linkId) {
			for (var i in this.indexPages) {
				if (this.indexPages[i].getLinkId() == linkId) {
					return this.indexPages[i];
				}
			}
			return;
		},
		
		getEstatePage: function (type, id) {
			if (type === 'estate_top') {
				return this.estatePages.top;
			}
			else if (type === 'estate_rent') {
				return this.estatePages.chintai_top;
			}
			else if (type === 'estate_purchase') {
				return this.estatePages.baibai_top;
			}
			else if (type === 'estate_type') {
				return this.getEstateTypePage(id);
			}
			else if (type === 'estate_special') {
				return this.getEstateSpecialPage(id);
			}
			return null;
		},
		getEstateTypePage: function (id) {
			if (!this.estatePages.estateTypes) {
				return null;
			}
			for (var i=0,l=this.estatePages.estateTypes.length;i<l;i++) {
				if (this.estatePages.estateTypes[i].getId() == id) {
					return this.estatePages.estateTypes[i];
				}
			}
			return null;
		},
		getEstateSpecialPage: function (id) {
			if (!this.estatePages.specials) {
				return null;
			}
			for (var i=0,l=this.estatePages.specials.length;i<l;i++) {
				if (this.estatePages.specials[i].getId() == id) {
					return this.estatePages.specials[i];
				}
			}
			return null;
		},
		getEstatePages: function () {
			return this.estatePages;
		},
		isEstatePageElement: function ($el) {
			return $el.hasClass('app-sitemap-estate-page-item');
		},
		getPageByEstateLinkId: function (estate_page_id) {
			for (var i=0,l=this.estatePages.length;i<l;i++) {
				if (this.estatePages[i].getLinkId() === estate_page_id) {
					return this.estatePages[i];
				}
			}
			return null;
		},
		
		getPages: function () {
			return this.pages;
		},
		
		getAllPages: function () {
			var result = [];
			$.each(this.pages, function (i,page) {
				result.push(page);
			});
			$.each(this.estatePages, function (i, page) {
				result.push(page);
			});
			return result;
		},

		getIndexPages: function () {
			var result = [];
			$.each(this.indexPages, function (i,page) {
				result.push(page);
			});
			return result;
		},
		
		hasType: function (type) {
			return !!this.$container.find('.app-sitemap-page-item[data-type="'+type+'"]').length;
        },
        isSiteMapArticle: function($el, page) {
            return $el.closest('.sitemap-fix').length && page.data.page_type_code == app.SiteMap.Types.TYPE_USEFUL_REAL_ESTATE_INFORMATION;
        }
	};
	app.SiteMap.instance = null;
	app.SiteMap.getInstance = function () {
		if (!app.SiteMap.instance) {
			app.SiteMap.instance = new app.SiteMap();
		}
		return app.SiteMap.instance;
	};
	
	app.SiteMap.Types = {};
	app.SiteMap.TypeNames = {};
	app.SiteMap.Categories = {};
	app.SiteMap.CategoryNames = {};
	
	app.SiteMap.TypeNamesForSelect = {};
	
	app.SiteMap.getTypeName = function (type) {
		return app.SiteMap.TypeNames[ type ] || '';
	};
	
	app.SiteMap.getTypeNameForSelect = function (type) {
		if (type === app.SiteMap.Types.TYPE_SHOP_INDEX || type === app.SiteMap.Types.TYPE_SHOP_DETAIL) {
			return '店舗案内';
		}
		return app.SiteMap.getTypeName(type).replace(/一覧|詳細/, '');
	};
	
	app.SiteMap.getCategoryName = function (category) {
		return app.SiteMap.CategoryNames[ category ] || '';
	};
	
	app.SiteMap.FixedMenuTypes = [];
	app.SiteMap.GlobalMenuTypes = [];
	app.SiteMap.NotInMenuTypes = [];
	app.SiteMap.UniqueTypes = [];
	
	app.SiteMap.HasDetailPageTypes = [];
	app.SiteMap.DetailPageTypes = [];
	app.SiteMap.HasMultiPageTypes = [];
	app.SiteMap.ChildTypes = {};
	app.SiteMap.CreateableCategoryList = {};
	app.SiteMap.CategoryMap = {};
	
	app.SiteMap.getCategoryFromType = function (type) {
		for (var category in app.SiteMap.CategoryMap) {
			if (app.arrayIndexOf(type, app.SiteMap.CategoryMap[category]) >= 0) {
				return parseInt(category);
			}
		}
		return null;
    };
    
    app.SiteMap.isOldArticleTemplateCategory = function(category) {
        var categories = [
            app.SiteMap.Categories.CATEGORY_SALE,
            app.SiteMap.Categories.CATEGORY_PURCHASE,
            app.SiteMap.Categories.CATEGORY_OWNERS_RENTAL_MANAGEMENT,
            app.SiteMap.Categories.CATEGORY_RESIDENTIAL_RENTAL,
            app.SiteMap.Categories.CATEGORY_BUSINESS_LEASE,
        ];
        return $.inArray(category, categories) > -1;
    }
	
	app.SiteMap.isUniqueType = function (type) {
		return app.arrayIndexOf(type, app.SiteMap.UniqueTypes) > -1;
	};
	
	app.SiteMap.isFixedType = function (type) {
		return app.arrayIndexOf(type, app.SiteMap.FixedMenuTypes) > -1;
	};
	
	app.SiteMap.hasDetailPageType = function (type) {
		return app.arrayIndexOf(type, app.SiteMap.HasDetailPageTypes) > -1;
	};
	
	app.SiteMap.isDetailPageType = function (type) {
		return app.arrayIndexOf(type, app.SiteMap.DetailPageTypes) > -1;
	};
	
	app.SiteMap.hasMultiPageType = function (type) {
		return app.arrayIndexOf(type, app.SiteMap.HasMultiPageTypes) > -1;
	};
	
	app.SiteMap.init = function (token, $container) {
		return app.SiteMap.getInstance().init(token, $container);
	};

	app.SiteMap.freewordSearchModal = function (module, select, pageNameClass, oldModal) {
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
							'</li>' + app.SiteMap.ToolTipSearchSpecialLabel +
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
					'<h3>ページ一覧' + app.SiteMap.ToolTipTitle + '</h3>' +
					'<table class="tb-basic">' +
						'<thead>' +
							'<tr>' +
								'<th>ページ名</th>' +
								'<th>更新日' + app.SiteMap.ToolTipUpdateDate + '</th>' +
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
		var sitemap = app.SiteMap.getInstance();
		var pages = sortUpdateDate(sitemap.getAllPages().concat(sitemap.getIndexPages()));
		var canAliasPages = createPageList(pages);
		createPagination($('.page-list .tb-basic'), $('.page-list .tb-basic tbody tr'));
        editModal.show();
		$('.edit-modal-sitemap #search-narrow-down').on('click', function () {
			searchNarrowDown($('.page-list .tb-basic'), $('.page-list .tb-basic tbody tr'), canAliasPages);
			createPagination($('.page-list .tb-basic'), $('.page-list .tb-basic tbody tr'));
		});
		editModal.onClose = function (ret, modal) {
            if (typeof editModal.oldModal != 'undefined') {
                editModal.oldModal.toggleClass('is-hide', false);
            }
			if (!ret) {
				return;
			}
            var selectPage = modal.$el.find('input[name="select-page"]:checked');
			if (selectPage.val() == undefined) {
				app.modal.alert('', 'ページを選択してください。');
				return false;
			}
			module.find(pageNameClass).text('選択中ページ：' + selectPage.data('title'));
			module.find('.link-wrapper .errors').text('');
			select.val(selectPage.val());
		}
	}

	function createPageList(pages) {
		var fixedArea = new app.SiteMap.FixedArea($('.edit-sitemap').eq(0).find('.sitemap-fix > ul'));
		var isDisplayEstateTop = false;
		var canAliasPages = [];
		if (pages.length > 0) {
			$('.page-list .tb-basic').removeClass('is-hide');
			$('.page-list .no-page').addClass('is-hide');
		} else {
			$('.page-list .tb-basic').addClass('is-hide');
			$('.page-list .no-page').removeClass('is-hide');
		}
		$.each(fixedArea.getChildren(), function() {
			if (this.data.link_id == 'estate_top') {
				isDisplayEstateTop = true;
			}
		});
		$.each(pages, function (id, page) {
			if (!isDisplayEstateTop && page.data.link_id == 'estate_top') {
				return;
			}
			if (page.canAlias() && page.issetArticleTop()) {
                if (!(page.isCategoryArticleOrignal() && page.getParentId() == null)) {
                    canAliasPages.push(page);
                    var dataPageTitle = page.getTitleWithFilenameSearch();
                    if (page.getTitleWithFilenameSearch().length <= 15) {
                        var pageTitle = page.getTitleWithFilenameSearch();
                    } else {
                        var pageTitle = page.getTitleWithFilenameSearch().substr(0, (15)) + '...'
                    }
                    var publishStatus = page.isPublic() ? '公開' : '下書き';
                    var appendContent =
                        '<tr>' +
                            '<td>' +
                                '<label for="' + page.getLinkId() + '">' +
                                    '<input type="radio" name="select-page" data-title="' + dataPageTitle + '" id="' + page.getLinkId() + '" value="' + page.getLinkId() + '">' + pageTitle +
                                '</label>' +
                            '</td>' +
                            '<td>' + page.data.update_date.substr(0, 10).replace(/-/g, '/') + '</td>' +
                            '<td>' + publishStatus + '</td>' +
                        '</tr>';
                    $('.page-list tbody').append(appendContent);
                }
			}
		});
		return canAliasPages;
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
					if (canAliasPages[i].data.estate_page_type == 'estate_special') {
						searchPages.push(canAliasPages[i]);
					}
					break;
				case 'real-estate':
					// TODO 不動産お役立ち情報が追加されたら実装するpage_type_code
                    if(canAliasPages[i].isRealEstateSiteMap()) {
                        searchPages.push(canAliasPages[i]);
                    }
					break;
				case 'blog':
					var blogIndexTypeCode = 14;
					var blogDetailTypeCode = 15;
					if (
						canAliasPages[i].data.page_type_code == blogIndexTypeCode ||
						canAliasPages[i].data.page_type_code == blogDetailTypeCode
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
					if (page.getTitleWithFilenameSearch().toLowerCase().indexOf(pageTitle) != -1) {
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
			if (a.data.update_date > b.data.update_date) {
				return -1;
			} else {
				return 1;
			}
		});
		return pages;
	}
	
	app.SiteMap.addModal = function () {
		var contents = '<div class="modal-sitemap js-scroll-container" data-scroll-container-max-height="500" style="overflow-y:auto;">' +
							'<dl class="modal-sitemap-new">' +
								'<dt><label><input type="radio" checked="checked" name="radio-addpage" value="create">新規のページを追加</label></dt>' +
								'<dd>' +
									'<div class="category-select">' +
										'<label>カテゴリ</label>' +
										'<select></select>' +
										'<div class="errors"></div>' +
									'</div>' +
									'<div class="type-select">' +
										'<label>ページ</label>' +
										'<select id="page_type_code"></select>' +
										'<div class="errors"></div>' +
									'</div>' +
								'</dd>' +
							'</dl>' +
							'<dl class="modal-sitemap-edit">' +
								'<dt><label><input type="radio" name="radio-addpage" value="add">既存ページを追加</label></dt>' +
								'<dd>' +
									'<div>' +
										'<select id="id"></select>' +
										'<div class="errors"></div>' +
									'</div>' +
								'</dd>' +
							'</dl>' +
							'<dl class="modal-sitemap-link">' +
								'<dt><label><input type="radio" name="radio-addpage" value="link">リンクを追加</label></dt>' +
								'<dd>' +
									'<div class="app-sitemap-link-module">' +
                                        '<div class="custom-sitemap-label"><label><input type="checkbox" name="link_target_blank" value="1">別窓で開く</label></div>' +
                                        '<div class="link-wrapper">' +
                                            '<label>' +
                                                '<input type="radio" name="radio-addpage-link">既存ページから選ぶ' +
                                                '<a class="btn-t-gray search-page" href="javascript:;">ページを検索</a>' +
                                            '</label>' +
                                            '<ul>' +
                                                '<li>' +
                                                    '<span class="page-name"></span>' +
                                                '</li>' +
                                            '</ul>' +
                                            '<div id="link_page_id"></div>' +
                                            '<div class="errors"></div>' +
                                        '</div>' +
									'</div>' +
									'<div class="app-sitemap-link-module">' +
                                        '<div class="link-wrapper">' +
                                            '<label><input type="radio" name="radio-addpage-link">URLからリンクを作る</label>' +
                                            '<ul>' +
                                                '<li>' +
                                                    '<label>URL</label>' +
											'<input type="text" id="link_url" name="link_url" maxlength="2000" class="watch-input-count"><span class="input-count">0/2000</span>' +
                                                    '<div class="errors"></div>' +
                                                '</li>' +
                                                '<li>' +
                                                    '<label>リンク名</label>' +
                                                    '<input type="text" id="title" name="title" maxlength="20" class="watch-input-count"><span class="input-count">0/30</span>' +
                                                    '<div class="errors"></div>' +
                                                '</li>' +
                                            '</ul>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="app-sitemap-link-module link-house-module">' +
                                        '<div class="link-wrapper">' +
                                            '<label><input type="radio" name="radio-addpage-link">物件詳細を選ぶ</label>' +
                                            '<ul>' +
                                                '<li class="search-house-method">' +
                                                    '<label><input type="radio" name="radio_search_house" class="search-method">条件で探す</label>' +
                                                    '<label><input type="radio" name="radio_search_house" class="search-method">物件番号で探す</label>' +
                                                '</li>' +
                                                '<li class="content-search-method">' +
                                                    '<div>' +
                                                        '<a class="btn-t-gray btn-search-all-house" href="javascript:;">物件を検索</a>' +
                                                    '</div>' +
                                                    '<div>' +
                                                    '<input type="text" id="house_no" name="house_no" class="input-house-no" placeholder="物件番号（8・10・11桁）を入力してください">' +
                                                    '<a class="btn-t-gray btn-search-house-no" href="javascript:;">検索</a>' +
                                                    '</div>' +
                                                    '<div class="error"></div>' +
                                                '</li>' +
                                                '<div class="error"></div>' +
                                                '<div class="errors"></div>' +
                                                '<li class="display-house-title" style="margin-left: 0;display: none">' +
                                                    '<label>選択中の物件'+ app.LinkHouse.toolTip('display_house_title')+'</label>' +
                                                    '<div class="house-title">' +
                                                    '<label></label>' +
                                                    '<a href="javascript:;" class="btn-p-pc btn-preview-link-house is-hide" data-type="pc"></a>' +
                                                    '<input type="hidden" id="link_house" name="link_house" value="">' +
                                                    '</div>' +
                                                '</li>' +
                                                '<li class="member-no-info is-hide" style="margin-left: 0;">' +
                                                    '<label></label>' +
                                                    '<label class="display-house-no"></label>' +
                                                    '<input type="hidden" id="link_house_type" name="link_house_type" value="">' +
                                                '</li>' +
                                                '<li>' +
                                                    '<label>リンク名</label>' +
                                                    '<input type="text" id="title_house" name="title_house" maxlength="20" class="watch-input-count"><span class="input-count">0/20</span>' +
                                                    '<div class="errors"></div>' +
                                                '</li>' +
                                            '</ul>' +
                                        '</div>' +
									'</div>' +
								'</dd>' +
							'</dl>' +
						'</div>';
		var modal = app.modal.popup({
			title: 'ページまたはリンクの追加',
			contents: contents,
			modalBodyInnerClass: 'align-top',
			ok: '追加',
			autoRemove: false
		});

		// タイトル
		var $title = modal.$el.find('.modal-header h2');

		// 入力変更した場所を選択する
		modal.$el.find('.modal-sitemap dd').find('input:not([type="checkbox"]),select').on('change', function () {
			$(this).parents('dl').find('dt input').prop('checked', true);

			// var $link = $(this).parents('.app-sitemap-link-module');
			// if ($link.length) {
			// 	$link.find('input[type="radio"]').prop('checked', true);
            // }
            
		});
		
		// 新規ページ
		var $newPageModule         = modal.$el.find('.modal-sitemap-new');
		var $newPageCategoryModule = $newPageModule.find('.category-select');
		var $newPageCategorySelect = $newPageCategoryModule.find('select');
		var $newPageTypeModule     = $newPageModule.find('.type-select');
		var $newPageTypeSelect     = $newPageTypeModule.find('select');
		
		// 既存ページ
		var $editPageModule = modal.$el.find('.modal-sitemap-edit');
		var $editPageSelect = $editPageModule.find('select');
		
		// リンク
		var $linkModule = modal.$el.find('.modal-sitemap-link');
		var $linkPageModule = $linkModule.find('> dd div.app-sitemap-link-module:eq(0)');
		var $linkPageSelect = $linkPageModule.find('#link_page_id');
		var $linkUrlModule  = $linkModule.find('> dd div.app-sitemap-link-module:eq(1)');
		var $linkUrlValue   = $linkUrlModule.find('input[name="link_url"]');
		var $linkUrlTitle   = $linkUrlModule.find('input[name="title"]');
        var $linkUrlTargetBlank = $linkPageModule.find('input[name="link_target_blank"]');

        var $linkHouseModule = $linkModule.find('> dd div.app-sitemap-link-module:eq(2)');
        var $linkHouseValue   = $linkHouseModule.find('input[name="link_house"]');
        var $linkHouseTitle   = $linkHouseModule.find('input[name="title_house"]');
        var $methodLinkHouse = $linkHouseModule.find('.search-house-method');
		
		var $errors = modal.$el.find('.errors, .error');
		modal.clearErrors = function () {
			$errors.empty();
			modal.$el.find('.is-error').removeClass('is-error');
		};
		
		modal.pageTypes = {};

    $newPageModule.on('change', '.category-select select', function () {
			var category = $(this).val();
			var types = modal.pageTypes[category] || [];

			$newPageTypeSelect.empty();
			$.each(types, function (i, type) {
                if (!($.inArray(type, app.SiteMap.allTypeArticlePage) > -1)) {
                    $newPageTypeSelect.append('<option value="'+ type +'">'+ app.SiteMap.getTypeNameForSelect(type) +'</option>');
                }
			});
		});
		
		modal.$el.find('input:radio').on('change', function () {
            var isDisabled, isHide;
			
			isDisabled = !$newPageModule.find('input[name="radio-addpage"]').prop('checked');

			$newPageCategoryModule.find('label').after( $newPageCategorySelect.remove().prop('disabled', isDisabled) );
			$newPageTypeSelect.prop('disabled', isDisabled);

			$newPageModule.find('dd label').toggleClass('is-disable', isDisabled);
			$newPageModule.find('select').toggleClass('is-disable', isDisabled);
			
			isDisabled = !$editPageModule.find('input[name="radio-addpage"]').prop('checked');
			$editPageModule.find('select').prop('disabled', isDisabled);
			$editPageModule.find('select').toggleClass('is-disable', isDisabled);
			$editPageModule.find('dd label').toggleClass('is-disable', isDisabled);
			
			isDisabled = !$linkModule.find('input[name="radio-addpage"]').prop('checked');
			$linkModule.find('dd input:radio,input:checkbox').prop('disabled', isDisabled);
			$linkModule.find('dd label').toggleClass('is-disable', isDisabled);
			
			isDisabled = !$linkModule.find('input[name="radio-addpage"]').prop('checked') ||
							!$linkPageModule.find('input:radio').prop('checked');
			$linkPageModule.find('select').prop('disabled', isDisabled);
			$linkPageModule.find('select,.link-wrapper .errors,.link-wrapper span,.link-wrapper a').toggleClass('is-disable', isDisabled);
			$linkPageModule.find('.link-wrapper').toggleClass('selected', !isDisabled);

			isDisabled = !$linkModule.find('input[name="radio-addpage"]').prop('checked') ||
							!$linkUrlModule.find('input:radio').prop('checked');
			$linkUrlModule.find('input:text').prop('disabled', isDisabled);
            $linkUrlModule.find('li:lt(2)').toggleClass('is-disable', isDisabled);
			$linkUrlModule.find('.link-wrapper').toggleClass('selected', !isDisabled);
            
            isDisabled = !$linkModule.find('input[name="radio-addpage"]').prop('checked') ||
                            !$linkHouseModule.find('input:radio').prop('checked');
            $linkHouseModule.find('input:text').prop('disabled', isDisabled);
            $linkHouseModule.find('li,a').toggleClass('is-disable', isDisabled);
			$linkHouseModule.find('.link-wrapper').toggleClass('selected', !isDisabled);
            $methodLinkHouse.find('input:radio').prop('disabled', isDisabled);
            if (!app.SiteMap.hasSearchSetting) {
                $linkHouseModule.find('label').toggleClass('is-disable', !app.SiteMap.hasSearchSetting);
                $linkHouseModule.find('input').prop('disabled', !app.SiteMap.hasSearchSetting);
            }
            

            isHide = !$methodLinkHouse.find('input[name="radio_search_house"]').eq(0).prop('checked');
            $linkHouseModule.find('li.content-search-method div:nth-child(1)').toggleClass('is-hide', isHide);

            isHide = !$methodLinkHouse.find('input[name="radio_search_house"]').eq(1).prop('checked');
            $linkHouseModule.find('li.content-search-method div:nth-child(2)').toggleClass('is-hide', isHide);

			
		});

		$linkPageModule.find('a.search-page').on('click', function() {
			var isDisabled = $(this).hasClass('is-disable');
			if (!isDisabled) {
				app.SiteMap.freewordSearchModal($linkPageModule, $linkPageSelect, '.page-name', modal.$el);
			}
		});
		
		/**
		 * 作成可能タイプ設定
		 * @param {Array} types
		 * @param {Boolean} inMenu
		 * @param {app.SiteMap.Page=} parent
		 */
		modal.setTypes = function (types, parent, sort, sort_page_id) {
			modal.clearErrors();
			
			var sitemap = app.SiteMap.getInstance();
			
			modal.types = types;
			modal.parent = parent;
			modal.sort = sort;
            modal.sort_page_id = sort_page_id;
			// 新規ページ設定
			var pageTypes = modal.pageTypes = {};
			$.each(types, function (i, type) {
				// 存在する一意ページ除外
				if (app.SiteMap.isUniqueType(type) && app.SiteMap.getInstance().hasType(type)) {
					return;
				}
				
				// リンク除外
				if (type === app.SiteMap.Types.TYPE_LINK || type == app.SiteMap.Types.TYPE_ALIAS || type == app.SiteMap.Types.TYPE_ESTATE_ALIAS || type == app.SiteMap.Types.TYPE_LINK_HOUSE) {
					return;
				}
				
                var category = app.SiteMap.getCategoryFromType(type);
				if (category && !app.SiteMap.isOldArticleTemplateCategory(category)) {
					if (!pageTypes[category]) {
						pageTypes[category] = [];
					}
					pageTypes[category].push(type);
				}
			});
			
			$newPageCategorySelect.empty();
			$.each(pageTypes, function (category) {
				$newPageCategorySelect.append('<option value="'+ category +'">'+ app.SiteMap.getCategoryName(category) +'</option>');
			});
			$newPageCategorySelect[0].selectedIndex = 0;
			$newPageCategorySelect.change();
			$newPageCategoryModule.toggleClass('is-hide', !$newPageCategorySelect.children().length);
			$newPageTypeModule.toggleClass('is-hide', !$newPageCategorySelect.children().length);
			$newPageModule.toggleClass('is-hide', $newPageCategoryModule.hasClass('is-hide') && $newPageTypeModule.hasClass('is-hide'));
			
			// 既存ページ設定
			$editPageSelect.empty();
			if (parent.inMenu()) {
				$.each(sitemap.free.getChildren(), function (i, page) {
					// 作成可能なページタイプor詳細ページの場合は親タイプが作成可能な場合
                    // if (!page.isArticlePage()) {
                    if (
                        (app.arrayIndexOf(page.getType(), types) > -1) ||
                        (page.isDetailPageType() && app.arrayIndexOf(page.getParentType(), types) > -1)
                    ) {
                        $editPageSelect.append($('<option/>').attr('value', page.getId()).text(page.getTitleWithFilename()));
                    }
                    // }
				});
			}
			$editPageSelect[0].selectedIndex = 0;
			$editPageModule.toggleClass('is-hide', !$editPageSelect.children().length);
			
			// リンク設定
			if (app.arrayIndexOf(app.SiteMap.Types.TYPE_ALIAS, types) > -1) {
				// エイリアスが作成可能な場合
				$linkPageModule.removeClass('is-hide');
				$linkPageSelect.empty();
                $linkPageSelect.val('');
				var fixedArea = new app.SiteMap.FixedArea($('.edit-sitemap').eq(0).find('.sitemap-fix > ul'));
				var isDisplayEstateTop = false;
				$.each(fixedArea.getChildren(), function() {
					if (this.data.link_id == 'estate_top') {
						isDisplayEstateTop = true;
					}
				});
				// 既存ページのタイトル
				var selectPage = $linkPageModule.find('input[name="select-page"]:checked');
				if (selectPage.val() == undefined) {
					$('.app-sitemap-link-module .page-name').text('');
				}
			}
			else {
				$linkPageModule.addClass('is-hide');
			}
			
			if (app.arrayIndexOf(app.SiteMap.Types.TYPE_LINK, types) > -1) {
				// URLリンクが作成可能な場合
				$linkUrlModule.removeClass('is-hide');
				$linkUrlValue.val('').change();
				$linkUrlTitle.val('').change();
				$linkUrlTargetBlank.prop('checked', true);
			}
			else {
				$linkUrlModule.addClass('is-hide');
            }

            if (app.SiteMap.isLite) {
                $linkHouseModule.addClass('is-hide');
            } else {
                if (app.arrayIndexOf(app.SiteMap.Types.TYPE_LINK_HOUSE, types) > -1) {
                    $methodLinkHouse.find('input[name="radio_search_house"]').eq(0).prop('checked', true).change();
                    $linkHouseModule.removeClass('is-hide');
                    $linkUrlTargetBlank.prop('checked', true);
                    app.LinkHouse.clearInfoLinkHouse($linkHouseModule);
                } 
                else {
                    modal.$el.find('.modal-sitemap').css('height', 'auto');
                    $linkHouseModule.addClass('is-hide');
                }
            }
			$linkModule.toggleClass('is-hide', $linkPageModule.hasClass('is-hide') && $linkUrlModule.hasClass('is-hide') && $linkHouseModule.hasClass('is-hide'));
			// タイトルと各モジュール初期選択
			var title = '';
			if ((parent instanceof app.SiteMap.Page) && parent.hasDetailPageType()) {
				title = app.SiteMap.getTypeNameForSelect(parent.getType());
				
				// 一覧ページの場合、ページ選択セレクトボックスを非表示
				$newPageCategoryModule.addClass('is-hide');
				$newPageTypeModule.addClass('is-hide');
			}
			else {
				title = 'ページ';
			}
			
			if (!$linkModule.hasClass('is-hide')) {
				// リンクが作成可能な場合タイトルに追加
				title += 'またはリンク';
			}
			
			title += 'の追加';
			$title.text(title);
			
			if (!$newPageModule.hasClass('is-hide')) {
				$newPageModule.find('input[type="radio"]').eq(0).prop('checked', true);
			}
			else if (!$editPageModule.hasClass('is-hide')) {
				$editPageModule.find('input[type="radio"]').eq(0).prop('checked', true);
			}
			else if (!$linkModule.hasClass('is-hide')) {
				$linkModule.find('input[type="radio"]').eq(0).prop('checked', true);
			}
			
			$linkPageModule.find('input[type="radio"]').prop('checked', !$linkPageModule.hasClass('is-hide'));
			$linkUrlModule.find('input[type="radio"]').prop('checked', $linkPageModule.hasClass('is-hide'));

			modal.$el.find('input:radio:eq(0)').change();
		};
		
        function sortAll(page, sitemap) {
            var result = [];
            var page = $('.app-sitemap-page-item[data-id='+page.items[0].id+']');
            var childs = $(page).parent().children();

            $.each(childs, function () {
                result.push($(this).attr('data-id'));
            });

            app.api('/site-map/api-sort', {_token: sitemap.token, sort: result}, function (res) {
                sitemap.setData(res.items);
            })
        }
        
		// 閉じる処理
		modal.onClose = function (ret, modal) {
			if (!ret) {
				return;
			}
			
			var sitemap = app.SiteMap.getInstance();
			
			var addType = modal.$el.find('input[name="radio-addpage"]:checked').val();
			var url;
			var data = {};
			
			// 共通パラメータ
			data._token = sitemap.token;
			data.parent_page_id = modal.parent.getId();
			data.sort = modal.parent.newSortNumber(modal.sort);
			
			switch (addType) {
				// 新規のページを追加
				case 'create':
					url = '/site-map/api-create-page';
					data.page_type_code = $newPageTypeSelect.val();
					break;
				// 既存ページを追加
				case 'add':
					url = '/site-map/api-add-page';
					data.id = $editPageSelect.val();
					break;
				// リンクを追加
				case 'link':
					// 既存ページから選ぶ
					if ($linkPageModule.find('input[type="radio"]').prop('checked')) {
						url = '/site-map/api-create-alias';
						data.link_page_id = $linkPageSelect.val();
					}
					// URLからリンクを作る
					else {
						url = '/site-map/api-create-link';
                        if ($linkUrlModule.find('input[type="radio"]').prop('checked')) {
                            data.link_url = $linkUrlValue.val();
                            data.title = $linkUrlTitle.val();
                        } else {
                            data.link_house = $linkHouseModule.find('input[name="link_house"]').val();
                            data.title_house = $linkHouseTitle.val();
                            data.house_type = $linkHouseModule.find('input[name="link_house_type"]').val();
                            data.search_type = 0;
                            if ($linkHouseModule.find('input[name="radio_search_house"]').eq(1).prop('checked')) {
                                data.search_type = 1;
                            }
                            if (data.search_type == 1) {
                                data.house_no = $linkHouseModule.find('input[name="house_no"]').val();
                            }
                        }
					}
					if ($linkUrlTargetBlank.prop('checked')) {
						data.link_target_blank = 1;
					}
					break;
				default:
					return;
			}
			
			app.api(url, data, function (res) {
				modal.clearErrors();
				
				if (res.errors) {
					app.modal.alert('', '登録内容に誤りがあります。');
					app.setErrors(modal.$el, res.errors);
					return;
				}
				if (addType == 'link') {
                    app.updateAlertPublish();
                }
				sitemap.setData(res.items, true, modal.sort_page_id);
                sortAll(res, sitemap);
				modal.close();
			});
			
			return false;
		};
		
		return modal;
	};
	
	app.SiteMap.editLinkModal = function () {
		var contents = '<div class="modal-sitemap">' +
							'<dl class="modal-site-link">' +
                                '<div class="target-blank-link-edit">' +
                                    '<label>' +
                                        '<input type="checkbox" value="1" name="link_target_blank">別窓で開く' +
                                    '</label>' +
                                '</div>' +
								'<dt>' +
									'<span>既存ページから選ぶ</span>' +
                                    '<a class="btn-t-gray search-page" href="javascript:;">ページを検索</a>' +
								'</dt>' +
								'<dd>' +
									'<div>' +
										'<ul>' +
											'<li>' +
												'<span class="page-link-name"></span>' +
											'</li>' +
										'</ul>' +
										'<div id="link_page_id"></div>' +
									'</div>' +
								'</dd>' +
							'</dl>' +
							'<dl class="modal-url-rink">' +
                                '<div class="target-blank-link"><label><input type="checkbox" value="1" name="link_target_blank">別窓で開く</label></div>' +
								'<dt style="display:block">URLからリンクを作る</dt>' +
								'<dd>' +
									'<div class="category-select">' +
										'<label>URL</label>' +
										'<input id="link_url" type="text" name="link_url" maxlength="2000" class="watch-input-count"><span class="input-count">0/2000</span>' +
										'<div class="errors"></div>' +
									'</div>' +
									'<div class="type-select">' +
										'<label>リンク名</label>' +
										'<input id="title" type="text" name="title" maxlength="20" class="watch-input-count"><span class="input-count">0/30</span>' +
										'<div class="errors"></div>' +
									'</div>' +
								'</dd>' +
                            '</dl>' +
                            '<dl class="modal-sitemap-link">' +
                                '<div class="target-blank-link"><label><input type="checkbox" value="1" name="link_target_blank">別窓で開く</label></div>' +
                                '<dt class="is-disable" style="width: 35%">物件詳細を選ぶ</dt>' +
                                '<dd style="width: 95%;margin-left: 30px">' +
                                    '<div class="link-house-module link-house-module-edit">' +
                                        '<ul>' +
                                            '<li class="search-house-method">' +
                                                '<label><input type="radio" name="radio_search_house" class="search-method">条件で探す</label>' + 
                                                '<label><input type="radio" name="radio_search_house" class="search-method"> 物件番号で探す</label>' + 
                                            '</li>' +
                                            '<li class="content-search-method">' +
                                                '<div>' +
                                                    '<a class="btn-t-gray btn-search-all-house" href="javascript:;">物件を検索</a>' +
                                                '</div>' +
                                                '<div>' +
                                                '<input type="text" id="house_no" name="house_no" placeholder="物件番号（8・10・11桁）を入力してください" class="input-house-no">' +
                                                '<a class="btn-t-gray btn-search-house-no" href="javascript:;">検索</a>' +
                                                '</div>' +
                                                '<div class="error"></div>' +
                                            '</li>' +
                                            '<div class="error"></div>' +
                                            '<div class="errors"></div>' +
                                            '<li class="display-house-title" style="background-color:#D1E0ED;margin:0;padding:10px 10px 0;">' +
                                                '<label>選択中の物件'+app.LinkHouse.toolTip('display_house_title')+'</label>' +
                                                '<div class="house-title">' +
                                                '<label style="padding: 0 0 5px 0"></label>' +
                                                '<a href="javascript:;" class="btn-p-pc btn-preview-link-house is-hide" data-type="pc"></a>' +
                                                '<input type="hidden" id="link_house" name="link_house" value="">' +
                                                '</div>' +
                                            '</li>' +
                                            '<li class="member-no-info is-hide" style="background-color:#D1E0ED;padding:0 10px;">' +
                                                '<label style="padding: 5px 0 10px 0"></label>' +
                                                '<label class="display-house-no"></label>' +
                                                '<input type="hidden" id="link_house_type" name="link_house_type" value="">' +
                                            '</li>' +
                                            '<li>' +
                                                '<label>リンク名</label>' + 
                                                '<input type="text" id="title_house" name="title_house" maxlength="20" class="watch-input-count"><span class="input-count">0/20</span>' +
                                                '<div class="errors" style="margin-left: 110px;"></div>' +
                                            '</li>' +
                                        '</ul>' +
                                    '</div>' +
                                '</dd>' +
                            '</dl>' +
						'</div>';
		var modal = app.modal.popup({
			title: 'リンクの編集',
			contents: contents,
			modalBodyInnerClass: 'align-top',
			autoRemove: false
		});
		
		var sitemap = app.SiteMap.getInstance();
		
		var $modules = modal.$el.find('.modal-sitemap dl');
		$modules.find('dt,dd').css('border-top', '0 none');
		var $linkPageModule = $modules.eq(0);
		var $linkPageSelect = $linkPageModule.find('#link_page_id');
		var $linkUrlModule  = $modules.eq(1);
		var $linkUrlValue   = $linkUrlModule.find('input[name="link_url"]');
		var $linkUrlTitle   = $linkUrlModule.find('input[name="title"]');
        var $linkUrlTargetBlank = $modules.find('input[name="link_target_blank"]');
        var $linkHouseModule = $modules.eq(2);
        var $linkHouseName = $linkHouseModule.find('input[name="title_house"]');

        $linkHouseModule.find('input:radio').on('change', function () {
            var isHide;
            isHide = !$linkHouseModule.find('input[name="radio_search_house"]').eq(0).prop('checked');
            $linkHouseModule.find('li.content-search-method div:nth-child(1)').toggleClass('is-hide', isHide);

            isHide = !$linkHouseModule.find('input[name="radio_search_house"]').eq(1).prop('checked');
            $linkHouseModule.find('li.content-search-method div:nth-child(2)').toggleClass('is-hide', isHide);
        });
        $linkHouseModule.find('input:radio').eq(0).prop('checked', true).change();
		var $linkUrlTargetBlank = $modules.find('input[name="link_target_blank"]');

		$linkPageModule.find('a.search-page').on('click', function() {
			app.SiteMap.freewordSearchModal($linkPageModule, $linkPageSelect, '.page-link-name', modal.$el);
		})
		
		modal.setLink = function (link) {
			modal.$el.find('.errors, .error').empty();
			modal.$el.find('.is-error').removeClass('is-error');
			
			this.link = link;
			
			if (
				link.getType() === app.SiteMap.Types.TYPE_ALIAS ||
				link.getType() === app.SiteMap.Types.TYPE_ESTATE_ALIAS
			) {
				$linkPageModule.removeClass('is-hide');
				$linkUrlModule.addClass('is-hide');
                $linkHouseModule.addClass('is-hide');
				
				$linkPageSelect.empty();
				$linkPageSelect[0].selectedIndex = 0;
				$linkPageSelect.val(link.getType() === app.SiteMap.Types.TYPE_ESTATE_ALIAS ?
									link.data.link_estate_page_id:
									link.data.link_page_id);

                $('.page-link-name').empty();
				var allPages = sitemap.getAllPages().concat(sitemap.getIndexPages());
				$.each(allPages, function (id, page) {
					if (page.data.link_id == $linkPageSelect.val()) {
						$('.page-link-name').text('選択中ページ：' + page.getTitleWithFilenameSearch());
						return;
					}
				});
			}
			else if (link.getType() === app.SiteMap.Types.TYPE_LINK){
				$linkPageModule.addClass('is-hide');
                $linkUrlModule.removeClass('is-hide');
                $linkHouseModule.addClass('is-hide');
				
				$linkUrlValue.val(link.data.link_url || '').change();
				$linkUrlTitle.val(link.data.title || '').change();
            }
            else {
                $linkPageModule.addClass('is-hide');
                $linkUrlModule.addClass('is-hide');
                $linkHouseModule.removeClass('is-hide');
                var display = link.data.link_house.length && app.SiteMap.hasSearchSetting;
                $linkHouseModule.find('input[name="radio_search_house"]').eq(0).prop('checked', true).change();
                $linkHouseModule.find('dt, li').toggleClass('is-disable', !display);
                $linkHouseModule.find('.display-house-title').toggleClass('is-hide', !display);
                $linkHouseModule.find('li input[type="text"]').val('');
                $linkHouseModule.find('li input').prop('disabled', !display);
                var linkHouse = app.SiteMap.LinkHouse;
                linkHouse.setContainer($linkHouseModule);
                if (display) {
                    $linkHouseName.val(link.data.title).change();
                    linkHouse.actionListHouse(link.data.link_house);
                }
            }
			$linkUrlTargetBlank.prop('checked', link.data.link_target_blank);
		};
		
		modal.onClose = function (ret, modal) {
			if (!ret) {
				return;
			}
			
			var url;
			
			var data = {};
			data._token = app.SiteMap.getInstance().token;
			data.id = modal.link.getId();
			
			if (
				modal.link.getType() === app.SiteMap.Types.TYPE_ALIAS ||
				modal.link.getType() === app.SiteMap.Types.TYPE_ESTATE_ALIAS
			) {
				url = '/site-map/api-update-alias';
				data.link_page_id = $linkPageSelect.val();
				if ($linkUrlTargetBlank.eq(0).prop('checked')) {
					data.link_target_blank = 1;
				}
			}
			else {
				url = '/site-map/api-update-link';
                if (modal.link.getType() === app.SiteMap.Types.TYPE_LINK) {
                    if ($linkUrlTargetBlank.eq(1).prop('checked')) {
                        data.link_target_blank = 1;
                    }
                    data.link_url = $linkUrlValue.val();
                    data.title = $linkUrlTitle.val();
                } else {
                    if ($linkUrlTargetBlank.eq(2).prop('checked')) {
                        data.link_target_blank = 1;
                    }
                    data.link_house = $linkHouseModule.find('input[name="link_house"]').val();
                    data.title_house = $linkHouseName.val();
                    data.house_type = $linkHouseModule.find('input[name="link_house_type"]').val();
                    data.search_type = 0;
                    if ($linkHouseModule.find('input[name="radio_search_house"]').eq(1).prop('checked')) {
                        data.search_type = 1;
                    }
                    if (data.search_type == 1) {
                        data.house_no = $linkHouseModule.find('input[name="house_no"]').val();
                    }
                }
			}
			
			app.api(url, data, function (res) {
				modal.$el.find('.errors, .error').empty();
				modal.$el.find('.is-error').removeClass('is-error');
				if (res.errors) {
					app.modal.alert('', '登録内容に誤りがあります。');
					app.setErrors(modal.$el, res.errors);
					return;
				}
				app.updateAlertPublish();
				sitemap.setData(res.items);
				modal.close();
			});
			
			return false;
		};
		
		return modal;
    };
    
    // app.SiteMap.deleteArticleModal = function(clone) {
    //     var contents = '<div class="modal-article modal-contents-delete-page" data-scroll-container-max-height="500" style="overflow-y:auto;">'+
    //                         '<div class="alert-normal">「下書き」ページを削除できます。公開中のページは「サイトの公開/更新」の「公開設定（詳細設定）」より公開停止を行ってください。公開停止後、「削除」することができます。</div>' +
    //                         '<div class="modal-delete-article"></div>'+
    //                     '</div>';
    //     var modal = app.modal.popup({
	// 		title: '',
	// 		contents: contents,
	// 		modalBodyInnerClass: 'align-top',
	// 		ok: '削除する',
	// 		autoRemove: false
    //     });
    //     var contentDeleteArticle = modal.$el.find('.modal-delete-article');
    //     contentDeleteArticle.on('click', '.checkbox-delete:not(.is-disabled)', function() {
    //         $(this).closest('.app-sitemap-page-item').find('.checkbox-delete:not(.is-disabled)').toggleClass('checked', !$(this).hasClass('checked'));
    //         modal.checkDelete($(this).closest('.app-sitemap-page-item'));
    //         modal.enableBtnOk();
    //     });
    //     modal.$el.find('.modal-header').remove();
    //     modal.setContents = function(clone) {
    //         clone.find('.block-legend, .block-legend-description').remove();
    //         clone.find('.item:not(.add)').append('<div class="checkbox-delete"></div>');
    //         clone.find('.app-sitemap-page-item').each(function() {
    //             $(this).find('.action').addClass('is-hide');
    //             if ($(this).find('>.item.is-empty, >.item.is-draft').length) {
    //                 $(this).find('>.item .checkbox-delete').toggleClass('is-disabled', false);
    //             } else {
    //                 $(this).find('>.item .checkbox-delete').toggleClass('is-disabled', true);
    //             }
    //         })
    //         contentDeleteArticle.html(clone);
    //         modal.enableBtnOk();
    //     }

    //     modal.checkDelete = function(self) {
    //         var ul, parent, checked, li;
    //         var ul = self.closest('ul');
    //         var parent = ul.parent();
    //         if (parent.hasClass('app-sitemap-page-item')) {
    //             li = ul.find('>.app-sitemap-page-item');
    //             checked = li.find('>.item .checkbox-delete.checked');
    //             parent.find('>.item .checkbox-delete:not(.is-disabled)').toggleClass('checked', li.length == checked.length);
    //             modal.checkDelete(parent);
    //         }
    //     }

    //     modal.enableBtnOk = function() {
    //         var countCheck = modal.$el.find('.checkbox-delete.checked').length;
    //         modal.$el.find('.modal-btns .btn-t-blue').toggleClass('is-disable', countCheck > 0 ? false : true);
    //     }

    //     modal.checkCanDelete = function() {
    //         var isDelete = true;
    //         modal.$el.find('.checkbox-delete.checked').each(function() {
    //             var $page = $(this).closest('.app-sitemap-page-item');
    //             var page = app.SiteMap.getInstance().getPage($page.attr('data-id'));
    //             if (page.public_flg) {
    //                 isDelete = false;
    //             }
    //             console.log($page.find('.checkbox-delete:not(.checked)').length);
    //             if ($page.find('.checkbox-delete:not(.checked)').length) {
    //                 isDelete = false;
    //             }
    //             var $parent = $page.parent();
    //             if ($parent.children().length <= 1 && !$parent.hasClass('leve1')) {
    //                 if (!$parent.closest('.app-sitemap-page-item').find('>.item .checkbox-delete.checked').length) {
    //                     isDelete = false;
    //                 }
    //             }
    //         })
    //         return isDelete;
    //     }

    //     modal.onClose = function(ret, modal) {
    //         if (!ret) {
    //             return;
    //         }

    //         if (!modal.checkCanDelete()) {
    //             app.modal.popup({
    //                 contents: '<p style="text-align: center;">上層のページと下層のページは合わせて削除してください。どちらかのページのみ削除することはできません。</p>',
    //                 ok: 'OK',
    //                 cancel: false,
    //                 closeButton: false,
    //             }).show();
    //             return false;
    //         }

    //         var pages = [];
    //         contentDeleteArticle.find('.checkbox-delete').each(function() {
    //             if ($(this).hasClass('checked')) {
    //                 pages.push($(this).closest('.app-sitemap-page-item').attr('data-id'));
    //             }
    //         });

    //         var url = '/site-map/api-delete-page-article';
    //         var sitemap = app.SiteMap.getInstance();
    //         var params= {};
    //         params._token = sitemap.token;
    //         params.pages = pages;
    //         app.modal.confirm('確認', 'ページを削除します。よろしいですか？',function(ret) {
    //             if(!ret) {
    //                 modal.show();
    //                 return;
    //             }
    //             var closer = app.loading();
    //             app.api(url, params, function (res) {
    //                 closer();
    //             	if (res.errors) {
    //             		app.modal.alert('', '登録内容に誤りがあります。');
    //             		app.setErrors(modal.$el, res.errors);
    //             		return;
    //                 }
    //             	$.each(pages, function(i, id) {
    //                     $('.app-sitemap-page-item[data-id="'+id+'"]').remove();
    //                 });
    //                 sitemap.setData([]);
    //                 var links = [
    //                     {title: 'ページの作成/更新 （不動産お役立ち情報）', url: '/site-map/article'},
    //                     {title: 'ホームへ', url: '/'}
    //                 ];
    //                 var link = app.modal.message({
    //                     message: 'ページを削除しました。',
    //                     links: links,
    //                     ok: false,
    //                     cancel: false,
    //                     closeButton: false,
    //                     onClose: function (){
    //                         if (app.polling) app.polling.start();
    //                     }
    //                 });
    //                 link.$el.on('click', '.i-s-link', function(e) {
    //                     e.preventDefault();
    //                     var href = $(this).attr('href');
    //                     if (href == '/site-map/article') {
    //                         link.close();
    //                     } else {
    //                         window.location = href;
    //                     }
    //                 })
    //             });
    //         }, false).show();
    //     }

    //     return modal;
    // }

    app.SiteMap.setLinkModal = function() {
        var contents = '<div class="modal-contents-set-link"></div>';
        var modal = app.modal.popup({
			title: '',
			contents: contents,
			modalBodyInnerClass: 'align-top',
			ok: '保存',
            autoRemove: false,
            closeButton: false
        });
        modal.setContents = function(clone) {
            modal.$el.find('.modal-contents-set-link').html(clone.html());
            modal.$el.find('input[value="'+app.SiteMap.sideLayoutArticleType+'"]').prop('checked', true);
        }
        modal.onClose = function(ret, modal) {
            if (!ret) {
                return;
            }
            app.modal.confirmFirstAddPageArticle().onClose = function(ret, mod) {
                if (!ret) {
                    modal.show();
                    return;
                }
                var data = {};
                var url = '/site-map/api-save-set-link-article';
                var sitemap = app.SiteMap.getInstance();
                data._token = sitemap.token;
                data.type = modal.$el.find('input[name="type-set-link"]:checked').val();
                var closer = app.loading();
                app.api(url, data, function (res) {
                    closer();
                    if (res.errors) {
                        app.modal.alert('', '登録内容に誤りがあります。');
                        app.setErrors(modal.$el, res.errors);
                        return;
                    }
                    app.SiteMap.sideLayoutArticleType = data.type;
                    var text = '「'+ app.SiteMap.sideLayoutArticleResult[app.SiteMap.sideLayoutArticleType].replace(/(<([^>]+)>)/gi, "") + '」';
                    app.updateAlertPublish();
                    app.modal.message({
                        message: ' 設定を保存しました。',
                        ok: 'OK',
                        cancel: false,
                        closeButton: false,
                        onClose: function (){
                            $('.block-set-link .result-set-link').eq(0).html(text);
                            $('.block-set-link img').attr('src', '../images/sitemap/set_link_result_'+app.SiteMap.sideLayoutArticleType+'.png');
                        }
                    });
                    
                });
            }
        }
        return modal;
    }

    app.SiteMap.addArticleModal = function() {
        var contents = '<div class="moduled-add-page js-scroll-container" data-scroll-container-max-height="235">'+
                            '<div class="step-contents add-step0">' +
                                '<div class="item-content">'+
                                    '<div class="errors"></div>'+
                                    '<div class="item-normal">'+
                                        '<label><input type="radio" name="radio-select">大カテゴリーの追加</label>'+
                                        '<div class="errors-ar"></div>'+
                                        '<div class="item-page">'+
                                            '<label><input class="checkbox-all" type="checkbox" name="checkbox-all">すべて選択／すべて解除</label>'+
                                            '<ul>'+
                                                '<li>'+
                                                    '<label><input type="checkbox" name="check-item">すべて選択／すべて解除</label>'+
                                                '</li>'+
                                            '</ul>' +
                                        '</div>'+
                                    '</div>' +
                                    '<div class="item-original">'+
                                        '<label><input type="radio" name="radio-select" value="'+app.SiteMap.Types.TYPE_LARGE_ORIGINAL+'">オリジナル大カテゴリーの追加</label>'+
                                        '<div class="errors-ar"></div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="js-modal-btn">'+
                                    '<a class="btn-t-gray btn-modal-prev" href="javascript:;">戻る</a>'+
                                    '<a class="btn-t-blue btn-modal-next-step1" href="javascript:;"> 次へ</a>'+
                                '</div>'+
                            '</div>'+
                            '<div class="step-contents add-step1 is-hide">' +
                                '<div class="item-content">'+
                                    '<table class="tb-basic">' +
                                        '<tr>'+
                                            '<td>すべて選択中</td>'+
                                            '<td>(すべて選択中)</td>'+
                                            '<td><a class="btn-t-gray">変更</a></td>'+
                                        '</tr>'+
                                    '</table>'+
                                '</div>'+
                                '<div class="js-modal-btn">'+
                                        '<a class="btn-t-gray btn-modal-prev" href="javascript:;">戻る</a>'+
                                        '<a class="btn-t-blue btn-modal-next-step2" href="javascript:;"> 次へ</a>'+
                                '</div>'+
                            '</div>'+
                            '<div class="step-contents add-step2 is-hide">' +
                                '<div class="item-content">'+
                                    '<div class="item-page">'+
                                        '<div class="errors"></div>'+
                                        '<div class="item-normal">'+
                                        '<label><input class="checkbox-all" type="checkbox" name="checkbox-all">すべて選択／すべて解除</label>'+
                                        '<ul>'+
                                            '<li>'+
                                                '<label><input type="checkbox" name="radio-select">すべて選択／すべて解除</label>'+
                                            '</li>'+
                                        '</ul>' +
                                        '</div>'+
                                        '<div class="item-original">'+
                                        '<label><input class="check-original" type="checkbox" name="check-item" value="'+app.SiteMap.Types.TYPE_SMALL_ORIGINAL+'">オリジナル小カテゴリー</label>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="js-modal-btn">'+
                                    '<a class="btn-t-gray btn-modal-prev" href="javascript:;">戻る</a>'+
                                    '<a class="btn-t-blue btn-modal-next-step3" href="javascript:;"> 次へ</a>'+
                                '</div>'+
                            '</div>'+
                            '<div class="step-contents add-step3 is-hide">' +
                                '<div class="item-content">'+
                                    '<div class="item-normal">'+
                                        '<h3 class="heading-area"><label class="js-estate-select-group-check">東京23区</label></h3>'+
                                        '<div class="errors"></div>'+
                                        '<div class="item-page">'+
                                            '<label><input class="checkbox-all" type="checkbox" name="checkbox-all">すべて選択／すべて解除</label>'+
                                            '<ul>'+
                                                '<li>'+
                                                    '<label><input type="checkbox" name="radio-select">すべて選択／すべて解除</label>'+
                                                '</li>'+
                                            '</ul>' +
                                            '<label><input class="check-original" type="checkbox" name="check-item">オリジナル</label>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="item-original">'+
                                        '<h3 class="heading-area"><label class="js-estate-select-group-check">東京23区</label></h3>'+
                                        '<div class="item-page">'+
                                            '<label><input type="checkbox" name="check-item" value="">すべて選択／すべて解除</label>'+
                                            '<div class="errors"></div>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="js-modal-btn">'+
                                    '<a class="btn-t-gray btn-modal-prev" href="javascript:;">戻る</a>'+
                                    '<a class="btn-t-blue btn-modal-next-step-4" href="javascript:;"> 次へ</a>'+
                                '</div>'+
                            '</div>'+
                            '<div class="step-contents add-step4 is-hide">' +
                                '<div class="text-content">'+
                                    '<p>ひな形（定型例文）のご利用について</p>' +
                                    '<p>（１）ひな形を使用したことによって生じたトラブル、いかなる損害に対しても当社は一切責任を負いませんので記載されている内容は必ず確認していただき、適宜編集してご利用ください。</p>' +
                                    '<p>（２）作成・編集済みのページにひな形を再度適用させる場合、現在入力されている内容は破棄されます。</p>' +
                                '</div>'+
                                '<div class="js-modal-btn">'+
                                    '<a class="btn-t-gray btn-modal-prev" href="javascript:;">戻る</a>'+
                                    '<a class="btn-t-blue btn-modal-next-step5" href="javascript:;">ひな形を適用させる</a>'+
                                '</div>'+
                            '</div>'+
                    '</div>';
        var modal = app.modal.popup({
            title: 'aaa',
			contents: contents,
			modalBodyInnerClass: 'align-top modal-article-contents',
			ok: false,
            cancel:false,
			autoRemove: false
        });
        var input = 'input:not(.is-used)';
        var inputCheck = 'input[name="check-item"]:not(:disabled, .is-used):checked';
        var moduleAddPage = modal.$el.find('.moduled-add-page');
        var stepContent = moduleAddPage.find('.step-contents');
        modal.$el.find('input[name="radio-select"]').on('change', function() {
            var radiofirst = modal.$el.find('input[name="radio-select"]').eq(0);
            var radiosecond = modal.$el.find('input[name="radio-select"]').eq(1);
            var isCheck =  radiofirst.prop('checked');
            if (isCheck) {
                radiofirst.closest('.item-normal').css('background-color', '#eff4f8');
                radiosecond.closest('.item-original').css('background-color', '#fff');
            } else {
                radiofirst.closest('.item-normal').css('background-color', '#fff');
                radiosecond.closest('.item-original').css('background-color', '#eff4f8');
            }
            radiofirst.closest('.item-normal').find('.item-page ' + input).prop('disabled', !isCheck);
            var checkboxNotUsed = radiofirst.closest('.item-normal').find('.item-page input.is-used');
            var checkbox = radiofirst.closest('.item-normal').find('.item-page input[name="check-item"]');
            isCheck = !isCheck ? isCheck : !(checkboxNotUsed.length == checkbox.length);
            modal.$el.find('.checkbox-all').prop('disabled', !isCheck);
            if (isCheck) {
                radiofirst.closest('.item-normal').find('.item-page ' + input).parent('label').css('color', '#333');
            } else {
                radiofirst.closest('.item-normal').find('.item-page ' + input).parent('label').css('color', '#aaa');
            }
        });

        modal.$el.on('change', '.checkbox-all', function() {
            var check = $(this).prop('checked');
            $(this).closest('.item-page').find('ul '+input).prop('checked', check);
        });
        modal.$el.on('change', 'input[name="check-item"]:not(.check-original)', function() {
            var countCheck = $(this).closest('ul').find(inputCheck).length;
            var countItem = $(this).closest('ul').find(input).length;
            if (countCheck != 0 && countCheck == countItem) {
                $(this).closest('.item-page').find('.checkbox-all').prop('checked', true);
            } else {
                $(this).closest('.item-page').find('.checkbox-all').prop('checked', false);
            }
        });
        modal.$el.on('click', '.article-search-list__more', function() {
            var $more = $(this);
            $more.closest('.item-content').css('overflow-y', 'auto');
			if ($more.hasClass('is-opened')) {
				$more.parent().find('dl').hide();
				$more.removeClass('is-opened');
				$more.text(modal.MORE_CLOSED);
			} else {
                $more.parent().find('dl').show();
                $more.addClass('is-opened');
                $more.text(modal.MORE_OPENED);
			}
        })
        modal.MORE_CLOSED = '…記事を見る';
        modal.MORE_OPENED = '…閉じる';
        var title = '', screens = [], step, previousStep, pages, data, displayAll, isOriginal;
        modal.setTypes = function(category, type, parent, element, sort, sort_page_id) {
            pages = {}, data = {}, displayAll = {}, step = 0;
            modal.isMaxCategory = false;
            modal.parent = parent;
            modal.category = category;
            modal.element = element;
            modal.sort = sort;
            modal.sort_page_id = sort_page_id;
            modal.pageExists = element.find(' > .app-sitemap-page-item').map(function() {
                return parseInt(this.getAttribute('data-type')); 
            }).get();
            if (category == app.SiteMap.Categories.CATEGORY_LARGE) {
                title = ['大カテゴリーの追加', '小カテゴリーと記事を選択', '小カテゴリーの選択', '記事の選択'];
                screens = [0, 1, 2, 3, 4];
            } else if (category == app.SiteMap.Categories.CATEGORY_SMALL) {
                title = ['小カテゴリーの追加', '記事を選択', '記事を選択してください。'];
                screens = [0, 1, 2, 4];
            } else {
                title = ['記事の追加'];
                screens = [0, 4];
            }
            stepContent.eq(screens[step]).attr('data-page-type', type).toggleClass('is-hide', false);
            modal.setCurrentStep(0);
            modal.setTitle();
            modal.renderStep();
        };
        modal.renderStep = function() {
            modal.clearError();
            modal.$el.find('.modal-header').toggleClass('is-hide', step == 4);
            var container = stepContent.eq(screens[step]);
            var scrollArea = null;
            switch (screens[step]) {
                case 0:
                    modal.renderStep1(container);
                    scrollArea = container.find('.item-normal');
                    break;
                case 1:
                    modal.renderStep2(container);
                    scrollArea = container.find('.item-content');
                    break;
                case 2:
                    modal.renderStep1(container);
                    scrollArea = container.find('.item-normal');
                    break;
                case 3:
                    modal.renderStep3(container);
                    scrollArea = container.find('.item-content');
                    break;
                case 4:
                    scrollArea = container.find('.text-content');
                    break;
                default:
                    break;
            }
            if (scrollArea !== null) {
                scrollArea.scrollTop(0);
            }
            setTimeout(function() {
                var itemContent = container.find('.item-content');
                if (itemContent.height() < 450) {
                    itemContent.next('.js-modal-btn').removeAttr('style');
                    itemContent.removeAttr('style');
                } else {
                    itemContent.css({'overflow': 'auto'});
                    if (step == 0) {
                        itemContent.removeAttr('style');
                        itemContent.next('.js-modal-btn').css({'margin-top': '50px'});
                    }
                }
            }, 100)
        }
        modal.renderStep1 = function(container) {
            var parentType = container.attr('data-page-type');
            var eleItem = container.find('.item-page ul');
            eleItem.empty();
            var value, text, category, label, length, max = 0, message = '', messageOriginal = '';
            switch (screens[step]) {
                case 0:
                    if (screens.length == 5) {
                        category = app.SiteMap.Categories.CATEGORY_LARGE;
                        value = app.SiteMap.Types.TYPE_LARGE_ORIGINAL;
                        length = modal.element.find('>.app-sitemap-page-item[data-type="'+value+'"]').length;
                        max = app.SiteMap.MaxOriginalLarge;
                        message = '<p>大カテゴリー（ひな形あり）はすべて作成済みです。</p>';
                        messageOriginal = '<p>オリジナル大カテゴリーはこれ以上作成できません。</p>';
                    } else if (screens.length == 4) {
                        value = app.SiteMap.Types.TYPE_SMALL_ORIGINAL;
                        category = app.SiteMap.Categories.CATEGORY_SMALL;
                        length = modal.element.find('>.app-sitemap-page-item[data-type="'+value+'"]').length;
                        max = app.SiteMap.MaxOriginalSmall;
                        message = '<p>小カテゴリー（ひな形あり）はすべて作成済みです。</p>';
                        messageOriginal = '<p>オリジナル小カテゴリーはこれ以上作成できません。</p>';
                    } else {
                        value = app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL;
                        category = app.SiteMap.Categories.CATEGORY_ARTICLE;
                        message = '<p>記事（ひな形あり）はすべて作成済みです。</p>';
                    }
                    text = app.SiteMap.getCategoryName(category) + 'の追加';
                    container.find('.item-normal label').contents()[1].textContent = text
                    text = app.SiteMap.getTypeNameForSelect(value) + 'の追加';
                    label =  container.find('.item-original label');
                    eleItem.closest('.item-normal').toggleClass('is-hide', typeof app.SiteMap.ChildTypes[parentType] == 'undefined');
                    if (max != 0 && length == max) {
                        container.find('.item-original input:radio').prop('disabled', true);
                        container.find('.item-original .errors-ar').eq(0).html(messageOriginal)
                        container.find('.item-original input:radio').parent('label').css('color', '#aaa');
                    } else {
                        container.find('.item-original input:radio').prop('disabled', false);
                        container.find('.item-original input:radio').parent('label').css('color', '#333');
                    }
                    break;
                case 2:
                    if (screens.length == 5) {
                        value = app.SiteMap.Types.TYPE_SMALL_ORIGINAL;
                        var length = modal.element.find('>.app-sitemap-page-item[data-type="'+value+'"]').length;
                        if (length >= app.SiteMap.MaxOriginalSmall) {
                            container.find('.item-original input').prop('checked', false).prop('disabled', true);
                        }
                    } else {
                        value = app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL;
                    }
                    text = app.SiteMap.getTypeNameForSelect(value)+ 'の追加';
                    label =  container.find('.check-original').closest('label');
                    eleItem.closest('.item-page').find('.item-normal').toggleClass('is-hide', typeof app.SiteMap.ChildTypes[parentType] == 'undefined');
                    break;
            
                default:
                    break;
            }
            label.contents()[1].textContent = text;
            label.find('input').val(value);
            label.find('input').prop('checked', $.inArray(value, pages[parentType]) > -1);
            var index = 1;
            if (typeof app.SiteMap.ChildTypes[parentType] != 'undefined') {
                $.each(app.SiteMap.ChildTypes[parentType], function(i, type) {
                    var checked = ($.inArray(type, pages[parentType]) > -1) || typeof pages[parentType] == 'undefined';
                    var disabled = false;
                    if ($.inArray(type, modal.pageExists) > -1) {
                        disabled = true;
                        checked = false;
                    }
                    eleItem.append('<li><label><input type="checkbox" name="check-item" value="'+ type +'">'+ modal.getTitlePage(type, disabled) +'</label></li>');
                    eleItem.find('li:nth-child('+ index +') input[name="check-item"]').prop('checked', checked).change().prop('disabled', disabled).toggleClass('is-used', disabled);
                    eleItem.find('li:nth-child('+ index +') input[name="check-item"].is-used').parent('label').css('color', '#aaa');
                    index++;
                });
            }
            if (eleItem.find('.is-used').length != 0 && typeof pages[parentType] == 'undefined') {
                eleItem.find('input[name="check-item"]').prop('checked', false).change();
            }
            container.find('.item-normal input:radio').eq(0).prop('disabled', !eleItem.find(input).length);
            if (!eleItem.find(input).length) {
                container.find('.item-normal .errors-ar').eq(0).html(message);
                container.find('.item-normal .errors-ar').prev('label').css('color', '#aaa');
                isOriginal = true;
            } else {
                container.find('.item-normal .errors-ar').prev('label').css('color', '#333');
            }
            var disableNext = container.find('input:radio:disabled').length ==  container.find('input:radio').length;
            modal.isMaxCategory = disableNext;
            container.find('.btn-modal-next-step1').toggleClass('is-disable', disableNext);
            if (isOriginal) {
                container.find('.item-original input:radio').eq(0).prop('checked', true).change();
                isOriginal = false;
            } else {
                container.find('.item-normal input:radio').eq(0).prop('checked', true).change();
            }
        }
        modal.renderStep2 = function(container) {
            var parentType = stepContent.eq(0).attr('data-page-type');
            container.find('.tb-basic').empty();
            $.each(pages[parentType], function(i, type) {
                var tr = $('<tr></tr>');
                var td = '<th>'+ app.SiteMap.getTypeNameForSelect(type) +'</th>' +
                            '<td></td>' +
                            '<td><a class="btn-t-gray btn-modal-select" data-parent-type="'+type+'">変更</a></td>';
                container.find('.tb-basic').append(tr.append(td));
                var $selected = container.find('.tb-basic tr').eq(i).find('td').eq(0);
                var selectedHtml = '<span>（すべて選択中）</span>';
                var displayChild = false;
                if (type in displayAll && displayAll[type] === false && typeof pages[type] != 'undefined') {
                    selectedHtml = '<ul class="list-item">';
                    var listChild = '<div class="list-child-item">';
                    $.each(pages[type], function(i, child) {
                        var comma = i < pages[type].length -1 ? ",": "";
                        selectedHtml += '<li><span>'+app.SiteMap.getTypeNameForSelect(child) + comma +'</span></li>';
                        if (typeof pages[child] != 'undefined') {
                            listChild += '<dl>';
                            listChild += '<dt><span>'+app.SiteMap.getTypeNameForSelect(child)+'</span></dt>';
                            listChild += '<dd class="pb10">';
                            $.each(pages[child], function(index, val) {
                                var comma = ",";
                                // last article no comma
                                if (index === pages[child].length - 1) {
                                    listChild += '<span>' + app.SiteMap.getTypeNameForSelect(val) + '</span>';
                                } else {
                                    listChild += '<span>' + app.SiteMap.getTypeNameForSelect(val) + comma +'</span>';
                                }
                            });
                            listChild += '</dd>';
                            listChild += '</dl>';
                            displayChild = true;
                        }
                    });
                    selectedHtml += '</ul>';
                    if (displayChild) {
                        selectedHtml += listChild;
                    }

                }
                $selected.html(selectedHtml);
                $selected.find('.list-child-item').each(function() {
                    if ($(this).find('dl').length > 0) {
                        $(this).find('dl').hide();
                        $(this).append($('<a class="article-search-list__more" style="cursor: pointer;">'+modal.MORE_CLOSED+'</a>'))
                    }
                });
            });
        };
        var normal = stepContent.eq(3).find('.item-normal').clone();
        var original = stepContent.eq(3).find('.item-original').clone();
        modal.renderStep3 = function(container) {
            var pageType = parseInt(stepContent.eq(screens[step - 1]).attr('data-page-type'));
            container.find('.item-normal').remove();
            container.find('.item-original').remove();
            $.each(pages[pageType], function(i, type) {
                if (type == app.SiteMap.Types.TYPE_SMALL_ORIGINAL) {
                    original.find('.heading-area label').text(app.SiteMap.getTypeNameForSelect(type)).attr('data-page-type', app.SiteMap.Types.TYPE_SMALL_ORIGINAL);
                    original.find('.item-page label').html('<input type="checkbox" name="check-item" value="'+app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL+'">' + app.SiteMap.getTypeNameForSelect(app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL));
                    original.find('input[name="check-item"]').prop('checked', true);
                    container.find('.item-content').append(original);
                } else {
                    var element = normal;
                    var heading = element.find('.heading-area label');
                    var item = element.find('.item-page ul');
                    heading.text(app.SiteMap.getTypeNameForSelect(type)).attr('data-page-type', type);
                    item.empty();
                    var countCheck = 0;
                    normal.find('input[name="checkbox-all"]').removeAttr('checked');
                    $.each(app.SiteMap.ChildTypes[type], function(i, child) {
                        item.append('<li><label><input type="checkbox" name="check-item" value="'+ child +'">'+ app.SiteMap.getTypeNameForSelect(child) +'</label></li>');
                        var checked = typeof pages[type] == 'undefined' || (typeof pages[type] != 'undefined' && $.inArray(child, pages[type]) > -1);
                        if(checked) {
                            item.find('li').eq(i).find('input').attr('checked', 'checked');
                            countCheck ++;
                        }
                    });
                    if (countCheck == app.SiteMap.ChildTypes[type].length) {
                        normal.find('input[name="checkbox-all"]').attr('checked', 'checked');
                    }
                    var checked = '';
                    if ($.inArray(app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL, pages[type]) > -1) {
                        checked = 'checked="checked"';
                    }
                    var label = '<input class="check-original" type="checkbox" name="check-item" value="'+app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL+'" '+checked+'>' + app.SiteMap.getTypeNameForSelect(app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL);
                    normal.find('.check-original').closest('label').html(label);
                    container.find('.item-content').append('<div class="item-normal">'+normal.html()+'</div>');
                }
            });
            
        }
        modal.setTitle = function() {
            modal.$el.find('.modal-header h2').text(title[step]);
        }

        modal.getTitlePage = function(type, isUsed) {
            var text = '';
            if (typeof isUsed != 'undefined' && !!isUsed) {
                text = '<span class="created">作成済み</span>';
            }
            return app.SiteMap.getTypeNameForSelect(type) + text;
        }

        modal.setDataAddPage = function(type, element) {
            var childs1, childs2;
            switch (screens.length) {
                case 5:
                    data[type] = {};
                    $.each(pages[type], function(i, value) {
                            data[type][value] = {}
                        if (typeof pages[value] == 'undefined') {
                            if (value == app.SiteMap.Types.TYPE_LARGE_ORIGINAL) {
                                childs1 = [app.SiteMap.Types.TYPE_SMALL_ORIGINAL];
                            } else {
                                childs1 = app.SiteMap.ChildTypes[value];
                            }
                        } else {
                            childs1 = pages[value];
                        }
                        $.each(childs1, function(j, val) {
                            data[type][value][val] = {};
                            if (typeof pages[val] == 'undefined') {
                                if (val == app.SiteMap.Types.TYPE_SMALL_ORIGINAL) {
                                    childs2 = [app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL];
                                } else {
                                    childs2 = app.SiteMap.ChildTypes[val];
                                }
                            } else {
                                childs2 = pages[val];
                            }
                            if (typeof childs2 != 'undefined') {
                                $.each(childs2, function(n, v){
                                    data[type][value][val]['"'+v+'"'] = [];
                                })
                            }
                        });
                    });
                    break;
                case 4:
                    $.each(pages[type], function(i, value) {
                        data[value] = {}
                        if (typeof pages[value] == 'undefined') {
                            if (value == app.SiteMap.Types.TYPE_SMALL_ORIGINAL) {
                                childs1 = [app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL];
                            } else {
                                childs1 = app.SiteMap.ChildTypes[value];
                            }
                        } else {
                            childs1 = pages[value];
                        }
                        $.each(childs1, function(j, val) {
                            data[value]['"'+val+'"'] = [];
                        });
                    })
                    break;
                case 2:
                    $.each(pages[type], function(i, val) {
                        data['"'+val+'"'] = [];
                    });
                    break;
            
                default:
                    break;
            }
        }

        modal.setCurrentStep = function(go) {
            step = go;
        }

        modal.checkError = function(element) {
            var check = true;
            var message = '<p>作成するページを選択してください。</>'
            modal.clearError();
            element.each(function() {
                if (!$(this).find('input:checkbox:checked').length) {
                    check = false;
                }
            });
            if (!check) {
                app.modal.alert('',' 作成するページを選択してください。', function() {
                    element.each(function() {
                        if (!$(this).find('input:checkbox:checked').length) {
                            if (screens[step] == 0) {
                                $(this).closest('.item-content').find('.errors-ar').eq(0).css('display', 'inline-block');
                                $(this).closest('.item-content').find('.errors-ar').eq(0).html(message);
                            } else {
                                $(this).find('.errors').eq(0).css('display', 'inline-block');
                                $(this).find('.errors').eq(0).html(message);
                            }
                        }
                    });
                })
            }
            return check;
        }

        modal.clearError = function() {
            modal.$el.find('.errors, .errors-ar').css('display', 'none');
            modal.$el.find('.errors, .errors-ar').html('');
        }

        modal.goStep = function(go) {
            if (go < 0 ) {
                modal.close();
                return;
            }
            stepContent.toggleClass('is-hide', true);
            stepContent.eq(go).toggleClass('is-hide', false);
            modal.setCurrentStep(go);
            modal.setTitle(screens[step]);
            modal.renderStep();
        }

        modal.$el.on('click', '.btn-modal-prev', function() {
            if (isOriginal && step == 4) {
                modal.goStep(0);
            } else {
                if (step == 4) {
                    modal.goStep(previousStep);
                } else {
                    modal.goStep(step - 1);
                }
            }
        });

        modal.$el.on('click', '.btn-modal-next-step1:not(.is-disable)', function() {
            previousStep = step;
            var radOriginal = stepContent.eq(screens[step]).find('.item-original input:radio');
            var type = stepContent.eq(screens[step]).attr('data-page-type');
            isOriginal = false
            if (radOriginal.length && radOriginal.prop('checked')) {
                pages[type] = [parseInt(radOriginal.val())];
                isOriginal = true;
            } else {
                if (!modal.checkError(stepContent.eq(screens[step]).find('.item-normal'))) {
                    return;
                }
                pages[type] = stepContent.eq(screens[step]).find('input[name="check-item"]').filter(':checked').map(function() {
                    return parseInt(this.value);
                }).get();
            }
            if (screens.length == 2 || isOriginal) {
                modal.goStep(4);
            } else {
                modal.goStep(step + 1);
            }
            
        });
        modal.$el.on('click', '.btn-modal-next-step2', function() {
            previousStep = step;
            modal.goStep(4);
            return;
        })

        modal.$el.on('click', '.btn-modal-next-step3', function() {
            previousStep = step;
            var container = stepContent.eq(screens[step]);
            if (!modal.checkError(container.find('.item-page'))) {
                return;
            }
            var type = container.attr('data-page-type');
            pages[type] = container.find('input[name="check-item"]').filter(':checked').map(function() {
                return parseInt(this.value);
            }).get();
            switch (screens.length) {
                case 4:
                    if ((typeof app.SiteMap.ChildTypes[type] != 'undefined' &&
                        pages[type].length != app.SiteMap.ChildTypes[type].length) ||
                        pages[type].includes(app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL)) {
                        displayAll[type] = false;
                    } else {
                        displayAll[type] = true;
                    }
                    modal.goStep(step - 1);
                    break;
                case 5:
                    modal.goStep(step + 1);
                    break;
            
                default:
                    break;
            }
            return;
        });
        modal.$el.on('click', '.btn-modal-next-step-4', function() {
            var container = stepContent.eq(screens[step]);
            if (!modal.checkError(container.find('.item-normal, .item-original'))) {
                return;
            }
            var largeType =parseInt(container.attr('data-page-type'));
            displayAll[largeType] = true;
            pages[largeType] = container.find('.js-estate-select-group-check').map(function() {
                return parseInt(this.getAttribute('data-page-type'));
            });
            if (typeof app.SiteMap.ChildTypes[largeType] != 'undefined') {
                if ((pages[largeType].length != app.SiteMap.ChildTypes[largeType].length) || pages[largeType].get().includes(app.SiteMap.Types.TYPE_SMALL_ORIGINAL)) {
                    displayAll[largeType] = false;
                }
            }
            container.find('.item-content > div').each(function() {
                var smallType = parseInt($(this).find('.js-estate-select-group-check').attr('data-page-type'));
                pages[smallType] = $(this).find('input[name="check-item"]').filter(':checked').map(function() {
                    return parseInt(this.value);
                }).get();
                if (smallType != app.SiteMap.Types.TYPE_SMALL_ORIGINAL) {
                    if ((pages[smallType].length != app.SiteMap.ChildTypes[smallType].length) || pages[smallType].includes(app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL)) {
                        displayAll[largeType] = false;
                    }
                }
            })
            modal.goStep(1);
        })

        modal.$el.on('click', '.btn-modal-select', function() {
            stepContent.eq(screens[step + 1]).attr('data-page-type', $(this).attr('data-parent-type'));
            stepContent.eq(3).attr('data-page-type', $(this).attr('data-parent-type'));
            modal.goStep(step + 1);
        });

        modal.$el.on('click', '.btn-modal-next-step4', function() {
            previousStep = step;
            var container = stepContent.eq(screens[step]);
            container.find('> div').each(function() {
                var type = $(this).find('.heading-area label').attr('data-page-type');
                pages[type] = $(this).find('input[name="check-item"]').filter(':checked').map(function() {
                    return parseInt(this.value);
                }).get();
            });
            modal.goStep(1);
        });

        modal.$el.on('click', '.btn-modal-next-step5', function() {
            if (app.SiteMap.isFirstCreatePageArticle) {
                app.modal.confirmFirstAddPageArticle().onClose = function(ret) {
                    if (!ret) {
                        return;
                    }
                    var type = stepContent.eq(0).attr('data-page-type');
                    modal.setDataAddPage(type);
                    modal.addPageApi();
                }
                return;
            }
            var type = stepContent.eq(0).attr('data-page-type');
            modal.setDataAddPage(type);
            modal.addPageApi();
            app.updateAlertPublish();
        });
        
        modal.addPageApi = function() {
            var params = {}, sort;
            var url = '/site-map/api-create-page-article';
            var sitemap = app.SiteMap.getInstance();
            params._token = sitemap.token;
            params.parent_page_id = typeof modal.parent != 'undefined'? modal.parent.getId() : null;
            params.level = typeof modal.parent != 'undefined'? modal.parent.level : 1;
            params.sort = typeof modal.parent != 'undefined'? modal.parent.newSortNumber(modal.sort) : 0;
            params.pages = JSON.stringify(data);
            params.isFirstCreatePageArticle = app.SiteMap.isFirstCreatePageArticle;
            modal.close();
            isOriginal = false;
            var closer = app.loading();
            app.api(url, params, function (res) {
                closer();
				if (res.errors) {
					app.modal.alert('', '登録内容に誤りがあります。');
					app.setErrors(modal.$el, res.errors);
					return;
                }
                if (res.error) {
					app.modal.alert('', res.message);
					return;
                }
                var pageOriginal = res.items.filter(function(item) {
                    var original = [app.SiteMap.Types.TYPE_LARGE_ORIGINAL, app.SiteMap.Types.TYPE_SMALL_ORIGINAL, app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL]
                    return $.inArray(item['page_type_code'], original) > -1
                });
                if (pageOriginal.length) {
                    var options = {
                        title : '',
                        contents: '<div">'+
                                        '<p style="font-weight: bold; text-align: center;">【推奨】基本設定の修正</p>'+
                                        '<p style="text-align: center;">オリジナルカテゴリーまたはオリジナル記事の基本設定の内容を仮登録しました。</p>'+
                                        '<p style="text-align: center;">ページの内容に沿って修正してください。</p>'+
                                    '</div>',
                        ok: 'OK',
                        cancel: false,
                        closeButton: false,
                    };
                    app.modal.popup(options).show();
                }
                if (typeof modal.sort_page_id == 'undefined') {
                    sitemap.setData(res.items);
                } else {
                    modal.sortAllArticle(res.items, sitemap);
                }
                app.SiteMap.isFirstCreatePageArticle = 0;
			});
        }
        modal.onClose = function(ret, modal) {
            if (!ret) {
                stepContent.toggleClass('is-hide', true);
                return;
            }
        }
        modal.sortAllArticle = function(pages, sitemap) {
            var items = pages.filter(function(item) {
                return item['page_category_code'] == modal.category;
            });
            sitemap.setData(items, true, modal.sort_page_id);
            var items = pages.filter(function(item) {
                return item['page_category_code'] != modal.category;
            });
            sitemap.setData(items);
            var result = [];
            var childs = modal.element.children();

            $.each(childs, function () {
                result.push($(this).attr('data-id'));
            });

            app.api('/site-map/api-sort', {_token: sitemap.token, sort: result}, function (res) {
                sitemap.setData(res.items);
            })
        }

        return modal;
    }

    app.SiteMap.usePageArticleModal = function() {
        var modal = app.modal.popup({
			title: '利用可能な記事一覧',
			contents: '',
			modalBodyInnerClass: 'align-top',
			ok: '追加',
			autoRemove: false
        });
        modal.setContents = function(html) {
            modal.$el.find('.modal-body-inner').html(html);
            modal.$el.find('.large-column').each(function() {
                var tr = $(this).closest('tr');
                var largeType = parseInt($(this).attr('data-type'));
                var count = 0;
                $.each(app.SiteMap.ChildTypesAdvancePlan[largeType], function(i, small) {
                    modal.$el.find('.small-column[data-type="'+small+'"]').attr('rowspan', app.SiteMap.ChildTypesAdvancePlan[small].length);
                    count += app.SiteMap.ChildTypesAdvancePlan[small].length;
                });
                tr.find('.large-column').attr('rowspan', count);
            })
        }
        return modal;
    }
	
	app.SiteMap.Area = function ($container) {
		this.$container = $container;
		this.$add = $container.find('> .last');
	};
	app.SiteMap.Area.prototype = {
		getId: function () {
			return null;
		},
		
		inMenu: function () {
			return false;
		},
		
		empty: function () {
			this.$container.empty();
			if (this.$add.length) {
				this.$container.append(this.$add);
			}
			return this;
		},
		
		addChild: function (child, above) {
			if (child.parent) {
				child.parent.removeChild(child);
			}
			
			if (!this.$container.find('.app-sitemap-page-item[data-id="'+child.getId()+'"]').length) {
				if (this.$add.length) {
					if (typeof above !==  "undefined") {
                        this.$container.find('.app-sitemap-page-item[data-id="'+above+'"]').after(child.$el);
                    } else {
                        this.$add.before(child.$el);
					}
				}else {
					this.$container.append(child.$el);
				}
			}
			return this;
		},
		
		getChildren: function () {
			var children = [];
			this.$container.children(':not(.last, .drop-zone-inside)').each(function () {
				children.push( app.SiteMap.getInstance().getPage($(this).attr('data-id')) );
			});
			return children;
		},
		
		newSortNumber: function () {
			return 0;
		},
		
		sortChildren: function () {
			var children = this.getChildren();
			children.sort(this._sortChildrenMethod);
			
			var self = this;
			var $before;
			$.each(children, function (i, child) {
				if ($before) {
					$before.after(child.$el);
				}
				else {
					self.$container.prepend(child.$el);
				}
				$before = child.$el;
			});
			return this;
		},
		
		_sortChildrenMethod: function (a, b) {
			// type,id順
			if (a.getType() === b.getType()) {
				return a.getId() - b.getId();
			}
			else {
				return a.getType() - b.getType();
			}
        },
        removeAddButton: function() {
            var self = this;
            var eleLevel = this.$container.find('ul[class*="level"]');
            if ($('.level1').find('>.app-sitemap-page-item').length) {
                $('.level1').find('>.last').toggleClass('is-hide', true);
            }
            if (eleLevel.length) {
                eleLevel.each(function(i, element) {
                    var max = 0;
                    var type = parseInt($(this).closest('.app-sitemap-page-item').attr('data-type'));
                    var level = parseInt($(this).attr('class').replace('level', ''));
                    var childLength = typeof app.SiteMap.ChildTypes[type] != 'undefined' ? app.SiteMap.ChildTypes[type].length : 0;
                    switch (level) {
                        case 2:
                            max = app.SiteMap.MaxOriginalLarge + childLength;
                            break;
                        case 3:
                            max = app.SiteMap.MaxOriginalSmall + childLength;
                            break;
                        case 4:
                            return;
                        default:
                            break;
                    }
                    var length = $(element).find('>.app-sitemap-page-item').length;
                    $(element).find('>.app-sitemap-page-item').removeClass('not-border-left');
                    if (length != 0 && length == max) {
                        $(element).data('can-display-add-btn', true);
                        $(element).find('>.last').toggleClass('is-hide', true);
                        $(element).find('>.app-sitemap-page-item').last().addClass('not-border-left');
                    }
                });
            } else {
                self.$container.find('>.last').toggleClass('is-hide', false);
            }
        }
	};
	app.SiteMap.FixedArea = app.inherits(app.SiteMap.Area, function () {
		app.SiteMap.Area.apply(this, arguments);
	}, {
		getChildren: function () {
			var children = [];
			this.$container.children(':not(.last, .drop-zone-inside)').each(function () {
				if ($(this).attr('data-type') === 'estate_top') {
					children.push( app.SiteMap.getInstance().estatePages.top );
				}else if($(this).attr('data-type') === 'estate_rent') {
					children.push( app.SiteMap.getInstance().estatePages.chintai_top );
				}else if($(this).attr('data-type') === 'estate_purchase') {
					children.push( app.SiteMap.getInstance().estatePages.baibai_top );
				}else {
					children.push( app.SiteMap.getInstance().getPage($(this).attr('data-id')) );
				}
			});
			return children;
		},
		addChild: function (child) {
			if (child.parent) {
				child.parent.removeChild(child);
			}
			
			if (!this.$container.find('.app-sitemap-page-item[data-id="'+child.getId()+'"][data-type="'+child.getType()+'"]').length) {
				if (this.$add.length) {
					this.$add.before(child.$el);
				}
				else {
					this.$container.append(child.$el);
				}
			}
			return this;
		},
		sortChildren: function () {
			app.SiteMap.Area.prototype.sortChildren.call(this);
			// 物件ページ用処理
			this.$container.find('.clear').removeClass('clear');
			this.$container.find('[data-type="estate_top"]').prev().addClass('clear');
			return this;
		},
		_sortChildrenMethod: function (a, b) {
			// 物件ページ用処理
			// 物件ページのタイプを固定メニューでは扱わないタイプID30としてソートする
			if (a instanceof app.SiteMap.EstatePage) {
				return 30 - b.getType();
			}
			else if (b instanceof app.SiteMap.EstatePage) {
				return a.getType() - 30;
			}
			
			// type,id順
			if (a.getType() === b.getType()) {
				return a.getId() - b.getId();
			}
			else {
				return a.getType() - b.getType();
			}
        },
        changePositionRealEstate: function() {
            this.$container.find('.app-sitemap-page-item[data-type="17"]').after(this.$container.find('.app-sitemap-page-item[data-type="100"]'));
        }
		
	});
	app.SiteMap.MenuArea = app.inherits(app.SiteMap.Area, function () {
			app.SiteMap.Area.apply(this, arguments);
		},
		{
			getId: function () {
				return 0;
			},
			
			inMenu: function () {
				return true;
			},
			
            getGlobalNumber: function () {
                return app.SiteMap.GlobalMenuNumber;
            },
            
			updateGlobal: function () {
                if (!app.SiteMap.isSiteMapArticle) {
                    var _filterNumber = app.SiteMap.MenuArea.prototype.getGlobalNumber.call(this);
                    this.$container.children().removeClass('global').filter(':lt('+_filterNumber+')').addClass('global');
                }
				return this;
			},
			
			newSortNumber: function (sort) {
				var _sortNumber = 0;
                if (typeof sort !== "undefined") {
                    _sortNumber = sort + 1;
                    return _sortNumber;
                }
                console.log(this.getChildren());
				$.each(this.getChildren(), function (i, page) {
					if (page.data.sort >= _sortNumber) {
						_sortNumber = page.data.sort + 1;
					}
				});
				
				return _sortNumber;
			},
			
			sortChildren: function () {
				app.SiteMap.Area.prototype.sortChildren.call(this);
				this.updateGlobal();
			},
			
			_sortChildrenMethod: function (a, b) {
				return a.data.sort - b.data.sort;
			}

		});
	
	app.SiteMap.Page = function (data) {
		this.setData(data || {});

		this.parent = null;
		this.children = [];
		var remove_title = '階層外へ移動させる';
        var icon = 'move-layer';
        if (this.isLink()) {
            remove_title = 'メニューから削除';
            icon = 'remove';
        }
        var btnRemove = '<li><a class="app-sitemap-page-item-remove" href="javascript:;"><i class="i-e-'+ icon +'"></i>' + remove_title + '</a></li>';
        if (app.SiteMap.isSiteMapArticle) {
            btnRemove = '';
        }
		
		this.$el = $('<li class="app-sitemap-page-item" data-id="'+this.getId()+'" data-type="'+this.getType()+'">' +
						'<div class="detail is-hide"></div>' +
						'<div class="item">' +
							'<div class="label">' +
								'<span class="status"></span>' +
								'<span class="type"></span>' +
							'</div>' +
							'<span class="page-name"></span>' +
							'<div class="action">' +
								'<a class="app-sitemap-page-item-edit" href="javascript:;"><i class="i-e-edit">編集</i></a>' +
								'<div class="pull">' +
									'<a href="javascript:;"><i class="i-e-set">操作</i></a>' +
									'<ul>' +
										'<li><a class="app-sitemap-page-item-add-page" href="javascript:;"><i class="i-e-add"></i>この下にページを追加</a></li>' +
										'<li><a class="app-sitemap-page-item-drag-drop" href="javascript:;"><i class="i-e-drag-drop"></i>順番を並べ替える</a></li>' +
										btnRemove +
									'</ul>' +
								'</div>' +
							'</div>' +
						'</div>' +
						'<ul>' +
							'<li class="last is-hide"><div class="item add"><a href="javascript:;">追加</a></div></li>' +
						'</ul>' +
					'</li>');
		
		this.$item = this.$el.find('> .item');
		this.$childContainer = this.$el.find('> ul');
		this.$addBtn = this.$childContainer.find('.last');
		
	};
	
	app.SiteMap.Page.prototype = {
		getId: function () {
			return this.data.id;
		},
		
		getLinkId: function () {
			return this.data.link_id;
		},
		
		setData: function (data) {
			this.data = this.prepareData(data);
			return this;
		},
		
		updateData: function (data) {
			$.extend(this.data, this.prepareData(data));
			return this;
		},
		
		prepareData: function (data) {
			return data;
		},
		
		remove: function () {
			if (this.parent) {
				this.parent.removeChild(this);
			}
			else {
				this.$el.remove();
			}
			return this;
		},
		
		addChild: function (child, above) {
			if (!child.parent || child.parent !== this) {
				
				if (child.parent) {
					child.parent.removeChild(child);
				}
				this.children.push(child);
				child.parent = this;
                if (typeof above !== "undefined") {
                    this.$childContainer.find('.app-sitemap-page-item[data-id="'+above+'"]').after(child.$el);
                } else {
					this.$childContainer.find('> .last').before(child.$el);
				}
			}
			
			return this;
		},
		
		removeChild: function (child) {
			var idx = app.arrayIndexOf(child, this.children);
			if (idx >= 0) {
				this.children.splice(idx, 1);
				child.$el.remove();
				child.parent = null;
			}
			return this;
		},
		
		getChildren: function () {
			return this.children;
		},
		
		newSortNumber: function () {
			var _sortNumber = 0;
			$.each(this.getChildren(), function (i, page) {
				if (page.data.sort >= _sortNumber) {
					_sortNumber = page.data.sort + 1;
				}
			});
			
			return _sortNumber;
		},
		
		getType: function () {
			return this.data.page_type_code;
		},
		
		getParentType: function () {
			return this.getType() - 1;
		},
		getTypeNameLabel: function(){
			if(this.isTopOriginal()){
				var data = this.data;

				var type = this.getType();
				if(type == app.SiteMap.Types.TYPE_INFO_INDEX || type == app.SiteMap.Types.TYPE_INFO_DETAIL){
                    if(data.hasOwnProperty('text') && data.text){
                        return data.text;
					}
				}
			}
            return this.getTypeName();
		},
		isTopOriginal: function() {
			return app.SiteMap.IsTopOriginal;
		},
		isAgency: function() {
			return app.SiteMap.IsAgency;
		},
		getTypeName: function () {
			return app.SiteMap.getTypeName( this.getType() );
		},
		
		getChildTypes: function () {
			var level = this.getLevel();
			var childTypes = [];
			$.each(app.SiteMap.ChildTypes[ this.getType() ] || [], function (i, type) {
				if (app.SiteMap.hasDetailPageType(type) && level >= app.SiteMap.MaxLevel - 1) {
					return;
				}
				else if (level >= app.SiteMap.MaxLevel) {
					return;
				}
				childTypes.push(type);
			});
			return childTypes;
		},
        
        getParentId: function() {
            return this.data.parent_page_id;
        },
		
		getParent: function () {
			if (this.parent) {
				return this.parent;
			}
			if (this.data.parent_page_id && app.SiteMap.getInstance().getPage(this.data.parent_page_id)) {
				return app.SiteMap.getInstance().getPage(this.data.parent_page_id);
			}
			return null;
		},
		
		canAddChild: function () {
            return !!this.getChildTypes().length && !this.isTopType() && !this.isMultiIndex() && this.inMenu() ||
            this.isArticleTop(true) || this.isArticleOriginal() ;
		},
		
		canAlias: function () {
			return !this.isLink() && !this.isNew() && !this.isEstateForm();
		},
		
		getTitle: function () {
			if (this.getType() === app.SiteMap.Types.TYPE_ALIAS) {
				var linkPage = app.SiteMap.getInstance().getPageByLinkId(this.data.link_page_id);
				if (linkPage) {
					return linkPage.getTitle();
				}
				linkPage = app.SiteMap.getInstance().getIndexPageByLinkId(this.data.link_page_id);
				if (linkPage) {
					return linkPage.getTitleSearch();
				}
			}
			else if (this.getType() === app.SiteMap.Types.TYPE_ESTATE_ALIAS) {
				var linkPage = app.SiteMap.getInstance().getPageByEstateLinkId(this.data.link_estate_page_id);
				if (linkPage) {
					return linkPage.getTitle();
				}
			}
			else if (this.isMultiDetail()) {
				return this.getTypeName() + '（' + this.data.count + '件）';
			}
			return this.data.title || '';
		},

		getTitleSearch: function () {
            if (this.getType() === app.SiteMap.Types.TYPE_ALIAS) {
                var linkPage = app.SiteMap.getInstance().getPageByLinkId(this.data.link_page_id);
                if (linkPage) {
                    return linkPage.getTitle();
                }
            }
            else if (this.getType() === app.SiteMap.Types.TYPE_ESTATE_ALIAS) {
                var linkPage = app.SiteMap.getInstance().getPageByEstateLinkId(this.data.link_estate_page_id);
                if (linkPage) {
                    return linkPage.getTitle();
                }
            }
            return this.data.title || '';
		},
		
		getTitleWithFilename: function () {
			var filename = this.getFilename();
			if (filename) {
				filename = '（'+filename+'）';
			}
			return this.getTitle() + filename;
		},

		getTitleWithFilenameSearch: function () {
            var filename = this.getFilename();
            if (filename) {
                filename = '（'+filename+'）';
            }
            return this.getTitleSearch() + filename;
		},
		
		getFilename: function () {
			return this.isNew() || !this.data.filename ? '' : this.data.filename;
		},
		
		isTopType: function () {
			return this.getType() === app.SiteMap.Types.TYPE_TOP;
		},
		
		isFixedType: function () {
			return app.SiteMap.isFixedType(this.getType());
		},
        
        isArticleTop: function(isSiteMap) {
            return app.SiteMap.isSiteMapArticle == isSiteMap && this.data.page_type_code == app.SiteMap.Types.TYPE_USEFUL_REAL_ESTATE_INFORMATION;
        },

        isRealEstateSiteMap: function() {
            return $.inArray(this.data.page_category_code, app.SiteMap.AllCategoryArticle) > -1 && !app.SiteMap.isSiteMapArticle ;
        },
        isArticlePage: function() {
            return $.inArray(this.data.page_type_code, app.SiteMap.allTypeArticlePage) > -1 && !app.SiteMap.isSiteMapArticle ;
        },
		
		isLink: function () {
			return app.SiteMap.getCategoryFromType(this.getType()) === app.SiteMap.Categories.CATEGORY_LINK;
		},
		
		isEstateForm: function () {
			var type = this.getType();
			return (
                type == app.SiteMap.Types.TYPE_FORM_LIVINGLEASE ||
                type == app.SiteMap.Types.TYPE_FORM_OFFICELEASE ||
                type == app.SiteMap.Types.TYPE_FORM_LIVINGBUY ||
                type == app.SiteMap.Types.TYPE_FORM_OFFICEBUY
			);
		},
		
		hasDetailPageType: function () {
			return app.SiteMap.hasDetailPageType(this.getType());
		},
		
		isDetailPageType: function () {
			return app.SiteMap.isDetailPageType(this.getType());
		},
		
		/**
		 * 複数ページかどうか（ブログ等）
		 */
		isMultiIndex: function () {
			return app.SiteMap.hasMultiPageType(this.getType());
		},
		
		isMultiDetail: function () {
			var parent = this.getParent();
			return !!parent && parent.isMultiIndex();
		},
		
		isNew: function () {
			return this.data.new_flg;
		},
		
		/**
		 * @todo 
		 */
		isDraft: function () {
			return !this.data.public_flg;
		},
		
		isPublic: function () {
			return this.data.public_flg;
		},
		
		getItemClass: function () {
			var cls = [];

			if (this.isLink()) {
				cls.push('is-link');
				if (this.isDraft()) {
					cls.push('is-draft');
				}
				return cls.join(' ');
			}

			if (this.isNew()) {
				cls.push('is-empty');
			}
			if (this.isDraft()) {
				cls.push('is-draft');
			}
			
			if (this.isMultiDetail()) {
				cls.push('is-multi-detail');
			}
			
			return cls.join(' ');
		},
		
		inMenu: function () {
			return !isNaN(parseInt(this.data.parent_page_id));
		},
		
        isGlobal: function(pageId) {
            var globalNav = false;
            $.each(app.SiteMap.globalNav, function(index, value) {
                if (value["id"] == pageId) {
                    globalNav = true;
                }
            });
            return globalNav;
        },
		
		hasEditMenu: function () {
			if (this.isTopOriginal() && !this.isAgency()) {
				return !this.isTopType() && this.inMenu() && !this.isMultiDetail() && !this.isGlobal(this.getId());
			}

			// return !this.isTopType() && this.inMenu() && !this.isMultiDetail();
			return this.inMenu() && !this.isMultiDetail();
		},
		
		getLevel: function () {
			var parent = this.getParent();
			if (parent) {
				return parent.getLevel() + 1;
			}
			else {
				return 1;
			}
        },
        
        isArticleOriginal: function() {
            var articleOriginal = [
                app.SiteMap.Types.TYPE_LARGE_ORIGINAL,
                app.SiteMap.Types.TYPE_SMALL_ORIGINAL,
            ];

            return $.inArray(this.getType(), articleOriginal) > -1;
        },
        isCategoryArticleOrignal: function() {
            var articleOriginal = [
                app.SiteMap.Types.TYPE_LARGE_ORIGINAL,
                app.SiteMap.Types.TYPE_SMALL_ORIGINAL,
                app.SiteMap.Types.TYPE_ARTICLE_ORIGINAL,
            ];

            return $.inArray(this.getType(), articleOriginal) > -1;
        },
		
		render: function (recursive) {
			// 会員専用ページ、公開中のリンクはメニューから削除付加
			if (
				(this.getType() === app.SiteMap.Types.TYPE_MEMBERONLY) ||
				// (this.isLink() && this.isPublic()) ||
				(this.getType() === app.SiteMap.Types.TYPE_TOP)
			) {
				this.$el.find('> .item .app-sitemap-page-item-remove').parent().remove();
			}
			
			// ページ情報
			this.$el.attr({'data-id': this.getId(), 'data-type': this.getType()});
			// 詳細まとめ一覧の場合、詳細を追加
			if (this.isMultiIndex()) {
				if (!this.children.length) {
					this.addChild((new app.SiteMap.Page(this.data.detail)).render());
				}
			}
			// 詳細まとめ表示
			this.$item.prev().toggleClass('is-hide', !this.isMultiDetail());
			// 状態
			this.$item.attr('class', 'item ' + this.getItemClass());
			// TOP個別クラス
			this.$item.toggleClass('home', this.isTopType());
			// ページタイプ名
			this.$item.find('.type').text(this.getTypeNameLabel());
			// ページ名
			this.$item.find('.page-name').text(this.getTitle());
			// 一覧編集ボタン
			this.$item.find('.action > a i')
				.attr('class', this.isMultiIndex() ? 'i-e-list' : 'i-e-edit')
				.toggleClass('is-hide', this.isMultiDetail());
			// 設定ボタン表示
			this.$item.find('.action .pull').toggleClass('is-hide', !this.hasEditMenu());
			// 階層クラス
			this.$childContainer.attr('class', 'level' + (this.getLevel() + 1));
            // 追加ボタン
			this.$addBtn.toggleClass('is-hide', !this.canAddChild());
			this.$addBtn.siblings().removeClass('last');
			if (!this.canAddChild()) {
				this.$addBtn.prev().addClass('last');
            }

            if (this.isArticleTop(true)) {
                this.$el.addClass('not-border-left not-border-bottom');
            }
			
			if (recursive) {
				$.each(this.children, function (i, child) {
					child.render(true);
				});
			}
			
			return this;
        },
        issetArticleTop: function() {
            return !(this.data.id == 0 && this.data.page_type_code == app.SiteMap.Types.TYPE_USEFUL_REAL_ESTATE_INFORMATION);
        }
	};
	
	
	app.SiteMap.EstatePage = app.inherits(app.SiteMap.Page, function () {
		app.SiteMap.Page.apply(this, arguments);
	},
	{
		getType: function () {
			return this.data.estate_page_type;
		},
		isEstateTop: function () {
			return this.data.estate_page_type === 'estate_top';
		},
		isEstateRent: function () {
			return this.data.estate_page_type === 'estate_rent';
		},
		isEstatePurchase: function () {
			return this.data.estate_page_type === 'estate_purchase';
		},
		/**
		 * 物件種目ページかどうか
		 */
		isEstateType: function () {
			return this.data.estate_page_type === 'estate_type';
		},
		isEstateSpecial: function () {
			return this.data.estate_page_type === 'estate_special';
		},
		isPublic: function () {
			return this.data.public_flg;
		},
		isDraft: function () {
			return !this.isPublic();
		},
		getItemClass: function () {
			var cls = [];
			if (this.isDraft()) {
				cls.push('is-draft');
			}
			return cls.join(' ');
		},
		getTypeName: function () {
			if (this.data.estate_page_type === 'estate_special') {
				return '特集';
			}
			else {
				return this.getTitle();
			}
		},
		getLevel: function () {
			if (this.isEstateType()) {
				return 2;
			}
			else {
				return 1;
			}
		},
		getEditPageUrl: function () {
			if (this.isEstateTop() || this.isEstateRent() || this.isEstatePurchase()) {
				return '/estate-search-setting';
			}
			else if (this.isEstateType()) {
				return '/estate-search-setting/detail?class='+this.data.estate_class;
			}
			else if (this.isEstateSpecial()) {
				return '/estate-special/detail?id='+this.data.id;
			}
			return false;
		},
		render: function (recursive) {
			// 物件検索用クラス
			this.$el.addClass('app-sitemap-estate-page-item');
			// メニューから削除付加
			this.$el.find('> .item .app-sitemap-page-item-remove').parent().remove();
			// 物件検索トップ用クラス
			this.$el.toggleClass('article-search', this.isEstateTop());
			
			// ページ情報
			//this.$el.attr({'data-id': this.getId(), 'data-type': this.getType()});
			// 状態
			this.$item.attr('class', 'item ' + this.getItemClass());
			// ページタイプ名
			this.$item.find('.type').text(this.getTypeName());
			// ページ名
			this.$item.find('.page-name').text(this.getTitle());
			// 一覧編集ボタン
			this.$item.find('.action > a i')
				.attr('class', 'i-e-edit');
			// 設定ボタン表示
			this.$item.find('.action .pull').toggleClass('is-hide', true);
			// 階層クラス
			this.$childContainer.attr('class', 'level' + (this.getLevel() + 1));
			// 追加ボタン
			this.$addBtn.toggleClass('is-hide', true);
			this.$addBtn.siblings().removeClass('last');
			this.$addBtn.prev().addClass('last');
			
			if (recursive) {
				$.each(this.children, function (i, child) {
					child.render(true);
				});
			}
			
			return this;
		}
    });
    
    function setHeightlabelCategory() {
        var editSiteMap = $('.edit-sitemap').eq(0);
        var height = editSiteMap.outerHeight() - 95;
        var alertHeight = editSiteMap.find('.alert-delete-article').innerHeight() + 10;
        height = height - alertHeight;
        editSiteMap.find('.category-label').css({'top': 95 + alertHeight, 'height': height});
    }

    function setTypeNameLabel() {
        var largeIndex = 1;
        var types = Array.prototype.slice.call(document.querySelectorAll('[data-type="' + app.SiteMap.Types.TYPE_LARGE_ORIGINAL + '"] .label .type'));
        types.forEach(function(elem) {
            if(elem.closest('.level3') === null) {
                elem.innerText = app.SiteMap.getTypeName(app.SiteMap.Types.TYPE_LARGE_ORIGINAL) + largeIndex++;
            }
        });
    }

})(app);
