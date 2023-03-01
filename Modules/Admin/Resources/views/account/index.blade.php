@extends('admin::layouts.default')

@section('title', __('アカウント管理'))

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>アカウント管理</h1>
	<div class="main-contents-body">
		<div style="text-align:right;margin-top:-70px;">
			<a href="/admin/account/edit" class="btn-t-blue">新規作成</a>
		</div>

		<div class="main-contents-body">
			<div class="section">
				<h2 id="search_title">検索</h2>
				<form action="/admin/account" name="search" method="post">
					@csrf
					<table class="form-basic" id="search_area">
						<tr>
							<th style="width:100px;"><span>担当者名</span></th>
							<td>
								<input type="text" style="width:80%" ; name="<?php echo $view->search_form->getElement('name')->getName(); ?>" value="<?php echo h($view->search_form->getElement('name')->getValue()); ?>">

							</td>
							<th style="width:100px;"><span>ログインID</span></th>
							<td>
								<input type="text" style="width:80%" ; name="<?php echo $view->search_form->getElement('login_id')->getName(); ?>" value="<?php echo h($view->search_form->getElement('login_id')->getValue()); ?>">
							</td>
						</tr>
						<tr>
							<th style="width:100px;"><span>権限</span></th>
							<td colspan="3">
								<?php $view->search_form->form("privilege_edit_flg"); ?>
								<?php $view->search_form->form("privilege_manage_flg"); ?>
								<?php $view->search_form->form("privilege_create_flg"); ?>
								<?php $view->search_form->form("privilege_open_flg"); ?>
							</td>
						</tr>
						<tr>
							<td colspan="4" style="text-align:center;">
								<input type="submit" name="search" value="検索" class="btn-t-blue">
							</td>
						</tr>
					</table>
				</form>
			</div>

			<div class="section">
				<table class="tb-basic">
					<thead>
						<tr>
							<th>担当者名</th>
							<th>ログインID</th>
							<th>修正権限</th>
							<th>管理権限</th>
							<th>代行作成権限</th>
							<th>代行更新権限</th>
							<th></th>
						</tr>
					</thead>
					<?php foreach ($view->managers as $key => $val) : ?>
						<tr>
							<td><?php echo h($val->name); ?></td>
							<td><?php echo h($val->login_id); ?></td>
							<td><?php if ($val->privilege_edit_flg == "1") : ?>有り<?php else : ?>無し<?php endif; ?></td>
							<td><?php if ($val->privilege_manage_flg == "1") : ?>有り<?php else : ?>無し<?php endif; ?></td>
							<td><?php if ($val->privilege_create_flg == "1") : ?>有り<?php else : ?>無し<?php endif; ?></td>
							<td><?php if ($val->privilege_open_flg == "1") : ?>有り<?php else : ?>無し<?php endif; ?></td>
							<td><a href="/admin/account/edit/?id=<?php echo h($val->id); ?>" class="btn-t-gray size-s">編集</a></td>
						</tr>
					<?php endforeach; ?>
				</table>
				{{$view->managers->links('admin::paginating',['search_param' => $view->search_param])}}
			</div>
		</div>
	</div>
</div>
@endsection