@extends('admin::layouts.default')
@section('content')
    @section('title')
    グループ会社設定
    @stop
    @section('script')
    <script src="/js/admin/company_group.js" type="text/javascript"></script>
    @stop
		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>グループ会社設定</h1>
			<div style="width:100%;text-align:right;padding:5px;margin-top:-50px;">
				<a href="/admin/company/detail/?id=<?php echo h($view->params['company_id']);?>" class="btn-t-gray">戻る</a>
			</div>
			<div class="main-contents-body">
				<div class="section">
					<table class="tb-basic">
						<tr>
							<th>会員No</th>
							<td><span id="parent_company_no"><?php echo h($view->company->member_no); ?></span></td>
							<th>会員名</th>
							<td><span id="parent_member_name"><?php echo h($view->company->member_name); ?></span></td>
							<th>会社名</th>
							<td><span id="parent_company_name"><?php echo h($view->company->company_name); ?></span></td>
						</tr>
					</table>
				</div>

				<h2>会社設定</h2>
				<div class="section">
					<form action="/admin/company/group?company_id={{$view->params['company_id']}}" method="POST" name="group_form">
						@csrf
					<input type="hidden" name="company_id" id="company_id" value="<?php echo h($view->params['company_id']);?>">
					<input type="hidden" name="add_company_id" id="add_company_id" value="">
					<table class="form-basic">
						<tr class="is-require">
							<th><span>会員No</span></th>
							<td>
								<input type="text" name="member_no" id="member_no" value="">
							</td>
							<td><button type="button" id="get_company" class="btn-t-blue" name="get_company">登録</button>
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
							<th>会社名</th>
							<th>設定日</th>
							<th></th>
						</tr>
						<?php foreach($view->rows as $key => $val) : ?>
							<tr>
								<td><?php echo h($val->member_no); ?></td>
								<td><?php echo h($val->company_name); ?></td>
								<td><?php echo h(str_replace("-", "/", substr(h($val->create_date), 0, 10))); ?></td>
								<td style="text-align:center;">
	<!--								<a href="/admin/company/group-del/?company_id=<?php echo h($view->params['company_id']);?>&del_id=<?php echo $val->id; ?>" class="btn-t-gray size-s">削除</a> -->
									<button type="button" class="btn-t-gray size-s del_button" id="del_button" value="<?php echo h($val->member_no); ?>">削除</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<table>

				</div>
			</div>
		</div>

		<table class="form-basic" style="width:500px;display:none" id="add_area">
			<tr>
				<td colspan="2"><span id="area_comment"></span>
			</tr>
			<tr>
				<th><span>会員No</span></th>
				<td><span id="area_member_no"></span>
				</td>
			</tr>
			<tr>
				<th><span>会社名</span></th>
				<td><span id="area_company_name"></span></td>
			</tr>
			<tr>
				<th><span>会員名</span></th>
				<td><span id="area_member_name"></span></td>
			</tr>
			<tr>
				<th><span>所在地</span></th>
				<td><span id="area_location"></span></td>
			</tr>
		</table>

		<form action="/admin/company/group-del" method="POST" name="group_del_form">
			@csrf
			<input type="hidden" name="del_pearent_company_id" id="del_pearent_company_id" value="<?php echo h($view->params['company_id']);?>">
			<input type="hidden" name="del_company_id" id="del_company_id" value="">
			<input type="hidden" name="del_id" id="del_id" value="">
		</form>
@endsection