@extends('admin::layouts.default')

@section('title', __('地図検索設定完了'))

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>地図検索設定完了</h1>
	<div class="main-contents-body">
		<div class="btn-agreement">
			<ul>
				<input type="hidden" id="company_id" name="company_id" value="<?php echo $view->company_id; ?>">
				<li><a href="/admin/company/edit?id=<?php				echo $view->company_id; ?>" class="btn-t-blue   size-l">契約者詳細編集	</a></li>
				<li><a href="/admin/company/private?company_id=<?php	echo $view->company_id; ?>" class="btn-t-blue   size-l">非公開設定		</a></li>
				<li><a href="/admin/company/tag?company_id=<?php		echo $view->company_id; ?>" class="btn-t-blue   size-l"><?php echo $view->original_tag; ?></a></li>
				<li><a href="/admin/company/group?company_id=<?php		echo $view->company_id; ?>" class="btn-t-blue   size-l">グループ会社設定	</a></li>
				<?php if($view->contract_type == 0) : ?>
					<?php
						if (
							($view->cms_plan == config('constants.cms_plan.CMS_PLAN_STANDARD')) ||
							($view->reserve_cms_plan == config('constants.cms_plan.CMS_PLAN_STANDARD'))
						) :
					?>
						<li><a href="/admin/map-option/edit?id=<?php               echo $view->company_id;?>" class="btn-t-orange size-l">地図検索</a></li>
					<?php endif; ?>
					<?php
						if (!($view->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE')) || (!($view->reserve_cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') ) && $view->reserve_cms_plan > 0)) :
					?>
						<li><a href="/admin/company/second-estate?company_id=<?php echo $view->company_id;?>" class="btn-t-orange size-l">2次広告自動公開設定</a></li>
						<li><a href="/admin/company/estate-group?company_id=<?php echo $view->company_id;?>" class="btn-t-orange size-l">物件グループ設定</a></li>
						<?php if ($view->original::checkPlanCanUseTopOriginal($view->cms_plan) || $view->original::checkPlanCanUseTopOriginal($view->reserve_cms_plan)): ?>
							<li><a href="/admin/company/original-setting?company_id=<?php echo $view->company_id ?>" class="btn-t-orange size-l"><?php echo $view->original_setting_title; ?></a></li>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</ul>
		</div>
		<a href="/admin/company?search_id=<?php echo $view->company_id; ?>">検索結果一覧へ</a>
	</div>
</div>
@endsection
