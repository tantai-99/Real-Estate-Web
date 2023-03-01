
<div id="g-header">
	<?php $cmsini = getConfigs('cms');?>
	@if ($cmsini->header->mark->class)
		@if($cmsini->header->mark->label === '検証HP2')
			<div class="h-mark testing2">{{ $cmsini->header->mark->label }}</div>
		@else
			<div class="h-mark {{ $cmsini->header->mark->class }}">{{ $cmsini->header->mark->label }}</div>
		@endif
	@endif
	<div class="h-logo"><img src="/images/common/logo_admin.png" alt=""></div>
	@if($profile = getInstanceUser('admin')->getProfile())
	<div style="position: relative;float: right;line-height: 1;margin: 15px 0 0;padding: 8px 0;">
		<span style="margin-right:30px;">ログイン者：{{ h($profile->name) }}</span>
		<a href="{{ route('admin.auth.logout') }}">ログアウト</a>
	</div>
	@endif
</div>
