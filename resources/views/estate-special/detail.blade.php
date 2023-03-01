
@extends('layouts.default')
@section('content')

@section('style')
<link rel="stylesheet" href="/css/estate_extension.css">
@stop
@section('script')
<script src='/js/app.estate.js?v=2020052700' type="text/javascript"></script>
@stop
@section('title') 特集設定 @stop
<script type="text/javascript">
	(function () {
		'use strict';
		
		$(function () {
			
			var Master = {
				prefMaster                 : <?php echo json_encode($view->prefMaster)?>,
				searchTypeMaster           : <?php echo json_encode($view->searchTypeMaster)?>,
				searchTypeDirectMaster     : <?php echo json_encode($view->searchTypeDirectMaster)?>,
				searchTypeConst            : <?php echo json_encode($view->searchTypeConst)?>,
				estateTypeMaster           : <?php echo json_encode($view->estateTypeMaster)?>,
				shumokuTypeMaster          : <?php echo json_encode($view->shumokuTypeMaster)?>,
				specialPublishEstateMaster : <?php echo json_encode($view->specialPublishEstateMaster)?>,
				specialTesuryoKokokuhiMaster : <?php echo json_encode($view->specialTesuryoKokokuhiMaster)?>,
				specialSearchPageTypeMaster: <?php echo json_encode($view->specialSearchPageTypeMaster)?>
			};
			
			var publishStatus = <?php echo $view->special->is_public?>;
			
			var EstateMaster = app.estate.EstateMaster;
			
			var setting = <?php echo json_encode($view->specialSetting)?>;
			function hasSearchType(type) {
				return $.inArray(''+type, setting.area_search_filter.search_type) > -1;
			}
			function needShikugunSetting() {
				return hasSearchType(Master.searchTypeConst.TYPE_AREA);
			}
			function needEnsenSetting() {
				return hasSearchType(Master.searchTypeConst.TYPE_ENSEN);
			}
			
			var pageBasicSetting = new app.estate.ConfirmSpecialPageBasicView(publishStatus);
			pageBasicSetting.render(setting);
			$('.js-confirm-page-basic-setting').append(pageBasicSetting.$element);
			
			var specialBasicSetting = new app.estate.ConfirmSpecialBasicView(Master);
			specialBasicSetting.render(setting);
			$('.js-confirm-special-basic-setting').append(specialBasicSetting.$element);
			
			var $confirmShikugun = $('.js-confirm-shikuguns')
			if (hasSearchType(Master.searchTypeConst.TYPE_AREA) && setting.method_setting == 1) {
				var confirmShikugun = new app.estate.ConfirmShikugunView(Master);
				confirmShikugun.render(setting);
				$confirmShikugun.append(confirmShikugun.$element);
			}
			else {
				$confirmShikugun.hide();
			}
			
			var $confirmEnsen = $('.js-confirm-ensens');
			if (hasSearchType(Master.searchTypeConst.TYPE_ENSEN) && setting.method_setting == 1) {
				var confirmEnsen = new app.estate.ConfirmEnsenView(Master);
				confirmEnsen.render(setting);
				$confirmEnsen.append(confirmEnsen.$element);
			}
			else {
				$confirmEnsen.hide();
			}
			
			if (setting.method_setting == 1) {
				var specialSearchFilter = new app.estate.ConfirmSpecialSearchFilterView();
				specialSearchFilter.render(setting);
				$('.js-confirm-special-search-filter').append(specialSearchFilter.$element);
				var specialMethod = new app.estate.ConfirmHouseSpecialBasicView(Master);
				specialMethod.render(setting, setting.method_setting);
				$('.js-confirm-method-special-setting').append(specialMethod.$element);
			} else {
				$('.js-confirm-special-search-filter').hide();
				$('.js-confirm-method-special-setting').hide();
			}

			if (setting.method_setting == 3) {
				$('.js-confirm-method-special-setting').show();
				var specialSecond = new app.estate.ConfirmSecondHouseSpecialView(Master);
				specialSecond.render(setting);
				$('.js-confirm-method-special-setting').append(specialSecond.$element);
			}

			var specialHousesList = new app.estate.ConfirmSpecialHousesListView();
			if (setting.method_setting == 2 && setting.houses_id.length > 0) {
				specialHousesList.render(setting);
				$('.js-confirm-special-houses-list').append(specialHousesList.$element);
			} else {
				$('.js-confirm-special-houses-list').hide();
			}

			var page = null;
			var sort = null;
			$(document).on('click','.sort-table a', function() {
				sort = $(this).parent().data('value');
				specialHousesList.render(setting, page, sort);
			});
			$(document).on('click', '.paging li:not(.is-active) a', function() {
				page = $(this).data('page');
				specialHousesList.render(setting, page, sort);
			});
			
			// 特集コピー・削除用パラメータ
			var params = {
				id: <?php echo $view->special->id?>,
				_token: '<?php echo csrf_token() ?>',
			};
			
			// 特集コピー
			$('.js-copy').on('click', function () {
				app.modal.confirm('確認', 'この特集をコピーします。よろしいですか？', function (ret) {
					if (!ret) {
						return;
					}
					
					app.api('/estate-special/api-copy', params, function (data) {
						if (data.error) {
							app.modal.alert('', data.message);
							return;
						}
						app.modal.message({
							message: '特集をコピーしました。',
							links: [
								{title: '複製された特集の編集へ', url: '/estate-special/edit?id='+data.id},
								{title: '特集トップへ', url: '/estate-special'},
								{title: 'ホームへ', url: '/'}
							],
							ok: '閉じる',
							cancel: false
						});
					});
				});
			});
			
			// 特集削除
			$('.js-delete').on('click', function () {
				app.modal.confirm('確認', 'この特集を削除します。よろしいですか？', function (ret) {
					if (!ret) {
						return;
					}
					app.api('/estate-special/api-delete', params, function () {
						app.modal.message({
							title: '',
							message: '特集を削除しました。',
							closeButton: false,
							cancel: false,
							ok: '特集トップへ',
							onClose: function () {
								location.href = '/estate-special';
							}
						}).show();
					});
				});
				return false;
			});


		<?php if($view->all_upload_flg):?>
				// 特集編集
				$('.special-item-edit').on('click', function () {
					if(publishStatus) {
						var editpage = $(this).attr('href');
						var contents =  '<div style="margin: 40px 8px;">'+
											'<p>現在共通設定が未反映の状態です。この特集を修正・保存した場合、次回サイトの更新時に自動的に修正内容が反映されます。</p>' +
											'<p>修正内容を即時反映させたくない場合は、先にサイトの公開/更新画面で公開処理を行ってから、編集を行ってください。</p>' +
										'</div>';
						var modal = app.modal.popup({
							contents: contents,
							cancel: false,
							closeButton: false,
							autoRemove: false,
							onClose: function () { location.href = editpage; },
						});
						modal.show();
						return false;
					}
				});
		<?php endif;?>
		});
	})();
</script>
<style>
	ul.step:after {
		content: "";
		display: block;
		clear: both;
		margin-bottom: 20px;
	}
	ul.step li {
		float: left;
		margin: 0 10px;
		padding: 5px;
		border: 1px solid #555;
	}
	ul.step li.is-active {
		background-color: #59e;
		color: #fff;
	}
</style>
	@section('titles') 特集設定 @stop
<div class="main-contents article-search">
	@csrf
	<h1>物件特集の確認画面</h1>
	<div class="main-contents-body">
		<?php if(!$view->canEdit):?>
		<div class="alert-strong"><a href="<?php echo route('default.estate-search-setting.index')?>">物件検索設定</a>が設定されていないため、編集できません</div>
		<?php endif;?>
		
		<div class="section js-confirm-page-basic-setting">
			<h2>ページの基本設定</h2>
		</div>
		
		<div class="section js-confirm-special-basic-setting">
			<h2>特集の基本設定</h2>
		</div>

        <div class="section js-confirm-method-special-setting">
            <h2>物件の設定</h2>
        </div>
		
		<div class="section confirm-area js-confirm-shikuguns">
			<h2>市区郡</h2>
            <span>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>
		</div>
		
		<div class="section confirm-station js-confirm-ensens">
			<h2>沿線・駅</h2>
		</div>
		
		<div class="section js-confirm-special-search-filter">
			<h2>絞り込み条件</h2>
		</div>

        <div class="section js-confirm-special-houses-list">
            <h2>公開する物件の個別設定一覧</h2>
        </div>
	
		<div class="btn-three">
			<ul>
				<li><a href="<?php echo route('default.estatespecial.index')?>" class="btn-t-gray">設定トップに戻る</a></li>
				<li>
					<?php
                        // 削除可、公開中の削除不可、予約中の削除不可に分岐
						$delRestrictClass = "js-delete";
						if(!$view->special->canDelete() && $view->special->isPublic()) {
							$delRestrictClass = "js-not-delete";
						} else if($view->special->isScheduled()) {
							$delRestrictClass = "js-not-delete-sched";
						}
					?>
					<a href="javascript:;" class="btn-t-blue size-l <?php echo $delRestrictClass;?>">削除する</a>
				</li>
				<?php if($view->canEdit):?>
				<li><a href="javascript:;" class="btn-t-blue size-l js-copy">コピーする</a></li>
				<li><a href="<?php echo route('default.estatespecial.edit')?>?id=<?php echo $view->special->id?>" class="btn-t-blue size-l special-item-edit">設定を変更する</a></li>
				<?php else:?>
				<li><a class="btn-t-blue size-l is-disable">コピーする</a></li>
				<li><a class="btn-t-blue size-l is-disable">設定を変更する</a></li>
				<?php endif;?>
			</ul>
		</div>
	</div>
</div>
@endsection
	