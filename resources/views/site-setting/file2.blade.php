@extends('layouts.default')

@section('title', __('ファイル管理'))

@section('script')

<script type="text/javascript" src="/js/upload.js"></script>

<script type="text/javascript">
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
</script>
<script type="text/javascript">
$(function () {

	var _token = '<?php  echo csrf_token(false) ?>';

	var $hpFile2 = $('.hp-file2');
	var hpFile2 = app.hpFile2($hpFile2, true);

	var $categorySelect = $('#category_id');
	var categories = [];
	$categorySelect.find('option').each(function (i) {
		var $this = $(this);
		if (i === 0) {
			return;
		}

		categories.push({
			id: $this.val(),
			name: $this.text()
		});
	});

	hpFile2.setCategories(categories);
	hpFile2.setFile2s(<?php echo json_encode($view->hpFile2s)?>);
	hpFile2.filter();
	hpFile2.render();

	$('.main-contents .hp-file2-upload').initUploadHPFile2(function (data) {
		hpFile2.addFile2(data.item).filter().render();
	});

	var categoryModal = app.modal.categoryFile2Edit( _token, function ( data ) {
		var $option = $categorySelect.find('option:eq(0)');
		$categorySelect.empty().append($option);
		$.each(data.categories, function (i, category) {
			$categorySelect.append($('<option/>').val(category.id).text(category.name));
		});

		hpFile2.setCategories(data.categories);
	});
	categoryModal.setCategories(categories);

	// 拡大ファイル
	$hpFile2.on('click', '.img-list-thumb', function () {
		var $file2 = $(this).parent();
		var dataId = $file2.attr( 'data-id' )	;
		window.open( '/file/hp-file2?file2_id=' + dataId,	'newtab'	) ;
	});

	// ファイル操作
	$hpFile2.on('click', '.action-menu ul a', function () {
		var $i = $(this).find('i');
		var $file2 = $(this).parents('li[data-id]');
		var file2Id = parseInt($file2.attr('data-id'));
		var title = $file2.find('p').html();

		// 使用ページ
		if ($i.hasClass('i-e-list')) {
			var $pages = $('<div class="img-use-list js-scroll-container" data-scroll-container-max-height="300" style="height:300px"></div>');
			var xhr = app.api('/site-setting/api-get-hppages-by-usefile2', {id: file2Id}, function (data) {
				if (data.pages.length) {
					$.each(data.pages, function (i, page) {
						$pages.append('<dl><dt>'+app.h(page.title)+'</dt><dd><a href="/page/edit?id='+page.id+'" class="btn-t-gray size-s">編集</a></dd></dl>');
					});
				}
				else {
					$pages.parents('.align-top').removeClass('align-top');
					$pages.replaceWith('<div class="modal-message"><strong>ファイルを使用しているページは<br>ありません。</strong></div>');
				}
			});

			app.modal.popup({
				title: 'ファイルを使用しているページ',
				contents: $pages,
				modalBodyInnerClass: 'align-top',
				onClose: function () {
					xhr.abort();
				},
				ok: false,
				cancel: false
			}).show();
		}
		// カテゴリ移動
		else if ($i.hasClass('i-e-move')) {
			var selectedIndex = 0;
			var $select = $('<select><option value="0">選択してください</option></select>');
			var $errors = $('<div class="errors"></div>');
			var categoryId = parseInt($file2.attr('data-category-id'));
			$.each(hpFile2.getCategories(), function (i, category) {
				$select.append($('<option/>').val(category.id).text(category.name));
				if (parseInt(category.id) === categoryId) {
					selectedIndex = i + 1;
				}
			});
			$select[0].selectedIndex = selectedIndex;
			var modal = app.modal.popup({
				title: 'カテゴリ移動',
				contents: $select.add($errors),
				ok: '設定'
			});

			var $ok = modal.$el.find('.ok');
			modal.onClose = function (ret, modal) {
				if (!ret) {
					return;
				}

				if ($ok.hasClass('is-disable')) {
					return false;
				}

				$ok.addClass('is-disable');

				$errors.empty();
				$select.removeClass('is-error');

				var categoryId = $select.val();
				app.api('/site-setting/api-edit-file2-category', {id: file2Id, category_id: categoryId, _token: _token}, function (res) {
					if (res.errors) {
						$.each(res.errors.category_id, function (i, error) {
							$errors.append($('<p/>').text(error));
						});
						$select.addClass('is-error');
					}
					else {
						hpFile2.updateFile2Data(file2Id, {category_id: categoryId}).filter().render();
						modal.close();
					}
				})
				.always(function () {
					$ok.removeClass('is-disable');
				});

				return false;
			};

			modal.show();
		}
		// 削除
		else if ($i.hasClass('i-e-delete')) {
			var message = title + 'を削除します。\nよろしいですか？';
			app.modal.confirm('', message, function (ret) {
				if (!ret) {
					return;
				}

				app.api('/site-setting/api-remove-file2', {id: file2Id, _token: _token}, function (res) {
					if (res.error) {
						app.modal.alert('', res.error);
					}
					else {
						hpFile2.removeFile2ById(file2Id).filter().render();
					}
				});
			});
		}
	});


	// カテゴリ編集
	$('.edit-category-btn').on('click', function () {
		categoryModal.show();
	});
});
</script>
@endsection

@section('content')
			
<div class="main-contents">
	<h1>ファイル管理</h1>
	<div class="main-contents-body">
		<!-- ファイルを登録する -->
		<div class="section img-folder hp-file2-upload">
			<h2>ファイルを登録する<a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.content-common')?>','_blank','width=920,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>

			<form data-api-action="/site-setting/api-save-file2">
			@csrf
			<div class="f-file-upload">
				<?php $view->form->form('hp_file2_content_id')?>
				<div class="up-img">
					<div class="up-btn">
						<input type="file" name="file" />
					</div>

					<div class="up-area is-hide">
						または、ファイルをドロップしてください。
					</div>

					<small>pdf,xls,xlsx,doc,docx,ppt,pptx（5MBまで）</small><br>
					<small>※著作権又はその他の知的財産権で保護されているファイルを使用する場合は、当該権利者の許諾を得るものとし、当該権利者の許諾を得ていないものは使用しないでください。</small>
				</div>

				<div class="up-preview">
					<p></p>
					<a href="javascript:;" class="i-e-delete is-hide"></a>
				</div>
			</div>

			<div class="img-up-info">
				<dl>
					<dt><?php echo $view->form->getElement('title')->getLabel() ?><i class="i-l-require">必須</i></dt>
					<dd>
						<div class="is-require input-img-title">
							<?php $view->form->form('title')?>
							<span class="input-count"></span>
						</div>
					</dd>
				</dl>

				<dl>
					<dt>ファイルカテゴリ<a href="javascript:;" class="i-s-link edit-category-btn">カテゴリ編集</a></dt>
					<dd>
						<?php $view->form->form('category_id')?>
					</dd>
				</dl>
			</div>

			<div class="errors"></div>

			<div class="item-add">
				<a class="btn-t-blue save" href="javascript:;">登録</a>
			</div>
			</form>

		</div>
		<!-- /ファイルを登録する -->


		<!-- ファイルを表示する -->
		<div class="section img-folder hp-file2">
			<h2>ファイルを表示する<a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.content-common')?>','_blank','width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>

			<div class="img-category-list">
				<h3>ファイルカテゴリ</h3>
				<a href="javascript:;">全て</a>
				<ul>
				</ul>
			</div>

			<div class="img-folder-list">
				<ul class="img-list">
				</ul>

				<ul class="paging" data-page-size="24">
				</ul>
			</div>

		</div>
		<!-- /ファイルを表示する -->
	</div>
</div>
@endsection