@extends('admin::layouts.default')

@section('title', $view->original_tag . '-完了')

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1><?php echo $view->original_tag; ?>完了</h1>
	<div class="main-contents-body">


		<div style="text-align:center;margin-top:10px;">
			<a href="/admin/company/detail?id=<?php echo $view->params['company_id']; ?>" class="btn-t-blue">詳細へ戻る</a>
			<a href="/admin/company" class="btn-t-blue">一覧へ</a>
		</div>


	</div>
</div>
@endsection