@extends('layouts.default')
@section('title', __('2次広告除外設定'))
@section('script')
<script type="text/javascript" src="/js/app.second.estate.exclusion.js"> </script>
@endsection
@section('content')
<div class="main-contents article-search">
	<h1>2次広告自動公開設定：物件取込み除外会社設定（除外選択）</h1>
	<div class="main-contents-body">
		<h2>物件取込み除外会社設定（除外選択）</h2>

		<form action="/second-estate-exclusion/search" method="post" name="search_form" id="search_form">
			@csrf
		<input type="hidden" name="search" value="sub">
        <div class="section special-remove-create">
			<div class="company-search-field clearfix">
	            <p>2次広告自動公開の物件取込み除外する会社を下記の会社名か電話番号から検索してください</p>
	            <div>
	              <table class="tb-basic">
	                <thead>
	                  <tr>
	                    <th class="alL">会社名で探す</th>
	                  </tr>
	                </thead>
	                <tbody>
	                  <tr>
	                    <td class="alL">
	                      <p>会社名を入力してください（部分一致）</p>
	                      <?php if(isset($view->params['submit_name']) && $view->params['submit_name'] != "" && 
			                      isset($view->params['BukkenShogo']) && $view->params['BukkenShogo'] != "" && 
	    		                  count($view->rows) == 0) : ?>
	                      <p class="alert-strong">会社が見つかりません。<br>会社名をご確認の上、再度ご入力ください。</p>
		                  <?php endif;?>
	                      <input type="search" name="BukkenShogo" value="<?php if(isset($view->params['BukkenShogo'])) echo h($view->params['BukkenShogo'])?>" placeholder="アットホーム">
	                      <input type="hidden" id="input_name" value="<?php if(isset($view->params['BukkenShogo'])) echo h($view->params['BukkenShogo'])?>">
	                      <input type="hidden" id="submit_name" name="submit_name">
						  <a href="javascript:void(0);" class="btn-t-gray size-s update-setting-btn" data="type1">検索</a>

	                    </td>
	                  </tr>
	                </tbody>
	              </table>
	            </div>
	            <div>
	              <table class="tb-basic">
	                <thead>
	                  <tr>
	                    <th class="alL">電話番号で探す</th>
	                  </tr>
	                </thead>
	                <tbody>
	                  <tr>
	                    <td class="alL">
	                      <p>半角でハイフンは不要</p>
	                      <?php if(isset($view->params['submit_phone']) && $view->params['submit_phone'] != "" && 
			                      isset($view->params['DaihyoTel']) && $view->params['DaihyoTel'] != "" && 
			                      count($view->rows) == 0) : ?>
	                      <p class="alert-strong">該当の電話番号の会社が見つかりません。<br>半角でハイフンを入れずご入力ください。</p>
		                  <?php endif;?>
	                      <input type="search" name="DaihyoTel" value="<?php if(isset($view->params['DaihyoTel']))  echo h($view->params['DaihyoTel'])?>" placeholder="例：0300000000">
	                      <input type="hidden" id="input_phone" value="<?php if(isset($view->params['DaihyoTel'])) echo h($view->params['DaihyoTel'])?>">
	                      <input type="hidden" id="submit_phone" name="submit_phone">
						  <a href="javascript:void(0);" class="btn-t-gray size-s update-setting-btn" data="type2">検索</a>
	                    </td>
	                  </tr>
	                </tbody>
	              </table>
	            </div>
	        </div>
        </div>
		</form>

		<?php if(count($view->rows) > 0) : ?>
		<h2>2次広告自動公開の物件取込み除外する会社を選択して下さい</h2>
		<div class="section">
			<form action="/second-estate-exclusion/detail" method="post" name="form" id="form">
				@csrf
			<div id="targetId">
			<?php 
			if(isset($view->params['kaiin_no']) && is_array($view->params['kaiin_no'])) {
				foreach ($view->params['kaiin_no'] as $key => $value) {
	        		echo "<input type='hidden' value='". h($value) ."' id='". h($key) ."' name='kaiin_no[". h($value) ."]'>";
				} 				
			}
			?>
			</div>
			<input type='hidden' value="<?php if(isset($view->params['page']))  echo h($view->params['page'])?>" name='page'>
			<input type='hidden' value="<?php if(isset($view->params['BukkenShogo']))  echo h($view->params['BukkenShogo'])?>" name='BukkenShogo'>
			<input type='hidden' value="<?php if(isset($view->params['DaihyoTel']))  echo h($view->params['DaihyoTel'])?>" name='DaihyoTel'>
			<input type='hidden' value="<?php if(isset($view->params['submit_name']))  echo h($view->params['submit_name'])?>" name='submit_name'>
			<input type='hidden' value="<?php if(isset($view->params['submit_phone']))  echo h($view->params['submit_phone'])?>" name='submit_phone'>
			</form>
			<p class="mb10"><?php echo h($view->total_count); ?>件中　<?php echo h($view->now_count_first); ?>件〜<?php echo h($view->now_count_last); ?>件を表示　　※灰色の枠は除外設定済みの会社になります。</p>
			<table class="tb-basic tb-checkbox">
				<thead>
					<tr>
						<th class="nowrap">選択</th>
						<th class="nowrap">商号</th>
						<th class="nowrap">所在地</th>
						<th class="nowrap">最寄駅</th>
						<th class="nowrap">TEL</th>
				</thead>
				<tbody>
					<?php foreach ($view->rows as $key => $val):?>
					<tr<?php if(isset($view->exclusion[$val['kaiinNo']])) : ?> style="background-color: gray;"<?php endif;?>>
						<td style="text-align: center;">
						<?php if(!isset($view->exclusion[$val['kaiinNo']])) : ?>
						<input type="checkbox" class="add_no" 
								name="kaiin_no[<?php echo $val['kaiinNo'];?>]" id="<?php echo h($val['kaiinNo'])?>" value="<?php echo h($val['kaiinNo'])?>"
								<?php if(isset($view->params["kaiin_no"][$val['kaiinNo']])) : ?> checked<?php endif;?>>
						<?php endif;?>
						</td>
						<td><?php echo h($val['bukkenShogo'])?></td>
						<td>
							<?php if(isset($val['todofukenName']) && $val['todofukenName'] != "") echo h($val['todofukenName']) ?>
							<?php if(isset($val['cityName']) && $val['cityName'] != "") echo h($val['cityName'])?>
							<?php if(isset($val['townName']) && $val['townName'] != "") echo h($val['townName'])?>
							<?php if(isset($val['banchi']) && $val['banchi'] != "") echo h($val['banchi'])?>
							<?php if(isset($val['buildingName']) && $val['buildingName'] != "") echo h($val['buildingName'])?>
						</td>
						<td>
							<?php if(isset($val['railLineName']) && $val['railLineName'] != "") echo h($val['railLineName'])?>
							<?php if(isset($val['stationName']) && $val['stationName'] != "") echo h($val['stationName'])?>
						</td>
						<td class="nowrap"><?php echo h($val['daihyoTel'])?></td>
					<?php endforeach;?>
				</tbody>
			</table>
		</div>
		<?php if($view->paginator) echo $view->paginator->links('pagination',['search_param' => $view->search_param]) ?>

		<div class="section btn-area">
			<a href="/second-estate-exclusion" class="btn-t-gray" name="back" id="back_btn">除外会社一覧画面に戻る</a>
			<input type="button" class="btn-t-gray size-l" name="regist" id="detail_btn" value="選択した会社を追加する">
		</div>
		<?php endif; ?>
	</div>
</div>


<?php if(isset($view->params['r']) && $view->params['r'] == true) :?>
<!-- modal -->
<div id="modal">
	<div class="modal-set">
		<div class="modal-contents-wrap">
			<div class="modal-contents">
				<div class="modal-header">
					<h2>設定完了</h2>
					<div class="modal-close"><a href="javascript:vois(0);" class="btn-modal"><i class="i-e-delete"></i></a></div>
				</div>
				<div class="modal-body">
					<div class="modal-body-inner">
						<div class="modal-message" style="text-align: left;">
							選択した会社を2次広告自動公開の物件取込み除外の会社に設定いたしました。<br />
							解除する場合は「物件取込み除外会社設定一覧」画面より設定を解除してください。<br />
							<br /><br />
							<a href="/">＞ホームへ</a><br />
							<a href="/second-estate-exclusion">＞ 物件取込み除外会社設定一覧</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /modal -->
<?php endif; ?>
@endsection
