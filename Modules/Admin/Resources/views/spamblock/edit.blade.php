@extends('admin::layouts.default')

@section('title', __('迷惑メール条件管理編集'))

@section('content')
<div class="main-contents">
	<h1>迷惑メール条件管理編集</h1>
	<div class="main-contents-body" id="spam-edit">

		<form action="/admin/spamblock/edit" method="post" name="form" id="form">
		@csrf
			<?php $view->form->form("id"); ?>
			<div class="section">
				<table class="form-basic">
					<?php foreach ($view->form->getElements() as $name => $element) : ?>
						<?php if ($element->getType() == "hidden") continue; ?>
						<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>
						<?php if ($name == "member_no_add") continue; ?>
						<tr>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php if ($name == "member_no") : ?>
									<?php $view->form->form('member_no_add'); ?>
									<input type="submit" class="btn-t-gray" id="add" name="add" value="追加"><br>
									追加された会員: <?php echo $element->getValue() ?>
									<input type="hidden" name="<?php echo $element->getName(); ?>" value="{{ $element->getValue() }}" />
								<?php else : ?>
									<?php $view->form->form($name); ?>
								<?php endif; ?>
								<?php if ($element->getDescription() != "") : ?>
									<br>
									<span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
								<?php endif; ?>
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
							<input type="submit" id="conf" class="btn-t-blue" name="conf" value="確認">
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
@endsection
