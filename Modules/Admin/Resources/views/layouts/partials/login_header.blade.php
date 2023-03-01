<div id="g-header">
    <?php $cmsini = getConfigs('cms');?>
    @if ($cmsini->header->mark->class)
		@if($cmsini->header->mark->label === '検証HP2')
			<div class="h-mark testing2">{{ $cmsini->header->mark->label }}</div>
		@else
			<div class="h-mark {{ $cmsini->header->mark->class }}">{{ $cmsini->header->mark->label }}</div>
		@endif
	@endif
	<div class="h-logo">
		<img src="/images/common/logo_admin.png" alt="">
	</div>
</div>
