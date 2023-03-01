<meta charset="UTF-8">
<title>@if (trim($__env->yieldContent('title')))@yield('title') | @endif {{ $title ?? '' }}</title>

<meta name="csrf-token" content="{{ csrf_token() }}" />
<link rel="stylesheet" href="/css/normalize.css">
<link rel="stylesheet" href="/css/common.css">
<link rel="stylesheet" href="/css/page-element.css">
@yield('style')
<?php $cmsini = getConfigs('cms') ?>
@if ($cmsini->header->mark->class)
@if($cmsini->header->mark->label === '検証HP2')
<link rel="shortcut icon" href="/images/common/favicon_testing2_admin.ico">
@else
<link rel="shortcut icon" href="/images/common/favicon_{{ $cmsini->header->mark->class }}_admin.ico">
@endif
@else
<link rel="shortcut icon" href="/images/common/favicon.ico">
<link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico">
@endif

<script type="text/javascript" src="/js/libs/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="/js/libs/jquery.ah-placeholder.js"></script>
<script type="text/javascript" src="/js/libs/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="/js/app.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/admin/common.js"></script>

@yield('script')

@yield('scripts')
<script type="text/javascript">
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
</script>