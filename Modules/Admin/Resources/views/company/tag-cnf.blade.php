@extends('admin::layouts.default')

@section('title', $view->original_tag . '-確認')
<?php /* headTitle($view->original_tag . '-確認') */ ?>

@section('content')
<div class="main-contents">
	<h1><?php echo $view->original_tag; ?>確認</h1>
	<div class="main-contents-body">

		<form action="/admin/company/tag-cnf/company_id/{{$view->params['company_id']}}" method="post" name="form">
			@csrf
			<div class="section">
				<h2>Googleアカウント情報</h2>

				<?php $view->form->getSubForm('google')->form("id") ?>
				<?php $view->form->getSubForm('google')->form("company_id") ?>
				<input type="hidden" name="company_id" value="<?php echo $view->params['company_id']; ?>" id="company_id">

				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('google')->getElements() as $name => $element) : ?>
						<?php if ($element->getType() == "hidden" && $element->getName() != "file_name") continue; ?>
						<tr>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php echo nl2br(h($element->getValue())); ?>
								<input type="hidden" name="google[<?php echo $element->getName(); ?>]" value="<?php echo h($element->getValue()); ?>" />
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<?php /*
				<div class="section">
					<h2>その他タグ</h2>
					<table class="form-basic">
					<?php foreach ($view->form->other as $name => $element):?>
						<?php if($element->getType() == "hidden") continue; ?>
						<tr>
							<th><span><?php echo $element->getLabel()?></span></th>
							<td>
								<?php echo nl2br(h($element->getValue())); ?>
								<input type="hidden" name="other[<?php echo $element->getName(); ?>]" value="<?php echo $view->escape($element->getValue()); ?>" />
							</td>
						</tr>
						<?php endforeach;?>
					</table>
				</div>
				*/ ?>

			<div class="section" style="text-align:center;">
				<input type="submit" id="back" class="btn-t-gray" name="back" value="戻る">
				<input type="submit" id="submit" class="btn-t-blue" name="submit" value="登録">
			</div>

		</form>
	</div>
</div>
@endsection