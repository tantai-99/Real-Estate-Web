<!DOCTYPE html>
<html lang="ja">

<head>
	@include('layouts.partials.head')
</head>
<?php

use Library\Custom\Model\Lists\CmsPlan;

$cmpCmsPlanObj = new CmsPlan();
$controllerName = getControllerName();
$sideTemplate = 'layouts/partials/side/' . $controllerName . '.blade.php';
$hasSide = @file_exists(config('view.paths')[0] . '/' . $sideTemplate);
?>

<body class="<?php if ($controllerName == 'index') : ?>home <?php endif ?><?php if ($hasSide) : ?>column2<?php endif ?>">

	@include('layouts.partials.header')
	<!-- g-navi -->
	@include('layouts.partials.global_nav')
	<!-- /g-navi -->

	<!-- contents -->
	<div id="contents">

		@if($hasSide)
		@include('layouts.partials.side.'. $controllerName )
		@endif

		<div id="main">
			<?php if (!isCurrent('index', 'index')) : ?>
				<ul id="topicpath">
					@foreach ($view->topicPath()->getTopics() as $topic)
					<li @if ($topic['is_last']) class="last" @endif;>@if ($topic['is_link']) <a href="{{ h($topic['link']) }}">@endif <?php echo $topic['title'] ?> @if ($topic['is_link']) </a>@endif</li>
					@endforeach
				</ul>
				
			<?php endif; ?>

			@yield('content')

			@include('layouts.partials.footer')
		</div>
	</div>
	<!-- /contents -->

</body>

</html>