
<meta charset="UTF-8">

<title>@yield('title') | ホームページ作成ツール</title>
<link rel="shortcut icon" type="image/x-icon" href="/images/favicon.ico">
<link rel="stylesheet" href="/css/normalize.css">
<link rel="stylesheet" href="/css/common.css">
<link rel="stylesheet" href="/css/page-element.css">
<?php $cmsini = getConfigs('cms');?>
@if ($cmsini->header->mark->class)
	@if($cmsini->header->mark->label === '検証HP2')
		<link rel="shortcut icon" href="/images/common/favicon_testing2_admin.ico">
	@else
		<link rel="shortcut icon" href="/images/common/favicon_{{ $cmsini->header->mark->class }}_admin.ico">
	@endif
@else
	<link rel="shortcut icon" href="/images/common/favicon.ico">
@endif

<script type="text/javascript" src="/js/libs/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="/js/libs/jquery.ah-placeholder.js"></script>
<script type="text/javascript" src="/js/libs/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
