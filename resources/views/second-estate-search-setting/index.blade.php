@extends('layouts.default')
@section('title', __('2次広告自動公開設定'))
@section('content')
<div class="main-contents article-search">
	<h1>2次広告自動公開設定</h1>
	<div class="main-contents-body">
		<h2>2次広告自動公開の設定一覧</h2><br>
        <div class="btn-back-pageright">
            <p><a href="{{ url('/second-estate-exclusion') }}" class="i-s-link">物件取込み除外会社設定</a></p>
        </div>
        <!--<div class="alert-strong">反映時間を説明する文言が入ります。反映時間を説明する文言が入ります。反映時間を説明する文言が入ります。</div>-->
		<div class="section">
			<table class="tb-basic">
				<thead>
					<tr>
						<th class="nowrap">物件種別</th>
						<th class="nowrap">設定状況</th>
						<th class="nowrap">都道府県</th>
						<th class="nowrap">物件種目</th>
						<th class="nowrap">市区郡/沿線・駅</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($view->searchClasses as $class => $label):?>

					<tr<?php if (!isset($view->searchSettings[$class])):?> class="is-no-setting"<?php endif;?>>
						<th class="alL nowrap"><?php echo $label?></th>

						<?php if (isset($view->searchSettings[$class])):?>
						<?php $searchSetting = $view->searchSettings[$class]->toSettingObject();?>

						<td class="alL"><?php echo $searchSetting->getDisplayEnabled()?></td>
						<td class="alL">
							<ul class="list-item">
							<?php foreach ($searchSetting->area_search_filter->getDisplayPref() as $prefName):?>
								<li><?php echo h($prefName)?></li>
							<?php endforeach;?>
							</ul>
						</td>
						<td class="alL">
							<?php foreach ($searchSetting->getDisplayEstateType() as $estateType):?>
								<?php echo h($estateType)?><br>
							<?php endforeach;?>
						</td>
						<td class="alL nowrap">
							<span><?php echo h($searchSetting->area_search_filter->getDisplaySearchType())?></span>
						</td>
						<td class="alC"><a href="<?php echo route('detail') ?>?class=<?php echo h($class)?>" class="btn-t-gray size-s update-setting-btn">設定を確認</a></td>
						<?php else:?>
						<td class="alL">（未設定）</td>
						<td class="alL">（未設定）</td>
						<td class="alL">（未設定）</td>
						<td class="alL">（未設定）</td>
						<td class="alC"><a href="<?php echo route('edit') ?>?class=<?php echo h($class)?>" class="btn-t-gray size-s update-setting-btn">設定を行う</a></td>
						<?php endif;?>
					</tr>
					<?php endforeach;?>
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection