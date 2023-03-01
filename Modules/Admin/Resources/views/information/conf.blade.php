<?php

use Library\Custom\Model\Lists\InformationDisplayPageCode;
use Library\Custom\Model\Lists\InformationDisplayTypeCode;

?>
@extends('admin::layouts.default')

@section('title', __('お知らせ作成・変更確認'))

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>お知らせ作成・変更確認</h1>
	<div class="main-contents-body">

		<form action="/admin/information/conf" method="post" name="form">
			@csrf
			<?php $view->form->getSubForm('basic')->form("id"); ?>

			<div class="section">
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('basic')->getElements() as $name => $element) : ?>
						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php if ($element->getName() == "display_page_code") : ?>
									<?php
									$list = new InformationDisplayPageCode();
									$list = $list->getAll();
									print($list[$element->getValue()]);
									?>
									<input type="hidden" name="basic[<?php echo $element->getName(); ?>]" value="<?php echo /* $view->escape($element->getValue()) */ $element->getValue(); ?>" />

								<?php elseif ($element->getName() == "display_type_code") : ?>
									<?php
									$list = new InformationDisplayTypeCode();
									$list = $list->getAll();
									print($list[$element->getValue()]);
									?>
									<input type="hidden" name="basic[<?php echo $element->getName(); ?>]" value="<?php echo /* $view->escape($element->getValue()) */ $element->getValue(); ?>" />

								<?php elseif ($element->getName() == "important_flg" || $element->getName()  == "new_flg") : ?>
									<?php if ($element->getValue() == null) {
										print("なし");
										print('<input type="hidden" name="basic[' . $element->getName() . '][]" value="0" />');
									} else {
										print("あり");
										print('<input type="hidden" name="basic[' . $element->getName() . '][]" value="1" />');
									}
									?>
								<?php else : ?>
									<?php echo nl2br(h($element->getValue())); ?>
									<input type="hidden" name="basic[<?php echo $element->getName(); ?>]" value="<?php echo /* $view->escape($element->getValue()) */ $element->getValue(); ?>" />
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>

				<table class="form-basic">
					<?php if ($view->form->getSubForm('basic')->getElement('display_type_code')->getValue() == "1") : ?>

						<?php foreach ($view->form->getSubForm('designation')->getElements()  as $name => $element) : ?>
							<tr>
								<th><?php echo $element->getLabel() ?></th>
								<td>
									<?php echo nl2br(h($element->getValue())); ?>
									<input type="hidden" name="designation<?php echo "[" . $element->getName() . "]"; ?>" value="<?php echo h($element->getValue()); ?>" />
								</td>
							</tr>
						<?php endforeach; ?>

					<?php elseif ($view->form->getSubForm('basic')->getElement('display_type_code')->getValue() == "2") : ?>

						<?php foreach ($view->form->getSubForm('detail')->getElements()  as $name => $element) : ?>
							<tr>
								<th><?php echo $element->getLabel() ?></th>
								<td>
									<?php echo nl2br(h($element->getValue())); ?>
									<input type="hidden" name="detail[<?php echo $element->getName(); ?>]" value="<?php echo h($element->getValue()); ?>" />
								</td>
							</tr>
						<?php endforeach; ?>

						<?php if (is_array($view->params['name'])) : ?>
							<?php foreach ($view->params['name'] as $key => $val) : ?>
								<?php if ($val != "") : ?>
									<tr>
										<th>ファイル</th>
										<td><?php echo h($val); ?>
											<input type="hidden" name="name[<?php echo h($key); ?>]" value="<?php echo h($val); ?>" />
											<input type="hidden" name="file_id[<?php echo h($key); ?>]" value="<?php echo h($view->params['file_id'][$key]); ?>" />
											<input type="hidden" name="tmp_file[<?php echo h($key); ?>]" value="<?php echo h($view->params['tmp_file'][$key]); ?>" />
											<input type="hidden" name="up_file_name[<?php echo h($key); ?>]" value="<?php echo h($view->params['up_file_name'][$key]); ?>" />
										</td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>

					<?php elseif ($view->form->getSubForm('basic')->getElement('display_type_code')->getValue() == "3") : ?>
						<tr>
							<th><?php echo $view->form->getSubForm('one_file')->getElement('name')->getLabel() ?></th>
							<td>
								<?php echo nl2br(h($view->form->getSubForm('one_file')->getElement('name')->getValue())); ?>
								<input type="hidden" name="one_file[<?php echo $view->form->getSubForm('one_file')->getElement('name')->getName(); ?>]" value="<?php echo /* $view->escape($view->form->one_file->name->getValue()) */ $view->form->getSubForm('one_file')->getElement('name')->getValue(); ?>" />
								<input type="hidden" name="one_file[<?php echo $view->form->getSubForm('one_file')->getElement('file_id')->getName() ?>]" value="<?php echo /* $view->escape($view->form->one_file->file_id->getValue()) */ $view->form->getSubForm('one_file')->getElement('file_id')->getValue(); ?>" />
								<input type="hidden" name="one_file[<?php echo $view->form->getSubForm('one_file')->getElement('tmp_file')->getName() ?>]" value="<?php echo /* $view->escape($view->form->one_file->tmp_file->getValue()) */ $view->form->getSubForm('one_file')->getElement('tmp_file')->getValue(); ?>" />
								<input type="hidden" name="one_file[<?php echo $view->form->getSubForm('one_file')->getElement('up_file_name')->getName() ?>]" value="<?php echo /* $view->escape($view->form->one_file->up_file_name->getValue()) */ $view->form->getSubForm('one_file')->getElement('up_file_name')->getValue(); ?>" />
							</td>
						</tr>
					<?php endif; ?>

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