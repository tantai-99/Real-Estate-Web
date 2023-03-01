@extends('admin::layouts.default')

@section('title', __('お知らせ管理'))

@section('style')
<link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css">
<link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">
@stop
@section('scripts')
<script type="text/javascript" src="/js/libs/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
<script type="text/javascript" src="/js/admin/information_search.js"></script>
@stop

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>お知らせ管理</h1>
	<div class="main-contents-body">
		<div style="text-align:right;margin-top:-70px;">
			<a href="/admin/information/edit" class="btn-t-blue">新規作成</a>
		</div>

		<div class="main-contents-body">
			<div class="section">
				<h2 id="search_title">検索</h2>
				<form action="/admin/information" name="search" method="post">
					@csrf
					<table class="form-basic" id="search_area">
						<tr>
							<th style="width:80px;"><span>タイトル</span></th>
							<td>
								<input type="text" style="width:90%;" name="<?php echo $view->search_form->getElement('title')->getName(); ?>" value="<?php echo h($view->search_form->getElement('title')->getValue()); ?>">
							</td>
							<th style="width:80px;"><span>公開区分</span></th>
							<td style="width:40%;">
								<?php $view->search_form->form("display_page_code"); ?>
							</td>
						</tr>
						<tr>
							<th style="width:80px;"><span>公開日</span></th>
							<td colspan="3" style="width:300px;">
								<?php echo $view->search_form->form("start_date"); ?> ～ <?php echo $view->search_form->form("end_date"); ?> <br />
								<span style="font-size:10px;color:#848484"><?php echo $view->search_form->getElement('start_date')->getDescription(); ?></span>
								<?php foreach ($view->search_form->getElement('start_date')->getMessages() as $error) : ?>
									<p class="errors"><?php echo h($error) ?></p>
								<?php endforeach; ?>
								<?php foreach ($view->search_form->getElement('end_date')->getMessages() as $error) : ?>
									<p class="errors"><?php echo h($error) ?></p>
								<?php endforeach; ?>
							</td>
						</tr>
						<td colspan="4" style="text-align:center;">
							<input type="submit" name="search" value="検索" class="btn-t-blue">
						</td>
						<tr>
						</tr>
					</table>
				</form>
			</div>

			<div class="section">
				<table class="tb-basic">
					<thead>
						<tr>
							<th>お知らせID</th>
							<th>タイトル</th>
							<th>公開区分</th>
							<!--							<th>表示方法</th>-->
							<th></th>
							<th>公開開始日</th>
							<th>公開終了日</th>
							<th></th>
						</tr>
					</thead>
					<?php foreach ($view->information as $key => $val) : ?>
						<tr>
							<td style="text-align:center;"><?php echo h($val->id); ?></td>
							<td><?php echo mb_strimwidth(h($val->title), 0, 30, "..."); ?></td>
							<td><?php if (isset($view->display_page_codes[h($val->display_page_code)])) echo $view->display_page_codes[h($val->display_page_code)]; ?></td>
							<!--							<td><?php if (isset($view->display_type_codes[h($val->display_type_code)])) echo $view->display_type_codes[h($val->display_type_code)]; ?></td>-->
							<td><?php if ($val->important_flg == 1) echo "重要"; ?></td>
							<td><?php echo str_replace("-", "/", substr(h($val->start_date), 0, 10)); ?></td>
							<td><?php if ($val->end_date != "0000-00-00 00:00:00") echo str_replace("-", "/", substr(h($val->end_date), 0, 10)); ?></td>
							<td style="text-align:center;"><a href="/admin/information/edit/?id=<?php echo h($val->id); ?>" class="btn-t-gray size-s">編集</a></td>
						</tr>
					<?php endforeach; ?>

				</table>

				{{$view->information->links('admin::paginating',['search_param' => $view->search_param])}}
			</div>
		</div>
	</div>
</div>
@endsection