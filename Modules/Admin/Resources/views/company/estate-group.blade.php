@extends('admin::layouts.default')

@section('title', __('物件グループ設定'))

@section('content')
	<script type="text/javascript" src="/js/admin/modal.js"></script>
	<script type="text/javascript" src="/js/admin/estate_group.js"></script>
		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>物件グループ設定</h1>
			<div style="width:100%;text-align:right;padding:5px;margin-top:-50px;">
				<a href="/admin/company/detail/?id=<?php echo $params['company_id'] ;?>" class="btn-t-gray">戻る</a>
			</div>
			<div class="main-contents-body">
				<div class="section">
					<table class="tb-basic">
						<tr>				
							<th>会員No</th>
							<td><span id="parent_company_no"><?php echo $view->company['member_no'] ; ?></span></td>
							<th>会員名</th>
							<td><span id="parent_member_name"><?php echo $view->company['member_name'] ; ?></span></td>
							<th>会社名</th>
							<td><span id="parent_company_name"><?php echo $view->company['company_name']; ?></span></td>
						</tr>
					</table>
				</div>
				<h2>会社設定</h2>
				<div class="section">
					<form action="/admin/company/estate-group?company_id={{$params['company_id']}}" method="POST" name="group_form">
						@csrf
					<input type="hidden" name="company_id" id="company_id" value="<?php echo $params['company_id'];?>">
					<input type="hidden" name="add_member_no" id="add_member_no" value="">
					<table class="form-basic">
						<tr class="is-require">
							<th><span>会員No</span></th>
							<td>
								<input type="text" name="member_no" id="member_no" value="">
							</td>
							<td><button type="button" id="add_company" class="btn-t-blue" name="add_company">登録</button>
							</td>
						</tr>
					</table>
					</form>
				</div>
				<h2>一覧</h2>
				<div class="section">
					<table class="tb-basic">
						<tr>
							<th>会員No</th>
							<th>インターネットコード</th>
							<th>会員名</th>
							<th>設定日</th>
							<th>会員設定</th>
						</tr>
					<?php foreach($view->companies as $key => $val) : ?>
						<tr>
							<td><?php echo h($val->memberNo); ?></td>
							<td><?php echo h($val->kaiLinkNo); ?></td>
							<td><?php echo h($val->memberName); ?></td>
							<td><?php echo h(str_replace("-", "/", substr(h($val->createDate), 0, 10))); ?></td>
							<td style="text-align:left;">
								<button type="button" class="btn-t-gray size-s ref_button" id="ref_button" value="<?php echo h($val->memberNo); ?>">参照</button>
								<button type="button" class="btn-t-gray size-s del_button" id="del_button" value="<?php echo h($val->memberNo); ?>">削除</button>
							</td>
						</tr>
					<?php endforeach; ?>
					<table>
				</div>
			</div>
		</div>

		<div style="display:none" id="add_area">
			<p id="area_comment"></p>
			<dl class="confirm-basic">
				<dt>会員No</dt>
					<dd><span id="area_member_no"></span></dd>
				<dt>インターネットコード</dt>
					<dd><span id="area_inte_code"></span></dd>
				<dt>会員名</dt>
					<dd><span id="area_member_name"></span></dd>
				<dt>所在地</dt>
					<dd><span id="area_location"></span></dd>
        	</dl>
		</div>
		<form action="/admin/company/estate-group-del" method="POST" name="group_del_form">
			@csrf
			<input type="hidden" name="del_parent_company_id" id="del_parent_company_id" value="<?php echo h($params['company_id']);?>">
			<input type="hidden" name="del_member_no" id="del_member_no" value="">
			<input type="hidden" name="del_associate_id" id="del_associate_id" value="">
		</form>
@endsection