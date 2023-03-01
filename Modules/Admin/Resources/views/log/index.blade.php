@extends('admin::layouts.default')
@section('content')

@section('title')
	ログ管理
@stop

@section('style')
<link rel="stylesheet" href="/js/libs/themes/jquery-ui/jquery-ui.min.css">
@stop
@section('script')
<script src="/js/libs/themes/blue/style.css"></script>
<script src="/js/libs/jquery-ui.min.js" type="text/javascript"></script>
<script src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js" type="text/javascript" > </script>
<script src="/js/admin/modal.js" type="text/javascript"></script>
<script src="/js/admin/log_search.js" type="text/javascript" > </script>
@stop
		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>ログ管理</h1>
			<div class="main-contents-body">
				<div class="section" style="margin-top:40px;">
					<form action="/admin/log" name="search" method="post">
						@csrf
						<table class="form-basic" id="search_area">
							<tr>
								<th style="width:80px;"><span>種類</span></th>
								<td><?php $view->form->form("log_type"); ?>
								</td>
								<th style="width:80px;"><span id="name">担当者ＣＤ</span></th>
								<td><?php $view->form->form("athome_staff_id"); ?></td>
							</tr>
							<tr>
								<th style="width:80px;"><span>会員No</span></th>
								<td><?php $view->form->form("member_no"); ?></td>
								<th style="width:80px;"><span>会社名</span></th>
								<td><?php $view->form->form("company_name"); ?></td>
							</tr>
							<tr>
								<th style="width:80px;"><span>操作日時<?php echo $view->toolTip('log_edit_operation_date', 'log-edit')?></span></th> <!-- còn xót tooltip !-->
								
								<td colspan="3">
									<?php $view->form->form("datetime_s"); ?>　～　<?php $view->form->form("datetime_e"); ?><br />
									<span style="font-size:10px;color:#848484"><?php echo $view->form->getElement('datetime_s')->getDescription(); ?></span>

									<?php foreach ($view->form->getElement('datetime_s')->getMessages() as $error):?>
									<p class="errors"><?php echo h($error)?></p>
									<?php endforeach;?>
									<?php foreach ($view->form->getElement('datetime_e')->getMessages() as $error):?>
									<p class="errors"><?php echo h($error)?></p>
									<?php endforeach;?>
								</td>
							</tr>
							<tr>
								<td colspan="4" style="text-align:center;">
									<input type="submit" name="submit" value="出力" class="btn-t-blue">
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
		</div>

<script>
$('#log_type').change(function() {
	if($(this).val() == "1"){
		$("#name").html("担当者ＣＤ");
	}else{
		$("#name").html("担当者名");
	}

	// 会員操作ログ・公開処理ログは『担当者名』設定不可
	if($(this).val() == "3" || $(this).val() == "4"){
		$("#name").parent().addClass("is-disable");
		$("#athome_staff_id").val("");
		$("#athome_staff_id").attr("disabled", true);
	}else{
		$("#name").parent().removeClass("is-disable");
		$("#athome_staff_id").attr("disabled", false);
	}
});
</script>
@endsection