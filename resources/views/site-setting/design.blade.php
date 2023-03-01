<?php
use Library\Custom\Model\Master\Theme;
use Library\Custom\Model\Master\Color;
use Library\Custom\Model\Master\Layout;
use App\Repositories\MTheme\MThemeRepositoryInterface;
?>
@php
	$layout = 'layouts.default';
	$titleInitialize = __('デザイン選択');
	if (isset($view->layout)) {
		$layout = 'layouts.'.$view->layout;
		$titleInitialize = __('');
	}
@endphp
@extends($layout)

@section('style')
<link rel="stylesheet" href="/css/libs/spectrum.css">
@stop

@section('title', $titleInitialize)

@section('script')
	<?php if(isCurrent(null, 'initialize')):?>
	<script type="text/javascript" src="/js/initialize.js" ></script>
	<?php endif;?>
	<script type="text/javascript" src="/js/libs/spectrum.js" > </script>
	<script type="text/javascript">
	$(function () {

		$('.section').each(function () {
			var $section = $(this);
			var $input = $section.find('input[type="hidden"]');

			$section.on('click', '[data-id]', function () {
				var $this = $(this);
				$section.find('.is-active').removeClass('is-active');
				$this.addClass('is-active');
				$input.val($this.attr('data-id')).change();
			});

			if (!$section.find('[data-id].is-active:not(.is-hide)').length) {
				$section.find('[data-id]:not(.is-hide)').click();
			}
		});

		var $themeSection = $('.design-theme');
		var $theme = $themeSection.find('input[name="theme_id"]');
		var $colors = $('.design-color [data-id]');
		var $layouts = $('.design-layout [data-id]');

		//デザインパターン追加（カラー自由版）
		var $colorCode = $('#color_code').val();
		if(getCurrentTheme().indexOf("custom_color") != -1) {
			$(".design-color").addClass("is-hide");
			if($("#color_code").val() == "") $("#color_code").val(spectrum);
		}else{
			$(".design-color-code").addClass("is-hide");
		}
		//デザインパターン追加（カラー自由版）

		function getCurrentTheme() {
			return $themeSection.find('[data-id="' + ($theme.val() || '') + '"]').attr('data-theme-name');
		}

		$('.design-theme input[type="hidden"]').on('change', function () {
			var themeName = getCurrentTheme();
			$colors.addClass('is-hide')
				.filter('[data-theme-name="' + themeName + '"]').removeClass('is-hide').eq(0).click();
			$layouts.addClass('is-hide')
				.filter('[data-theme-name="' + themeName + '"]').removeClass('is-hide').eq(0).click();

			//デザインパターン追加（カラー自由版）
			if(themeName.indexOf("custom_color") != -1) {
				$(".design-color").addClass("is-hide");
				$(".design-color-code").removeClass("is-hide");
				if($("#color_code").val() == "") $("#color_code").val(spectrum);
				$colors.addClass('is-hide');
				$('input[name="color_id"]').val(0);
			}else{
				$(".design-color-code").addClass("is-hide");
				$('#color_code').val("");
				$(".design-color").removeClass("is-hide");
			}
			//デザインパターン追加（カラー自由版）
		});

		// サンプル
		$( '.lock-design-note  button'	).hide() ;
		var showSample = function () {

			//デザインパターン追加（カラー自由版）
			var color = 0;
			var color_code = '';

			if(getCurrentTheme().indexOf("custom_color") != -1) {
				var pattern = new RegExp( '[0-9a-fA-F]{6}', 'g' );
				var cc = $('#color_code').val().replace('#', '');
				if(cc == "") {
					$(".design-color-code .errors").html("値を設定してください。");
					return;
				}else if(cc.length != 6 || cc.match(pattern) == null) {
					$(".design-color-code .errors").html("値を設定してください。");
					return;
				}
				color_code = cc;

			}else{
				color = $colors.filter('.is-active').attr('data-name');
			}
			//デザインパターン追加（カラー自由版）
			
			var params = {
				theme: getCurrentTheme(),
				color: color,
				color_code: color_code,
				layout: $layouts.filter('.is-active').attr('data-name'),
				type: $(this).attr('data-type')
			};

			var url = '/design-sample?' + $.param(params);
			var features = 'width=1020,height=600,menubar=no,toolbar=no,location=no,scrollbars=yes';
			window.open(url, '_blank', features);
		} ;
		$( '.show-sample'		).on( 'click', 'a'		, showSample ) ;
		$( '.lock-design-note'	).on( 'click', 'button'	, function() {
			showSample()	;
		});

		$form = $('form');
		app.initApiForm($form, $form.find('input[type="submit"]'), function (data) {
			if (data.redirectTo) {
				location.href = data.redirectTo;
				return;
			}

		var links = [
		{title: '公開設定へ', url: '/publish/simple'},
		{title: 'ホームへ', url: '/'}
		];

		if ($('.i-m-deputize').parent('li').find('a:contains("代行作成テストサイト確認")').hasClass('is-disable')){
		links.shift();
		}

			app.modal.message({
				message: '設定を保存しました。',
				links: links,
				ok: '閉じる',
				cancel: false
			});
		});

		// color picker
		var spectrum = "#434343";
		if($('#color_code').val() != '') spectrum = '#' + $('#color_code').val();
		$("#color_code").spectrum({
			color: spectrum,
			showInput: true,
			containerClassName: "spectrum-wrap",
			replacerClassName: "spectrum-btn",
			showInitial: true,
			showPalette: true,
			showSelectionPalette: false,
			chooseText: "選択",
			cancelText: "キャンセル",
			preferredFormat: "hex",
			localStorageKey: "spectrum",
			palette: [
				["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", 
				"rgb(204, 204, 204)", "rgb(217, 217, 217)", "rgb(255, 255, 255)"],
				["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
				"rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
				["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
				"rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
				"rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
				"rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
				"rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
				"rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
				"rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
				"rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
				"rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
				"rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
			]
		});
	});
</script>
@endsection
@section('content')

<div class="main-contents">

	<?php if(!($isInitialize = isCurrent(null, 'initialize'))):?>
	<h1>デザイン選択</h1>
	<?php endif;?>

	<div class="main-contents-body">

	<?php if($hasReserve = getInstanceUser('cms')->getCurrentHp()->hasReserve()):?>
	<div class="alert-strong">「サイトの公開/更新」画面にて公開・停止の予約設定がされています。予約設定解除後にこのページを「保存」できます。</div>
	<?php endif;?>

	<?php if ($isInitialize):?>
		@include('initialize._step')
	<?php endif;?>


	<?php if (isset($view->messages) && ($view->messages || $view->form->hasErrors())):?>
	<div class="section">
		<?php if($view->form->hasErrors()):?>
		<p class="alert-strong">入力エラーがあります。</p>
		<?php endif;?>
		<?php if ($view->messages) foreach ((array)$view->messages as $message):?>
		<p class="alert-normal"><?php echo h($message)?></p>
		<?php endforeach;?>
	</div>
	<?php endif;?>
	<form data-api-action="<?php if(!$isInitialize){ echo route('default.site-setting.api-save-design'); } else { echo route('api-save-design'); } ?>" method="post">
			@csrf

		<div class="section design-theme">
			<input type="hidden" name="theme_id" value="<?php echo h($view->form->getElement('theme_id')->getValue())?>">
			<div class="section-header">
				<h2>テーマ<?php echo $view->toolTip('design_theme')?></h2>
				<ul class="header-description flL">
					<li>
						<dl>

							<dt><i class="i-dt-color"></i></dt>
							<dd>・・・カラーコードでカラーを選択できます。</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt><i class="i-dt-wide"></i></dt>
							<dd>・・・メインイメージがワイドになるデザインです。</dd>
						</dl>
					</li>
				</ul>
			</div>
			<div class="errors error-theme_id"></div>

			<!-- Place somewhere in the <body> of your page -->
			<div class="flexslider">
			<ul class="slides">
				<?php foreach (Theme::getInstance()->getThemeRowsetByGroup(getInstanceUser('cms')->getProfile()->id) as $group):?>
				<li>
					<?php foreach ($group as $row):?>
					<a href="javascript:;" data-id="<?php echo $row->id?>" data-theme-name="<?php echo $row->name?>" class="<?= ( $view->form->getElement('theme_id')->getValue() == $row->id ) ? 'is-active' : 'is-lock-design' ;?>">
						<div <?php if ( $row->plan == 0 ) : ?>style="opacity: 0.5"<?php endif;?> >
							<img src="/cms_designselectpage/theme/<?php echo $row->name?>/pc.jpg" alt="<?php echo h($row->name)?>">
							<img src="/cms_designselectpage/theme/<?php echo $row->name?>/sp.jpg" alt="<?php echo h($row->name)?>">
							<?php if ( $row->plan == 0 ) : ?>
								<div class="lock-design-note">
									<p>
										<?php if($row->plan_standard == 0) : ?>
											アドバンスプランでご利用可能です。
										<?php elseif ($row->plan_lite == 0) : ?>
											アドバンスまたはスタンダードプランでご利用可能です。
										<?php endif ; ?>
										<br />デザインはサンプルよりご覧いただけます。</p>
									<ul>
										<?php $color = ( \App::make(MThemeRepositoryInterface::class)->isFreeColorTheme($row->id) ) ? 'color_code=4a86e8' : 'color=default' ?>
										<li><button type="button" class="btn-pW-pc" onclick="javascript:;" data-type="pc">PCサイトのサンプルを見る</button></li>
										<li><button type="button" class="btn-pW-sp" onclick="javascript:window.open('/design-sample?theme=<?php echo h($row->name)?>&<?= $color ?>&layout=left&type=sp', '_blank', 'width=1020,height=600,menubar=no,toolbar=no,location=no,scrollbars=yes')">スマホサイトのサンプルを見る</button></li>
									</ul>
							</div>
							<?php endif ; ?>
						</div>
						<?php if(\App::make(MThemeRepositoryInterface::class)->isFreeColorTheme($row->id)) :?>
						<i class="i-dt-new">NEW</i>
						<?php endif;?>
						<span><?php echo h($row->title)?></span>
						<?php if(\App::make(MThemeRepositoryInterface::class)->isFreeColorTheme($row->id)) :?>
						<div class="icon-wrap">
							<i class="i-dt-color">カラーコードでカラーを選択できます。</i>
							<?php if(\App::make(MThemeRepositoryInterface::class)->getThemeName($row->id) == "standard02_custom_color") : ?><i class="i-dt-wide">メインイメージがワイドになるデザインです。</i><?php endif; ?>
						</div>
						<?php endif;?>
					</a>
					<?php endforeach;?>
				</li>
				<?php endforeach;?>
			</ul>
			</div>

		</div>

		<div class="section design-color">
			<input type="hidden" name="color_id" value="<?php echo h($view->form->getElement('color_id')->getValue())?>">
			<h2>ベースカラー<?php echo $view->toolTip('design_color')?></h2>
			<ul>
				<?php $themeName = Theme::getInstance()->get( $view->form->getElement('theme_id')->getValue() )?>
				<?php foreach (Color::getInstance()->getRowset() as $row):?>
				<?php if($row->name == "custom_color") continue; ?>
				<li data-id="<?php echo $row->id?>" data-name="<?php echo $row->name?>" data-theme-name="<?php echo $row->theme_name?>" class="<?php if($themeName != $row->theme_name):?>is-hide<?php endif;?> <?php if ($view->form->getElement('color_id')->getValue() == $row->id):?>is-active<?php endif;?>">
					<a href="javascript:;">
						<img style="width:100%" alt="<?php echo $row->name?>" src="/cms_designselectpage/theme/<?php echo $row->theme_name?>/color/<?php echo $row->name?>/color.gif">
					</a>
				</li>
				<?php endforeach;?>
			</ul>
		</div>

		<?php //デザインパターン追加（カラー自由版）?>
		<div class="section design-color-code">
			<h2>ベースカラーコード<?php echo $view->toolTip('design_color_code')?></a>
			</h2>
			<input type="text" name="color_code" id="color_code" value="<?php echo h($view->form->getElement('color_code')->getValue())?>" width="100" maxlength="6" style="display: none;">
			<div class="errors"></div>
		</div>

		<?php //デザインパターン追加（カラー自由版）?>

		<div class="section design-layout">
			<input type="hidden" name="layout_id" value="<?php echo h($view->form->getElement('layout_id')->getValue())?>">
			<h2>レイアウト<?php echo $view->toolTip('design_layout')?></h2>

			<ul>
				<?php $themeName = Theme::getInstance()->get( $view->form->getElement('theme_id')->getValue() )?>
				<?php foreach (Layout::getInstance()->getRowset() as $row):?>
				<li data-id="<?php echo $row->id?>" data-name="<?php echo $row->name?>" data-theme-name="<?php echo $row->theme_name?>" class="<?php if($themeName != $row->theme_name):?>is-hide<?php endif;?> <?php if ($view->form->getElement('layout_id')->getValue() == $row->id):?>is-active<?php endif;?>">
					<a href="javascript:;">
						<img alt="<?php echo $row->name?>" src="/cms_designselectpage/theme/<?php echo $row->theme_name?>/layout/<?php echo $row->name?>.gif">
					</a>
				</li>
				<?php endforeach;?>
			</ul>
		</div>


		<div class="show-sample">
			<span>サンプルを見る</span>

			<a href="javascript:;" class="btn-t-gray" data-type="pc">PCサイトサンプル</a>
			<a href="javascript:;" class="btn-t-gray" data-type="sp">スマホサイトサンプル</a>
		</div>


		<div class="btns">
			<?php if ($isInitialize):?>
			<a class="btn-t-blue size-l" id="back" href="javascript:void(0)" data-link="<?php echo route('default.initialize.index')?>">戻る</a>
			<?php endif;?>
			<input type="submit" value="<?php if ($isInitialize):?>保存して次へ<?php else:?>保存<?php endif;?>" class="btn-t-blue size-l<?php if($hasReserve):?> is-disable<?php endif;?> <?php if (!$isInitialize):?>basic-setting<?php endif;?>">
		</div>

	</form>

</div>
</div>
@endsection