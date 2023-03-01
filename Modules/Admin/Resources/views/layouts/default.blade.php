<!DOCTYPE html>
<html lang="ja">
<head>
@include('admin::layouts.partials.head')
</head>
<body class="column2">

@include('admin::layouts.partials.header')


    <?php
    $profile = getInstanceUser('admin')->getProfile(); 
    $requestInfo = getRequestInfo();
    ?>


<!-- contents -->
<div id="contents">
<div id="side">
		<div class="inner">
			<ul class="side-menu">

				@if($profile->privilege_edit_flg == 1)
				<li<?php if($requestInfo['controller'] == "company") : ?> class="is-active"<?php endif; ?>><a href="/admin/company/">契約管理</a></li>
				@endif

				@if($profile->privilege_manage_flg == 1)
				<li<?php if($requestInfo['controller'] == "account") : ?> class="is-active"<?php endif; ?>><a href="/admin/account/">アカウント管理</a></li>
				@endif

				<li @if($requestInfo['controller'] == "password") class="is-active" @endif><a href="/admin/password/">パスワード変更</a></li>
				<li @if($requestInfo['controller'] == "log") class="is-active" @endif><a href="/admin/log/">ログ管理</a></li>
				<li @if($requestInfo['controller'] == "information") class="is-active" @endif><a href="/admin/information/">お知らせ管理</a></li>
				<li @if($requestInfo['controller'] == "spam-block") class="is-active" @endif><a href="/admin/spamblock/">迷惑メール条件管理</a></li>
			</ul>
		</div>
	</div>

	<div id="main">
		<ul id="topicpath">
			@foreach ($view->topicPath()->getTopics() as $topic)
				<li @if($topic['is_last']) class="last" @endif>@if($topic['is_link'])<a href="{{ h($topic['link']) }}">@endif<?php echo $topic['title'] ?>@if($topic['is_link'])</a>@endif</li>
			@endforeach
		</ul>
		
		@yield('content')
		
		@include('admin::layouts.partials.footer')
	</div>
</div>
<!-- /contents -->

</body>
</html>