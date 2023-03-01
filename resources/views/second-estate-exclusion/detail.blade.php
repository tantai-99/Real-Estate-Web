@extends('layouts.default')
@section('title', __('2次広告除外設定'))
@section('script')
<script type="text/javascript" src="/js/app.second.estate.exclusion.js"> </script>
@endsection
@section('content')
<?php ?>
<div class="main-contents article-search">
	<h1>2次広告自動公開設定：物件取込み除外会社設定（除外選択）</h1>
	<div class="main-contents-body">
		<h2>物件取込み除外会社設定（除外選択）</h2>
		<p class="mb10"><b>下記の会社を2次広告自動公開の物件取込み除外対象にしてもよろしいですか？</b></p>

		<?php if(count($view->rows) > 0) : ?>
		<div class="section">
			<form action="/second-estate-exclusion/regist" method="post" name="form" id="form">
				@csrf
			<div id="targetId">
			<?php foreach ($view->params['kaiin_no'] as $key => $value) {
        		echo "<input type='hidden' value='". h($value) ."' id='". h($key) ."' name='kaiin_no[". h($value) ."]'>";
			} ?>
			</div>
			<input type='hidden' value="<?php if(isset($view->params['page']))  echo h($view->params['page'])?>" name='page'>
			<input type='hidden' value="<?php if(isset($view->params['BukkenShogo']))  echo h($view->params['BukkenShogo'])?>" name='BukkenShogo'>
			<input type='hidden' value="<?php if(isset($view->params['DaihyoTel']))  echo h($view->params['DaihyoTel'])?>" name='DaihyoTel'>
			<input type="hidden" name="search" value="sub">
			<input type='hidden' value="<?php if(isset($view->params['submit_name']))  echo h($view->params['submit_name'])?>" name='submit_name'>
			<input type='hidden' value="<?php if(isset($view->params['submit_phone']))  echo h($view->params['submit_phone'])?>" name='submit_phone'>

			<p class="mb10"><?php echo h(count($view->rows)); ?>件</p>
			<table class="tb-basic tb-checkbox">
				<thead>
					<tr>
						<th class="nowrap">商号</th>
						<th class="nowrap">所在地</th>
						<th class="nowrap">最寄駅</th>
						<th class="nowrap">TEL</th>
				</thead>
				<tbody>
					<?php foreach ($view->rows as $key => $val):?>
					<tr>
						<td><?php echo h($val['bukkenShogo'])?></td>
						<td>
							<?php if(isset($val['address']) && $val['address'] != "") echo h($val['address'])?>
						</td>
						<td>
							<?php if(isset($val['railLineName']) && $val['railLineName'] != "") echo h($val['railLineName'])?>
							<?php if(isset($val['stationName']) && $val['stationName'] != "") echo h($val['stationName'])?>
						</td>
						<td><?php echo h($val['daihyoTel'])?></td>
					<?php endforeach;?>
				</tbody>
			</table>
		</div>
		<div class="section btn-area">
			<input type="submit" class="btn-t-gray" name="back" id="back_btn" value="戻る">
			<input type="submit" class="btn-t-blue size-l" name="regist" id="regist_btn" value="選択した会社を除外設定にする">
		</div>
		</form>
		<?php endif; ?>
	</div>
</div>
@endsection
