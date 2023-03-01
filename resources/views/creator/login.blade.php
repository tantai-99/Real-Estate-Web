@extends('layouts.login')

@section('content')
<!-- メインコンテンツ -->
<div class="main-contents login">
	<h1>ログイン</h1>
	<div class="main-contents-body">
		<?php if(app('request')->r==1 && getInstanceUser('cms')->isSessionTimeout()):?>
		<div class="alert-strong">セッションタイムアウト<br>60分間無操作状態が続いたため、自動的にログアウトしました。</div>
		<?php endif; ?>
		
		<form action="{{route('creator.login')}}" method="post">
			@csrf
			<div class="login-input">
				<?php foreach ($view->form->getElements() as $name => $element):?>
					<?php $view->form->form($name)?>
					<?php foreach ($element->getMessages() as $error):?>
						<p class="error"><?php echo h($error)?></p>
					<?php endforeach;?>
				<?php endforeach;?>
			</div>
			<div class="login-btn">
				<input type="submit" value="ログイン" class="btn-t-blue">
			</div>
		</form>
	</div>
</div>
<!-- /メインコンテンツ -->
@endsection