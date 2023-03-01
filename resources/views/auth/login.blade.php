@extends('layouts.login')

@section('content')
<!-- メインコンテンツ -->
<div class="main-contents login">
	<h1>ログイン</h1>
	<div class="main-contents-body">
		<?php if(app('request')->r==1 && getInstanceUser('cms')->isSessionTimeout()):?>
		<div class="alert-strong">セッションタイムアウト<br>60分間無操作状態が続いたため、自動的にログアウトしました。</div>
		<?php endif;?>
		<form action="{{route('default.auth.login')}}" method="post">
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
		<a href="/static/forgot_password.html" class="i-s-link">パスワードを忘れた方はこちら</a>
	</div>
</div>

<div class="main-contents info">
	<h2>アットホームからのお知らせ
		<?php if($view->count > 5) : ?>
			<a href="/auth/list" class="i-s-link">お知らせをすべて見る</a>
		<?php endif; ?>
	</h2>
	<div class="main-contents-body <?php if($view->count==0) echo 'is-empty' ; ?>">
		<?php if($view->count >= 1) : ?>
		<ul>
		<?php foreach($view->information as $key => $val) : ?>
			<li>
				<div class="info-l">
					<span><?php echo date('Y年m月d日', strtotime($val['start_date'])); ?></span>
				</div>
				<div class="info-r">
					<p><a href="/auth/detail/?id=<?php echo h($val['id']); ?>&back=top"><?php echo h($val['title']); ?></a></p>
				</div>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php else: ?>
			<p>現在お知らせはありません。</p>
		<?php endif; ?>
	</div>
</div>
@stop




<!-- /メインコンテンツ -->
