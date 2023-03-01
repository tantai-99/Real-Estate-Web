@php
	$layout = 'layouts.default';
	$titleInitialize = __('初期設定');
	if (isset($view->layout)) {
		$layout = 'layouts.'.$view->layout;
		$titleInitialize = __('');
	}
@endphp
@extends($layout)
@section('title', $titleInitialize)

@section('script')
<script type="text/javascript" src="/js/upload.js"></script>
<script type="text/javascript" src="/js/page.change.js"></script>
<script type="text/javascript">
	$(function() {

		$('.f-logo').on('change', '.upload-file-id', function() {
			var $this = $(this);
			var $req = $(this).parents('.f-logo-input').find('.i-l-require');
			$req.toggleClass('is-hide', !$this.val());
			$req.parent().next().find('> div').toggleClass('is-require', !!$this.val());
		});

		$('.upload-block').initUpload();

		var $fbTimeLineFlg = $('input[name="fb_timeline_flg"]');

		function onChangeFbTimeLineFlg(e) {
			$('#fb_page_url').parents('.item-set-list').toggleClass('is-hide', $fbTimeLineFlg.filter(':checked').val() !== '1')
		}
		$fbTimeLineFlg.on('change', onChangeFbTimeLineFlg);
		onChangeFbTimeLineFlg();

		var $twTimeLineFlg = $('input[name="tw_timeline_flg"]');

		function onChangeTwTimeLineFlg() {
			$('#tw_widget_id,#tw_username').parents('.item-set-list').toggleClass('is-hide', $twTimeLineFlg.filter(':checked').val() !== '1')
		}
		$twTimeLineFlg.on('change', onChangeTwTimeLineFlg);
		onChangeTwTimeLineFlg();

		$form = $('form');
		app.initApiForm($form, $form.find('input[type="submit"]'), function(data) {
			if (data.redirectTo) {
				location.href = data.redirectTo;
				return;
			}

			var links = [{
					title: '公開設定へ',
					url: '/publish/simple'
				},
				{
					title: 'ホームへ',
					url: '/'
				}
			];

			if ($('.i-m-deputize').parent('li').find('a:contains("代行作成テストサイト確認")').hasClass('is-disable')) {
				links.shift();
			}

			app.modal.message({
				message: '設定を保存しました。',
				links: links,
				ok: '閉じる',
				cancel: false
			});
		});

		$("#footerlinkCaution").on('click', function() {
			var $content = '<div class="section">' +
				'  <img src="/images/common/footerlink_1.png" align="right" style="padding:5px 2px;margin-left:15px;width:300px;border: 1px #c0c0c0 solid;"/>' +
				'  <p>フッターリンク一覧はホームページでいうと右図の<span style="color:blue;">青枠の部分</span>になります。</p>' +
				'</div>' +
				'<br clear="all"/>' +
				'<br/>' +
				'<div class="section">' +
				'  <img src="/images/common/footerlink_2.png" align="right" style="padding:5px 2px;margin-left:15px;width:300px;border: 1px #c0c0c0 solid;"/>' +
				'  <p>「ページの作成/更新」ページ画面の右図の<span style="color:red;">赤枠の部分</span>の左から第2階層、第3階層、第4階層、第5階層となります。</p>' +
				'</div>' +
				'<br clear="all"/>';

			app.modal.popup({
				title: "表示の注意",
				modalBodyInnerClass: 'align-top',
				contents: $content,
				ok: false,
				cancel: false
			}).show();
		});
		
		$('input[name="hankyo_plus_use_flg"]:radio').change( function() {
			var radioval = $(this).val();
			if(radioval == 1 ){
				var $content = '<div class="section js-scroll-container" data-scroll-container-max-height="400" style="overflow-y:auto;width:800px;padding-right:10px;">'
							+ '<p><span style="color:red;">※注意事項</span></p>'
							+ '<div style="margin-left: 1em;">'
							+ '<p><span style="border-bottom: #c0c0c0 1px solid;font-weight:bold;">反響プラスでエンドユーザーの閲覧履歴データを取得・利用する場合、貴社におけるエンドユーザー向け個人情報規程類（プライバシーポリシーや個人情報保護方針など）に、閲覧履歴データを取得・利用する旨の条項を必ず記載し、同意を得るための手続きをとるようお願いいたします。</span></p>'
							+ '<p>エンドユーザーの閲覧履歴データは個人情報に該当するため、エンドユーザーの同意なく閲覧履歴データを取得・利用した場合、個人情報保護法に抵触し、罰則を受けることがありますのであらかじめご了承ください。また、貴社が罰則等を受けたこと等により生じたいかなる損害（直接、間接、特別、派生、結果障害、逸失利益、営業機会の損失又は消失等に関する損害を含みます）に対しても、当社は責任を負わないものとします。</p>'
							+ '</div>'
							+ '<p><span style="font-weight:bold;">「個人情報の取扱い」の変更方法</span></p>'
							+ '<div style="margin-left: 1em;">'
							+ '<p>現在の貴社「個人情報の取扱い」に閲覧履歴データの取得・利用する文言を追加</p>'
							+ '<div style="margin-left: 1em;">'
							+ '<p class="policy-item" style="font-size: 13px;"><?php if ($isInitialize = isCurrent(null, 'initialize')):?>①設定画面「⑤プライバシーポリシー作成」に進む。<?php else:?>①ページの作成/更新＞プライバシーポリシー を選択し、「プライバシーポリシー」を表示する。<?php endif ;?></p>'
							+ '<p class="policy-item" style="font-size: 13px;">②以下赤枠部分に、現在の貴社「プライバシーポリシー」が表示されています。個人情報の利用目的に、エンドユーザーの閲覧履歴を取得・利用する旨を記載してください。<br>記載例）本サイトの利用状況・閲覧履歴データ等を集計・分析し、希望に沿う物件情報等をご紹介するため</p>'
							+ '<div style="margin-left: 1em;">'
							+ '<?php if ($isInitialize = isCurrent(null, 'initialize')):?><img src="/images/common/hankyo_privacy_initialize.png" /><?php else:?><img src="/images/common/hankyo_privacy.png" /><?php endif ;?>'
							+ '</div>'
							+ '</div>'
							+ '</div>'
							+ '</div>';
				
				app.modal.popup({
					title: "",
					modalBodyInnerClass: 'align-top',
					contents: $content,
					ok: '同意する',
					cancel: '戻る',
					onClose: function(ret){
						if(!ret){
							$('#hankyo_plus_use_flg-0').prop('checked', true);
							return;
						}
					},
				}).show();
			};
		});
	});
</script>
@endsection

@section('content')
<div class="main-contents">

	<?php if(!($isInitialize)):?>
		<h1>初期設定</h1>
	<?php endif; ?>

	<div class="main-contents-body">
		<?php if (method_exists(getInstanceUser('cms')->getCurrentHp(), 'hasReserve')) : ?>
			<?php if ($hasReserve = getInstanceUser('cms')->getCurrentHp()->hasReserve()) : ?>
				<div class="alert-strong">「サイトの公開/更新」画面にて公開・停止の予約設定がされています。予約設定解除後にこのページを「保存」できます。</div>
			<?php endif; ?>
		<?php endif; ?>

		@if ($isInitialize)
		@include('initialize._step')
		@endif

		<?php /* if ($view->messages || $view->form->hasErrors()) : */ ?>
		<!-- <div class="section"> -->
		<?php /* if ($view->form->hasErrors()) : */ ?>
		<!-- <p class="alert-strong">入力エラーがあります。</p> -->
		<?php /* endif; */ ?>
		<?php /* if ($view->messages) foreach ((array)$view->messages as $message) : */ ?>
		<!-- <p class="alert-normal"><?php /* echo h($message) */ ?></p> -->
		<?php /* endforeach; */ ?>
		<!-- </div> -->
		<?php /* endif; */ ?>

		<form data-api-action="<?php if(!$isInitialize){ echo route('api-save-index'); } else { echo route('initialize.api-save-index'); } ?>" method="post" enctype="multipart/form-data">
			@csrf
			<div class="section">
				<h2>サイト名・サイトの説明・キーワード（全ページ共通）<a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.tdk-common') ?>', '', 'width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>

				<table class="form-basic">
					<?php $name = 'title'; ?>
					<tr class="<?php if ($view->form->getElement($name)->isRequired()) : ?>is-require<?php endif; ?>">
						<th><span><?php echo $view->form->getElement($name)->getLabel() ?><?php echo $view->toolTip('site_title') ?></span></th>
						<td><?php $view->form->form($name) ?><span class="input-count"></span>
							<div class="errors">
								<?php foreach ($view->form->getElement($name)->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>

					<?php $name = 'description'; ?>
					<tr class="<?php if ($view->form->getElement($name)->isRequired()) : ?>is-require<?php endif; ?>">
						<th><span><?php echo $view->form->getElement($name)->getLabel() ?><?php echo $view->toolTip('site_description') ?></span></th>
						<td><?php $view->form->form($name) ?><span class="input-count"></span>
							<div class="errors">
								<?php foreach ($view->form->getElement($name)->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>

					<tr class="is-require only-first">
						<th><span>キーワード<?php echo $view->toolTip('site_keyword') ?></span></th>
						<td>
							<?php foreach ($view->form->getSubForm('keywords')->getElementsByGroup() as $group) : ?>
								<div class="inner input-keyword">
									<?php foreach ($group as $name => $elem) : ?>
										<span><?php $view->form->getSubForm('keywords')->form($elem->getName()) ?><span class="input-count"></span></span>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>

							<div class="errors">
								<?php foreach ($view->form->getSubForm('keywords')->getGroupErrors() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>

				</table>
			</div>

			<div class="section">
				<h2>ファビコン</h2>
				<table class="form-basic">
					<tr class="<?php if ($view->form->getElement('favicon')->isRequired()) : ?>is-require<?php endif; ?>">
						<th><span><?php echo $view->form->getElement('favicon')->getLabel() ?><?php echo $view->toolTip('site_favicon') ?></span></th>
						<td class="upload-block">
							<div class="f-img-upload">
								<?php $view->form->form('favicon') ?>
								<div class="up-img">
									<div class="up-btn">
										<input type="file" name="file">
									</div>
									<div class="up-area is-hide">または、ファイルをドロップしてください。</div>
									<small>jpg,jpeg,gif,png,ico(容量 10MB、サイズ 縦：32px　横：32px まで。サイズ超過時は範囲内に収まるように自動縮小されます。）</small>
								</div>
								<div class="up-preview favicon"><a class="i-e-delete is-hide" href="javascript:;"></a></div>
							</div>
							<div class="errors">
								<?php foreach ($view->form->getElement('favicon')->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
					<tr class="<?php if ($view->form->getElement('webclip')->isRequired()) : ?>is-require<?php endif; ?>">
						<th><span><?php echo $view->form->getElement('webclip')->getLabel() ?><?php echo $view->toolTip('site_webclip') ?></span></th>
						<td class="upload-block">
							<div class="f-img-upload">
								<?php $view->form->form('webclip') ?>
								<div class="up-img">
									<div class="up-btn">
										<input type="file" name="file">
									</div>
									<div class="up-area is-hide">または、ファイルをドロップしてください。</div>
									<small>jpg,jpeg,gif,png (容量 10MB、サイズ 縦：152px　横：152px まで。サイズ超過時は範囲内に収まるように自動縮小されます。）</small>
								</div>
								<div class="up-preview"><a class="i-e-delete is-hide" href="javascript:;"></a></div>
							</div>
							<div class="errors">
								<?php foreach ($view->form->getElement('webclip')->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<?php if (!getInstanceUser('cms')->checkHasTopOriginal()) : ?>
				<div class="section">
					<h2>ヘッダー・フッター<a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.content-common') ?>', '', 'width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>
					<p class="mb10">ヘッダー・フッターに表示したい情報を入力してください。</p>
					<table class="form-basic">
						<?php foreach (array('company_name', 'adress', 'tel', 'office_hour', 'outline') as $name) : ?>
							<tr class="<?php if ($view->form->getElement($name)->isRequired()) : ?>is-require<?php endif; ?>">
								<th><span><?php echo $view->form->getElement($name)->getLabel() ?><?php echo $view->toolTip('site_' . $name) ?></span></th>
								<td><?php $view->form->form($name) ?><span class="input-count"></span>
									<div class="errors">
										<?php foreach ($view->form->getElement($name)->getMessages() as $message) : ?>
											<p class="error"><?php echo h($message) ?></p>
										<?php endforeach; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>

						<tr>
							<th>
								<span>
									<?php echo $view->form->getElement('footer_link_level')->getLabel() ?><?php echo $view->toolTip('site_footer_link_level') ?>
									<a href="javascript:void(0)" id="footerlinkCaution"><img src="/images/common/icon_announe.png" /></a>
								</span>
							</th>
							<td>
								<div class="footer-link-list">
									<?php $view->form->form('footer_link_level') ?>
									<div class="errors">
										<?php foreach ($view->form->getElement('footer_link_level')->getMessages() as $message) : ?>
											<p class="error"><?php echo h($message) ?></p>
										<?php endforeach; ?>
									</div>
								</div>
							</td>
						</tr>

						<tr>
							<th><span>サイトロゴ<?php echo $view->toolTip('site_logo_pc') ?><i class="i-l-require" style="float:right;">必須</i></span>（PC）</th>
							<td class="f-logo upload-block">
								<div class="f-logo-input">
									<dl>
										<dt>画像</dt>
										<dd>
											<div class="f-img-upload">
												<?php $view->form->form('logo_pc') ?>
												<div class="up-img">
													<div class="up-btn">
														<input type="file" name="file">
													</div>
													<div class="up-area is-hide">または、ファイルをドロップしてください。</div>
													<small>jpg,jpeg,gif,png (容量 10MB、サイズ 縦：60px　横：280px まで。サイズ超過時は範囲内に収まるように自動縮小されます。）</small>
												</div>
											</div>
										</dd>
									</dl>
									<dl>
										<dt><?php echo $view->form->getElement('logo_pc_title')->getLabel() ?><i class="i-l-require<?php if (!$view->form->getElement('logo_pc_title')->isRequired()) : ?> is-hide<?php endif; ?>">必須</i></dt>
										<dd>
											<div class="<?php if ($view->form->getElement('logo_pc_title')->isRequired()) : ?>is-require<?php endif; ?>">
												<?php $view->form->form('logo_pc_title') ?>
												<span class="input-count"></span>
											</div>
										</dd>
									</dl>
								</div>
								<div class="f-logo-preview">
									<div class="up-preview">
										<a class="i-e-delete" href="javascript:;"></a>
									</div>
									<div class="input-img-title">
										<?php $view->form->form('logo_pc_text') ?>
										<span class="input-count"></span>
									</div>
								</div>
								<div class="errors">
									<?php foreach ($view->form->getGroupErrors(array('logo_pc', 'logo_pc_title', 'logo_pc_text')) as $message) : ?>
										<p class="error"><?php echo h($message) ?></p>
									<?php endforeach; ?>
								</div>
							</td>
						</tr>

						<tr>
							<th><span>サイトロゴ<?php echo $view->toolTip('site_logo_sp') ?><i class="i-l-require" style="float:right;">必須</i></span>（スマホ）</th>
							<td class="f-logo upload-block">
								<div class="f-logo-input">
									<dl>
										<dt>画像</dt>
										<dd>
											<div class="f-img-upload">
												<?php $view->form->form('logo_sp') ?>
												<div class="up-img">
													<div class="up-btn">
														<input type="file" name="file">
													</div>
													<div class="up-area is-hide">または、ファイルをドロップしてください。</div>
													<small>jpg,jpeg,gif,png (容量 10MB、サイズ 縦：200px　横：200px まで。サイズ超過時は範囲内に収まるように自動縮小されます。）</small>
												</div>
											</div>
										</dd>
									</dl>
									<dl>
										<dt><?php echo $view->form->getElement('logo_sp_title')->getLabel() ?><i class="i-l-require<?php if (!$view->form->getElement('logo_sp_title')->isRequired()) : ?> is-hide<?php endif; ?>">必須</i></dt>
										<dd>
											<div class="<?php if ($view->form->getElement('logo_sp_title')->isRequired()) : ?>is-require<?php endif; ?>">
												<?php $view->form->form('logo_sp_title') ?>
												<span class="input-count"></span>
											</div>
										</dd>
									</dl>
								</div>
								<div class="f-logo-preview">
									<div class="up-preview">
										<a class="i-e-delete" href="javascript:;"></a>
									</div>
									<div class="input-img-title">
										<?php $view->form->form('logo_sp_text') ?>
										<span class="input-count"></span>
									</div>
								</div>
								<div class="errors">
									<?php foreach ($view->form->getGroupErrors(array('logo_sp', 'logo_sp_title', 'logo_sp_text')) as $message) : ?>
										<p class="error"><?php echo h($message) ?></p>
									<?php endforeach; ?>
								</div>
							</td>
						</tr>
					</table>
				</div>
			<?php endif ?>

			<?php if (!getInstanceUser('cms')->checkHasTopOriginal()) : ?>
				<div class="section">
					<h2>著作権表記<?php echo $view->toolTip('copylight') ?></h2>
					<p class="color-label">著作権者を表す著作権表記（コピーライト表記）の英文社名を入れてください。</p>
					<div class="f-copyright">
						Copyright(c)
						<div class="input-count-wrap">
							<?php $view->form->form('copylight') ?><span class="input-count"></span>
						</div>
						.All Rights Reserved.
					</div>

					<div class="errors">
						<?php foreach ($view->form->getElement('copylight')->getMessages() as $message) : ?>
							<p class="error"><?php echo h($message) ?></p>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif ?>

			<div class="section">
				<h2>SNS</h2>
				<table class="form-basic">
					<tr>
						<th><span>Facebook<?php echo $view->toolTip('site_facebook') ?></span></th>
						<td class="f-switch">
							<div class="switch-area">
								<?php $name = 'fb_like_button_flg'; ?>
								<label><?php echo $view->form->getElement($name)->getLabel() ?></label>
								<div class="switch">
									<?php foreach ($view->form->getElement($name)->getValueOptions() as $value => $label) : ?>
										<input type="radio" id="<?php echo $name ?>_<?php echo $value ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" class="<?php echo $value ? 'on' : 'off' ?>" <?php if ($value == $view->form->getElement($name)->getValue()) : ?> checked="checked" <?php endif; ?>>
										<label for="<?php echo $name ?>_<?php echo $value ?>"><?php echo h($label) ?></label>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="switch-area">
								<?php $name = 'fb_timeline_flg'; ?>
								<label><?php echo $view->form->getElement($name)->getLabel() ?></label>
								<div class="switch">
									<?php foreach ($view->form->getElement($name)->getValueOptions() as $value => $label) : ?>
										<input type="radio" id="<?php echo $name ?>_<?php echo $value ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" class="<?php echo $value ? 'on' : 'off' ?>" <?php if ($value == $view->form->getElement($name)->getValue()) : ?> checked="checked" <?php endif; ?>>
										<label for="<?php echo $name ?>_<?php echo $value ?>"><?php echo h($label) ?></label>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="item-set-list">
								<dl class="is-require">
									<dt><span><?php echo $view->form->getElement('fb_page_url')->getLabel() ?></span></dt>
									<dd><?php $view->form->form('fb_page_url') ?><span class="input-count"></span>
										<small>「Facebookページ」のURLを設定してください。</small>
										<small>「Facebookページ」は<a target="_blank" href="https://www.facebook.com/pages/create/ ">こちら</a>よりご登録いただくページとなります。</small>
									</dd>
								</dl>

							</div>

							<div class="errors">
								<?php foreach ($view->form->getGroupErrors(array('fb_like_button_flg', 'fb_timeline_flg', 'fb_page_url')) as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th><span>Twitter<?php echo $view->toolTip('site_twitter') ?></span></th>
						<td class="f-switch">
							<div class="switch-area">
								<?php $name = 'tw_tweet_button_flg'; ?>
								<label><?php echo $view->form->getElement($name)->getLabel() ?></label>
								<div class="switch">
									<?php foreach ($view->form->getElement($name)->getValueOptions() as $value => $label) : ?>
										<input type="radio" id="<?php echo $name ?>_<?php echo $value ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" class="<?php echo $value ? 'on' : 'off' ?>" <?php if ($value == $view->form->getElement($name)->getValue()) : ?> checked="checked" <?php endif; ?>>
										<label for="<?php echo $name ?>_<?php echo $value ?>"><?php echo h($label) ?></label>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="switch-area">
								<?php $name = 'tw_timeline_flg'; ?>
								<label><?php echo $view->form->getElement($name)->getLabel() ?></label>
								<div class="switch">
									<?php foreach ($view->form->getElement($name)->getValueOptions() as $value => $label) : ?>
										<input type="radio" id="<?php echo $name ?>_<?php echo $value ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" class="<?php echo $value ? 'on' : 'off' ?>" <?php if ($value == $view->form->getElement($name)->getValue()) : ?> checked="checked" <?php endif; ?>>
										<label for="<?php echo $name ?>_<?php echo $value ?>"><?php echo h($label) ?></label>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="item-set-list">
								<dl class="is-hide">
									<dt><?php echo $view->form->getElement('tw_widget_id')->getLabel() ?></dt>
									<dd><?php $view->form->form('tw_widget_id') ?><span class="input-count"></span></dd>
								</dl>
							</div>
							<div class="item-set-list">
								<dl class="is-require">
									<dt><span><?php echo $view->form->getElement('tw_username')->getLabel() ?></span></dt>
									<dd><?php $view->form->form('tw_username') ?><span class="input-count"></span></dd>
								</dl>
							</div>

							<div class="errors">
								<?php foreach ($view->form->getGroupErrors(array('tw_tweet_button_flg', 'tw_timeline_flg', 'tw_widget_id', 'tw_username')) as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>

							<div class="item-set-list" style="margin-bottom:10px;">
								<small style="color:black;">※twitter.comがサポートしていないブラウザ（Internet Explorer11など）では正常に表示されないことがあります。</small>
							</div>
						</td>
					</tr>
					<tr>
						<th><span>LINE<?php echo $view->toolTip('site_line') ?></span></th>
						<td class="f-switch">
							<div class="switch-area">
								<?php $name = 'line_button_flg'; ?>
								<label><?php echo $view->form->getElement($name)->getLabel() ?></label>
								<div class="switch">
									<?php foreach ($view->form->getElement($name)->getValueOptions() as $value => $label) : ?>
										<input type="radio" id="<?php echo $name ?>_<?php echo $value ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" class="<?php echo $value ? 'on' : 'off' ?>" <?php if ($value == $view->form->getElement($name)->getValue()) : ?> checked="checked" <?php endif; ?>>
										<label for="<?php echo $name ?>_<?php echo $value ?>"><?php echo h($label) ?></label>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="errors">
								<?php foreach ($view->form->getElement('line_button_flg')->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
				</table>
			</div>


			<div class="section">
				<h2>LINE公式アカウント</h2>
				<table class="form-basic">
					<tr>
						<th><span><?php echo $view->form->getElement('line_at_freiend_qrcode')->getLabel() ?><?php echo $view->toolTip('site_line_at_freiend_qrcode') ?></span></th>
						<td>
							<?php $view->form->form('line_at_freiend_qrcode') ?>
							<div class="errors">
								<?php foreach ($view->form->getElement('line_at_freiend_qrcode')->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th><span><?php echo $view->form->getElement('line_at_freiend_button')->getLabel() ?><?php echo $view->toolTip('site_line_at_freiend_button') ?></span></th>
						<td>
							<?php $view->form->form('line_at_freiend_button') ?>
							<div class="errors">
								<?php foreach ($view->form->getElement('line_at_freiend_button')->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
				</table>
			</div>


			<div class="section">
				<h2>QRコード</h2>
				<table class="form-basic">
					<tr>
						<th><span><?php echo $view->form->getElement('qr_code_type')->getLabel() ?><?php echo $view->toolTip('site_qr') ?></span></th>
						<td>
							<?php $view->form->form('qr_code_type') ?>
							<div class="errors">
								<?php foreach ($view->form->getElement('qr_code_type')->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<div style="height:0;width:0;overflow:hidden;"><input type="text" name="_dummytext"></div>
			<div class="section">
				<h2>テストサイトパスワード設定</h2>
				<table class="form-basic">
					<tr class="<?php if ($view->form->getElement('test_site_password')->isRequired()) : ?>is-require<?php endif; ?>">
						<th><span><?php echo $view->form->getElement('test_site_password')->getLabel() ?><?php echo $view->toolTip('site_test_password') ?></span></th>
						<td><?php $view->form->form('test_site_password') ?><span class="input-count"></span>
							<div class="errors">
								<?php foreach ($view->form->getElement('test_site_password')->getMessages() as $message) : ?>
									<p class="error"><?php echo h($message) ?></p>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<?php if( $view->cms_plan > config('constants.cms_plan.CMS_PLAN_LITE')):?>
			<div class="section">
				<h2>反響プラス<?php echo $view->toolTip('hankyo_plus')?></h2>
				<table class="form-basic">
					<tr>
						<th><span><?php echo $view->form->getElement('hankyo_plus_use_flg')->getLabel() ?><?php echo $view->toolTip('hankyo_plus_check')?></span></th>
						<td>
							<?php $view->form->form('hankyo_plus_use_flg')?>
							<div class="errors">
								<?php foreach ($view->form->getElement('hankyo_plus_use_flg')->getMessages() as $message):?>
									<p class="error"><?php echo h($message)?></p>
								<?php endforeach;?>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<?php else: ?>
				<input type="hidden" name="hankyo_plus_use_flg" value="0">
			<?php endif;?>

			<div class="btns">
				<input class="btn-t-blue size-l<?php if ($hasReserve) : ?> is-disable<?php endif; ?> <?php if (!$isInitialize) : ?>basic-setting<?php endif; ?>" type="submit" value="<?php if ($isInitialize) : ?>保存して次へ<?php else : ?>保存<?php endif; ?>">
			</div>
			<div style="height:0;width:0;overflow:hidden;"><input type="password" name="_dummypass"></div>
		</form>

	</div>
</div>
@endsection