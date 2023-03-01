@extends('admin::layouts.default')

@section('title', __('2次広告自動公開設定確認'))

@section('style')
	<link href='/js/libs/themes/blue/style.css' media="screen" rel="stylesheet" type="text/css">
	<link href='/js/libs/themes/jquery-ui/jquery-ui.min.css' media="screen" rel="stylesheet" type="text/css">
@stop

@section('script')
	<script type="text/javascript"  src="/js/libs/jquery-ui.min.js"></script>
	<script type="text/javascript"  src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
	<script type="text/javascript"  src="/js/admin/second_estate_edit.js"></script>
@stop

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>2次広告自動公開設定完了</h1>
	<div class="btn-back-pageright">
		<a href="/admin/company" class="btn-t-gray">戻る</a>
	</div>
	<div class="main-contents-body">
		<div class="btn-agreement">
			<ul>
				<input type="hidden" id="company_id" name="company_id" value="<?php echo $view->params['company_id'];?>">
		
				<li><a href="/admin/company/edit?id=<?php echo $view->params['company_id'];?>" class="btn-t-blue size-l">契約者詳細編集</a></li>
				<li><a href="/admin/company/private?company_id=<?php echo $view->params['company_id'];?>" class="btn-t-blue size-l">非公開設定</a></li>
				<li><a href="/admin/company/tag?company_id=<?php echo $view->params['company_id'];?>" class="btn-t-blue size-l"><?php echo $view->original_tag; ?></a></li>
				<li><a href="/admin/company/group?company_id=<?php echo $view->params['company_id'];?>" class="btn-t-blue size-l">グループ会社設定</a></li>
				<?php if($view->contract_type == 0) : ?>
					<?php
						if (
							($view->cms_plan == config('constants.cms_plan.CMS_PLAN_STANDARD')) ||
							($view->reserve_cms_plan == config('constants.cms_plan.CMS_PLAN_STANDARD'))
						) :
					?>
						<li><a href="/admin/map-option/edit?id=<?php               echo $view->params['company_id'];?>" class="btn-t-orange size-l">地図検索</a></li>
					<?php endif; ?>
					<?php
						if (!($view->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE')) || (!($view->reserve_cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') ) && $view->reserve_cms_plan > 0)) :
					?>
						<li><a href="/admin/company/second-estate?company_id=<?php echo $view->params['company_id'];?>" class="btn-t-orange size-l">2次広告自動公開設定</a></li>
						<li><a href="/admin/company/estate-group?company_id=<?php echo $view->params['company_id'];?>" class="btn-t-orange size-l">物件グループ設定</a></li>
						<?php if (Library\Custom\Model\Lists\Original::checkPlanCanUseTopOriginal($view->cms_plan) || Library\Custom\Model\Lists\Original::checkPlanCanUseTopOriginal($view->reserve_cms_plan)): ?>
							<li><a href="/admin/company/original-setting?company_id=<?php echo $view->params['company_id'] ?>" class="btn-t-orange size-l"><?php echo $view->original_setting_title; ?></a></li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if (isset($view->original_plan) && $view->original_plan) :?>
						<li><a href="/admin/company/original-edit?company_id=<?php echo $view->params['company_id'] ?>" class="btn-t-orange size-l"><?php echo $view->original_edit_title; ?></a></li>
					<?php endif; ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
@endsection

