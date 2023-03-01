(function () {
	'use strict';
	
	$.fn.initUpload = function () {
		this.each(function (i, element) {
			var $this = $(this);
			
			var $fileId = $this.find('.upload-file-id');
			
			if ($fileId.hasClass('initUpload')) {
				return;
			}
			$fileId.addClass('initUpload');
			
			var $preview = $this.find('.up-preview');
			var $dropZone = $this.find('.up-area');
			var $errors = $this.find('.errors');
			var $deleteBtn = $this.find('.i-e-delete');
			var type = $fileId.attr('data-file-type');
			
			function clearError() {
				$errors.empty();
			}
			
			function error(message) {
				var $error = $('<p class="error"></p>');
				$error.text(message || 'アップロードに失敗しました。');
				$errors.append($error);
			}

			var fileInfoXhr;
			function setFileId(id) {
				if (id === '0') {
					id = '';
				}
				$preview.find(':not(.i-e-delete)').remove();
				$deleteBtn.toggleClass('is-hide', !id);
				$fileId.val(id || '').change();
				
				if (!id) {
					return;
				}
				
				if (type === 'file') {
					if (fileInfoXhr) {
						fileInfoXhr.abort();
					}
					fileInfoXhr = app.api('/api-upload/hp-file-info', {id: id}, function (data) {
						if (data.info) {
							$preview.prepend($('<p/>').text(data.info.filename));
						}
					});
				} else if ( type === 'file2' ) {
					if ( fileInfoXhr ) {
						fileInfoXhr.abort() ;
					}
					$preview.html( '<img src="/images/common/loading.gif" id="loadingGif" />' ) ;
					fileInfoXhr = app.api( '/api-upload/hp-file2-info', {id: id}, function ( data ) {
						$preview.find( '#loadingGif' ).remove() ;
						if ( data.info ) {
							$preview.prepend( $('<p/>').text( data.info.filename ) ) ;
						}
					});
				}
				else {
					var src = $fileId.attr('data-view') + '?id=' + id + '&_t=' + (new Date()).getTime();
					var $image = $('<img/>');
					$image.attr('src', src);
					$preview.prepend($image);
				}
			}
			
			$this.on('app-upload-reset', function () {
				setFileId('');
			});
			
			setFileId($fileId.val());
			
			$deleteBtn.on('click', function () {
				setFileId('');
			});
			
			if ('draggable' in $dropZone[0]) {
				$dropZone.removeClass('is-hide')
					.on('dragover', function () {
						$(this).addClass('is-dragover');
					})
					.on('dragleave drop', function () {
						$(this).removeClass('is-dragover');
					});
			}
			
			$this.find('.up-btn').fileupload({
				dropZone: !$dropZone.hasClass('is-hide') && $dropZone,
	            add: function (e, data) {
	            	if (data.fileInput && !data.fileInput.is('[type="file"]')) {
	            		return;
	            	}
	            	
	                if (e.isDefaultPrevented()) {
	                    return false;
	                }
	                if (data.autoUpload || (data.autoUpload !== false)) {
	                    data.process().done(function () {
	                        data.submit();
	                    });
	                }
	            },
				
				
				url: $fileId.attr('data-upload-to'),
				dataType: 'json',
				// formData: [],
				start: function (e) {
					clearError();
				},
				change: function (e) {
					clearError();
				},
				done: function (e, data) {
					if (!data.result.success) {
						error(data.result.error);
					}
					else if (data.result.data.errors) {
						$.each(data.result.data.errors, function (i, message) {

							//全体の容量が多い場合はポップアップで表示する
							if(i == "over_capacity") {
								app.modal.alert('', message.data_max);
							}else{
								error(message);
							}
						});
					}
					else {
						var id = data.result.data.id;
						setFileId(id);
					}
				},
				fail: function () {
					clearError();
					error();
				}
			});
		});
	};
	
	$.fn.initUploadHPImage = function (fn) {
		this.each(function () {
			var $this = $(this);
			var $form = $this.find('form');
			var $trigger = $this.find('.save');
			var $inputs = $this.find('input:not([type="file"])');
			var $title = $this.find('input[name="title"]').change();
			var $category = $this.find('select');
			
			$form.on('change keyup', 'input:not([type="file"])', function () {
				var notEmpty = true;
				$inputs.each(function () {
					notEmpty = !!$(this).val();
					return notEmpty;
				});
				
				$trigger.toggleClass('is-disable', !notEmpty);
			});
			$inputs.eq(0).trigger('keyup');
			
			app.initApiForm($form.eq(0), $trigger, function (data) {
				app.modal.alert('', '登録が完了しました。', function() {
					
					$title.val('').change();
					$category[0].selectedIndex = 0;
					
					$this.trigger('app-upload-reset');
					
					fn && fn(data);
				});
			});
			
			$this.initUpload();
			
		});
	};

	$.fn.initUploadHPFile2 = function ( fn ) {
		this.each( function () {
			var $this		= $(this);
			var $form		= $this.find( 'form'						)			;
			var $trigger	= $this.find( '.save'						)			;
			var $inputs		= $this.find( 'input:not([type="file"])'	)			;
			var $title		= $this.find( 'input[name="title"]'			).change()	;
			var $category	= $this.find( 'select'						)			;
			
			$form.on( 'change keyup', 'input:not([type="file"] )', function () {
				var notEmpty = true ;
				$inputs.each( function () {
					notEmpty = !!$(this).val() ;
					return notEmpty ;
				});
				
				$trigger.toggleClass( 'is-disable', !notEmpty ) ;
			});
			$inputs.eq( 0 ).trigger( 'keyup' ) ;
			
			app.initApiForm( $form.eq( 0 ), $trigger, function ( data ) {
				app.modal.alert( '', '登録が完了しました。', function() {
					
					$title.val( '' ).change() ;
					$category[ 0 ].selectedIndex = 0 ;
					
					$this.trigger( 'app-upload-reset' ) ;
					
					fn && fn( data ) ;
				});
			});
			
			$this.initUpload() ;
		});
	};
})();
