
@extends('layouts.default')
@section('content')

	@section('script')
	<script type="text/javascript">
	$(function () {
		'use strict';
		
		$('.js-sort').on('change', function () {
			location.href = location.pathname + '?order=' + this.value;
		});
	});
	</script>
	@stop
	@section('title')特集設定 @stop

<div class="main-contents article-search">

	<h1>物件特集の作成/更新</h1>
	<div class="main-contents-body">
		<div class="section special-create">
			<p class="btn-create"><a href="" class="btn-t-blue">新規作成</a></p>
			
			<?php if(!$view->canEdit):?>
			<div class="alert-strong"><a href="<?php echo route('default.estate-search-setting.index')?>">物件検索設定</a>が設定されていないため、編集できません</div>
			<?php endif;?>
			
			<p class="btn-create">
				
				<?php if($view->canEdit):?>
				<a href="<?php echo route('default.estatespecial.new')?>" class="btn-t-blue">新規作成</a>
				<?php else:?>
				<a class="btn-t-blue is-disable">新規作成</a>
				<?php endif;?>
			</p>
			
			<div class="special-sort-option">
				<p class="num-total"><?php
					if(isset($view->specials))
					{
						echo ($view->specials) ? $view->specials->getFoundRows(): 0;
					}
					else
					{
						echo 0;
					}
					?>件
				 </p>
				<p class="heading-sort">並び替え</p>
				<?php $order = request()->order ?: 1?>
				<select class="select-sort js-sort">
					<?php foreach ($view->sortOptions as $option => $label):?>
					<option value="<?php echo $option?>"<?php if($order == $option):?> selected<?php endif;?>><?php echo $label?></option>
					<?php endforeach;?>
				</select>
			</div>
			
			<table class="tb-basic">
				<thead>
					<tr>
						<th class="nowrap">特集名</th>
						<th class="nowrap">状態</th>
						<th class="nowrap">物件種目</th>
						<th class="nowrap">検索方法</th>
						<th class="nowrap">作成日</th>
						<th class="nowrap">設定</th>
					</tr>
				</thead>
				<tbody>
					<?php if (isset($view->specials) && $view->specials->count() > 0):?>
						
						<?php foreach ($view->specials as $row):?>
						<?php $settingObject = $row->toSettingObject();
						?>
						<tr>
							<td class="alL"><?php echo h($row->title)?></td>
							<td class="page-edit-status nowrap">
								<?php if ($row->is_public):?>
								<span class="is-public">公開中</span>
								<?php else:?>
								<span class="is-draft">下書き</span>
								<?php endif;?>
							</td>
							
							<td class="alL"><?php echo implode('<br>', $settingObject->getDisplayEstateType())?></td>
							<td class="alC">
								<?php echo $settingObject->area_search_filter->getDisplayHasSearchPage()?><br>
								<?php $displaySearchTypes = $settingObject->area_search_filter->getDisplaySearchType()  ?>
								
								<?php if(!$view->mapOption && array_key_exists(config('constants.search_type_list.TYPE_SPATIAL'),$displaySearchTypes)) {
									unset($displaySearchTypes[config('constants.search_type_list.TYPE_SPATIAL')]); }
									?>
								<?php if(count($displaySearchTypes) > 0): ?>（<?php echo implode(',', $displaySearchTypes)?>）<?php endif; ?>
							</td>
							<td class="alL"><?php echo date('Y年n月j日', strtotime($row->create_special_date))?></td>
							<td class="alC"><a href="<?php echo route('default.estatespecial.detail')?>?id=<?php echo $row->id?>" class="btn-t-gray size-s update-setting-btn">設定確認</a></td>
						</tr>
						<?php endforeach;?>
					<?php endif;?>
				</tbody>
			</table>
		</div>
	</div>
</div>

@endsection