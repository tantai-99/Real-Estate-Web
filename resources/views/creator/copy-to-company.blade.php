@extends('layouts.default')

@section('title', __('代行更新'))

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
<?php //ATHOME_HP_DEV-4426: Add script road after clone data ?>
function cloneDataReload() {
    app.modal.alert('', '操作が完了しました。', function () {
        location.reload();
    });
}
</script>
@endsection
@section('content')
<!-- メインコンテンツ -->
<div class="main-contents publish">
	<h1>代行更新</h1>
	<div class="main-contents-body">
		<?php if($hasReserve = getInstanceUser('cms')->getProfile()->getCurrentHp()->hasReserve()):?>
		<div class="alert-strong">会員サイトに日時指定で予約されているページがあります。<br>
			日時指定で予約されたページがある状態で代行更新をすることはできません。<br>
			予約を解除していただくよう会員ご担当者さまにご連絡ください。</div>
		<?php endif;?>
        <?php //ATHOME_HP_DEV-4426: Add iframe clone data ?>
		<form action="<?php echo $view->route('api-copy-to-company')?>" method="post" target="cloneIframe">
			@csrf
			<div class="section">
				<h2>下記会員の会員側データを、代行作成データに更新します。</h2>
				
				@include('_deputize-info')
				
				<div class="publish-btn">
					<input type="submit" value="代行更新" class="btn-t-blue size-l<?php if($hasReserve):?> is-disable<?php endif;?>">
				</div>
			</div>
		</form>
        <?php //ATHOME_HP_DEV-4426: Add iframe clone data ?>
        <iframe name="cloneIframe" style="width:100%;border: solid 1px #a0a0a0;margin: 3px; display:block;"></iframe>
	</div>
</div>
<!-- /メインコンテンツ -->
@endsection
