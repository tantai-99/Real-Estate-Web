<?php

use Library\Custom\Model\Estate\SearchTypeList;
?>
@extends('layouts.default')

@section('title', __('物件検索設定'))

@section('content')
<div class="main-contents article-search">
	<h1>物件検索設定</h1>
	<div class="main-contents-body">
		<h2>検索エンジンの設定状況</h2>
		<div class="section">

			<table class="tb-basic">
				<thead>
					<tr>
						<th class="nowrap">物件種別</th>
						<th class="nowrap">物件種目</th>
						<th class="nowrap">探し方</th>
						<th class="nowrap">都道府県</th>
						<th class="nowrap">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($view->searchClasses as $class => $label) : ?>
						<tr<?php if (!isset($view->searchSettings[$class])) : ?> class="is-no-setting" <?php endif; ?>>
							<th class="alL nowrap"><?php echo $label ?></th>
							<?php if (isset($view->searchSettings[$class])) : ?>
								<?php $searchSetting = $view->searchSettings[$class]->toSettingObject(); ?>
								<td class="alL">
									<?php foreach ($searchSetting->getDisplayEstateType() as $estateType) : ?>
										<?php echo h($estateType) ?><br>
									<?php endforeach; ?>
								</td>
								<td class="alL nowrap">
									<?php foreach ($searchSetting->area_search_filter->getDisplaySearchType() as $searchType => $searchTypeName) : ?>
										<?php if ($searchType == SearchTypeList::TYPE_SPATIAL && !$view->mapOption) continue;  ?>
										<?php echo h($searchTypeName) ?><br>
									<?php endforeach; ?>
								</td>
								<td class="alL">
									<ul class="list-item">
										<?php foreach ($searchSetting->area_search_filter->getDisplayPref() as $prefName) : ?>
											<li><?php echo h($prefName) ?></li>
										<?php endforeach; ?>
									</ul>
								</td>
								<td class="alC"><a href="<?php echo route('default.estate-search-setting.detail') ?>?class=<?php echo h($class) ?>" class="btn-t-gray size-s update-setting-btn">設定を確認</a></td>
							<?php else : ?>
								<td class="alL">（未設定）</td>
								<td class="alL">（未設定）</td>
								<td class="alL">（未設定）</td>
								<td class="alC"><a href="<?php echo route('default.estate-search-setting.edit') ?>?class=<?php echo h($class) ?>" class="btn-t-gray size-s update-setting-btn">設定を行う</a></td>
							<?php endif; ?>
							</tr>
						<?php endforeach; ?>
				</tbody>
			</table>

		</div>
	</div>
</div>
@endsection