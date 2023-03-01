<!DOCTYPE html>
<html lang="ja">
<head>
@include('layouts.partials.head')
</head>
<body>
@if (getUser() instanceof Library\Custom\User\Agency)
    @include('layouts.partials.header-agency')
@else
	@include('layouts.partials.header')
@endif
<!-- contents -->
<div id="contents" class="w-fix">
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