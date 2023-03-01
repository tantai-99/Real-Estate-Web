<!doctype html>
<html lang="ja">
<head>
@include('layouts.partials.head')
</head>
<body>

<div id="g-header">
	<div class="h-logo"><img src="/images/common/logo.png" alt=""></div>
</div>

<!-- contents -->
<div id="contents">
	<div id="main">

		@yield('content')

		<div id="g-footer">
			<small>Copyright(C)At Home Co., Ltd.</small>
		</div>
	
	</div>
</div>
<!-- /contents -->

</body>
</html>