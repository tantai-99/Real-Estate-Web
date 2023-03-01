@extends('admin::layouts.default')

@section('title', __('契約者登録確認'))

@section('script')
<script type="text/javascript" src="/js/modal.js"></script>
<script type="text/javascript" src="/js/admin/modal.js"></script>
<script type="text/javascript" src="/js/admin/company_conf.js"></script>
@endsection

@section('content')

<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>契約者登録確認</h1>
	<div class="main-contents-body">
	<?php if(isset($view->form)):?>
		<form action="{{route('admin.company.postConf')}}" method="post" name="form" id="form01">
			@csrf
			<!-- <input type="hidden" name="_token" value="<?php /* echo $this->token; */ ?>" /> -->
			<?php $view->form->getSubForm('basic')->form("id"); ?>
			<?php $view->form->getSubForm('cms')->form("account_id"); ?>
			<?php $view->form->getSubForm('basic')->form("location"); ?>

			<?php if ($view->form->getSubForm('basic')->getElement('id')->getValue()) : ?>
				<div class="section">
					<h2>現在の契約情報</h2>
					<table class="form-basic">
						<?php foreach ($view->form->getSubForm('status')->getElements() as $name => $element) : ?>
							<tr>
								<th><?php echo $element->getLabel() ?></th>
								<td>
									<?php
									switch ($element->getName()) {
										case 'cms_plan':
											$view->cms_plan_list[null] = '';
											echo $view->cms_plan_list[$element->getValue()];
											break;
										default:
											echo h($element->getValue());
											break;
									}
									?>
								</td>
							</tr>
							<input type="hidden" name="status[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
						<?php endforeach; ?>
					</table>
				</div>
			<?php endif; ?>

			<div class="section">
				<h2>基本情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('basic')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php if ($element->getName() == "contract_type") : ?>
									<?php echo $view->company_agree_list[$element->getValue()]; ?>
								<?php elseif ($element->getName() == "cms_plan") : ?>
									<?php echo $view->cms_plan_list[$element->getValue()]; ?>
								<?php else : ?>
									<?php echo nl2br(h($element->getValue())); ?>
								<?php endif; ?>

								<input type="hidden" name="basic[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<h2>契約情報予約</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('reserve')->getElements() as $name => $element) : ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php
								switch ($element->getName()) {
									case 'contract_type':
										echo $view->company_agree_list[$element->getValue()];
										break;
									case 'reserve_cms_plan':
										$view->cms_plan_list[null] = '';
										echo $view->cms_plan_list[$element->getValue()];
										break;
									default:
										echo h($element->getValue());
										break;
								}
								?>
							</td>
						</tr>
						<input type="hidden" name="reserve[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<h2>解約情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('cancel')->getElements() as $name => $element) : ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php
								switch ($element->getName()) {
									default:
										echo h($element->getValue());
										break;
								}
								?>
							</td>
						</tr>
						<input type="hidden" name="cancel[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
					<?php endforeach; ?>
				</table>
			</div>

			<?php if ($view->fdp_display) { ?>
				<div class="section">
					<h2>FDP情報</h2>
					<table class="form-basic">
						<?php foreach ($view->form->getSubForm('fdp')->getElements() as $name => $element) : ?>

							<?php if ($element->getType() == "hidden") continue; ?>
							<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>

							<tr<?php if ($element->isRequired()) : ?> class="is-require" <?php endif; ?>>
								<th><span><?php echo $element->getLabel() ?></span></th>
								<td>
									<?php $view->form->getSubForm('fdp')->form($name); ?>
									<?php foreach ($element->getMessages() as $error) : ?>
										<p style="color:red;"><?php echo h($error) ?></p>
									<?php endforeach; ?>

								</td>
								</tr>
							<?php endforeach; ?>
					</table>
				</div>
			<?php } ?>

			<div class="section">
				<h2>サーバーコンパネ情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('cp')->getElements() as $name => $element) : ?>
						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php if ($name == "cp_password_used_flg") : ?>
									<?php $element->getValue() == "0" ? print("未設定") : print("設定"); ?>
								<?php else : ?>
									<?php echo nl2br(h($element->getValue())); ?>
								<?php endif; ?>
								<input type="hidden" name="cp[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<h2>CMS情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('cms')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php echo nl2br(h($element->getValue())); ?>
								<input type="hidden" name="cms[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<h2>FTP情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('ftp')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php if ($element->getName() == "ftp_pasv_flg") : ?>
									<?php if ($element->getValue() != "" && $view->form->getSubForm('basic')->getElement('contract_type')->getValue() != config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')) : ?>
										<?php echo $view->pasv[$element->getValue()]; ?>
										<input type="hidden" name="ftp[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
									<?php endif; ?>
								<?php else : ?>
									<?php echo nl2br(h($element->getValue())); ?>
									<input type="hidden" name="ftp[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
								<?php endif; ?>

							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<h2>公開処理通知</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('pn')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php if ($element->getName() == "publish_notify") : ?>
									<?php
									if (isset($element->getValueOptions()[$element->getValue()])) {
										echo $element->getValueOptions()[$element->getValue()];
									} else {
										echo $element->getValueOptions()['0'];
									}
									?>
									<input type="hidden" name="pn[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<h2>その他</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('other')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<tr>
							<th><?php echo $element->getLabel() ?></th>
							<td>
								<?php
								switch ($element->getName()) {
									case 'remarks':
										echo nl2br(h($element->getValue()));
										break;
									default:
										echo h($element->getValue());
										break;
								}
								?>
								<input type="hidden" name="other[<?php echo $element->getName(); ?>]" value="<?php /* echo escape($element->getValue()); */ echo $element->getValue(); ?>" />
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
							<?php if ($view->form->getSubForm('basic')->getElement('copy_from_member_no')) : ?>
								<input type="hidden" name="submit_regist" value="登録" />
								<a class="btn-t-blue size-m" id="regist_copy">登録＆コピー</a>
							<?php else : ?>
								<input type="submit" id="submit" name="submit_regist" value="登録" class="btn-t-blue company-regist">
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</form>

	<?php endif;?>
		<?php /*
					<table class="form-basic">
					<?php foreach ($this->form as $name => $element):?>
					<?php if($element->getType() == "hidden") continue; ?>
					<tr>
						<th><?php echo $element->getLabel()?></th>
						<td>
							<?php if($element->getName() == "contract_type") : ?>
								<?php echo $view->company_agree_list[$element->getValue()]; ?>

							<?php elseif($element->getName() == "ftp_pasv_flg") : ?>
								<?php if($element->getValue() != "") echo $view->pasv[$element->getValue()]; ?>

							<?php else : ?>
								<?php echo nl2br(h($element->getValue())); ?>
							<?php endif; ?>

							<input type="hidden" name="<?php echo $element->getName(); ?>" value="<?php  echo escape($element->getValue());  ?>" />
						</td>
					</tr>
					<?php endforeach; ?>
					<tr>
						<td colspan="2" style="text-align:center;">
							<input type="submit" id="back" name="back" value="戻る" class="btn-t-gray">
							<input type="submit" id="submit" name="submit" value="登録" class="btn-t-blue">
						</td>
					</tr>
					</table>
*/ ?>

	</div>
</div>

@endsection