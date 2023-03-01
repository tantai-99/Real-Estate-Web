@extends('admin::layouts.default')

@section('title', __('2次広告自動公開設定'))

@section('style')
	<link href='/js/libs/themes/blue/style.css' media="screen" rel="stylesheet" type="text/css">
	<link href='/js/libs/themes/jquery-ui/jquery-ui.min.css' media="screen" rel="stylesheet" type="text/css">
@stop

@section('script')
	<script type="text/javascript"  src="/js/libs/jquery-ui.min.js"></script>
	<script type="text/javascript"  src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
	<script type="text/javascript"  src="/js/admin/modal.js"></script>
	<script type="text/javascript"  src="/js/admin/second_estate_edit.js"></script>
@stop

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>2次広告自動公開設定</h1>
	<div style="text-align:right;margin-top:-50px;margin-right:20px;">
		<a href="/admin/company/detail/?id=<?php echo h($view->params['company_id']);?>" class="btn-t-gray">戻る</a>
	</div>
	<div class="main-contents-body">
		<input type="hidden" id="member_api_url" name="member_api_url" value="<?php echo $view->backbone->member->url; ?>">
		<input type="hidden" id="staff_api_url" name="staff_api_url" value="<?php echo $view->backbone->staff->url; ?>">
		
		<form action="{{ route('admin.company.second-estate.post') }}" method="post" name="form" id="form">
		<input type="hidden" name="company_id" id="company_id" value="<?php echo h($view->params['company_id']);?>">
		<?php $view->form->getSubForm('secondEstate')->form("id"); ?>
		@csrf
		<div class="section">
			<h2>2次広告自動公開</h2>
			<table class="form-basic">
			<?php foreach ($view->form->getSubForm('secondEstate')->getElements() as $name => $element):?>

			<?php if($element->getType() == "hidden") continue; ?>
			<?php if($name == "member_name") continue; ?>
			<?php if($name == "contract_staff_name") continue; ?>
			<?php if($name == "contract_staff_department") continue; ?>
			<?php if($name == "cancel_staff_name") continue; ?>
			<?php if($name == "cancel_staff_department") continue; ?>


			<tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
				<th><span><?php echo $element->getLabel()?></span></th>
				<td style="white-space: nowrap;">
					<?php if($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>

					<?php $view->form->getSubForm('secondEstate')->form($name);?>

					<?php if($name == "member_no") : ?>
						<button type="button" id="search_member_no" name="member_no_btn" class="btn-t-gray">参照</button><br />
					<?php endif; ?>
					<?php if($name == "contract_staff_id") : ?>
						<button type="button" id="search_contract_staff" name="contract_staff_btn" class="btn-t-gray search_staff" value="contract_staff">参照</button><br />
					<?php endif; ?>
					<?php if($name == "cancel_staff_id") : ?>
						<button type="button" id="search_cancel_staff" name="cancel_staff_btn" class="btn-t-gray search_staff" value="cancel_staff">参照</button><br />
					<?php endif; ?>

					<?php if($element->getDescription() != "") : ?>
					<br />
					<span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
					<?php endif; ?>

					<?php foreach ($element->getMessages() as $error):?>
					<p style="color:red;"><?php echo h($error)?></p>
					<?php endforeach;?>

					<?php if($name == "member_no") : ?>
						<span style="font-size:12px;">
						<?php echo $view->form->getSubForm('secondEstate')->getElement("member_name")->getLabel();?>：<?php $view->form->getSubForm('secondEstate')->form("member_name");?>
						</span>
						<?php foreach ($view->form->getSubForm('secondEstate')->getElement("member_name")->getMessages() as $error):?>
						<p style="color:red;"><?php echo h($error)?></p>
						<?php endforeach;?>
					<?php endif; ?>

					<?php if($name == "contract_staff_id") : ?>
						<span style="font-size:12px;">
						担当者名：<?php $view->form->getSubForm('secondEstate')->form("contract_staff_name");?><br />
						部署　：<?php $view->form->getSubForm('secondEstate')->form("contract_staff_department");?>
						</span>
						<?php if ($element->getValue() != "" && $view->form->getSubForm('secondEstate')->getElement("contract_staff_name")->getValue() == "") : ?>
						<p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if($name == "cancel_staff_id") : ?>
						<span style="font-size:12px;">
						担当者名：<?php $view->form->getSubForm('secondEstate')->form("cancel_staff_name");?><br />
						部署　：<?php $view->form->getSubForm('secondEstate')->form("cancel_staff_department");?>
						</span>
						<?php if ($element->getValue() != "" && $view->form->getSubForm('secondEstate')->getElement("cancel_staff_name")->getValue() == "") : ?>
						<p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
						<?php endif; ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach;?>
			</table>
		</div>
		
		<h2 class="is-require">エリア</h2>
		<?php foreach ($view->form->getSubForm('secondEstateArea')->getMessages() as $error):?>
		<?php if(!is_string($error))continue;  ?>
		<p style="color:red;"><?php echo h($error)?></p>
		<?php endforeach;?>

		<div class="section">
			<table class="form-basic select-area">
			<?php foreach ($view->form->getSubForm('secondEstateArea')->getElements() as $name => $element):?>
				<tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
				<th><span><?php echo $element->getLabel()?></span></th>
					<td>
						<ul>
							<?php $view->form->getSubForm('secondEstateArea')->simpleCheckBox($name); ?>
						</ul>
					</td>
				</tr>
			<?php endforeach;?>
			</table>
		</div>
		
		<div class="section">
			<h2>その他</h2>
			<table class="form-basic">
			<?php foreach ($view->form->getSubForm('other')->getElements() as $name => $element):?>

			<?php if($element->getType() == "hidden") continue; ?>

			<tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
				<th><span><?php echo $element->getLabel()?></span></th>
				<td>
					<?php $view->form->getSubForm('other')->form($name);?>
					<?php if($element->getDescription() != "") : ?>
					<br />
					<span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
					<?php endif; ?>

					<?php foreach ($element->getMessages() as $error):?>
					<p style="color:red;"><?php echo h($error)?></p>
					<?php endforeach;?>

				</td>
			</tr>
			<?php endforeach;?>
			</table>
		</div>


		<div class="section">
			<table class="form-basic">
			<tr>
				<td colspan="2" style="text-align:center;padding:10px;">
					<a href="{{ route('admin.company.detail') }}?id=<?php echo h($view->params['company_id']);?>" class="btn-t-gray" id="back">戻る</a>
					<button type="button" id="sub_edit" class="btn-t-blue" name="sub_edit" value="確認">確認</button>
					<input type="hidden" id="submit-confirm" name="submit-confirm" value="submit-confirm">

				</td>
			</tr>
			</table>
		</div>
		</form>
	</div>
</div>
@endsection


