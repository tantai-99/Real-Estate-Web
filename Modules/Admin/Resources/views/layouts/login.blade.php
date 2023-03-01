<!DOCTYPE html>
<html lang="ja">
<head>
@include('admin::layouts.partials.login_head')
</head>
<body class="login-top">

@include('admin::layouts.partials.login_header')

<!-- contents -->
<div id="contents">
	<div id="main">
        @yield('content')

		<div id="g-footer">
			<small>Copyright(C)At Home Co., Ltd.</small>
			<!-- <a href="#g-header">�y�[�W�̐擪��</a> -->
		</div>
	</div>
</div>
<!-- /contents -->
</body>
</html>
