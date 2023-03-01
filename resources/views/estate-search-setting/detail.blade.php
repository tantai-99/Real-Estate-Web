@extends('layouts.default')

@section('title', __('物件検索設定'))

@section('style')
<link href="/css/estate_extension.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript" src="/js/app.estate.js?v=20171213"></script>
<script type="text/javascript">
	(function() {

		'use strict';

		$(function() {
			var Master = {
				prefMaster: <?php echo json_encode($view->prefMaster) ?>,
				searchTypeMaster: <?php echo json_encode($view->searchTypeMaster) ?>,
				searchTypeConst: <?php echo json_encode($view->searchTypeConst) ?>,
				estateTypeMaster: <?php echo json_encode($view->estateTypeMaster) ?>
			};

			var setting = <?php echo json_encode($view->setting) ?>;

			var searchType = setting.area_search_filter.search_type || [];

			var $basicSetting = $('.js-basic-setting');
			var basicSetting = new app.estate.ConfirmBasicSettingView(Master, <?= $view->dispEstateRequest ?>);
			basicSetting.render(setting);
			$basicSetting.append(basicSetting.$element);

			var $shikugunSetting = $('.js-shikugun-setting');
			if ($.inArray('' + Master.searchTypeConst.TYPE_AREA, searchType) > -1) {
				var shikugunSetting = new app.estate.ConfirmShikugunView(Master);
				shikugunSetting.render(setting);
				$shikugunSetting.append(shikugunSetting.$element);
			} else {
				$shikugunSetting.hide();
			}

			var $ensenSetting = $('.js-ensen-setting');
			if ($.inArray('' + Master.searchTypeConst.TYPE_ENSEN, searchType) > -1) {
				var ensenSetting = new app.estate.ConfirmEnsenView(Master);
				ensenSetting.render(setting);
				$ensenSetting.append(ensenSetting.$element);
			} else {
				$ensenSetting.hide();
			}

			$('.js-delete').on('click', function() {
				var $this = $(this);
				var params = {
					'class': '<?php echo request()->class ?>',
					_token: '<?php /* echo $this->csrfToken(false) */ ?>'
				};
				<?php if (!$view->enaDelete) { ?>
					app.modal.message({
						title: '',
						message: '特集で利用中の種別のため削除できません。',
						closeButton: true,
						cancel: false,
						ok: 'OK'
					}).show();
				<?php } elseif ($view->isScheduled) { ?>
					app.modal.alertBanDeletePage('削除対象種目へのリンクが公開予約設定中のため、変更・削除ができません。', '「サイトの公開/更新」の「公開設定（詳細設定）」より公開予約の解除を行ってください。<br>公開予約の解除後、「削除」することができます。');
				<?php } else { ?>
					app.modal.confirm('確認', 'この物件種別の設定を全て削除します。\nよろしいですか？', function(ret) {
						if (!ret) {
							return;
						}
						app.api('/estate-search-setting/api-delete', params, function() {
							app.modal.message({
								title: '',
								message: '物件種別の設定を削除しました。',
								closeButton: false,
								cancel: false,
								ok: '設定トップへ',
								onClose: function() {
									location.href = '/estate-search-setting';
								}
							}).show();
						});
					});
				<?php } ?>
				return false;
			});
		});
	})();
</script>
@endsection

@section('content')
<div class="main-contents article-search">
	<h1>物件検索設定（<?php echo $view->estateClassName ?>）：設定確認</h1>
	<div class="main-contents-body">


		<div class="section js-basic-setting">
			<h2>検索エンジンの基本設定</h2>
		</div>

		<div class="section confirm-area js-shikugun-setting">
			<h2>取り扱いエリア</h2>
			<span>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>
		</div>

		<div class="section confirm-station js-ensen-setting">
			<h2>取り扱い沿線・駅</h2>
		</div>

		<div class="section btn-area">
			<a href="<?php echo route('default.estate-search-setting.index') ?>" class="btn-t-gray">設定トップに戻る</a>
			<a href="<?php echo route('default.estate-search-setting.edit') ?>?class=<?php echo request()->class  ?>" class="btn-t-blue size-l">設定を変更する</a>
		</div>
		<div class="section link-all-delete">
			<p><a href="javascript:;" class="js-delete">この物件種別の設定を全て削除する</a></p>
		</div>
	</div>
</div>
@endsection