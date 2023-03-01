(function () {

	function command( name ) {
		this.name = name;
		this.context = 'a';
		
		this.allowedContent = {
			'a': {
				attributes: [ 'href', 'target' ]
			}
		};
		
		this.unlinkStyle = new CKEDITOR.style({
			element: 'a',
			type: CKEDITOR.STYLE_INLINE,
			alwaysRemoveElement: 1
		});
	}
	
	command.prototype.exec = function (editor) {
		editor.focus();
		var selection = editor.getSelection();
		var range = selection.getRanges()[ 0 ];

		if (range.startOffset === range.endOffset) {
			alert('文字が選択されていません。');
			return false;
		}

		editor.fire( 'saveSnapshot' );
		
		this._toggleStyle(editor);
	};
	
	command.prototype.refresh = function (editor, path) {
		this.setState( this.unlinkStyle.checkActive( path, editor ) ? CKEDITOR.TRISTATE_ON : CKEDITOR.TRISTATE_OFF );
	};
	
	command.prototype._toggleStyle = function (editor) {
		if (this.unlinkStyle.checkActive( editor.elementPath(), editor )) {
			editor.removeStyle(this.unlinkStyle);
			
			setTimeout( function() {
				editor.fire( 'saveSnapshot' );
			}, 0 );
			
			return;
		}

		//選択範囲の解除
		app.page.linkModal.caretBack = function (editor) {
			editor.focus();
			var selection = editor.getSelection();
			var range = selection.getRanges()[ 0 ];

			range.endContainer = range.startContainer;
			range.endOffset = range.startOffset;
			range.select();
		}
		
		app.page.linkModal.onClose = function (ret, modal) {
			// app.page.linkModal.onClose = null;
			modal.$el.find('dd .errors, dd .error').html('');
			modal.$el.find('.search-btn .errors').html('');
			if (!ret) {
				app.page.linkModal.caretBack(editor);
				return;
			}
			
            var $checked = modal.$el.find('input[type="radio"]:checked').eq(0);
			if (!$checked) {
				app.page.linkModal.caretBack(editor);
				return;
			}

            var $urlElem = $checked.closest('dl').find('dd select,dd input:text:not(.input-house-no),dd input:hidden:not(.input-house-no)');
			if (!$urlElem.length) {
				$urlElem = $checked.closest('.search-btn').find('.select-page select');
			}
			var val = $urlElem.val();

			if (!val) {
				switch ($urlElem.attr('name')) {
					case 'elements[0][link_page_id]':
						$checked.closest('.search-btn').find('.errors').html('ページを選択してください。');
						break;
					case 'elements[0][link_url]':
						$checked.closest('dl').find('dd .errors').html('URLを入力してください。');
						break;
					case 'elements[0][file2]':
						$checked.closest('dl').find('dd .errors').html('ファイルを追加してください。');
						break;
					default:
						$checked.closest('dl').find('dd .errors').html('物件を選択してください。');
						break;
				}
				return false;
			}


            var url = null ;
			var title = null;
			if ($urlElem.attr('name') === 'elements[0][link_page_id]') {
				url = '###link_page_id:' + val + '###';
				title = $urlElem.closest('.search-btn').find('.page-name').text().replace('選択中ページ：', '');
			}
			if ($urlElem.attr('name') === 'elements[0][file2]' ) {
				val = $('#modal #elements-0-file2').val()	;
				url = '###link_file_id:' + val + '###';
				title = $('#modal .select-file2 .select-file2-title').text().replace('選択中ファイル：', '');
			}
			if ($urlElem.attr('name') === 'elements[0][link_house]' ) {
                url = $('#modal #elements-0-link_house').val();
                var linkHosue = {
                    'url': url,
                    'house_type': $('#modal #elements-0-link_house_type').val().split(',')
                } 
                url = '###link_house_url:' +JSON.stringify(linkHosue)+'###';
                title = $('#modal .house-title label').text();
            }
			if ( url === null ) {
				//urlチェック
				if (val.length > 2000) {
					$checked.closest('dl').find('dd .errors').html('2000 文字以内で入力してください。');
				}
				if (!$.checkUrlFormat(val)) {
					var oldMessage = $checked.closest('dl').find('dd .errors').html();
					$checked.closest('dl').find('dd .errors').html((oldMessage.length ? oldMessage + '<br>' : '') + 'URL形式で入力してください。');
				}
				if($checked.closest('dl').find('dd .errors').html()) {
					return false;
				}
                url = val;
                title = val;
			}
			
			var isBlank = modal.$el.find('input[type="checkbox"]').prop('checked');
			
			editor.focus();
			var plugin = CKEDITOR.plugins.link;
			var selection = editor.getSelection();
			
			var range = selection.getRanges()[ 0 ];

			// Use link URL as text with a collapsed cursor.
			if ( range.collapsed ) {
				// Short mailto link text view (#5736).
				var text = new CKEDITOR.dom.text( data.type == 'email' ?
					data.email.address : attributes.set[ 'data-cke-saved-href' ], editor.document );
				range.insertNode( text );
				range.selectNodeContents( text );
			}

			// Apply style.
			if (title === null) {
				var attrs = {
					href: url,
					title: url
				};
			} else {
				var attrs = {
					href: url,
					title: title
				};
			}
			if (isBlank) {
				attrs.target = '_blank';
			}
			var style = new CKEDITOR.style({
				element: 'a',
				attributes: attrs
			});
			
			style.type = CKEDITOR.STYLE_INLINE; // need to override... dunno why.
			style.applyToRange( range, editor );
			// range.select();

			app.page.linkModal.caretBack(editor);

			setTimeout( function() {
				editor.fire( 'saveSnapshot' );
			}, 0 );
			
		};
		app.page.linkModal.show();
		app.page.linkModal.$el.find('input').first().focus();
	};

	var name = 'custom_link';
	
	CKEDITOR.plugins.add(name, {
		lang: 'ja',
		icons: 'custom_link',
		hidpi: true,
		init: function( editor ) {
			var config = editor.config,
			lang = editor.lang.custom_color;
			
			
			editor.addCommand(name, new command(name));
			editor.ui.addButton(name, {
				label: 'リンク',
				command: name,
				toolbar: name + ',10'
			});
			
			var $tmp = $('<div/>');
			editor.on('paste', function (e) {
				var rep = false;
				if (e.data && e.data.dataValue) {
					$tmp.html(e.data.dataValue);
					$tmp.find('a').each(function () {
						var $this = $(this);
						var matched = $this.attr('href').match(/###link_page_id:\d+###/);
						if (matched) {
							rep = true;
							$this.attr('href', matched[0]);
						}
					});
				}
				
				if (rep) {
					e.data.dataValue = $tmp.html();
				}
			});
		},
		afterInit: function (editor) {
			// remove title
			editor.dataProcessor.htmlFilter.addRules({
				elements: {
					a: function (element) {
						if (element.attributes.title) {
							delete element.attributes.title;
						}
						return null;
					}
				}
			});
			
			//set title
			editor.dataProcessor.dataFilter.addRules({
				elements: {
					a: function (element) {
						if (element.attributes.href.indexOf('###link_page_id:') > -1) {
                            var id = element.attributes.href.replaceAll('###', '').replace('link_page_id:', '');
                            var title = $('option[value="'+id+'"]').eq(0).text();
							element.attributes.title = title;
						} else if (!element.attributes.title && element.attributes.href) {
							element.attributes.title = element.attributes.href;
						}
						return null;
					}
				}
			});
		}
	});

})();
