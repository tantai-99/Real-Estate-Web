@extends('admin::layouts.default')

@section('title', __('迷惑メール条件' . $view->actionName . '完了'))

@section('content')
<div class="main-contents">
	<h1>迷惑メール条件<?php echo $view->actionName ?>完了</h1>
	<div class="main-contents-body">
		<a href="/admin/spamblock">一覧に戻る</a>
	</div>
</div>
@endsection
