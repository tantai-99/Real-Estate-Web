@extends('admin::layouts.default')

@section('title', __('お知らせ作成・変更'))

@section('style')
<link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css">
<link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">
@stop
@section('scripts')
<script type="text/javascript" src="/js/libs/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
<script type="text/javascript" src="/js/admin/information_regist.js"></script>
<script type="text/javascript" src="/js/libs/jquery.ui.widget.js"></script>
<script type="text/javascript" src="/js/libs/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="/js/libs/jquery.fileupload.js"></script>
@stop

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>お知らせ作成・変更</h1>
	<div class="main-contents-body">

		<form action="/admin/information/edit" method="post" name="form" id="form" enctype="multipart/form-data">
			@csrf
			<?php $view->form->getSubForm('basic')->form("id"); ?>
			<div class="section">
				<table class="form-basic">
					<?php foreach ($view->form->getSubForm('basic')->getElements() as $name => $element) : ?>

						<?php if ($element->getType() == "hidden") continue; ?>

						<tr<?php if ($element->isRequired()) : ?> class="is-require" <?php endif; ?>>
							<th><span><?php echo $element->getLabel() ?></span></th>
							<td>
								<?php $view->form->getSubForm('basic')->form($name); ?>
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

				<div id="form_url">
					<table class="form-basic">
						<?php foreach ($view->form->getSubForm('designation')->getElements() as $name => $element) : ?>
							<?php if ($element->getType() == "hidden") continue; ?>

							<tr<?php if ($element->isRequired()) : ?> class="is-require" <?php endif; ?>>
								<th><span><?php echo $element->getLabel() ?></span></th>
								<td>
									<?php $view->form->getSubForm('designation')->form($name); ?>
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

				<div id="form_detail">
					<table class="form-basic">
						<?php foreach ($view->form->getSubForm('detail')->getElements() as $name => $element) : ?>

							<?php if ($element->getType() == "hidden") continue; ?>

							<tr<?php if ($element->isRequired()) : ?> class="is-require" <?php endif; ?>>
								<th><span><?php echo $element->getLabel() ?></span></th>
								<td>
									<?php $view->form->getSubForm('detail')->form($name); ?>
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

							<tr>
								<th><span>ファイル</span></th>
								<td>
									ファイル名：<input type="text" name="name[]" value="<?php if (isset($view->params['name'][0])) echo h($view->params['name'][0]) ?>" style="width:80%">
									<p style="color:red;" id="error_file" class="error_file"><?php if (isset($view->error_details['name'][0])) echo $view->error_details['name'][0]; ?></p>
									<br />
									<div class="f-img-upload">
										<div class="up-img">
											<div class="up-btn" upload-url="set-file-upload">
												<input type="file" id="file" name="file" />
											</div>
											<div class="up-area" upload-url="set-file-upload"> または、ファイルをドロップしてください。 </div>
											<input type="hidden" name="tmp_file[]" value="<?php if (isset($view->params['tmp_file'][0])) echo h($view->params['tmp_file'][0]) ?>" id="detail-tmp_file" class="tmp_file" />
											<input type="hidden" name="file_id[]" value="<?php if (isset($view->params['file_id'][0])) echo h($view->params['file_id'][0]) ?>" id="detail-file_id" class="file_id" />
											<input type="hidden" name="up_file_name[]" value="<?php if (isset($view->params['up_file_name'][0])) echo h($view->params['up_file_name'][0]) ?>" id="detail-up_file_name" class="up_file_name" />
											<span class="file_name" id="file_name"></span>
											<p style="color:red;" id="error_file" class="error_file">
												<small><?php echo $view->form->getSubForm('one_file')->getElement('up_file_name')->getDescription(); ?></small>
												<?php if (isset($view->error_details['file'][0])) {
													echo $view->error_details['file'][0];
												} ?>
											</p>
										</div>
									</div>
									<hr style="height: 2px;border: none;border-top: 2px #000000 dotted;border-color:#CCCCCC;" />
									ファイル名：<input type="text" name="name[]" value="<?php if (isset($view->params['name'][1])) echo h($view->params['name'][1]) ?>" style="width:80%">
									<p style="color:red;" id="error_file" class="error_file"><?php if (isset($view->error_details['name'][1])) echo $view->error_details['name'][1]; ?></p>
									<br />
									<div class="f-img-upload">
										<div class="up-img">
											<div class="up-btn" upload-url="set-file-upload">
												<input type="file" id="file" name="file" />
											</div>
											<div class="up-area" upload-url="set-file-upload"> または、ファイルをドロップしてください。 </div>
											<input type="hidden" name="tmp_file[]" value="<?php if (isset($view->params['tmp_file'][1])) echo h($view->params['tmp_file'][1]) ?>" id="detail-tmp_file" class="tmp_file" />
											<input type="hidden" name="file_id[]" value="<?php if (isset($view->params['file_id'][1])) echo h($view->params['file_id'][1]) ?>" id="detail-file_id" class="file_id" />
											<input type="hidden" name="up_file_name[]" value="<?php if (isset($view->params['up_file_name'][1])) echo h($view->params['up_file_name'][1]) ?>" id="detail-up_file_name" class="up_file_name" />
											<span class="file_name" id="file_name"></span>
											<p style="color:red;" id="error_file" class="error_file">
												<small><?php echo $view->form->getSubForm('one_file')->getElement('up_file_name')->getDescription(); ?></small>
												<?php if (isset($view->error_details['file'][1])) {
													echo $view->error_details['file'][1];
												} ?>
											</p>
										</div>
									</div>
									<hr style="height: 2px;border: none;border-top: 2px #000000 dotted;border-color:#CCCCCC;" />
									ファイル名：<input type="text" name="name[]" value="<?php if (isset($view->params['name'][2])) echo h($view->params['name'][2]) ?>" style="width:80%">
									<p style="color:red;" id="error_file" class="error_file"><?php if (isset($view->error_details['name'][2])) echo $view->error_details['name'][2]; ?></p>
									<br />
									<div class="f-img-upload">
										<div class="up-img">
											<div class="up-btn" id="up-btn_3" upload-url="set-file-upload">
												<input type="file" id="file" name="file" />
											</div>
											<div class="up-area"> または、ファイルをドロップしてください。 </div>
											<input type="hidden" name="tmp_file[]" value="<?php if (isset($view->params['tmp_file'][2])) echo h($view->params['tmp_file'][2]) ?>" id="detail-tmp_file" class="tmp_file" />
											<input type="hidden" name="file_id[]" value="<?php if (isset($view->params['file_id'][2])) echo h($view->params['file_id'][2]) ?>" id="detail-file_id" class="file_id" />
											<input type="hidden" name="up_file_name[]" value="<?php if (isset($view->params['up_file_name'][2])) echo h($view->params['up_file_name'][2]) ?>" id="detail-up_file_name" class="up_file_name" />
											<span class="file_name" id="file_name"></span>
											<p style="color:red;" id="error_file" class="error_file">
												<small><?php echo $view->form->getSubForm('one_file')->getElement('up_file_name')->getDescription(); ?></small>
												<?php if (isset($view->error_details['file'][2])) {
													echo $view->error_details['file'][2];
												} ?>
											</p>
										</div>
									</div>

								</td>
							</tr>
					</table>
				</div>
				<div id="form_file">
					<table class="form-basic">
						<tr class="is-require">
							<th><span>ファイル</span></th>
							<td>
								ファイル名：<?php $view->form->getSubForm('one_file')->form("name"); ?>
								<?php if ($view->form->getSubForm('one_file')->getElement('name')->getDescription() != "") : ?>
									<small><?php echo $view->form->getSubForm('one_file')->getElement('name')->getDescription(); ?></small>
								<?php endif; ?>
								<?php foreach ($view->form->getSubForm('one_file')->getElement('name')->getMessages() as $error) : ?>
									<p style="color:red;"><?php echo h($error) ?></p>
								<?php endforeach; ?>
								<br /><br /><br />
								<div class="f-img-upload">
									<div class="up-img">
										<div class="up-btn" id="up-btn" upload-url="set-file-upload">
											<input type="file" id="file" name="file" />
										</div>
										<div class="up-area"> または、ファイルをドロップしてください。 </div>
										<?php $view->form->getSubForm('one_file')->form("tmp_file"); ?>
										<?php $view->form->getSubForm('one_file')->form("file_id"); ?>
										<?php $view->form->getSubForm('one_file')->form("up_file_name"); ?>
										<span class="file_name" id="file_name"></span>
										<p style="color:red;" id="error_file" class="error_file"></p>
										<small><?php echo $view->form->getSubForm('one_file')->getElement('up_file_name')->getDescription(); ?></small>
										<?php foreach ($view->form->getSubForm('one_file')->getElement('up_file_name')->getMessages() as $error) : ?>
											<p style="color:red;" id="error_file"><?php echo h($error) ?></p>
										<?php endforeach; ?>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="section">
				<table class="form-basic">
					<tr>
						<td colspan="2" style="text-align:center;padding:10px;">
							<a href="/admin/information" class="btn-t-gray">戻る</a>
							<input type="submit" id="submit" class="btn-t-blue" name="submit" value="確認">
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
@endsection