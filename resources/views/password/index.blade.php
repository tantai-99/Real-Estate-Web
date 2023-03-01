<?php

use Library\Custom\Model\Lists\CmsPlan;

?>
@extends('layouts.default')

@section('script')
<script>
	//<!--
	$(document).ready(function() {
		$('#contents').addClass("w-fix");
	});
	//-->
</script>
@endsection

@section('content')
<!-- メインコンテンツ：ページ作成 -->
<div class="main-contents account">
	<h1>アカウント設定</h1>
	<div class="main-contents-body">
		<!-- 基本設定 -->
		<form action="/default/password" method="post" name="form" id="form">
			@csrf
			<div class="section">
				<?php if (isset($view->params['regist_flg']) && $view->params['regist_flg'] == "true") : ?>
					<div class="alert-normal" id="regist_ok">パスワードが更新されました。</div>
				<?php endif; ?>

				<h2>パスワード再設定</h2>
				<div class="item-set-list is-require">
					<?php $view->form->form("id"); ?>
					<input type="hidden" name="change" value="change">
					<dl>
						<dt>
							<span>現在のパスワード</span>
						</dt>
						<dd>
							<input type="password" class="watch-input-count" name="password">
							<span class="input-count"><?php $view->form->getElement('password'); ?>0</span>
							<?php foreach ($view->form->getElements() as $name => $element) : ?>
								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php if ($name == "password") : ?><?php echo h($error) ?><?php endif; ?></p>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</dd>
					</dl>
					<dl>
						<dt>
							<span>新しいパスワード</span>
						</dt>
						<dd>
							<input type="password" class="watch-input-count" name="new_password">
							<span class="input-count">0</span>
							<?php foreach ($view->form->getElements() as $name => $element) : ?>
								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php if ($name == "new_password") : ?><?php echo h($error) ?><?php endif; ?></p>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</dd>
					</dl>
					<dl>
						<dt>
							<span>新しいパスワード（確認）</span>
						</dt>
						<dd>
							<input type="password" class="watch-input-count" name="re_new_password">
							<span class="input-count">0</span>
							<?php foreach ($view->form->getElements() as $name => $element) : ?>
								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php if ($name == "re_new_password") : ?><?php echo h($error) ?><?php endif; ?></p>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</dd>
					</dl>
				</div>
				<h2>利用プラン情報</h2>
				<div class="item-set-list">
					<dl>
						<dt>
							<span>利用プラン</span>
						</dt>
						<dd>
							<span><?= (new CmsPlan())->getCmsPLanNameByList($view->plan) ?></span>
						</dd>
					</dl>
					<dl>
						<dt>
							<span>利用開始日</span>
						</dt>
						<dd>
							<span><?= $view->start ?></span>
						</dd>
					</dl>
				</div>
				<div class="account-btn">
					<input type="submit" id="submit" class="btn-t-blue" name="submit" value="保存">
				</div>
			</div>
			<!-- /基本設定 -->
	</div>
</div>
<!-- /メインコンテンツ：ページ作成 -->
@endsection