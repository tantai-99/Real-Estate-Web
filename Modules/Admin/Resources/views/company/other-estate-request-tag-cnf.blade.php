@extends('admin::layouts.default')

@section('title', $view->original_tag . '-確認')

@section('content')
<div class="main-contents">
	<h1><?php echo $view->original_tag; ?>確認</h1>
	<div class="main-contents-body">

		<form action="/admin/company/other-estate-request-tag-cnf/company_id/{{$view->params['company_id']}}" method="post" name="form">
			@csrf
			<?php $view->form->getSubForm('other')->form("id") ?>
			<?php $view->form->getSubForm('other')->form("company_id") ?>
			<input type="hidden" id="company_id" name="company_id" value="<?php echo h($view->params['company_id']); ?>" id="company_id">

			<?php $tag_title = array(
				'above_close_head_tag_residential_rental_request_thanks' => '居住用賃貸物件フォーム(サンクスページ)',
				'above_close_head_tag_business_rental_request_thanks'    => '事業用賃貸物件フォーム(サンクスページ)',
				'above_close_head_tag_residential_sale_request_thanks'   => '居住用売買物件フォーム(サンクスページ)',
				'above_close_head_tag_business_sale_request_thanks'      => '事業用売買物件フォーム(サンクスページ)',
				'above_close_head_tag_residential_rental_request_input'  => '居住用賃貸物件フォーム(入力フォームページ)',
				'above_close_head_tag_business_rental_request_input'     => '事業用賃貸物件フォーム(入力フォームページ)',
				'above_close_head_tag_residential_sale_request_input'    => '居住用売買物件フォーム(入力フォームページ)',
				'above_close_head_tag_business_sale_request_input'       => '事業用売買物件フォーム(入力フォームページ)',
			); ?>
			<?php foreach ($view->form->getSubForm('other')->getElements() as $name => $element) : ?>
				<?php if ($element->getType() == "hidden") continue; ?>

				<?php if (strpos($name, 'above_close_head_tag') === 0) : ?>
					<div class="section">
						<h2><?php echo $tag_title[$name]; ?></h2>
						<table class="form-basic">
						<?php endif; ?>

						<tr>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php echo nl2br(h($element->getValue())); ?>
								<input type="hidden" name="other[<?php echo $element->getName(); ?>]" value="<?php echo /* $view->escape($element->getValue()) */ $element->getValue(); ?>" />
							</td>
						</tr>

						<?php if (strpos($name, 'above_close_body_tag') === 0) : ?>
						</table>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>


			<div class="section" style="text-align:center;">
				<input type="submit" id="back" class="btn-t-gray" name="back" value="戻る">
				<input type="submit" id="submit" class="btn-t-blue" name="submit" value="登録">
			</div>

		</form>
	</div>
</div>
@endsection