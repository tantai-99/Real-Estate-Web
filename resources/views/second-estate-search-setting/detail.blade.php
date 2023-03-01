@extends('layouts.default')
@section('title', __('2次広告自動公開設定'))
@section('slyte')
<link rel="stylesheet" href="/css/estate_extension.css"> 
@endsection

@section('script')
<script type="text/javascript" src="/js/app.estate.js?v=20171213"> </script>
<script type="text/javascript">
(function () {
	
	'use strict';
	
	$(function () {
		var Master = {
			prefMaster       : <?php echo json_encode($view->prefMaster)?>,
			searchTypeMaster : <?php echo json_encode($view->searchTypeMaster)?>,
			searchTypeConst  : <?php echo json_encode($view->searchTypeConst)?>,
			estateTypeMaster : <?php echo json_encode($view->estateTypeMaster)?>,
			secondEnabledMaster: <?php echo json_encode($view->secondEnabledMaster)?>
		};
		
		var setting = <?php echo json_encode($view->setting)?>;
		
		function hasSearchType(type) {
			return ''+type === ''+setting.area_search_filter.search_type;
		}
		function needShikugunSetting() {
			return hasSearchType(Master.searchTypeConst.TYPE_AREA);
		}
		function needEnsenSetting() {
			return hasSearchType(Master.searchTypeConst.TYPE_ENSEN);
		}
		
		var searchType = setting.area_search_filter.search_type || '';
		
		var basicSetting = new app.estate.ConfirmSecondBasicView(Master);
		basicSetting.render(setting);
		$('.js-confirm-basic-setting').append(basicSetting.$element);
		
		var $shikugunSetting = $('.js-confirm-shikuguns');
		if (hasSearchType(Master.searchTypeConst.TYPE_AREA)) {
			var shikugunSetting = new app.estate.ConfirmShikugunView(Master);
			shikugunSetting.render(setting);
			$shikugunSetting.append(shikugunSetting.$element);
		}
		else {
			$shikugunSetting.hide();
		}
		
		var $ensenSetting = $('.js-confirm-ensens');
		if (hasSearchType(Master.searchTypeConst.TYPE_ENSEN)) {
			var ensenSetting = new app.estate.ConfirmEnsenView(Master);
			ensenSetting.render(setting);
			$ensenSetting.append(ensenSetting.$element);
		}
		else {
			$ensenSetting.hide();
		}
		
		var searchFilter = new app.estate.ConfirmSecondSearchFilterView();
		searchFilter.render(setting);
		//$('.js-confirm-search-filter').append(searchFilter.$element);
	});
})();
</script>
@endsection

@section('content')
<div class="main-contents article-search">
	<h1>2次広告自動公開設定（<?php echo $view->estateClassName?>）：設定確認</h1>
	<div class="main-contents-body">

		<div class="section js-confirm-basic-setting">
			<h2>2次広告自動公開の基本設定</h2>
		</div>
		
		<div class="section confirm-area js-confirm-shikuguns">
			<h2>市区郡</h2>
		</div>
		
		<div class="section confirm-station js-confirm-ensens">
			<h2>沿線・駅</h2>
		</div>
		
		<div class="section js-confirm-search-filter">
			<h2>絞り込み条件</h2>
		</div>
	
		<div class="section btn-area">
			<a href="<?php echo route('default.secondestatesearchsetting.index')?>" class="btn-t-gray">設定トップに戻る</a>
			<a href="<?php echo route('edit')?>?class=<?php echo h(request()->class);  ?>" class="btn-t-blue size-l">設定を変更する</a>
		</div>
	</div>
</div>
@endsection
