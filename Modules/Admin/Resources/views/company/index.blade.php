@extends('admin::layouts.default')
@section('content')
    @section('style')
    <link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css" >
    <link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css" >
    @stop
	@section('scripts')
	<script type="text/javascript" src="/js/libs/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
    <script type="text/javascript" src="/js/admin/company_search.js"></script></head>
    @stop

	@section('title')
		契約管理
		@stop
		<!-- メインコンテンツ1カラム -->
		<div class="main-contents">
			<h1>契約管理</h1>
			<div class="main-contents-body">
				<div style="text-align:right;margin-top:-70px;">
					<a href="/admin/company/csv" class="btn-t-blue">CSV出力</a>
					<?php if (!$view->agency): ?>
					<a href="/admin/company/edit" class="btn-t-blue">新規作成</a>
					<?php endif; ?>
				</div>
				<div class="section" style="margin-top:40px;">
					<h2 id="search_title">検索</h2>
					<form action="/admin/company" name="search" method="post">
						@csrf
						<table class="form-basic" id="search_area">
							<tbody>
								<tr>
									<th style="width:100px;"><span>契約</span></th>
									<td colspan="3">
										<?php
											$view->search_form->form('contract_type');
										?>
									</td>
								</tr>
								<tr>
									<th style="width:60px;"><span>会員No</span></th>
									<td>
										<?php $view->search_form->form('member_no'); ?>
									</td>
									<th style="width:60px;"><span>会社名</span></th>
									<td>
										<?php $view->search_form->form('company_name'); ?>
									</td>
								</tr>
								<tr>
									<th style="width:100px;"><span>利用開始日</span></th>
									<td colspan="3">
										<?php $view->search_form->form('start_date_s');?>　～　
										<?php $view->search_form->form('start_date_e');?>
										<?php foreach ($view->search_form->getElement('start_date_s')->getMessages() as $error):?>
										<p class="errors"><?php echo h($error)?></p>
										<?php endforeach;?>
										<?php foreach ($view->search_form->getElement('start_date_e')->getMessages() as $error):?>
										<p class="errors"><?php echo h($error)?></p>
										<?php endforeach;?>
										<br />
										<span style="font-size:10px;color:#848484"><?php echo $view->search_form->getElement('start_date_s')->getDescription(); ?></span>
									</td>
								</tr>
								<tr>
									<th style="width:100px;"><span>利用停止日</span></th>
									<td colspan="3">
										<?php $view->search_form->form('end_date_s');?>　～　
										<?php $view->search_form->form('end_date_e');?>
										<?php foreach ($view->search_form->getElement('end_date_s')->getMessages() as $error):?>
										<p class="errors"><?php echo h($error)?></p>
										<?php endforeach;?>
										<?php foreach ($view->search_form->getElement('end_date_e')->getMessages() as $error):?>
										<p class="errors"><?php echo h($error)?></p>
										<?php endforeach;?>
										<br />
										<span style="font-size:10px;color:#848484"><?php echo $view->search_form->getElement('end_date_s')->getDescription(); ?></span>
									</td>
								</tr>
								<tr>
									<td colspan="4" style="text-align:center;">
										<input type="submit" name="search" value="検索" class="btn-t-blue">
									</td>
								</tr>
							</tbody>
						</table>

					</form>
				</div>

				<div class="section detail-list">
					<table class="tb-basic">
						<thead>
						<tr>
							<th>契約</th>
							<th>会員No</th>
							<th>会員名</th>
							<th>会社名</th>
							<th>利用開始日</th>
							<th>利用停止日</th>
							<th></th>
							<th>PDF</th>
						</tr>
						</thead>
						@foreach ($view->company as $key => $row)															
							<tr>
							<?php $reserve_start_date_view= ( $row->reserve_start_date_view ? $row->reserve_start_date_view : $row->start_date_view) ;
								?>			
								<td >					
									<?php
										echo $view->company_list[$row->contract_type];
									?>
								</td>
								<td>{{$row->member_no}}</td>
								<td>{{mb_strimwidth(h($row->member_name), 0, 26, "...", "UTF-8") }}</td>
								<td>{{mb_strimwidth(h($row->company_name), 0, 26, "...", "UTF-8")}}</td>
								<td>{{h($reserve_start_date_view)}}</td> 
								<td>{{h($row->end_date_view)}}</td>
								<td><a href="/admin/company/detail/?id={{$row->id}}"  class="btn-t-gray size-s">詳細</a></td>

								@if($row->contract_type == 0 || $row->contract_type == 2)
								<td><a href="/admin/company/pdf/?id={{$row->id}}" target="_blank" class="btn-t-gray size-s">出力</a></td>
								@else
								<td><a href="#" target="_blank" class="btn-t-gray size-s is-disable" onclick="return false;">	出力</a></td>
								@endif
							</tr>
							@endforeach
					</table>
					{{$view->company->links('admin::paginating',['search_param' => $view->search_param])}}
				</div>
			</div>
		</div>
@endsection
