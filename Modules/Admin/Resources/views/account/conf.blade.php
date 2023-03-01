@extends('admin::layouts.default')

@section('title', __('アカウント作成・編集確認'))

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>アカウント作成・編集確認</h1>
	<div class="main-contents-body">

		<form action="/admin/account/conf" method="post" name="form">
			@csrf
			<!-- <input type="hidden" name="_token" value="<?php /* echo $view->token; */ ?>" /> -->
			
			<?php $view->form->form("id"); ?>

			<div class="section">
				<table class="form-basic">
					<?php foreach ($view->form->getElements() as $name => $element) : ?>
						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php if ($element->getName() == "privilege_flg") : ?>
									<?php
									$strs = array();
									foreach ($element->getValue() as $key => $val) {
										switch ($val) {
											case "1":
												$strs[] = "修正権限";
												break;
											case "2":
												$strs[] = "管理権限";
												break;
											case "3":
												$strs[] = "代行作成権限";
												break;
											case "4":
												$strs[] = "代行更新権限";
												break;
											default:
												continue 2;
												break;
										}
										print('<input class="' . $val . '" type="hidden" name="' . $element->getName() . '[]" value="' . h($val) . '" />');
									}
									print(implode(" / ", $strs));
									?>

								<?php else : ?>
									<?php if ($element->getName() == "password") : ?>
										**********
									<?php else : ?>
										<?php echo nl2br(h($element->getValue())); ?>
									<?php endif; ?>
									<input type="hidden" name="<?php echo $element->getName(); ?>" value="<?php echo /* $view->escape($element->getValue()) */ $element->getValue(); ?>" />
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<table class="form-basic">
					<tr>
						<td colspan="2" style="text-align:center;">
							<input type="submit" id="back" name="back" value="戻る" class="btn-t-gray">
							<input type="submit" id="submit" name="submit" value="登録" class="btn-t-blue">
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
@endsection