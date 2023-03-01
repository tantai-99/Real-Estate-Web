@extends('admin::layouts.default')

@section('title', __('契約者登録'))

@section('style')
<link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css">
<link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">
@endsection
@section('script')
<script type="text/javascript" src="/js/libs/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
<script type="text/javascript" src="/js/admin/company_regist.js"></script>
<script type="text/javascript" src="/js/libs/jquery.selectbox.js"></script>
<script type="text/javascript">
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	$(function() {
		if ($('#basic-copy_from_member_no').length) { // コピー時プランを変更出来ないようにする
			$('#reserve-reserve_cms_plan').disableSelection();
		}
		// 確定済み契約内容を変更する関連の処理
		$('#status-cms_plan').disableSelection();
		$('#status-contract_staff_name').addClass('is-lock');
		$('#status-contract_staff_department').addClass('is-lock');
		$('#status-contract_status').attr("disabled", true);
		$('#status-initial_start_date').attr("disabled", true);
		$('#status-start_date').attr("disabled", true);
		var buttonId = '#search_contract_staff';
		var targets = [
			'#status-applied_start_date',
			'#status-contract_staff_id',
			buttonId
		];
		var $changeStatus = $('#changeStatus');
		var swithObjs = [];

		setTimeout(function() {
			$('.is-lock').next('.ui-datepicker-trigger').hide();
		});

		for (var i = 0; i < targets.length; i++) {
			var $obj = $(targets[i]);
			swithObjs.push({
				'id': targets[i],
				'obj': $obj,
				'val': $obj.val()
			});
			$obj.datepicker("option", "disabled", true);
		}
		$(buttonId).hide();

		$changeStatus.on('click', function() {
			if ($changeStatus.prop('checked')) {
				setProp(swithObjs, 'disabled', false);
				$('.datepicker:not(.is-lock)').next('.ui-datepicker-trigger').show();
			} else {
				setProp(swithObjs, 'disabled', true);
				$('.is-lock').next('.ui-datepicker-trigger').hide();
			}
		});

		function setProp(objs, name, val) {
			for (var i = 0; i < objs.length; i++) {
				var $obj = objs[i]['obj'];
				$obj.prop(name, val);
				$obj.val(objs[i]['val']);
				$obj.datepicker("option", "disabled", val);
				$obj.toggleClass('is-lock');
			}
			$(buttonId).toggle();
		}

		// 契約情報予約エリア 未選択時は非活性化
		var $select_reserve_cms_plan = $('#reserve-reserve_cms_plan'); // select
		var $btn_reserve_cms_plan = $('#search_reserve_contract_staff'); // 参照ボタン
		var reserve_cms_plan_target = [
			'#reserve-reserve_applied_start_date',
			'#reserve-reserve_start_date',
			'#reserve-reserve_contract_staff_id'
		];
		if ($select_reserve_cms_plan.val() == 0) {
			for (var j = 0; j < reserve_cms_plan_target.length; j++) {
				$(reserve_cms_plan_target[j]).prop('disabled', true).addClass('is-lock');
			}
			$btn_reserve_cms_plan.hide();
		}

		$select_reserve_cms_plan.change(function() {
			if ($(this).val() == 0) {
				for (var j = 0; j < reserve_cms_plan_target.length; j++) {
					$(reserve_cms_plan_target[j]).prop('disabled', true).addClass('is-lock');
				}
				$btn_reserve_cms_plan.hide();
				$('.is-lock').next('.ui-datepicker-trigger').hide();
			} else {
				for (var j = 0; j < reserve_cms_plan_target.length; j++) {
					$(reserve_cms_plan_target[j]).prop('disabled', false).removeClass('is-lock');
				}
				$btn_reserve_cms_plan.show();
			}
			$('.datepicker:not(.is-lock)').next('.ui-datepicker-trigger').show();
		});
	});
</script>
@endsection

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>契約者登録</h1>
	<div style="text-align:right;margin-top:-50px;margin-right:20px;">
		<?php if ($view->form->getSubForm('basic')->getElement('id')->getValue() > 0) : ?>
			<a href="/admin/company/detail/?id=<?php echo h($view->form->getSubForm('basic')->getElement('id')->getValue()); ?>" class="btn-t-gray">戻る</a>
		<?php else : ?>
			<a href="/admin/company" class="btn-t-gray">戻る</a>
		<?php endif; ?>

	</div>
	<div class="main-contents-body">
		<input type="hidden" id="member_api_url" name="member_api_url" value="<?php echo $view->backbone->member->url; ?>">
		<input type="hidden" id="staff_api_url" name="staff_api_url" value="<?php echo $view->backbone->staff->url; ?>">
		<input type="hidden" id="default-ftp-ftp_server_port" name="default-ftp-ftp_server_port" value="<?php echo $view->default_ftp->port; ?>">
		<input type="hidden" id="default-ftp-ftp_server_name" name="default-ftp-ftp_server_name" value="<?php echo $view->default_ftp->server_name; ?>">
		<input type="hidden" id="default-ftp-ftp_password" name="default-ftp_password" value="<?php echo $view->default_ftp->password; ?>">

		<form action="{{route('admin.company.postEdit')}}" method="post" name="form" id="form">
			@csrf
			<input type="hidden" id="copy" name="copy" value="<?= @$_REQUEST['copy']; ?>">
			<input type="hidden" id="id" name="id" value="<?= @$_REQUEST['id']; ?>">
			<?php $view->form->getSubForm('basic')->form("id"); ?>
			<?php $view->form->getSubForm('cms')->form("account_id"); ?>
			<?php $view->form->getSubForm('basic')->form("location"); ?>

			<?php if ($view->form->getSubForm('basic')->getElement('id')->getValue()) : ?>
				<div class="section">
					<h2>現在の契約情報</h2>
					<?php if ($view->form->getSubForm('status')->getContractSatus() !== 'none') : ?>
						<input id="changeStatus" type="checkbox" />確定済み契約内容を変更する
					<?php endif; ?>
					<table class="form-basic">
						<?php foreach ($view->form->getSubForm('status')->getElements() as $name => $element) : ?>

							<?php if ($element->getType()	== "hidden") continue; ?>
							<?php if ($name					== "contract_staff_name") continue; ?>
							<?php if ($name					== "contract_staff_department") continue; ?>

							<tr>
								<th><span><?php echo $element->getLabel() ?></span></th>
								<td style="white-space: nowrap;">
									<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>
									<?php if ($element->getType() == "select") $element->setAttribute("style", "width:60%;"); ?>
									<?php $view->form->getSubForm('status')->form($name); ?>
									<?php if ($name == "contract_staff_id") : ?>
										<button type="button" id="search_contract_staff" name="contract_staff_btn" class="btn-t-gray search_staff" value="status-contract_staff" disabled="disabled">参照</button><br />
									<?php endif; ?>
									<?php foreach ($element->getMessages() as $error) : ?>
										<p style="color:red;"><?php echo h($error) ?></p>
									<?php endforeach; ?>
									<?php if ($name == "contract_staff_id") : ?>
										<span style="font-size:12px;">
											担当者名：<?php $view->form->getSubForm('status')->form("contract_staff_name"); ?><br />
											部署　　：<?php $view->form->getSubForm('status')->form("contract_staff_department"); ?>
										</span>
										<?php if ($element->getValue() != "" && $view->form->getSubForm('status')->getElement("contract_staff_name")->getValue() == "") : ?>
											<p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
										<?php endif; ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
			<?php endif; ?>


			<div class="section">
				<h2>基本情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('basic')->getElements() as $name => $element) : ?>

						<?php if ($view->form->getTypeElement($element)	== "hidden") continue; ?>
						<?php if ($name					== "member_name") continue; ?>
						<?php if ($name					== "member_linkno") continue; ?>

						<tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td style="white-space: nowrap;">
								<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>
								<?php $view->form->getSubForm('basic')->form($name); ?>

								<?php if ($name == "member_no") : ?>
									<button type="button" id="search_member_no" name="member_no_btn" class="btn-t-gray">参照</button><br />
								<?php endif; ?>
								<?php if ($name == "domain") : ?>
									<button type="button" id="make_domain" name="make_domain_btn" class="btn-t-gray">作成</button><br />
								<?php endif; ?>
								<?php if ($name == "contract_staff_id") : ?>
									<button type="button" id="search_contract_staff" name="contract_staff_btn" class="btn-t-gray search_staff" value="contract_staff">参照</button><br />
								<?php endif; ?>

								<?php if ($element->getDescription() != "") : ?>
									<br />
									<span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
								<?php endif; ?>

								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php echo h($error) ?></p>
								<?php endforeach; ?>

								<?php if ($name == "member_no") : ?>
									<span style="font-size:12px;">
										<?php echo $view->form->getSubForm('basic')->getElement("member_name")->getLabel(); ?>：<?php $view->form->getSubForm('basic')->form("member_name"); ?><br />
										<?php foreach ($view->form->getSubForm('basic')->getElement("member_name")->getMessages() as $error) : ?>
											<p style="color:red;"><?php echo h($error) ?></p>
										<?php endforeach; ?>

										<?php echo $view->form->getSubForm('basic')->getElement("member_linkno")->getLabel(); ?>：<?php $view->form->getSubForm('basic')->form("member_linkno"); ?>
									</span>
									<br />
									<?php foreach ($view->form->getSubForm('basic')->getElement("member_linkno")->getMessages() as $error) : ?>
										<p style="color:red;"><?php echo h($error) ?></p>
									<?php endforeach; ?>

								<?php endif; ?>

							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<?php if ($view->form->getSubForm('status')->getElement('contract_status')->getValue() == '停止中') : ?>
					<h2>再契約情報予約</h2>※解約情報は消さないでください。
				<?php else : ?>
					<h2>契約情報予約</h2>
				<?php endif; ?>
				
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('reserve')->getElements() as $name => $element) : ?>

						<?php if ($element->getType()	== "hidden") continue; ?>
						<?php if ($name				== "reserve_contract_staff_name") continue; ?>
						<?php if ($name				== "reserve_contract_staff_department") continue; ?>

						<tr<?php if($element->isRequired() || $element->getMessages()): ?> class="is-require"<?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td style="white-space: nowrap;">
								<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>
								<?php if ($element->getType() == "select") $element->setAttribute("style", "width:60%;"); ?>

								<?php $view->form->getSubForm('reserve')->form($name); ?>

								<?php if ($name == "reserve_contract_staff_id") : ?>
									<button type="button" id="search_reserve_contract_staff" name="contract_staff_btn" class="btn-t-gray search_staff" value="reserve-reserve_contract_staff">参照</button><br />
								<?php //dd($element->getMessages());?>
								<?php endif; ?>

								<?php if ($element->getDescription() != "") : ?>
									<br />
									<span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
								<?php endif; ?>

								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php echo h($error) ?></p>
								<?php endforeach; ?>

								<?php if ($name == "reserve_contract_staff_id") : ?>
									<span style="font-size:12px;">
										担当者名：<?php $view->form->getSubForm('reserve')->form("reserve_contract_staff_name"); ?><br />
										部署　　：<?php $view->form->getSubForm('reserve')->form("reserve_contract_staff_department"); ?>
									</span>
									<?php if ($element->getValue() != "" && $view->form->getSubForm('reserve')->getElement("reserve_contract_staff_name")->getValue() == "") : ?>
										<p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
									<?php endif; ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="section">
				<h2>解約情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('cancel')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<?php if ($name == "cancel_staff_name") continue; ?>
						<?php if ($name == "cancel_staff_department") continue; ?>

						<tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td style="white-space: nowrap;">
								<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>

								<?php $view->form->getSubForm('cancel')->form($name); ?>

								<?php if ($name == "cancel_staff_id") : ?>
									<button type="button" id="search_cancel_staff" name="cancel_staff_btn" class="btn-t-gray search_staff" value="cancel-cancel_staff">参照</button><br />
								<?php endif; ?>

								<?php if ($element->getDescription() != "") : ?>
									<br />
									<span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
								<?php endif; ?>

								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php echo h($error) ?></p>
								<?php endforeach; ?>

								<?php if ($name == "cancel_staff_id") : ?>
									<span style="font-size:12px;">
										担当者名：<?php $view->form->getSubForm('cancel')->form("cancel_staff_name"); ?><br />
										部署　：<?php $view->form->getSubForm('cancel')->form("cancel_staff_department"); ?>
									</span>
									<?php if ($element->getValue() != "" && $view->form->getSubForm('cancel')->getElement("cancel_staff_name")->getValue() == "") : ?>
										<p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
									<?php endif; ?>
								<?php endif; ?>
							</td>
						</tr>
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

							<tr>
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
				<p class="checkbox-pw-inactive">
					<label>
						<!--input type="hidden" name="cp[cp_password_used_flg]" value="0"><input type="checkbox" name="cp[cp_password_used_flg]" id="cp-cp_password_used_flg" value="1">コンパネパスワード非活性（切替対応） -->
						<?php $view->form->getSubForm('cp')->form("cp_password_used_flg"); ?><?php echo $view->form->getSubForm('cp')->getElement("cp_password_used_flg")->getLabel(); ?>
					</label>
				</p>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('cp')->getElements() as $name => $element) : ?>
						
						<?php if ($element->getType() == "hidden") continue; ?>
						<?php if ($name == "cp_password_used_flg") continue; ?>
						<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>

						<tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php $view->form->getSubForm('cp')->form($name); ?>
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
				<h2>CMS情報</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('cms')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>

						<tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>						
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php $view->form->getSubForm('cms')->form($name); ?>

								<?php if ($name == "login_id") : ?>
									<!-- button onclick='copyValue("cms-login_id","basic-member_no");' type="button" class="btn-t-gray">コピー</button><br/ -->
									<button type="button" id="memberno_copy" class="btn-t-gray">コピー</button><br />
									<div style="width:100%;text-align:right;color:#848484">※コピーボタンを押すと、会員Noが設定されます。</div>
								<?php endif; ?>

								<?php if ($name == "password") : ?>
									<!-- button onclick='copyValue("cms-password","cp-cp_password");' type="button" id="password_copy" class="btn-t-gray">コピー</button -->
									<button type="button" id="password_copy" class="btn-t-gray">コピー</button>
									<!-- button onclick='createPasswordForRandm("cms-password");' type="button" id="password_create" class="btn-t-gray">パスワード生成</button -->
									<button type="button" id="password_create" class="btn-t-gray">パスワード生成</button>
									<div style="width:100%;text-align:right;color:#848484">※コピーボタンを押すと、サーバーコンパネ情報のパスワードが設定されます。</div>
								<?php endif; ?>

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
				<h2>FTP情報</h2>
				<?php $view->form->getSubForm('ftp')->form("demo_domain"); ?>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('ftp')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>
						<?php if ($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>

						<tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td<?php if ($name == "ftp_pasv_flg") : ?> class="f-switch" <?php endif; ?>>

								<?php if ($name == "ftp_pasv_flg") : ?>
									<div class="switch-area">
										<div class="switch" id="switch">
											<input id="ftp-ftp_pasv_flg-0" value="0" class="on" type="radio" <?php if ($element->getValue() == "" || $element->getValue() == "0") : ?> checked="checked" <?php endif; ?> name="ftp[ftp_pasv_flg]"><label for="ftp-ftp_pasv_flg-0">有効</label>
											<input id="ftp-ftp_pasv_flg-1" value="1" class="off" type="radio" <?php if ($element->getValue() == "1") : ?> checked="checked" <?php endif; ?> name="ftp[ftp_pasv_flg]"><label for="ftp-ftp_pasv_flg-1">無効</label>
										</div>
									</div>
								<?php else : ?>
									<?php $view->form->getSubForm('ftp')->form($name); ?>
								<?php endif; ?>

								<?php if ($name == "ftp_directory") : ?>
									<!-- button onclick='copyValue("ftp-ftp_directory", "basic-domain");' type="button" id="ftp_directory_copy" class="btn-t-gray">コピー</button -->
									<button type="button" id="ftp_directory_copy" class="btn-t-gray">コピー</button>
									<div style="width:100%;text-align:right;color:#848484">※コピーボタンを押すと、利用ドメインが設定されます。</div>
								<?php endif; ?>

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
				<h2>公開処理通知</h2>
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('pn')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>

						<tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php $view->form->getSubForm('pn')->form($name); ?>

								<?php foreach ($element->getMessages() as $error) : ?>
									<p style="color:red;"><?php echo h($error) ?></p>
								<?php endforeach; ?>
								<p style="font-size:11px;color:#848484">
									※『公開開始・成功・失敗時に通知する』を選択した場合、アプリケーションサーバ上に詳細情報もログファイルに記録されるようになります
								</p>
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

						<tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>	
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php $view->form->getSubForm('other')->form($name); ?>
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

			<div class="section" style="width:100%;text-align:right;">
				<a><button type="button" id="lock" class="btn-t-gray" name="lock" value="">ロック解除</button></a>
			</div>

			<div class="section">
				<table class="form-basic">
					<tr>
						<td colspan="2" style="text-align:center;padding:10px;">
							<?php if ($view->form->getSubForm('basic')->getElement('id')->getValue() > 0) : ?>
								<a href="/admin/company/detail/?id=<?php echo h($view->form->getSubForm('basic')->getElement('id')->getValue()); ?>" class="btn-t-gray" id="back">戻る</a>
							<?php else : ?>
								<a href="/admin/company" class="btn-t-gray" id="back">戻る</a>
							<?php endif; ?>

							<button type="button" id="sub_edit" class="btn-t-blue" name="sub_edit" value="確認">確認</button>
							<input type="hidden" id="asd" name="asd" value="asd">

						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
@endsection