@extends('admin::layouts.default')

@section('title', __('CMSデータ削除-完了画面'))

@section('content')
		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>CMSデータ削除完了</h1>
			<div class="main-contents-body">

				<div style="text-align:center;">
					<a href="/admin/company/detail?id=<?php echo $view->params['company_id']; ?>" class="btn-t-blue">詳細に戻る</a>
					<a href="/admin/company?id=<?php echo $view->params['company_id']; ?>" class="btn-t-blue">一覧へ戻る</a>
				</div>
			</div>
		</div>
@endsection