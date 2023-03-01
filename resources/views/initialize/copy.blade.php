@extends('layouts.login-deputize')

@section('content')
<?php
    $agency = !getInstanceUser('cms')->isAgency();
?>
<!-- メインコンテンツ -->
<div class="main-contents account">
	<h1><?php if ($agency): ?>代行作成<?php else: ?>制作代行<?php endif; ?>コピー</h1>
	<div class="main-contents-body">
		<form action="{{route('default.copy')}}" method="post" target="copyIframe">
			@csrf
			<div class="section">
				<h2><?php if ($agency): ?>代行作成<?php else: ?>制作代行<?php endif; ?>する会員データのコピーを作成しましょう</h2>
			
				@include('_deputize-info')
				
			</div>
			<div class="btns-center">
				<a class="btn-t-gray size-l" href="<?php echo $view->route('re-select-company', 'creator')?>">戻る</a>
				<input type="submit" value="コピーする（データ取得）" class="btn-t-blue size-l" onclick="$(this).addClass('is-disable');return true;">
			</div>
		</form>
        <iframe name="copyIframe" style="width:100%;border: solid 1px #a0a0a0;margin: 3px; display:block;"></iframe>
	</div>
</div>
<!-- /メインコンテンツ -->
@endsection
