@extends('admin::layouts.default')

@section('title', $view->original_tag . '-確認')

@section('content')
<div class="main-contents">
	<h1><?php echo $view->original_tag; ?>確認</h1>
	<div class="main-contents-body">

		<form action="/admin/company/other-tag-cnf/company_id/{{$view->params['company_id']}}" method="post" name="form">
			@csrf
			<?php $view->form->getSubForm('other')->form("id") ?>
			<?php $view->form->getSubForm('other')->form("company_id") ?>
			<input type="hidden" id="company_id" name="company_id" value="<?php echo h($view->params['company_id']); ?>" id="company_id">

			<?php $tag_title = array(
				'above_close_head_tag' => '全ページ共通', 'above_close_head_tag_contact_thanks' => '会社問い合わせ(サンクスページ)', 'above_close_head_tag_assess_thanks' => '売却査定(サンクスページ)', 'above_close_head_tag_request_thanks' => '資料請求(サンクスページ)', 'above_close_head_tag_contact_input' => '会社問い合わせ(入力フォームページ)', 'above_close_head_tag_assess_input' => '売却査定(入力フォームページ)', 'above_close_head_tag_request_input' => '資料請求(入力フォームページ)'
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