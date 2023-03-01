@extends('admin::layouts.default')

@section('title', __('パスワード変更'))

@section('content')
	<script type="text/javascript" src="/js/admin/password.js"></script>

		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>パスワード変更</h1>
			<div class="main-contents-body">
				<div class="section">

					<?php if(isset($view->params['regist_flg']) && $view->params['regist_flg'] == "true") : ?>
					<div class="alert-normal" id="regist_ok">パスワードが更新されました。</div>
					<?php endif; ?>

					<table class="form-basic">
					<tr>
						<th>担当者名</th>
						<td><?php echo $view->manager['name']; ?></td>
					</tr>
					<tr>
						<th>ログインID</th>
						<td><?php echo $view->manager['login_id']; ?></td>
					</tr>
					<tr>
						<th>パスワード</th>
						<td>**********</td>
					</tr>
					<tr>
						<th>権限</th>
						<td>
							<?php
							$strs = array();
							if($view->manager['privilege_edit_flg'] == "1")  $strs[] = "修正権限";
							if($view->manager['privilege_manage_flg'] == "1")  $strs[] = "管理権限";
							if($view->manager['privilege_create_flg'] == "1")  $strs[] = "代行作成権限";
							if($view->manager['privilege_open_flg'] == "1")  $strs[] = "代行更新権限";
							print(implode(" / ", $strs));
							?>
						</td>
					</tr>
					</table>
				</div>

				<form action="/admin/password" method="post" name="form" id="form">
					@csrf
				<div class="section">
					<div class="alert-normal" style="background-color: #FFFFFF;">新しいパスワードを設定してください。</div>
					<?php $view->form->form("id"); ?>
					<input type="hidden" name="change" value="change">
					<table class="form-basic">
					<?php foreach ($view->form->getElements() as $name => $element):?>
					<?php if($element->getType() == "hidden") continue; ?>
					<tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
						<th><span><?php echo $element->getLabel()?></span></th>
						<td>
							<?php $view->form->form($name);?>
							<?php if($element->getDescription() != "") : ?>
							<br />
							<span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
							<?php endif; ?>
							<?php foreach ($element->getMessages() as $error):?>
							<p style="color:red;"><?php echo $error ?></p>
							<?php endforeach;?>
						</td>
					</tr>
					<?php endforeach;?>
					<tr>
						<td colspan="2" style="text-align:center;padding:10px;"><button type="submit" id="button" class="btn-t-blue" name="button" value="button">登録</button>
						</td>
					</tr>
					</table>
				</div>
				</form>
			</div>
		</div>
@endsection
	