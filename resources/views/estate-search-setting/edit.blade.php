@extends('layouts.default')

@section('title', __('物件検索設定'))

@section('style')
<link href="/css/estate_extension.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript" src="/js/app.estate.js?v=20180410"></script>
<script type="text/javascript">
	(function() {

		'use strict';

		$(function() {

			var Master = {
				prefMaster: <?php echo json_encode($view->prefMaster) ?>,
				searchTypeMaster: <?php echo json_encode($view->searchTypeMaster) ?>,
				searchTypeConst: <?php echo json_encode($view->searchTypeConst) ?>,
				estateTypeMaster: <?php echo json_encode($view->estateTypeMaster) ?>
			};

			var EstateMaster = app.estate.EstateMaster;
			var setting = <?php echo json_encode($view->setting) ?>;
			var isFDP = <?php echo ($view->fdp) ? 'true' : 'false'; ?>;
			var townLabel = <?php echo json_encode($view->townLabel); ?>;

			function hasSearchType(type) {
				return $.inArray('' + type, setting.area_search_filter.search_type) > -1;
			}

			function needShikugunSetting() {
				return hasSearchType(Master.searchTypeConst.TYPE_AREA) || hasSearchType(Master.searchTypeConst.TYPE_SPATIAL);
			}

			function needEnsenSetting() {
				return hasSearchType(Master.searchTypeConst.TYPE_ENSEN);
			}

			var step = new app.estate.Step($('.js-step'), $('#topicpath'));
			var $contents = $('.js-step-contents');

			//-----------------
			// 基本設定
			//-----------------
			var basicSetting = step.createContents($contents.eq(0), $('#topicpath .last').html(), function() {
				var i, l;

				// 種目
				var $estateType = this.$element.find('#enabled_estate_type input');
				$estateType.prop('checked', false);
				for (i = 0, l = setting.enabled_estate_type.length; i < l; i++) {
					$estateType.filter('[value="' + setting.enabled_estate_type[i] + '"]').prop('checked', true);
				}

				// 探し方
				var $searchType = this.$element.find('#search_type input:checkbox');
				$searchType.prop('checked', false);
				for (i = 0, l = setting.area_search_filter.search_type.length; i < l; i++) {
					$searchType.filter('[value="' + setting.area_search_filter.search_type[i] + '"]').prop('checked', true);
				}

				var $fdpType = this.$element.find('#display_fdp input');
				$fdpType.prop('checked', false);
				var fdpLen = 0;
				if (setting.display_fdp.fdp_type) {
					fdpLen = setting.display_fdp.fdp_type.length;
				}
				for (i = 0, l = fdpLen; i < l; i++) {
					if (isFDP) {
						$fdpType.filter('[value="' + setting.display_fdp.fdp_type[i] + '"]').prop('checked', true);
					}
				}

				// 「地図から探す」は、オプション
				if (setting.mapOption == false) {
					var $target = $searchType.filter('[value="' + setting.typeSpatial + '"]');
					$target.prop('checked', false);
					$target.prop('disabled', true);
					$target.parent().addClass('is-disable');
					$target.parent().hide();
				}

				// 町名検索
				var $chosonSearchContainer = this.$element.find('.choson-search-input');
				var $chosonSearch = this.$element.find('.choson-search-input input');
				$chosonSearch.prop('checked', false);
				$chosonSearch.filter('[value="' + setting.area_search_filter.choson_search_enabled + '"]').prop('checked', true);
				// 地域から探す場合のみ活性
				this.$element.find('#search_type-1').change(function() {
					var isDisabled = !$(this).prop('checked');
					$chosonSearchContainer.toggleClass('is-disable', isDisabled);
					$chosonSearch.prop('disabled', isDisabled);
					if (isDisabled) {
						$chosonSearch.filter('[value="0"]').prop('checked', true);
					}
				}).change();

				var $displayTownContainer = this.$element.find('#display_town');
				var $displayTown = this.$element.find('#display_town input');
				this.$element.find('#display_fdp-3').change(function() {
					var isDisabled = !$(this).prop('checked');
					$displayTownContainer.toggleClass('is-disable', isDisabled);
					$displayTown.prop('disabled', isDisabled);
					if (isDisabled) {
						// 4489: Change UI setting FDP
						$('#display_fdp p').toggleClass('is-disable', isDisabled);
						$displayTown.prop('checked', false);
						// 13008: Remove html error
						$displayTownContainer.find('.errors').html('');
						//$displayTownContainer.hide();
					} else {
						// 4489: Change UI setting FDP
						$('#display_fdp p').toggleClass('is-disable', isDisabled);
						$displayTownContainer.show();
						$displayTown.prop('checked', true);
					}
				}).change();

				$displayTown.prop('checked', false);
				var townLen = 0;
				if (setting.display_fdp.town_type) {
					townLen = setting.display_fdp.town_type.length;
				}
				for (i = 0, l = townLen; i < l; i++) {
					$displayTown.filter('[value="' + setting.display_fdp.town_type[i] + '"]').prop('checked', true);
				}


				var $searchOption = $(
					'<div class="search-option" style="display:none;">' +
					'<p>現在地から探す（スマホのみ）</p>' +
					'<ul>' +
					'<li><label><input type="radio" class="map_search_here_enabled" name="map_search_here_enabled" id="map_search_here_enabled1" value="1">利用する</label></li>' +
					'<li><label><input type="radio" class="map_search_here_enabled" name="map_search_here_enabled" id="map_search_here_enabled0" value="0">利用しない</label></li>' +
					'</ul>' +
					'</div>'
				);
				// 初期ph3では現在地から探すをはずす
				$searchOption = $('<input type="hidden" class="map_search_here_enabled" name="map_search_here_enabled" id="map_search_here_enabled0" value="0">');

				var $SPATIAL = this.$element.find('#search_type-' + Master.searchTypeConst.TYPE_SPATIAL);
				$SPATIAL.parents('label').after($searchOption);
				if (setting.map_search_here_enabled == 1) {
					$('#map_search_here_enabled1').prop('checked', true);
				} else {
					$('#map_search_here_enabled0').prop('checked', true);
				}
				$SPATIAL.on('change', function() {
					prop_search_here_enabled();
				});

				function prop_search_here_enabled() {
					if ($SPATIAL.prop('checked')) {
						$searchOption.fadeIn();
					} else {
						$searchOption.fadeOut();
					}
				}
				prop_search_here_enabled();

				// 都道府県
				var $pref = this.$element.find('#pref input');
				$pref.prop('checked', false);
				for (i = 0, l = setting.area_search_filter.area_1.length; i < l; i++) {
					$pref.filter('[value="' + setting.area_search_filter.area_1[i] + '"]').prop('checked', true);
				}

				// 物件リクエスト
				var $estateRequestFlg = this.$element.find('#estate_request_flg input');
				if (setting.estate_request_flg == 1) {
					$estateRequestFlg.prop('checked', true);
				}
				// get checkbox display_freeword checked
				var $displayFreeword = this.$element.find('#display_freeword input');
				if (setting.display_freeword == 1) {
					$displayFreeword.prop('checked', true);
				}

			});
			basicSetting.beforeNext = function() {
				var self = this;
				var checks = {};
				var checkLabels = {};
				$.each(['search_type', 'enabled_estate_type', 'pref', 'estate_request_flg', 'display_freeword', 'display_fdp', 'display_town'], function(i, id) {
					var $container = self.$element.find('#' + id);
					var $checked = $container.find('input:checkbox:checked:not(.map_search_here_enabled)');
					if (id == 'display_fdp') {
						$checked = $container.find('input:checkbox:checked:not(input[name="display_town_enabled"])');
					}
					if ($checked.length) {
						$container.find('.errors').html('');
						checks[id] = $checked.map(function() {
							return this.value;
						}).get();
						if (id == 'display_fdp' || id == 'display_town') {
							checkLabels[id] = $checked.map(function() {
								return $(this).closest('label').text();
							}).get();
						}
					} else {
						checks[id] = [];
						if (id == 'estate_request_flg' || id == 'display_freeword' || id == 'display_fdp' || (id == 'display_town' && !$('#display_fdp-3').is(':checked'))) return;
						$container.find('.errors').html('<p>値は必須です。</p>');
					}
				});

				if (
					!checks.search_type.length ||
					!checks.enabled_estate_type.length ||
					!checks.pref.length || (!checks.display_town.length && $('#display_fdp-3').is(':checked'))
				) {
					return false;
				}

				// データ更新
				setting.enabled_estate_type = checks.enabled_estate_type;
				setting.area_search_filter.search_type = checks.search_type;
				setting.area_search_filter.area_1 = checks.pref;
				setting.map_search_here_enabled = $('#map_search_here_enabled1').prop('checked') ? 1 : 0;
				// 物件リクエスト
				setting.estate_request_flg = checks.estate_request_flg;
				setting.display_freeword = checks.display_freeword;
				// 町名検索
				setting.area_search_filter.choson_search_enabled = this.$element.find('.choson-search-input input:checked').val() || 0;
				setting.is_fdp = isFDP;
				// 4489: Change UI setting FDP
				if (isFDP) {
					setting.display_fdp.fdp_type = checks.display_fdp;
					setting.display_fdp.town_type = checks.display_town;
					setting.town_label = townLabel[2];
					if (typeof checkLabels.display_fdp !== 'undefined' && checkLabels.display_fdp.length > 0) {
						setting.fdp_check_label = checkLabels.display_fdp.join('　');
					}
					if (typeof checkLabels.display_town !== 'undefined' && checkLabels.display_town.length > 0) {
						setting.town_check_label = checkLabels.display_town.join('　');
					}
				}
				// end 4489

			};
			basicSetting.next = function() {
				this.step.next(needShikugunSetting() ? 1 : 2);
			};

			//-----------------
			// 市区郡選択
			//-----------------
			var shikugunSetting = app.inherits(app.estate.StepContentsShikugun, function() {
				app.estate.StepContentsShikugun.apply(this, arguments);
			}, {
				getSetting: function() {
					return setting;
				}
			});
			step.addContents(new shikugunSetting(step, $contents.eq(1), Master));

			//-----------------
			// 沿線・駅選択
			//-----------------
			var ensenSetting = app.inherits(app.estate.StepContentsEnsen, function() {
				app.estate.StepContentsEnsen.apply(this, arguments);
			}, {
				getSetting: function() {
					return setting;
				}
			});
			step.addContents(new ensenSetting(step, $contents.eq(2), Master));

			//-----------------
			// 設定確認
			//-----------------
			var confirmSetting = step.createContents($contents.eq(3), '設定確認', function() {
				this.$buttons = this.$element.find('.js-prev-step,.js-next-step');

				this.basicSetting = new app.estate.ConfirmBasicSettingView(Master, <?= $view->dispEstateRequest ?>);
				$('.js-confirm-basic-setting').append(this.basicSetting.$element);

				this.confirmShikugun = new app.estate.ConfirmShikugunView(Master);
				this.$confirmShikugun = $('.js-confirm-shikuguns').append(this.confirmShikugun.$element);

				this.confirmEnsen = new app.estate.ConfirmEnsenView(Master);
				this.$confirmEnsen = $('.js-confirm-ensens').append(this.confirmEnsen.$element);
			});
			confirmSetting.show = function() {
				this.basicSetting.render(setting);

				this.renderShikugun();
				this.renderEnsen();

				app.estate.StepContents.prototype.show.call(this);
			};
			confirmSetting.renderShikugun = function() {
				if (!hasSearchType(Master.searchTypeConst.TYPE_AREA) && !hasSearchType(Master.searchTypeConst.TYPE_SPATIAL)) {
					this.$confirmShikugun.hide();
					return;
				}

				this.confirmShikugun.render(setting);

				this.$confirmShikugun.show();
			};
			confirmSetting.renderEnsen = function() {
				if (!hasSearchType(Master.searchTypeConst.TYPE_ENSEN)) {
					this.$confirmEnsen.hide();
					return;
				}

				this.confirmEnsen.render(setting);

				this.$confirmEnsen.show();
			};
			confirmSetting.prev = function() {
				if (this.$buttons.hasClass('is-disable')) {
					return;
				}
				this.step.prev(needEnsenSetting() ? 1 : 2);
			};
			confirmSetting.next = function() {
				if (this.$buttons.hasClass('is-disable')) {
					return;
				}
				this.$buttons.addClass('is-disable');
				this.step.lock(true);

				var self = this;

				if (!hasSearchType(Master.searchTypeConst.TYPE_AREA) && !hasSearchType(Master.searchTypeConst.TYPE_SPATIAL)) {
					setting.area_search_filter.area_2 = {};
					setting.area_search_filter.area_5 = {};
					setting.area_search_filter.area_6 = {};
				}
				if (!hasSearchType(Master.searchTypeConst.TYPE_ENSEN)) {
					setting.area_search_filter.area_3 = {};
					setting.area_search_filter.area_4 = {};
				}
				if (setting.area_search_filter.choson_search_enabled != 1) {
					setting.area_search_filter.area_5 = {};
					setting.area_search_filter.area_6 = {};
				}

				var saveData = $.extend({}, setting);
				saveData.area_search_filter = JSON.stringify(setting.area_search_filter);

				app.api('/estate-search-setting/api-save', saveData, function(data) {
						// 問い合わせフォームへのリンクを設定
						$('.js-estate-form-link').attr('href', '/page/edit?id=' + data.estateFormId);

						self.step.lock(false);
						self.step.next();
						self.step.lock(true);
						app.updateAlertPublish();
					})
					.fail(function() {
						self.step.lock(false);
					})
					.always(function() {
						self.$buttons.removeClass('is-disable');
					});
			};

			//-----------------
			// 設定完了
			//-----------------
			var completeSetting = step.createContents($contents.eq(4), '設定完了');
		});

	})();

	$(function() {
		var spEstateTypes = [<?php echo implode(",", $view->spEstateTypes); ?>];
		var pubEstateTypes = [<?php echo implode(",", $view->pubEstateTypes); ?>];
		$("#enabled_estate_type input[name='enabled_estate_type[]']").change(function() {
			if ($(this).prop("checked") === false) {

				/**
             ATHOME_HP_DEV-5184 : コメントアウト
			if($("#enabled_estate_type input[name='enabled_estate_type[]']").length === 1) {
				return;
			}
			*/

				if ($.inArray(Number($(this).val()), spEstateTypes) >= 0) {
					$(this).prop("checked", true)
					app.modal.message({
						title: '',
						message: '特集で利用中のため削除できません。',
						closeButton: true,
						cancel: false,
						ok: 'OK'
					}).show();
					return;
				}

				if ($.inArray(Number($(this).val()), pubEstateTypes) >= 0) {
					$(this).prop("checked", true)
					app.modal.alertBanDeletePage('削除対象種目へのリンクが公開予約設定中のため、変更・削除ができません。', '「サイトの公開/更新」の「公開設定（詳細設定）」より公開予約の解除を行ってください。<br>公開予約の解除後、「削除」することができます。');
				}
			}
		});
	});
</script>
@endsection

@section('content')
<div class="main-contents article-search">
	<h1>物件検索設定（<?php echo $view->estateClassName ?>）：<span class="js-h1-title">基本設定</span></h1>
	<div class="main-contents-body">

		<div class="setting-flow js-step">
			<p><img src="/images/article/bg_setting_flow1.png?v=20171213" alt="基本設定"></p>
			<p style="display:none;"><img src="/images/article/bg_setting_flow2.png?v=20171213" alt="市区郡・町名選択※「市区郡を対象」選択時"></p>
			<p style="display:none;"><img src="/images/article/bg_setting_flow3.png?v=20171213" alt="沿線・駅選択※「沿線・駅を対象」選択時"></p>
			<p style="display:none;"><img src="/images/article/bg_setting_flow4.png?v=20171213" alt="設定確認"></p>
			<p style="display:none;"><img src="/images/article/bg_setting_flow5.png?v=20171213" alt="設定完了"></p>
		</div>

		<div class="js-step-contents">
			<h2>検索エンジンの基本設定</h2>
			<div class="section">
				<p class="mb10">
					<?php echo $view->estateClassName ?>の基本設定を行います。<br>
					検索エンジンで取り扱う探し方・物件種目・都道府県を選択してください。
				</p>

				<table class="form-basic">
					<?php foreach ($view->form->getElements() as $name => $elem) : ?>
						<?php // 4489: Change UI setting FDP
						if ($name == 'fdp_not_use') : continue;
						endif; ?>
						<?php if ($elem->isRequired() == true) : ?>
							<tr class="is-require">
							<?php else : ?>
							<tr>
							<?php endif; ?>
							<th><span><?php echo $view->form->getElement($name)->getLabel() ?><?php echo $view->toolTip('estate_search_setting-' . $name) ?></span></th>
							<td id="<?php echo $name ?>" <?php if ($name == 'pref') : ?> class="prefectures" <?php endif; ?>>
								<?php if ($name == 'estate_request_flg') : ?>
									<label><?php echo $view->form->form($name) ?> 利用する</label>
								<?php elseif ($name == 'display_freeword') : ?>
									<label><?php echo $view->form->form($name) ?> 利用する</label>
								<?php // 4489: Change UI setting FDP
								elseif ($name == 'display_fdp') : ?>
									<?php if ($view->fdp) : ?>
										<?php echo $view->form->form($name); ?></label>
									<?php else : ?>
										<label class="is-disable fdp-not-use"><?php echo $view->form->form($name) ?></label>
									<?php endif; // end 4489 
									?>
								<?php else : ?>
									<?php echo $view->form->form($name) ?>
								<?php endif; ?>

								<?php if ($name == 'search_type') : ?>
									<!-- 町名検索設定 -->
									<ul class="choson-search-input">
										<li><label><input type="radio" name="choson_search_enabled" value="0">市区郡まで検索させる</label></li>
										<li><label><input type="radio" name="choson_search_enabled" value="1">町名まで検索させる</label></li>
									</ul>
								<?php endif; ?>

								<?php // 4489: Change UI setting FDP
								if ($name == 'display_fdp') : ?>
									<p class="fdp-text"><strong><?php echo $view->townLabel[1]; ?></strong></p>
									<ul id="display_town" class="choson-display-town <?php if (!$view->fdp) {
																							echo 'fdp-not-use';
																						} ?>">
										<?php foreach ($view->town as $key => $value) { ?>
											<li><label><input type="checkbox" name="display_town_enabled" value="<?php echo $key; ?>"><?php echo $value; ?></label></li>
										<?php } ?>
										<div class="errors"></div>
									</ul>

									<?php if (!$view->fdp) :
										echo $view->form->getElement('fdp_not_use')->getAttribute('data-register-link');
									?>
										<a href="<?php echo $view->form->getElement('fdp_not_use')->getAttribute('data-link'); ?>" target="_blank"><?php echo $view->form->getElement('fdp_not_use')->getAttribute('data-link-lable'); ?></a>
									<?php endif; ?>
								<?php endif; // 4489: Change UI setting FDP 
								?>

								<div class="errors"></div>
							</td>
							</tr>
						<?php endforeach; ?>
				</table>
			</div>

			<div class="section btn-area">
				<a href="<?php echo route('default.estate-search-setting.index') ?>" class="btn-t-gray js-confirm-leave-edit">設定トップに戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">次の設定に進む</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<h2>取り扱いエリア一覧</h2>
			<div class="section handing-area">
				<p class="mb10">
					ここで設定した市区郡が検索対象物件となります。
					<span style="display:none" class="bothAreaSetting"><br>※「地域から探す」と「地図から探す」で共通の取り扱いエリアとなります。別々で取り扱いエリアを設定することはできません。</span>
					<span><br>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>
				</p>

				<div class="errors"></div>
				<div class="js-table-container"></div>

			</div>
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">次の設定に進む</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<h2>取り扱い沿線一覧</h2>
			<div class="section handing-area">
				<p class="mb10">
					ここで設定した沿線・駅が検索対象物件となります。
				</p>

				<div class="errors"></div>
				<div class="js-table-container"></div>

			</div>
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">設定確認に進む</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<h2>検索エンジンの基本設定</h2>
			<div class="section js-confirm-basic-setting">
			</div>

			<div class="section confirm-area js-confirm-shikuguns">
				<h2>取り扱い市区郡</h2>
				<span>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>
			</div>

			<div class="section confirm-station js-confirm-ensens">
				<h2>取り扱い沿線・駅</h2>
			</div>

			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">設定を保存する</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<div class="section setting-finish">
				<h2>設定を保存しました。</h2>
				<p>自社用物件検索の設定が完了いたしました。</p>
				<p>
					これで、「ページの作成/更新」にお作りいただきました物件検索のページが追加されました。<br>
					物件検索を、自社のホームページ内に配置してください。
				</p>
				<p>
					なお、物件検索部分はシステムの都合上、他のページ設定のように単体でプレビューがございません。<br>
					表示の確認は、下記の「公開設定」から「テストサイトの更新処理に進む」へお進みいただき、テストサイトでご確認をお願いします。
				</p>

				<div class="link-pageend">
					<ul>
						<li class="strong"><a href="<?php echo $view->route('index', 'site-map') ?>" class="i-s-link">「ページの作成/更新」から物件検索を組み込む</a></li>
						<li class="strong"><a href="#" class="i-s-link js-estate-form-link">物件問い合わせ設定へ</a></li>
						<?php if ($view->acl()->isAllowed('simple', 'publish')) : ?>
							<li><a href="<?php echo route('default.publish.simple') ?>" class="i-s-link">公開設定へ</a></li>
						<?php endif; ?>
						<li><a href="/" class="i-s-link">ホームへ</a></li>
					</ul>
				</div>
			</div>

			<div class="section btn-area">
				<a href="<?php echo route('default.estate-search-setting.index') ?>" class="btn-t-blue size-l">物件検索設定トップへ</a>
			</div>
		</div>
	</div>
</div>
@endsection