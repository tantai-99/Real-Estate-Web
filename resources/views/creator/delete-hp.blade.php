@extends('layouts.default')
@section('script')
<script type="text/javascript">
	$(function() {
		'use strict';
		
		app.initApiForm($('form'), $('input[type="submit"]'), function (data) {
			app.modal.alert('', '操作が完了しました。', function () {
				location.href = data.redirectTo;
			});
		});
	});
</script>
@stop
@section('title') 制作代行サイト削除 @stop
<!-- メインコンテンツ -->
@section('content')
<div class="main-contents publish">
	<h1>制作代行サイト削除</h1>
	<div class="main-contents-body">
		<form data-api-action="<?php echo $view->route('api-delete-hp','creator')?>" method="post">
			@csrf
			<div class="section">
				<h2>下記会員の代行作成データを削除します。</h2>
				
				@include('_deputize-info')
				
				<div class="publish-btn">
					<input type="submit" value="削除" class="btn-t-blue size-l">
				</div>
			</div>
		</form>
	</div>
</div>




<!-- /メインコンテンツ -->
@endsection