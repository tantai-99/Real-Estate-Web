var uploader = {};
$(function(){
    uploader = function(token, kwargs, reload) {
        this.token = token;
        this.kwargs = kwargs;
        this.modal = null;
        this.reload = reload;
        
        this.progress = {
            isLoading: false,
            isSkip: false,
            percent: 0,
            update: function (percent) {
                this.percent = percent;
                if (this.progressDone && this.progressCurrent) {
                    this.progressCurrent.text(percent);
                    this.progressDone.css('width', percent + '%');
                }
            },
            stop: function(){
                this.percent = 0;
                this.isLoading = false;
            },
            skip: function() {
                this.stop();
                this.isSkip = true;
            }
        };
        
        var _self = this;
        
        this.closeAll = function(){
            if (this.modal)
                this.modal.close();
            
            this.progress.stop();
        },
        this.setModal = function($modal, override) {
            if (!override && this.modal) this.modal.close();
            
            this.modal = $modal;
            $modal.show();
        },
        this.finishProgress = function(res){
            var res = res.data;
            if (res && 'Ok' === res.message) {
				// console.log(res);

				var modal = null;
                var kwargs = _self.kwargs;
                kwargs['isSuccess'] = 1;
				
				if (res.errors.length) {
					var $contents = $('<div class="modal-message"></div>');
					var errors = res.errors;
					Object.keys(errors).forEach(function(key) {
						var error = errors[key];
						Object.keys(error).forEach(function(key) {
							var message = error[key];
							$contents.append('<p style="color:red;">' + message + '</p>');
						});
					});
					
					modal = app.modal.popup({
						title: 'ファイルを制作代行CMSへアップロードしております',
						contents: $contents,
                        modalContentsClass: 'modal-upload',
						onClose: function(ret){
							if (_self.reload) {
								location.reload();
							}
						},
						closeButton:false,
						cancel: false
					});
				} else {
					modal = app.modal.message({
						title: '',
						message: 'アップロードが完了しました。',
                        modalContentsClass: 'modal-progress',
						onClose: function(ret){
							if (_self.reload) {
								location.reload();
							}
						},
						closeButton:false,
						cancel: false
					});
				}
				
                app.api('/admin/company/api-synchronize-upload-progress', kwargs, function(data){
                    // console.log(data);
					_self.progress.update(100);
					if (null !== modal) _self.setModal(modal);
                });
            }
        },
        this.loadedProgress = function(){
            
            _self.progress.stop();
        },
        this.updateProgress = function(request, e) {
            _progress = _self.progress;
            
            if (!_progress.isLoading && !_progress.isSkip) {
                var $contents = $('<div class="progress">' +
                '<div class="modal-message">' +
                '<strong>ファイルを制作代行CMSへアップロードしております</strong>' +
                '</div>' +
                '<div class="progress-container">' +
                '<div class="progress-done"></div>' +
                '</div>' +
                '<div class="progress-count-wrap">' +
                '<span class="progress-current">0</span>%' +
                '</div>' +
                '</div>' + 
                '<div class="btn-pgr"><button class="btn-t-gray cancel">キャンセル</button></div>' +
                '<div class="btn-pgr"><div class="button-skip"></div></div>');
            
                var modal = app.modal.popup({
                    title: '',
                    contents: $contents,
                    modalContentsClass: 'modal-progress',
                    closeButton: false,
                    ok: false,
                    cancel: false,
                });
                _self.setModal(modal);
                
                var $btnCancel = $contents.find('.cancel');
                var $btnSkip = $contents.find('.skip');
                var _request = request;
                var _e = e;
                $btnCancel.on('click', function(e){
                    // console.log(_e);
                    e.preventDefault();
                    
                    _request.abort();
                    _progress.stop();
                    _self.getForm();
                    var modal = app.modal.message({
                        title: '',
                        message: 'アップロードが完了しました。',
                        onClose: function(ret){
                            console.log('stoped.');  
                        },
                        closeButton:false,
                        cancel: false
                    });
                    _self.setModal(modal);
                });
                $btnSkip.on('click', function(){
                    e.preventDefault();
                    
                    _progress.skip();
                    
                    var modal = app.modal.message({
                            title: '',
                            message: 'アップロードが完了しました。',
                            onClose: function(ret){
                                console.log('skip.');  
                            },
                            closeButton:false,
                            cancel: false
                        });
                    _self.setModal(modal);
                });
                
                _progress.isLoading = true;
                _progress.progressDone = $contents.find('.progress-done');
                _progress.progressCurrent = $contents.find('.progress-current');
            }
            
            if (_progress.isLoading) {
                var currentPercent = String(Math.round(e.loaded/e.total*100)-1);
                _progress.update(currentPercent);
            }
        },
        this.getForm = function(options){
            var token = _self.token;
            var kwargs = _self.kwargs;
            var head = '編集ファイル';
            var subHead = '直下のHTMLのみアップ可能';
            var key = null;
            if(options && options.hasOwnProperty('key')){
              key = options.key;
            }
            if(key){
              head = key;
              subHead = null;
            }
            var text = '<div><b>'+ head +'</b></div>';
            if(subHead){
              text += '<div>' + subHead + '</div>';
            }
          var closeButton = '<div class="close-button-modal-square"><a class="modal-close-button"><i class="i-e-delete"></i></a></div>';
            var $contents = $('<div class="section file-upload">' +
            '<form enctype="multipart/form-data" data-api-action="'+kwargs['api_action']+'">' +
            '<input type="hidden" name="company_id" value="'+kwargs['company_id']+'">' +
            '<input type="hidden" name="sub_dir" value="'+kwargs['sub_dir']+'">' +
            '<input type="hidden" name="_token" value="'+token+'">' +
            '<div class="f-up-table">' +
            '<div class="left"><div class="images"><div><img src="/images/icon/folder.png" /></div><div><img src="/images/icon/arrow_left.png" /></div></div><div class="f-up-table-head text">' + text + '</div></div>' +
            '<div class="right up-area"><div class="text">ファイルをドラッグ＆ドロップしてください</div><div class="f-file-up"><div class="up-btn btn-t-blue"><input id="upload_files" type="file" name="file[]" multiple /></div></div></div>' +
            '</div>' +
            '<div class="errors"></div>' +
            '<div class="modal-btns is-hide">' +
                '<a href="javascript:;" class="btn-t-blue save-btn save">登録</a>' +
            '</div>' +
            '</form>' +
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

            var show = modal.show;
            modal.show = function () {
                modal.$el.find( '.errors' ).empty() ;

                show.call( modal ) ;
            };
            
            $form = $contents.find('form');
            var $trigger = $contents.find('.save');
        
            $form.on('pre-submit',  function(){
                $form.attr({
                    // 'target': $iframe.attr('name'),
                    'method': 'POST',
                    'action': $form.attr('data-api-action'),
                    'is-upload': 1,
                });
            });
            
            app.initApiProcessForm($form, $trigger, _self.updateProgress, _self.loadedProgress, _self.finishProgress);
            
            $contents.find('#upload_files').on('change', function () {
                $trigger.click();
            });

            // drag and drop
            //_self.dropZone(modal);
            _self.dropFile($contents);

            _self.setModal(modal);

        },
        this.dropFile = function($contents) {
            var $form = $contents.find('form');
            var $dropZone = $contents.find('.up-area');
            
            if(!$dropZone && !$dropZone[0]) return alert('Cannot find dropzone element');
            
            var dropZone = $dropZone[0];
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
                dropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });
            
            dropZone.addEventListener('drop', function(e){
                var droppedFiles = e.dataTransfer.files;
                if (droppedFiles) {
                    var ajaxData = new FormData($form.get(0));
                    
                    $.each( droppedFiles, function(i, file) {
                        ajaxData.append('file[]', file);
                    });
                    
                    var url = $form.attr('data-api-action');
                    
                    $.ajax({
                        url: url,
                        type: "POST",
                        dataType:"JSON",
                        data: ajaxData,
                        processData: false,
                        contentType: false,
                        cache:false,
                        xhr: function() {
                            var myXhr = $.ajaxSettings.xhr();
                                if(myXhr.upload){
                                    myXhr.upload.addEventListener('progress', function(e){
                                        _self.updateProgress(myXhr, e);
                                    }, false);
                                    myXhr.upload.addEventListener('load', function(e){
                                         _self.loadedProgress();
                                    }, false);
                                }
                                return myXhr;
                        },
                        success: function(data){
                            // console.log('here.')
                           _self.finishProgress(data);
                        },
                        error: function (myXhr, textStatus, thrownError) {
                            _self.finishProgress({
                                'message': 'Ok',
                                'errors': [],
                                // 'errors': [
                                    // {'thrownError': thrownError}
                                // ]
                            });
                        }
                    });
                }
            }, false);
        },
        this.dropZone = function(modal){
          var upArea = modal.$el.find('.up-area');
          if(!upArea && !upArea[0]) return alert('Cannot find dropzone element');
          var file_input = upArea.find('#upload_files')[0];
          var dropZone = upArea[0];
          function preventDefaults(e){
            e.preventDefault();
            e.stopPropagation();
          }
          ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
            dropZone.addEventListener(eventName, preventDefaults, false);
          });
          dropZone.addEventListener('drop', handleDrop, false);

          function handleDrop(e){
            var files = e.dataTransfer.files;
            if(!files) return false;
            // set files into input file
            file_input.files = files;
          }
          
        };
    };    
    
    
    var token = app.getToken();
    var contextMenuFn = {
        updateName: function () {
          let contextModel = app.modal.editFileName(token, {
            api_action: '/admin/company/api-save-file',
            name: $(this).data('file'),
            company_id: app.getParameter('company_id'),
            sub_dir: app.getParameter('sub_dir'),
          });
          contextModel.show();
        },
        updateContent: function () {
          let contextModel = app.modal.editFileContent(token, {
            api_action: '/admin/company/api-save-file',
            api_get_file: '/admin/company/api-get-file-content',
            name: $(this).data('file'),
            company_id: app.getParameter('company_id'),
            sub_dir: app.getParameter('sub_dir'),
          });
          contextModel.show();
        }
    };

    $('.btn-revert').on('click', function(e){
        e.preventDefault();

        let modal = app.modal.revertFileContent(token, {
          'api_action': '/admin/company/api-save-file',
          name: $(this).data('file'),
          company_id: app.getParameter('company_id'),
          sub_dir: app.getParameter('sub_dir'),
        });
    });

    $('.btn-remove').on('click', function(e){
        e.preventDefault();

        let modal = app.modal.removeFile(token, {
          'api_action': '/admin/company/api-save-file',
          name: $(this).data('file'),
          company_id: app.getParameter('company_id'),
          sub_dir: app.getParameter('sub_dir'),
        });
    });

    $('.btn-upload').on('click', function(e){
        e.preventDefault();
        var up = new uploader(
            token,
            { 'api_action': '/admin/company/api-upload-file',
                company_id: app.getParameter('company_id'),
                sub_dir: $(this).data('key') || app.getParameter('sub_dir')
            },
            !$(this).data('key')
        );

        var key = $(this).attr('data-dir-key');
        var options = {};
        if(key){
          options.key = key;
        }
        up.getForm(options);
    });

    var appContextMenu;
    var $ctxMenus = document.getElementsByClassName('has-context-menu');
    for (i=0, len=$ctxMenus.length; i<len; i++) {
        $ctxMenus[i].addEventListener('contextmenu', function (e) {
          appContextMenu = new app.contextMenu($('#table-list'), {left: e.pageX+10, top: e.pageY}, function(){console.log(111);})
          appContextMenu.addMenus([
            {name: 'ファイル名を編集する', hide: !$(this).data('edit-name'), fileName: $(this).data('file'), icon: 'i-m-edit', onClick: contextMenuFn.updateName},
            {name: 'ファイル名を編集する', hide: !$(this).data('edit-data'), fileName: $(this).data('file'), icon: 'i-m-edit', onClick: contextMenuFn.updateContent},
          ]);

          appContextMenu.show();
        }, false);
    }
    $('#main').css({'min-width': '1150px'});
    if ($("#contents").width() - $('#g-header').width() > 60) {
        $('#g-header').css({'width': $("#contents").width()-1});
    }
    var tab2 = $('#side');
    if (tab2.length) {
        $(window).scroll(function () {
          $('#g-header').css({'width': $("#contents").width()});
        });
    }
});