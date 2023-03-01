@extends('layouts.default')

@section('title', __('ロールバック'))

@section('script')
<script type="text/javascript">
	$(function() {
		'use strict';

		app.initApiForm($('form'), $('input[type="submit"]'), function(data) {
			app.modal.alert('', '操作が完了しました。', function() {
				location.href = data.redirectTo;
			});
		});
	});
</script>
@endsection
<!-- メインコンテンツ -->
@section('content')
<div class="main-contents publish">
	<h1>ロールバック</h1>
	<div class="main-contents-body">
		<form data-api-action="<?php echo route('api-rollback') ?>" method="post">
			<div class="section">
				<h2>下記会員の会員側データを、代行更新前の状態に戻します。</h2>

					@include('_deputize-info')

				<div class="publish-btn">
					<input type="submit" value="ロールバック" class="btn-t-blue size-l">
				</div>
			</div>
		</form>
	</div>
</div>
@endsection




<!-- /メインコンテンツ -->