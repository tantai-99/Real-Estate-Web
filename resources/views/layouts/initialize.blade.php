<!DOCTYPE html>
<html lang="ja">
<head>
@include('layouts.partials.head')
</head>
<body class="first">

@include('layouts.partials.header')


<!-- contents -->
<div id="contents" class="w-fix first">
	<div id="main">

		@yield('content')

		<div id="g-footer">
			<small>Copyright(C)At Home Co., Ltd.</small>
			<a href="#g-header">ページの先頭へ</a>
		</div>
	
	</div>
</div>
<!-- /contents -->

</body>
</html>