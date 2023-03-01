<!DOCTYPE html>
<html lang="ja">
<head>
@include('admin::layouts.partials.head')
</head>
<body class="">

@include('admin::layouts.partials.header')


<!-- contents -->
<div id="contents">
	<div id="main">
		
		@yield('content')
		
		@include('admin::layouts.partials.footer')
	</div>
</div>
<!-- /contents -->

</body>
</html>