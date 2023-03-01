@extends('admin::layouts.default')

@section('title', __('地図検索'))

@section('script')
	<script type="text/javascript"  src="/js/modal.js"></script>
	<script type="text/javascript"  src="/js/admin/modal.js"></script>
	<script type="text/javascript"  src="/js/admin/company_regist.js"></script>
@stop

@section('content')

<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>地図検索</h1>
	<div class="main-contents-body">
		@if(isset($view->form))
		<form action="/admin/map-option/edit?id={{ h($view->form->getSubForm('map')->getValue('id')) }}" method="post" name="form" id="form01">
		@csrf
			<?php $view->form->getSubForm('map')->form( "id" ) ; ?>

			<div class="section">
				<h2>スタンダード地図オプション</h2>
				<table class="form-basic">
					<?php foreach ( $view->form->getSubForm('map')->getElements() as $name => $element ):?>
					<?php if( $element->getType() == "hidden"       ) continue ; ?>
						<tr>
						<th><?= $element->getLabel() ?></th>
						<td><?= $element->getValue() ?></td>
						<input type="hidden" name="map[<?php echo $element->getName(); ?>]" value="{{ $element->getValue() }}" />
						</tr>
					<?php endforeach;?>
				</table>
			</div>
			
			<div class="section">
				<table class="form-basic">
				<tr>
					<td colspan="2" style="text-align:center;">
						<input type="submit" id="back_map"   name="back_map"          value="戻る" class="btn-t-gray">
						<input type="submit" id="submit" name="submit_regist" value="登録" class="btn-t-blue">
					</td>
				</tr>
				</table>
			</div>
		</form>
		@endif

	</div>
</div>
@endsection
