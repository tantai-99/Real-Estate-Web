<meta charset="UTF-8">
<title>@if (trim($__env->yieldContent('title')))@yield('title') | @endif {{ $title }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="/css/normalize.css">
<link rel="stylesheet" href="/css/common.css?v=2020052700">
<link rel="stylesheet" href="/css/page-element.css?v=2015111900">
@yield('style')
@stack('style')

<?php $cmsini = getConfigs('cms') ?>
<?php if ($cmsini->header->mark->class) : ?>
	<?php if ($cmsini->header->mark->label === '検証HP2') : ?>
		<?php if (getInstanceUser('cms')->isCreator() || isCurrent(null, 'creator')) : ?>
			<link rel="shortcut icon" href="/images/common/favicon_testing2_creator.ico">
		<?php else : ?>
			<link rel="shortcut icon" href="/images/common/favicon_testing2_cms.ico">
		<?php endif; ?>
	<?php else : ?>
		<?php if (getInstanceUser('cms')->isCreator() || isCurrent(null, 'creator')) : ?>
			<link rel="shortcut icon" href="/images/common/favicon_<?php echo $cmsini->header->mark->class ?>_creator.ico">
		<?php else : ?>
			<link rel="shortcut icon" href="/images/common/favicon_<?php echo $cmsini->header->mark->class ?>_cms.ico">
		<?php endif; ?>
	<?php endif; ?>
<?php else : ?>
	<link rel="shortcut icon" href="/images/common/favicon.ico">
<?php endif; ?>

<script type="text/javascript" src="/js/libs/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="/js/libs/jquery.ah-placeholder.js"></script>
<script type="text/javascript" src="/js/libs/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="/js/app.js?v=2015111900"></script>
<script type="text/javascript" src="/js/common.js"></script>
@yield('script')
@stack('script')

<script type="text/javascript">
	(function() {
		/*@cc_on
		document.write('<link rel="stylesheet" href="/css/ie.css">');
		@*/
	})();
	<?php if (empty(getInstanceUser('cms')->getCurrentHp())) : ?>
		var has_reserve = "0";
	<?php else : ?>
		var has_reserve = "<?php echo getInstanceUser('cms')->getCurrentHp()->hasReserve() ? '1' : '0'; ?>";
	<?php endif; ?>
	window.onpageshow = function(event) {
		/* 
		 * event.persisted = true - The page is cached by the browser
		 * event.persisted = false - The page is NOT cached by the browser
		 */
		if (event.persisted) {
			window.location.reload();
		}
	};

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
</script>
