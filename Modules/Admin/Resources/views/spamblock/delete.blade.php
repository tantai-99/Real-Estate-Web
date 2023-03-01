@extends('admin::layouts.default')

@section('title', __('迷惑メール条件管理削除確認'))

@section('content')
<div class="main-contents">
	<h1>迷惑メール条件管理削除確認</h1>
	<div class="main-contents-body spam">

		<form action="/admin/spamblock/delete" method="post" name="form" id="form">
		@csrf
			<?php $view->form->form("id"); ?>
			<div class="section">
				<table class="form-basic">
					<?php foreach ($view->form->getElements() as $name => $element) : ?>
						<?php if ($element->getType() == "hidden") continue; ?>
						<?php if ($element->getName() == "member_no_add") continue; ?>
						<tr>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php if ($element->getName() === 'range_option') : ?>
									<?php echo ($element->getValue() == '0' ? '全会員' : '特定の会員') ?>
								<?php elseif ($element->getName() === 'email_option') : ?>
									<?php echo ($element->getValue() === '0' ? '完全一致' : '') ?>
									<?php echo ($element->getValue() === '1' ? '部分一致' : '') ?>
								<?php elseif ($element->getName() === 'member_no') : ?>
									<?php $values = explode(',', $element->getValue()) ?>
									<?php foreach ($values as $value) : ?>
										<?php echo $value ?>
									<?php endforeach ?>
								<?php else : ?>
									<?php echo $element->getValue() ?>
								<?php endif ?>
								<input type="hidden" name="<?php echo $element->getName(); ?>" value="{{ $element->getValue() }}" />
								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php echo h($error) ?></p>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<table class="form-basic">
					<tr>
						<td colspan="2" style="text-align:center;padding:10px;">
							<a href="/admin/spamblock" class="btn-t-gray">戻る</a>
							<input type="submit" id="submit" class="btn-t-blue" name="submit" value="削除">
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
@endsection