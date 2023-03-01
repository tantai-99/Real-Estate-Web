@extends('admin::layouts.default')

@section('title', __('アカウント作成・変更'))

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>アカウント作成・変更</h1>
	<div class="main-contents-body">

		<form action="/admin/account/edit" method="post" name="form" id="form">
			@csrf
			<?php $view->form->form("id"); ?>
			<div class="section">

				<table class="form-basic">
					<?php foreach ($view->form->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>

						<tr<?php if ($element->isRequired()) : ?> class="is-require" <?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php $view->form->form($name); ?>
								<?php if ($element->getDescription() != "") : ?>
									<br />
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
							<a href="/admin/account" class="btn-t-gray">戻る</a>
							<input type="submit" id="submit" class="btn-t-blue" name="submit" value="確認">
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
@endsection