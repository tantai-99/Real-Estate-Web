@extends('layouts.default')

@section('title', __('画像フォルダ'))

@section('script')
<script type="text/javascript" src="{{ asset('/js/upload.js')}}"></script>
<script type="text/javascript" src="{{ asset('/js/libs/jquery.lazyload.min.js')}}"></script>
<script type="text/javascript">
$(function() {

	var _token = '<?php  echo csrf_token(false) ?>';
	var $hpImage = $('.hp-image');
	var hpImage = app.hpImage($hpImage, true);
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
	hpImage.setCategories(categories);
	hpImage.setImages(<?php echo json_encode($view->hpImages)?>);
	hpImage.filter();
	hpImage.render();

	$('.main-contents .hp-image-upload').initUploadHPImage(function (data) {
		hpImage.addImage(data.item).filter().render();
	});

	var categoryModal = app.modal.categoryEdit(_token, function (data) {
		var $option = $categorySelect.find('option:eq(0)');
		$categorySelect.empty().append($option);
		$.each(data.categories, function (i, category) {
			$categorySelect.append($('<option/>').val(category.id).text(category.name));
		});

		hpImage.setCategories(data.categories);
	});
	categoryModal.setCategories(categories);

	// 拡大画像
	$hpImage.on('click', '.img-list-thumb', function () {
		var $image = $(this).parent();
		var $img = $(this).find('img').clone();
		app.modal.popup({
			title: $img.attr('alt'),
			contents: $('<a/>').attr({href: $img.attr('src'), target: '_blank'}).append($img),
			modalContentsClass: 'size-f',
			modalBodyInnerClass: 'align-top',
			ok: false,
			cancel: false
		}).show();
	});

	// 画像操作
	$hpImage.on('click', '.action-menu ul a', function () {
		var $i = $(this).find('i');
		var $image = $(this).parents('li[data-id]');
		var imageId = parseInt($image.attr('data-id'));
		var title = $image.find('.img-list-info > span').html();

		// 使用ページ
		if ($i.hasClass('i-e-list')) {
			var $pages =  $('<div class="img-use-list js-scroll-container" data-scroll-container-max-height="300" style="height:300px"></div>');
			var xhr = app.api('/site-setting/api-get-hppages-by-useimage', {id: imageId}, function (data) {
				if (data.pages.length) {
					$.each(data.pages, function (i, page) {
						$pages.append('<dl><dt>'+app.h(page.title)+'</dt><dd><a href="/page/edit?id='+page.id+'" class="btn-t-gray size-s">編集</a></dd></dl>');
					});
				}
				else {
					$pages.parents('.align-top').removeClass('align-top');
					$pages.replaceWith('<div class="modal-message"><strong>画像を使用しているページは<br>ありません。</strong></div>');
				}
			});
			app.modal.popup({
				title: '画像を使用しているページ',
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
			var categoryId = parseInt($image.attr('data-category-id'));
			$.each(hpImage.getCategories(), function (i, category) {
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
				app.api('/site-setting/api-edit-image-category', {id: imageId, category_id: categoryId, _token: _token}, function (res) {
					if (res.errors) {
						$.each(res.errors.category_id, function (i, error) {
							$errors.append($('<p/>').text(error));
						});
						$select.addClass('is-error');
					}
					else {
						hpImage.updateImageData(imageId, {category_id: categoryId}).filter().render();
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

				app.api('/site-setting/api-remove-image', {id: imageId, _token: _token}, function (res) {
					if (res.error) {
						app.modal.alert('', res.error);
					}
					else {
						hpImage.removeImageById(imageId).filter().render();
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
	<h1>画像フォルダ</h1>
	<div class="main-contents-body">
		<!-- 画像を登録する -->
		<div class="section img-folder hp-image-upload">
			<h2>画像を登録する<a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.content-common')?>','_blank','width=920,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>

			<form data-api-action="/site-setting/api-save-image"  enctype="multipart/form-data">
			@csrf 
			<div class="f-img-upload">
				<?php echo $view->form->form('hp_image_content_id') ?>
				<div class="up-img">
					<div class="up-btn">
						<input type="file" name="file" />
					</div>

					<div class="up-area">
						または、ファイルをドロップしてください。
					</div>

					<small>（１）jpg,jpeg,png (容量 10MB、サイズ 縦：960px　横：1280px まで。サイズ超過時は範囲内に収まるように自動縮小されます。）</small><br>
					<small>（２）gif（容量 2MB、サイズ 縦：960px　横：1280px まで。）</small><br>
					<small>※著作権又はその他の知的財産権で保護されている画像を使用する場合は、当該権利者の許諾を得るものとし、当該権利者の許諾を得ていないものは使用しないでください。</small>
				</div>
				<div class="up-preview">
					<a href="javascript:;" class="i-e-delete is-hide"></a>
				</div>
			</div>
			<div class="img-up-info">
				<dl>
					<dt><?php echo $view->form->getElement('title')->getLabel() ?>
						<i class="i-l-require">必須</i>
					</dt>
					<dd>
						<div class="is-require input-img-title">
							<?php $view->form->form('title')?>
							<span class="input-count"></span>
						</div>
					</dd>
				</dl>

				<dl>
					<dt>画像カテゴリ<a href="javascript:;" class="i-s-link edit-category-btn">カテゴリ編集</a></dt>
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
		<!-- /画像を登録する -->


		<!-- 画像を表示する -->
		<div class="section img-folder hp-image">
			<h2>画像を表示する<a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.content-common')?>','_blank', 'width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>

			<div class="img-category-list">
				<h3>画像カテゴリ</h3>
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
		<!-- /画像を表示する -->
	</div>
</div>

@endsection
