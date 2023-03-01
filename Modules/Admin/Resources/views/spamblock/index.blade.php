@extends('admin::layouts.default')

@section('title', __('迷惑メール条件管理'))

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>迷惑メール条件管理</h1>
	<div class="main-contents-body">
		<div style="text-align:right;margin-top:-70px;">
			<a href="/admin/spamblock/edit" class="btn-t-blue">新規作成</a>
		</div>

		<div class="section" style="margin-top:40px;">
			<h2 id="search_title">検索</h2>
			<form action="/admin/spamblock" name="search" method="post">
			@csrf
				<table class="form-basic" id="search_area">
					<?php foreach ($view->searchForm->getElements() as $name => $element) : ?>
						<tr>
							<th style="width:120px;"><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php $view->searchForm->form($name); ?>
								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php echo h($error) ?></p>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach ?>
					<td colspan="4" style="text-align:center;">
						<input type="submit" value="検索" class="btn-t-blue">
					</td>
				</table>
			</form>
		</div>

		<div class="section detail-list">
			<table class="tb-basic">
				<thead>
				<tr>
					<th>対象</th>
					<th>メールアドレス</th>
					<th>電話番号</th>
					<th></th>
				</tr>
				</thead>
				<?php foreach ($view->spamBlocks as $spamBlock) : ?>
					<tr>
						<td><?php echo $spamBlock['range_option'] === 0 ? '全会員' : '特定の会員' ?></td>
						<td><?php echo $spamBlock['email'] ?></td>
						<td><?php echo $spamBlock['tel'] ?></td>
						<td>
							<a href="/admin/spamblock/edit?id=<?php echo $spamBlock['id']; ?>" class="btn-t-gray size-s">編集</a>
							<a href="/admin/spamblock/delete?id=<?php echo $spamBlock['id']; ?>" class="btn-t-gray size-s">削除</a>
						</td>
					</tr>
				<?php endforeach ?>
			</table>
		</div>
	</div>
</div>
@endsection
