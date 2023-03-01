@extends('layouts.default')
@section('title', __('物件取込み除外会社設定'))
@section('script')
<script type="text/javascript" src="/js/app.second.estate.exclusion.js"> </script>
@endsection
@section('content')
<div class="main-contents article-search">
	<h1>2次広告自動公開設定：物件取込み除外会社設定</h1>
	<div style="text-align:right;margin-top:-50px;margin-right:20px;">
		<p class="btn-create"><a href="<?php echo route('default.secondestateexclusion.search')?>" class="btn-t-blue">新規追加</a></p>
    </div>
	<div class="main-contents-body">
		<h2>物件取込み除外会社設定一覧</h2>

        <div class="section special-remove-create">
        	<form action="/second-estate-exclusion/delete" method="POST" name="delete_form" id="delete_form">
        		@csrf
        		<dir id="targetId">
        			
        		</dir>
        	</form>
			<?php if(!isset($view->rows) || count($view->rows) == 0 ) :?>
			<div>
				<h3>現在設定されている会社はございません。</h3>
			</div>
			<?php endif;?>
			<table class="tb-basic tb-checkbox">
				<thead>
					<tr>
						<th class="nowrap">選択</th>
						<th class="nowrap">商号</th>
						<th class="nowrap">所在地</th>
						<th class="nowrap">最寄駅</th>
						<th class="nowrap">TEL</th>
 						<th class="nowrap">設定日</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($view->rows as $key => $val):?>
					<tr>
						<td style="text-align: center;">
							<input type="checkbox" class="delete_comapny_check" name="delete_id[]" id="delete_id_<?php echo h($val->id)?>" value="<?php echo h($val->id)?>">
						</td>
						<td><?php echo h($val->name)?></td>
						<td><?php echo h($val->address)?></td>
						<td><?php echo h($val->nearest_station)?></td>
						<td><?php echo h($val->tel)?></td>
						<td><?php echo date("Y年m月d日" , strtotime(h($val->update_date))) ?></td>
					</tr>
					<?php endforeach;?>
					<?php if(!isset($view->rows) || count($view->rows) == 0 ) :?>
					<tr>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
					</tr>
					<?php endif;?>
				</tbody>
			</table>
			<?php //echo $this->paginationControl($this->paginator, 'Sliding', '_pagination.phtml', array('search_param' => $this->search_param)); ?>
            <p class="mb20"></p>
            <p class="mb20">　<small>※上記で設定している除外会社は、ご利用状況により自動的に削除されることがあります。<br>　（例）当社への物件公開を行わなくなった場合など。</small></p>
            <dir class="section btn-area">
				<input type="button" class="btn-t-gray size-l" id="delete_company" value="選択した会社を設定解除する">
            </dir>


		</div>
	</div>
</div>
<?php if(isset($view->params['r']) && $view->params['r'] != '') :?>
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
							選択した会社を2次広告自動公開の物件取込み除外会社設定から、解除いたしました。<br />
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