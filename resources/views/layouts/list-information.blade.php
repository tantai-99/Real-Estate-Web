<!DOCTYPE html>
<html lang="ja">
<head>
@include('layouts.partials.head')
</head>
<body class="login-top">
@include('layouts.partials.header')


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