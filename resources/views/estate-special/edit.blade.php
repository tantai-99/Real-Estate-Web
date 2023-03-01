<?php
use Library\Custom\Model\Estate\TypeList;
?>
@extends('layouts.default')
@section('content')
	@section('style')
	<link rel="stylesheet" href="/css/estate_extension.css">
	@stop
	@section('script')
	<script src="/js/app.estate.js?v=2020052700" type="text/javascript"></script>
	<script>
		(function () {
			'use strict';	

			$(function () {

				var Master = {
					prefMaster                 : <?php echo json_encode($view->prefMaster)?>,
					searchTypeMaster           : <?php echo json_encode($view->searchTypeMaster)?>,
					SearchTypeCondition        : <?php echo json_encode($view->searchTypeConditionMaster)?>,   
					searchTypeDirectMaster     : <?php echo json_encode($view->searchTypeDirectMaster)?>,
					searchTypeConst            : <?php echo json_encode($view->searchTypeConst)?>,
					estateTypeMaster           : <?php echo json_encode($view->estateTypeMaster)?>,
					shumokuTypeMaster          : <?php echo json_encode($view->shumokuTypeMaster)?>,
					specialPublishEstateMaster : <?php echo json_encode($view->specialPublishEstateMaster)?>,
					specialTesuryoKokokuhiMaster : <?php echo json_encode($view->specialTesuryoKokokuhiMaster)?>,
					specialSearchPageTypeMaster: <?php echo json_encode($view->specialSearchPageTypeMaster)?>
				};

				var publishStatus = <?php echo isset($view->special) ? $view->special->is_public : 0?>;

				var EstateMaster = app.estate.EstateMaster;

				var baseSettings = <?php echo json_encode($view->baseSettings)?>;
				var currentBaseSetting;

				var setting = <?php echo json_encode($view->specialSetting)?>;
				const specialResetSetting = <?php echo json_encode($view->specialResetSetting)?>;
				var resetSetting = <?php echo isset($view->resetSetting) ? $view->resetSetting : 0?>;
				var resetHouse = <?php echo isset($view->resetSetting) ? $view->resetSetting : 0?>;

				function hasSearchType(type) {
					return $.inArray(''+type, setting.area_search_filter.search_type) > -1;
				}
				function needShikugunSetting() {
					return hasSearchType(Master.searchTypeConst.TYPE_AREA) || hasSearchType(Master.searchTypeConst.TYPE_SPATIAL);
				}
				function needEnsenSetting() {
					return hasSearchType(Master.searchTypeConst.TYPE_ENSEN);
				}

				var titles = {
					0: '作成/更新（基本設定）',
					1: '作成/更新（物件の設定）',
					2: '作成/更新（物件の個別設定）',
					3: '作成/更新',
					4: '作成/更新',
					5: '作成/更新',
					6: '確認画面',
					7: '作成/更新',
				};
				var step = new app.estate.Step($('.js-step'), $('#topicpath'), titles);
				var $contents = $('.js-step-contents');
				var only_second_stat = false;
				var exclude_second_stat = false;
				//-----------------
				// 基本設定
				//-----------------
				var basicSetting = step.createContents($contents.eq(0), $('#topicpath .last').html(), function () {
					this.$form = this.$element.find('form');
					this.$next = this.$element.find('.js-next-step');

					this.$keywords = this.$element.find('#keyword1,#keyword2,#keyword3');
					this.$estateClass   = this.$element.find('#enabled_estate_type input[type="radio"][name="estate_class"]');
					this.$estateType    = this.$element.find('#enabled_estate_type input[name="enabled_estate_type[]"]');
					this.$prefContainer = $('body').find('#template_modal #pref');
					this.$hasSearchPage = this.$element.find('#has_search_page input[name="has_search_page"]');
					this.$searchType    = this.$element.find('#has_search_page input[name="search_type[]"]');
					this.$chosonSearchInput = this.$element.find('.choson-search-input');
					this.$chosonSearchRadio = this.$chosonSearchInput.find('input');
					this.$publishEstate = this.$element.find('#publish_estate input');
					this.$tesuryoKokokuhi = this.$element.find('.tesuryo_kokokuhi input'); 
					this.$displayFreeword = this.$element.find('#display_freeword input[type="checkbox"]');
					this.$onlyErEnabled = this.$publishEstate.filter('[value="only_er_enabled"]');
					this.$secondEstateEnabled = this.$publishEstate.filter('[value="second_estate_enabled"]');
					this.$endMukeEnabled = this.$publishEstate.filter('[value="end_muke_enabled"]');
					this.$excludeSecond = this.$publishEstate.filter('[value="exclude_second"]');
					//　@TODO onlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!-- 			this.$onlySecond = this.$publishEstate.filter('[value="only_second"]'); -->
					this.$errors = this.$element.find('.errors');
					this.$methodSetting = this.$element.find('#method_setting input[type="radio"][name="method_setting"]');

					this.$searchOption = $(
						'<div class="search-option2" style="display:none;">'
						+ '<p>現在地から探す（スマホのみ）</p>'
						+ '<ul>'
						+ '<li><label><input type="radio" class="map_search_here_enabled" name="map_search_here_enabled" id="map_search_here_enabled1" value="1">利用する</label></li>'
						+ '<li><label><input type="radio" class="map_search_here_enabled" name="map_search_here_enabled" id="map_search_here_enabled0" value="0">利用しない</label></li>'
						+ '</ul>'
						+ '</div>'
					);
					// 初期ph3では現在地から探すをはずす
					this.$searchOption = $('<input type="hidden" class="map_search_here_enabled" name="map_search_here_enabled" id="map_search_here_enabled0" value="0">');

					if(setting.display_freeword == 1) {
						$('#display_freeword').prop('checked', true);
					}
					switch(setting.owner_change) {
						case 0:
							$('#enabled_estate_type input[type="radio"][name="owner_change"]').eq(0).prop('checked', true);
							break;
						case 1:
							$('#enabled_estate_type input[type="radio"][name="owner_change"]').eq(2).prop('checked', true);
							break;
						case 2:	
							$('#enabled_estate_type input[type="radio"][name="owner_change"]').eq(1).prop('checked', true);
							break;
					}

					var self = this;
					// 種別変更
					this.$element.on('change', '#enabled_estate_type input[type="radio"][name="estate_class"]', function () {
						self.updateCurrentBaseSetting();
					});
					this.$element.on('change', '#has_search_page input[name="has_search_page"]', function () {
						self.toggleDisableSearchType();
						self.toggleDisableChosonSearch(false);
						self.prop_search_here_enabled();
					});
					this.$element.on('change', '#enabled_estate_type input[type="radio"][name="owner_change"]', function () {
						switch($(this).val()) {
							case '2': // 『オーナーチェンジを除く』
								$(this).closest('div').find('input[type="checkbox"]').each(function() {
									if($(this).attr('label_val').indexOf('オーナーチェンジのみ') > 0) {
										$(this).prop('checked', false).trigger('change');
										$(this).prop('disabled', true);
										$(this).closest('label').addClass('is-disable');
									} else {
										$(this).prop('disabled', false);
										$(this).closest('label').removeClass('is-disable');
									}
								});
								break;
							default:
								$(this).closest('div').find('input[type="checkbox"]').each(function() {
									$(this).prop('disabled', false);
									$(this).closest('label').removeClass('is-disable');
								});
								break;
						}
					});

					// 町名検索
					this.$element.on('change', '#has_search_page input:checkbox[name="search_type[]"][value="1"]', function () {
						self.toggleDisableChosonSearch($(this).prop('checked'));
					});
					this.$element.on('click', '#publish_estate input[value="second_estate_enabled"]', function () {
						self.toggleDisableSecondEstate();
					});
					this.$element.on('click', '#publish_estate input[value="end_muke_enabled"]', function () {
						self.toggleDisableEndMuke();
					});
					this.$element.on('click', '#publish_estate-only_second', function () {
						//　@TODO toggleDisableOnlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!--                 self.toggleDisableOnlySecond(); -->
						if (only_second_stat) {
							self.$element.find('#publish_estate-only_second').prop('checked', false);
						//　@TODO toggleDisableOnlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!--                     self.toggleDisableOnlySecond(); -->
							only_second_stat = false;
						} else {
							only_second_stat = true;
							exclude_second_stat = false;
							self.toggleDisableExcludeSecond();
						}
					});
					this.$element.on('click', '#publish_estate-exclude_second', function () {
						self.toggleDisableExcludeSecond();
						if (exclude_second_stat) {
							self.$element.find('#publish_estate-exclude_second').prop('checked', false);
							self.toggleDisableExcludeSecond();
							exclude_second_stat = false;
						} else {
							exclude_second_stat = true;
							only_second_stat = false;
						//　@TODO toggleDisableOnlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!--                     self.toggleDisableOnlySecond(); -->
						}
					});
					// 検索画面なし＞地図から探すはなし
					if ($('.no_search_page #search_type-3')) {
						$('.no_search_page #search_type-3').closest('li').hide();
					}

					this.initForm();
					this.renderCantUse();
				}, {
					simpleInputs: [
						'title',
						'description',
						'filename',
						'comment'
					],

					initForm: function () {
						var self = this;
						var name;
						var i,l;
						var val;
						for (i=0,l=this.simpleInputs.length;i< l;i++) {
							name = this.simpleInputs[i];
							val = setting[name] || '';
							if (name === 'filename') {
								val = val.replace(/^sp\-/, '');
							}
							this.$element.find('#'+name).val(val).change();
						}

						var keyword;
						this.$keywords.val('');
						for (i=0,l=setting.keywords.length;i< l;i++) {
							keyword = setting.keywords[i];
							this.$keywords.eq(i).val(keyword || '');
						}

						this.$hasSearchPage.prop('checked', false);
						var $checked = this.$hasSearchPage.filter('[value="'+setting.area_search_filter.has_search_page+'"]').prop('checked', true);
						if (!$checked.length) {
							$checked = this.$hasSearchPage.eq(0).prop('checked', true);
						}
						var $searchType = $checked.closest('.js-has-search-page-container').find('input[name="search_type[]"]');
						$searchType.prop('checked', false);
						if ($checked.val() == 1) {
							// 検索ページありの場合
							for (i=0,l=setting.area_search_filter.search_type.length;i< l;i++) {
								$searchType.filter('[value="'+setting.area_search_filter.search_type[i]+'"]').prop('checked', true);
								if (setting.area_search_filter.search_type[i] == 1) {
									// 地域から探すが選択されている場合、町名検索設定
									this.$chosonSearchRadio.filter('[value="'+setting.area_search_filter.choson_search_enabled+'"]').prop('checked', true);
								}
							}
						} else {
							// 検索ページなしの場合
							for (i=0,l=setting.area_search_filter.search_type.length;i< l;i++) {
								if (setting.area_search_filter.search_type[i] == 1) {
									// 地域から探すが選択されている場合、町名検索設定
									$searchType.filter('[value="'+setting.area_search_filter.search_type[i]+'"][data-choson-search="'+setting.area_search_filter.choson_search_enabled+'"]').prop('checked', true);
								} else {
									$searchType.filter('[value="'+setting.area_search_filter.search_type[i]+'"]').prop('checked', true);
								}
							}
						}
						this.toggleDisableSearchType();
						if ($checked.val() == 1 && !$searchType.eq(0).prop('checked')) {
							this.toggleDisableChosonSearch(false);
						}

						// 物件種別
						this.$estateClass.prop('checked', false);
						if (setting.estate_class) {
							this.$estateClass.filter('[value="' + parseInt(setting.estate_class) + '"]').prop('checked', true);
						}

						this.$methodSetting.prop('checked', false);
						if (setting.method_setting) {
							this.$methodSetting.filter('[value="' + parseInt(setting.method_setting) + '"]').prop('checked', true);
							changeNavigation(setting.method_setting);
						}
						// 物件種目
						this.$estateType.prop('checked', false);
						if (setting.enabled_estate_type && setting.enabled_estate_type.length) {
							$.each(setting.enabled_estate_type, function (i, type) {
								self.$estateType.filter('[value="'+parseInt(type)+'"]').prop('checked', true);
							});
						}
						$("#enabled_estate_type input[initialck=1]").prop('checked', true);

						this.updateCurrentBaseSetting();

						//this.$publishEstate.each(function () {
							//var $this = $(this);
							//$this.prop('checked', !!setting[$this.val()]);
						//});
						this.toggleDisablePublishEstate();
						this.toggleDisableSecondEstate();
						this.toggleDisableEndMuke();
						this.toggleDisableExcludeSecond();
						//　@TODO toggleDisableOnlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!--                 this.toggleDisableOnlySecond(); -->

						if (this.$element.find('#publish_estate-only_second').prop('checked')) {
							only_second_stat = true;
						};
						if (this.$element.find('#publish_estate-exclude_second').prop('checked')) {
							exclude_second_stat = true;
						};

						//this.$tesuryoKokokuhi.each(function () {
							//var $this = $(this);
							//$this.prop('checked', !!setting[$this.val()]);
						//});

						if (setting.map_search_here_enabled == 1) {
							$('#map_search_here_enabled1').prop('checked', true);
						}
						this.$element.find('input[name^="search_type"]').on('change', function () {
							self.prop_search_here_enabled();
						});
						self.prop_search_here_enabled();

						//$(document).on('change', '#publish_estate input', function() {
							//self.publishEstateCheckCtl();
						//});

						//self.publishEstateCheckCtl();
						$(document).on('change', '#tesuryo_check', function() {
							self.tesuryoCheckCtl();
						});
						$('.tesuryo_kokokuhi input[type=radio]').each(function() {
							if($(this).prop('checked')) {
								$('#tesuryo_check').prop('checked', true);
							}
						});

						if($('#tesuryo_check').prop('checked') == false) {
							$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").attr('disabled', 'disabled');
						}

						this.$estateType.on('change', function() {
							if($(this).prop('checked') == true) {
								$(this).closest('li').find(".shumoku_shosai").each(function() {
									if($(this).prop('disabled') == false) {
										$(this).prop('checked', true);
									}
								});
							} else {
								$(this).closest('li').find(".shumoku_shosai").prop('checked', false);
							}

							if($(this).val() == 10 || $(this).val() == 11) {
								var typeCheckCnt = 0;
								$(this).closest(".mt10").find("input[name='enabled_estate_type[]']").each(function() {
									if($(this).prop('checked') && ($(this).val() == 10 || $(this).val() == 11)) {
										typeCheckCnt++;
									}
								});
								if(typeCheckCnt >= 1) {
									$(this).closest(".mt10").find(".shumoku_shosai_box").eq(0).find('input[type=radio]').eq(0).prop('checked', true).trigger('change');
									$(this).closest(".mt10").find(".shumoku_shosai_box").eq(0).find('input[type=radio]').each(function() {
										$(this).prop('disabled', true);
										$(this).closest('label').addClass('is-disable');
									});
								} else {
									$(this).closest(".mt10").find(".shumoku_shosai_box").eq(0).find('input[type=radio]').each(function() {
										$(this).prop('disabled', false);
										$(this).closest('label').removeClass('is-disable');
									});
								}
							}
						});

						$("#enabled_estate_type a").on('click', function() {
							if($(this).css('cursor') != 'pointer') {
								return;
							}

							if($(this).closest('li').find('.shumoku_shosai_box').css('display') == 'block') {
								$(this).closest('li').find('.shumoku_shosai_box').hide();
								$(this).text('詳細な種目を選ぶ');
								$(this).closest('li').css('margin-bottom', 'auto');
							} else {
								$(this).closest('li').find('.shumoku_shosai_box').css('left', $(this).closest('td').offset().left);

								var shosaiWidth = $(this).closest('td').css('width').replace('px', '') - 100;

								$(this).closest('li').find('.shumoku_shosai_box').css('width', shosaiWidth + 'px');
								$(this).closest('li').find('.shumoku_shosai_box').show();

								$(this).closest('li').css('margin-bottom', $(this).closest('li').find('.shumoku_shosai_box').css('height'));
								$(this).text('詳細な種目を隠す');
							}
						});
						$("#enabled_estate_type .shumoku_shosai_box input[type=checkbox]").on('change', function() {
							if($(this).prop('checked') == true) {
								$(this).closest('li').find("input[name='enabled_estate_type[]']").prop('checked', true);
							} else {
								var anyCheck = false;
								$(this).closest('div').find("input[type=checkbox]").each(function() {
									if($(this).prop('checked') == true) {
										anyCheck = true;
									}
								});
								if(anyCheck == false) {
									$(this).closest('li').find("input[name='enabled_estate_type[]']").prop('checked', false);
								}
							}
						});

						$('input[name="enabled_estate_type[]"]:checked').each(function() {
							if($(this).val() == 10 || $(this).val() == 11) {
								$(this).trigger('change');
							}
						});
						// オーナーチェンジを除くの場合の処理
						if($('#enabled_estate_type input[type="radio"][name="owner_change"]:checked').val() == '2') {
							$('#enabled_estate_type input[type="radio"][name="owner_change"]').eq(1).trigger('change');
						}
					},

					publishEstateCheckCtl: function() {
						var ckcnt = 0;
						$('#publish_estate input').each(function() {
							var val = $(this).val();
							switch(val) {
								case 'jisha_bukken':
								case 'niji_kokoku':
								case 'niji_kokoku_jido_kokai':
									if($(this).prop('checked')) {
										ckcnt++;
									}
									break;
								default:
									break;
							}
						});
						if(ckcnt == 0) {
							$('#publish_estate input[value=only_er_enabled]').eq(0).prop('checked', false);
							$('#publish_estate input[value=only_er_enabled]').attr('disabled', 'disabled');
							if($('#publish_estate input[value=only_er_enabled]').eq(0).parent().hasClass('is-disable') == false) {
								$('#publish_estate input[value=only_er_enabled]').eq(0).parent().addClass('is-disable');
							}
							$('#publish_estate input[value=niji_kokoku_jido_kokai]').attr('disabled', false);
							$('#publish_estate input[value=niji_kokoku_jido_kokai]').eq(0).parent().removeClass('is-disable');
						} else if((ckcnt == 1 && $('#publish_estate input[value=niji_kokoku_jido_kokai]').prop('checked')) || ckcnt == 3 || (ckcnt != 1 && $('#publish_estate input[value=niji_kokoku_jido_kokai]').prop('checked'))) {
							$('#publish_estate input[value=only_er_enabled]').eq(0).prop('checked', false);
							$('#publish_estate input[value=only_er_enabled]').attr('disabled', 'disabled');
							if($('#publish_estate input[value=only_er_enabled]').eq(0).parent().hasClass('is-disable') == false) {
								$('#publish_estate input[value=only_er_enabled]').eq(0).parent().addClass('is-disable');
							}
						} else {
							$('#publish_estate input[value=only_er_enabled]').attr('disabled', false);
							$('#publish_estate input[value=only_er_enabled]').eq(0).parent().removeClass('is-disable');
							if ($('#publish_estate input[value=only_er_enabled]').prop('checked')) {
								$('#publish_estate input[value=niji_kokoku_jido_kokai]').eq(0).prop('checked', false);
								$('#publish_estate input[value=niji_kokoku_jido_kokai]').attr('disabled', 'disabled');
								if($('#publish_estate input[value=niji_kokoku_jido_kokai]').eq(0).parent().hasClass('is-disable') == false) {
									$('#publish_estate input[value=niji_kokoku_jido_kokai]').eq(0).parent().addClass('is-disable');
								}
							} else {
								$('#publish_estate input[value=niji_kokoku_jido_kokai]').attr('disabled', false);
								$('#publish_estate input[value=niji_kokoku_jido_kokai]').eq(0).parent().removeClass('is-disable');
							}
						}
					},
					tesuryoCheckCtl: function() {
						if(!$('#tesuryo_check').prop('checked')) {
							$('.tesuryo_kokokuhi input[type=radio]').prop('checked', false).attr('disabled', 'disabled');
						} else {
							var ckcnt = 0;
							$('.tesuryo_kokokuhi input[type=radio]').each(function() {
								$(this).attr('disabled', false);
								if($(this).prop('checked')) {
									ckcnt++;
								}
							});
							if(ckcnt == 0) {
								$('.tesuryo_kokokuhi input[type=radio]').eq(0).prop('checked', true);
							}
						}
					},
					getEstateClass: function () {
						return this.$estateClass.filter(':checked').val() || null;
					},
					getMethodSetting: function () {
						return this.$methodSetting.filter(':checked').val() || null;
					},
					getEstateType: function () {
						return this.$estateType.filter('[name="enabled_estate_type[]"]:checked').map(function() {
							return this.value;
						}).get();
					},
					getPref: function () {
						return this.$prefContainer.find(':checked').map(function () {
							return this.value;
						}).get();
					},
					getHasSearchPage: function () {
						var $checked = this.$hasSearchPage.filter(':checked');
						if (!$checked.length) {
							return null;
						}
						return parseInt($checked.val());
					},
					getSearchType: function () {
						return this.$searchType.filter(':checked').map(function () {
							return this.value;
						}).get();
					},
					getChosonSearchEnabled: function () {
						if (!currentBaseSetting || currentBaseSetting.area_search_filter.choson_search_enabled != 1) {
							return 0;
						}
						var $checked = this.$chosonSearchInput.find('input:checked');
						if ($checked.length) {
							return $checked.eq(0).val();
						}
						return 0;
					},
					updateCurrentBaseSetting: function () {
						var estateClass = this.getEstateClass();
						if (!baseSettings[estateClass]) {
							this.$estateClass.eq(0).prop('checked', true).change();
							return;
						}

						var methodSetting = this.getMethodSetting();
						if (methodSetting == null) {
							this.$methodSetting.eq(0).prop('checked', true).change();
							return;
						}
						var beforeBaseSetting = currentBaseSetting;

						currentBaseSetting = baseSettings[estateClass];
						currentBaseSetting.mapOption = setting.mapOption;
						currentBaseSetting.methodSetting = methodSetting;

						this.renderEstateType();
						this.renderPref();
						this.renderSearchType();
						this.renderCantUse();
					},

					renderEstateType: function () {
						var self = this;
						$("#enabled_estate_type .shumoku_shosai_box").hide();
						$("#enabled_estate_type a").text('詳細な種目を選ぶ').css('cursor', '');
						$("#enabled_estate_type a").addClass('is-disable');

						$("#enabled_estate_type .shumoku_shosai_box").each(function() {
							$(this).closest('li').css('margin-bottom', 'auto');
						});

						this.$estateClass.each(function (i, elem) {
							var $types = self.$estateType.filter('[data-estate-class="' + elem.value + '"]');
							if (currentBaseSetting.estate_class == elem.value) {
								$types.prop('disabled', false);
								$types.closest('ul').removeClass('is-disable');

								$types.closest('ul').find(".shumoku_shosai").prop('disabled', false);
								$types.closest('ul').find("a").css('cursor', 'pointer');
								$types.closest('ul').find("a").removeClass('is-disable');

								// 種目変更に伴う、手数料の制御
								// 賃貸に切り替わると、手数料の分かれも含むを非活性化
								if(elem.value == 1 || elem.value == 2) {
									// 1 -> 2 or 2 -> 1 判定
									if($(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").closest('span').is(':visible')) {
										$("#tesuryo_check").prop('checked', false);
										// 手数料の分かれも含む選択中
										$("#tesuryo_check").prop('checked', false).trigger('change');
										// 手数料の分かれも含む非活性
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").attr('disabled', 'disabled');
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").prop('checked', false);
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").parent().addClass('is-disable');
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").closest('span').hide();
									}
								} else {
									if(!$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").closest('span').is(':visible')) {
										$("#tesuryo_check").prop('checked', false);
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").prop('checked', false);
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").attr('disabled', 'disabled');
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").parent().removeClass('is-disable');
										$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").closest('span').show();
									}
								}

								// オーナーチェンジのdisable状態解除
								$(".shumoku_shosai_box").find("input[type=radio]").each(function() {
									$(this).prop('disabled', false);
									$(this).closest('label').removeClass('is-disable');
								});
								return;
							}
							$types.prop('checked', false)
							$types.prop('disabled', true);
							$types.closest('ul').addClass('is-disable');

							$types.closest('ul').find(".shumoku_shosai").prop('checked', false);
							$types.closest('ul').find(".shumoku_shosai").prop('disabled', true);
						});
					},

					renderPref: function () {
						var checkedValues;
						if (this.$prefContainer.find('label').length) {
							checkedValues = this.$prefContainer.find('input:checked').map(function () {
								return this.value;
							}).get();
							// 再選択はチェックしない
							checkedValues = [];
						}
						else {
							checkedValues = setting.area_search_filter.area_1;
						}
						this.$prefContainer.find('div.is-required').remove();

						if (!currentBaseSetting) {
							return;
						}

						var prefCodes = currentBaseSetting.area_search_filter.area_1;
						var prefCode;
						var prefHtml = '<div class="is-required">';
						var checkedStr;
						var i,l;
						for (i=0,l=prefCodes.length;i< l;i++) {
							prefCode = prefCodes[i];
							checkedStr = '';
							if ($.inArray(prefCode, checkedValues) > -1) {
								checkedStr = ' checked';
							}
							prefHtml += ''+
								'<label>'+
									'<input type="checkbox" name="pref[]" value="'+prefCode+'"'+checkedStr+'>'+
									app.h(Master.prefMaster[prefCode])+
								'</label>';
						}
						prefHtml += '</div>';
						this.$prefContainer.find('.errors').before(prefHtml);

					},
					renderSearchType: function () {
						var i,l;
						this.$searchType.each(function () {
							var $this = $(this);

							if ($.inArray(this.value, currentBaseSetting.area_search_filter.search_type) < 0) {
								$this.prop('checked', false).parent().hide();
							}else if (this.value == 3 && !currentBaseSetting.mapOption){
								$this.prop('checked', false).parent().hide();
							}else {
								$this.parent().show();
							}
						});
						for (i=0,l=currentBaseSetting.area_search_filter.search_type;i< l;i++) {
							this.$searchType.filter('[value=""]').show();
						}

						// 町名検索設定非表示
						var chosonSearchEnabled = currentBaseSetting.area_search_filter.choson_search_enabled == 1;
						var $onlyChosonSearchEnabled = $('.js-only-choson-search-enabled');
						$onlyChosonSearchEnabled.toggleClass('is-hide', !chosonSearchEnabled);
						if (chosonSearchEnabled) {
							$onlyChosonSearchEnabled.prop('disabled', false);
						} else {
							$onlyChosonSearchEnabled.prop('checked', false);
							$onlyChosonSearchEnabled.prop('disabled', true);
						}

					},

					renderCantUse: function () {
						var i,l,prefCode;
						var errorHtml = '<p class="is-no-selecting">物件検索設定が変更された為、設定中の項目が選択できなくなりました。</p>';

						this.$errors.find('.is-no-selecting').remove();

						// 使えなくなった都道府県
						for (i=0,l=setting.area_search_filter.area_1.length;i< l;i++) {
							prefCode = setting.area_search_filter.area_1[i];
							if ($.inArray(prefCode, currentBaseSetting.area_search_filter.area_1) < 0) {
								this.$prefContainer.find('.errors').append(errorHtml);
								break;
							}
						}
						// 使えなくなった種目
						if (
							setting.enabled_estate_type && setting.enabled_estate_type.length
						) {
							var enabledEstateType;
							for (i=0;enabledEstateType = setting.enabled_estate_type[i];i++) {
								if (!this.$estateType.filter('[value="'+parseInt(enabledEstateType)+'"]')) {
									this.$element.find('#enabled_estate_type .errors').append(errorHtml);
									break;
								}
							}
						}
						// 使えなくなった検索タイプ
						var searchType;
						for (i=0,l=setting.area_search_filter.search_type.length;i< l;i++) {
							searchType = setting.area_search_filter.search_type[i];
							if ($.inArray(searchType, currentBaseSetting.area_search_filter.search_type) < 0) {
								this.$element.find('#has_search_page .errors').append(errorHtml);
								break;
							}
						}
					},
					toggleDisableSearchType: function () {
						this.$hasSearchPage.each(function () {
							var $this = $(this);
							var $container = $this.closest('.js-has-search-page-container').find('.js-search-type');
							var isChecked = $this.prop('checked');
							$container.toggleClass('is-disable', !isChecked);
							var $checks = $container.find('input:not(.map_search_here_enabled)').prop('disabled', !isChecked);
							if (!isChecked) {
								$checks.prop('checked', false);
							}
						});
					},
					toggleDisableChosonSearch: function (isEnabled) {
						setting.area_search_filter.search_type = [];
						this.$chosonSearchInput.toggleClass('is-disable', !isEnabled);
						this.$chosonSearchRadio.prop('disabled', !isEnabled);
						if (isEnabled) {
							this.$chosonSearchRadio.eq(0).prop('checked', true);
						} else {
							this.$chosonSearchRadio.prop('checked', false);
						}
					},
					toggleDisablePublishEstate: function () {
						var isChecked = this.$onlyErEnabled.prop('checked');

						if(isChecked == false) {
							this.publishEstateCheckCtl();
						}
						if (! isChecked &&
								(this.$endMukeEnabled.prop('checked') || this.$excludeSecond.prop('checked'))) {
							isChecked = true;
						}
						//　@TODO onlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!-- 				if (! isChecked && this.$onlySecond.prop('checked')) { -->
			// <!-- 				    isChecked = true; -->
			// <!-- 				} -->
						this.$secondEstateEnabled.prop('disabled', isChecked);
						this.$secondEstateEnabled.parent().toggleClass('is-disable', isChecked);
					},
					toggleDisableSecondEstate: function () {
						var isChecked = this.$secondEstateEnabled.prop('checked');
						if(! ($('#publish_estate input[value="second_estate_enabled"]').length)){
							isChecked = false;
						}
						this.$onlyErEnabled.prop('disabled', isChecked);
						this.$onlyErEnabled.parent().toggleClass('is-disable', isChecked);

						this.$endMukeEnabled.prop('disabled', isChecked);
						this.$endMukeEnabled.parent().toggleClass('is-disable', isChecked);

						this.$excludeSecond.prop('disabled', isChecked);
						this.$excludeSecond.parent().toggleClass('is-disable', isChecked);

						//　@TODO onlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!-- 				this.$onlySecond.prop('disabled', isChecked); -->
			// <!-- 				this.$onlySecond.parent().toggleClass('is-disable', isChecked); -->
					},
					toggleDisableEndMuke: function () {
						var isChecked = this.$endMukeEnabled.prop('checked');
						if (! isChecked &&
								(this.$onlyErEnabled.prop('checked') || this.$excludeSecond.prop('checked'))) {
							isChecked = true;
						}
						//　@TODO onlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!-- 				if (! isChecked && this.$onlySecond.prop('checked')) { -->
			// <!-- 				    isChecked = true; -->
			// <!-- 				} -->
						this.$secondEstateEnabled.prop('disabled', isChecked);
						this.$secondEstateEnabled.parent().toggleClass('is-disable', isChecked);
					},
					toggleDisableExcludeSecond: function () {
						var isChecked = this.$excludeSecond.prop('checked');
						if (! isChecked &&
								(this.$onlyErEnabled.prop('checked') || this.$endMukeEnabled.prop('checked'))) {
							isChecked = true;
						}
						//　@TODO onlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!-- 				if (! isChecked && this.$onlySecond.prop('checked')) { -->
			// <!-- 				    isChecked = true; -->
			// <!-- 				} -->
						this.$secondEstateEnabled.prop('disabled', isChecked);
						this.$secondEstateEnabled.parent().toggleClass('is-disable', isChecked);
					},
					//　@TODO toggleDisableOnlySecondは、物件APIが対応するまでの暫定処置(NHP-2307)
			// <!-- 			toggleDisableOnlySecond: function () { -->
			// <!-- 				var isChecked = this.$onlySecond.prop('checked'); -->
			// <!-- 				if (! isChecked && -->
			// <!-- 				        (this.$onlyErEnabled.prop('checked') || this.$endMukeEnabled.prop('checked') || -->
			// <!-- 				         this.$excludeSecond.prop('checked') )) { -->
			// <!-- 				    isChecked = true; -->
			// <!-- 				} -->
			// <!-- 				this.$secondEstateEnabled.prop('disabled', isChecked); -->
			// <!-- 				this.$secondEstateEnabled.parent().toggleClass('is-disable', isChecked); -->
			// <!-- 			}, -->
					next: function () {
						var self = this;

						if (this.$next.hasClass('is-disable')) {
							return;
						}

						// loading on
						this.$next.addClass('is-disable');

						var params = this.$form.serialize();
						params += '&has_search_page=' + this.getHasSearchPage();
						app.api('/estate-special/api-validate-basic', params, function (res) {
							self.$form.find('.is-error').removeClass('is-error');
							self.$form.find('.errors').empty();
							if (res.errors) {
								// レスポンスのエラー内容を表示
								app.setErrors(self.$form, res.errors);
								// 選択できなくなりましたエラーを表示
								self.renderCantUse();

								var $errorInput = $('.is-error:not(:hidden)');
								var $error = $('.errors p');
								var $target;
								if ($errorInput.length) {
									$target = $errorInput.eq(0);
								}
								else if ($error.length) {
									$target = $error.eq(0);
								}
								else {
									return;
								}

								app.scrollTo($target.offset().top - 50);
								return;
							}

							// 値更新
							self.updateSetting();

							var nextVal;
							changeNavigation(setting.method_setting);
							if (setting.method_setting == 2) {
								nextVal = 2;
							} else {
								nextVal = 1;
							}
							if (resetHouse == 2) {
								self.step.setNextStep(true);
								resetHouse = 0;
							}
							self.step.next(nextVal);
						})
						.always(function () {
							self.$next.removeClass('is-disable');
						});
					},

					updateSetting: function () {
						var name;
						var val;
						for (var i=0,l=this.simpleInputs.length;i< l;i++) {
							name = this.simpleInputs[i];
							val  = this.$element.find('#' + name).val();
							if (val === undefined) {
								val = null;
							}
							if (name === 'filename') {
								val = 'sp-' + (val || '');
							}
							setting[name] = val;
						}

						setting.keywords = [];
						this.$keywords.each(function () {
							var val = this.value;
							if (val !== undefined && val !== null && val !== '') {
								setting.keywords.push(val);
							}
						});
						var oldEstateType = setting.enabled_estate_type;
						setting.estate_class = currentBaseSetting.estate_class;
						setting.method_setting = currentBaseSetting.methodSetting;
						setting.enabled_estate_type = this.getEstateType();
						setting.area_search_filter.has_search_page = this.getHasSearchPage();
						if (this.getHasSearchPage() == 1) {
							setting.area_search_filter.search_type = this.getSearchType();
						}
						setting.area_search_filter.choson_search_enabled = this.getChosonSearchEnabled();
						//setting.area_search_filter.area_1 = this.getPref();
						setting.map_search_here_enabled = $('#map_search_here_enabled1').prop('checked') ? 1: 0;

						// 種目を変更した場合は絞り込み条件をリセットする
						// (種目毎に項目が変わるから)
						if (oldEstateType.join('-') != setting.enabled_estate_type.join('-')) {
							setting.search_filter.categories = [];
							if (setting.method_setting == 2) {
								resetHouseType();
							}
						}

						// 市区群から町名に変更した場合の対応
						if (setting.area_search_filter.choson_search_enabled == 1) {
							// 種別設定で町名まで選択されている、且つ特集で町名まで選択されていないものは町名設定をコピーする
							if (currentBaseSetting.area_search_filter.area_5) {
								for (var pref in currentBaseSetting.area_search_filter.area_5) {
									if (!setting.area_search_filter.area_2[pref]) {
										// 都道府県の設定がなければskip
										continue;
									}
									var shikuguns = currentBaseSetting.area_search_filter.area_5[pref];
									for (var shikugunCd in shikuguns) {
										if (!shikuguns[shikugunCd].length) {
											continue;
										}
										if ($.inArray(shikugunCd, setting.area_search_filter.area_2[pref]) < 0) {
											// 市区群の設定がなければskip
											continue;
										}

										if (!setting.area_search_filter.area_5[pref]) {
											// 町名設定の都道府県設定がなければ初期化
											setting.area_search_filter.area_5[pref] = {};
										}

										var baseChosons = shikuguns[shikugunCd];
										var chosons = setting.area_search_filter.area_5[pref][shikugunCd] || [];

										var newChosons = [];
										// ベース設定されているもののみで設定
										$.each(chosons, function (i, choson) {
											if ($.inArray(choson, baseChosons) > -1) {
												newChosons.push(choson);
											}
										});

										if (newChosons.length) {
											setting.area_search_filter.area_5[pref][shikugunCd] = newChosons;
										} else {
											// 町名設定の市区群設定がなければベース設定をコピー設定
											setting.area_search_filter.area_5[pref][shikugunCd] = $.map(baseChosons, function (choson) { return choson });
										}

										var newChoazasPref = {};   // 現在の都道府県用: setting.area_search_filter.area_6[pref]
										$.each(setting.area_search_filter.area_5[pref][shikugunCd], function(i, choson) {

											var currentBasicSettingChoazas = null;  // 検索設定での各町村の詳細

											if( currentBaseSetting.area_search_filter.area_6[pref] !== undefined 
											&& currentBaseSetting.area_search_filter.area_6[pref][shikugunCd] !== undefined
											&& currentBaseSetting.area_search_filter.area_6[pref][shikugunCd][choson] !== undefined ){
												currentBasicSettingChoazas = currentBaseSetting.area_search_filter.area_6[pref][shikugunCd][choson];
											}

											// 特集にて町字を未設定(全町字選択)
											if( setting.area_search_filter.area_6[pref] === undefined 
											|| setting.area_search_filter.area_6[pref][shikugunCd] === undefined
											|| setting.area_search_filter.area_6[pref][shikugunCd][choson] === undefined) {
												// 検索設定が町字の「絞り込み無->有」になった場合はそれに従う
												if(currentBasicSettingChoazas !== null) {
													newChoazasPref[choson] = currentBasicSettingChoazas;
												}
											} else {
												// 検索設定の町字絞り込みがなければ特集の選択継承
												if(currentBasicSettingChoazas === null) {	// 全町字選択可
													newChoazasPref[choson] = setting.area_search_filter.area_6[pref][shikugunCd][choson];
												} else {
													// 検索設定で不要となったものが選択されている場合は削除
													if(newChoazasPref[choson] === undefined) {
														newChoazasPref[choson] = [];
													}
													$.each(setting.area_search_filter.area_6[pref][shikugunCd][choson], function(i, choazaCode) {
														// 特集で設定しており、かつ、検索設定でも有効な場合のみ取得
														if(currentBasicSettingChoazas.indexOf(choazaCode) >= 0) {
															newChoazasPref[choson].push(choazaCode);
														}
													});
													// 特集で設定していた町字がすべて無効にされていた場合は、検索設定有効(他に従う)
													if(newChoazasPref[choson].length == 0) {
														newChoazasPref[choson] = currentBasicSettingChoazas;
													}
												}
											}
										});
										if (Object.keys(newChoazasPref).length > 0) {
											if(setting.area_search_filter.area_6 === undefined) {
												setting.area_search_filter.area_6 = {};
											}
											if(setting.area_search_filter.area_6[pref] === undefined) {
												setting.area_search_filter.area_6[pref] = {};
											}
											setting.area_search_filter.area_6[pref][shikugunCd] = newChoazasPref;
										}
									}
								}
							}
						}

						// 再選択したので、選択できなくなりましたエラー非表示
						this.renderCantUse();

						//this.$publishEstate.each(function () {
							//var $this = $(this);
							//setting[$this.val()] = ~~$this.prop('checked');
						//});

						//this.$tesuryoKokokuhi.each(function () {
							//var $this = $(this);
							//setting[$this.val()] = ~~$this.prop('checked');
						//});

						if(typeof setting['search_filter'] == 'undefined') {
							setting['search_filter'] = { 'categories' : Array() };
						}
						if(typeof setting['search_filter']['categories'] == 'undefined') {
							setting['search_filter']['categories'] = Array();
						}

						var shumoku_cno = -1;
						for(var cno=0; cno < setting['search_filter']['categories'].length; cno++) {
							if(setting['search_filter']['categories'][cno]['category_id'] == 'shumoku') {
								shumoku_cno = cno;
							}
						}
						if(shumoku_cno == -1) {
							shumoku_cno = setting['search_filter']['categories'].length;
							setting['search_filter']['categories'].push({ 'category_id' : 'shumoku', 'items' : Array() });
						} else {
							setting['search_filter']['categories'][ shumoku_cno ]['items'] = null;
							setting['search_filter']['categories'][ shumoku_cno ]['items'] = Array();
						}

						//
						// setting['search_filter']['categories'][ shumoku_cno ]['items'].push({'item_id' : '27', 'item_value' : 1 });
						$("#enabled_estate_type input[type=radio]:checked").eq(0).closest('li').find("input[type=checkbox]:checked").each(function() {
							if(typeof $(this).attr('name') === 'undefined') {
								setting['search_filter']['categories'][ shumoku_cno ]['items'].push({'item_id' : $(this).val(), 'item_value' : 1 });
							}
						});
					},

					prop_search_here_enabled: function (){
						var $check = this.$element.find('input[name=has_search_page]:checked')
							.parents('li').find('#search_type-' + Master.searchTypeConst.TYPE_SPATIAL);
						if ($check.prop('checked')) {
							$check.parent('label').after(this.$searchOption);
							this.$searchOption.fadeIn();
						} else {
							this.$searchOption.fadeOut();
						}
						if (typeof(this.$searchOption.find('input:checked').val()) === 'undefined') {
							if (setting.map_search_here_enabled == 0) {
								$('#map_search_here_enabled0').prop('checked', true);
							} else {
								$('#map_search_here_enabled1').prop('checked', true);
							}
						}
					},
					getShomukuShosai: function () {
						return $('#enabled_estate_type .shumoku_shosai_box .shumoku_shosai').filter(':checked').map(function() {
							return this.value;
						}).get();
					}
				});
				$('#enabled_estate_type input[type="radio"][name="estate_class"]').change(function () {
					var estateClass = setting.estate_class;
					var enabledEestateType = basicSetting.getEstateType();
					var shomuku = basicSetting.getShomukuShosai();
					var i,l;
					if (resetSetting == 1) {
						var msg = 'ページの基本設定以外は初期化されますがよろしいですか？';
						app.modal.confirm('確認', msg, function (res) {
							if (!res) {
								if (enabledEestateType && enabledEestateType.length) {
									$.each(enabledEestateType, function (i, type) {
										basicSetting.$estateType.filter('[value="'+parseInt(type)+'"]').prop('checked', true);
									});
								}
								$('#enabled_estate_type input[type="radio"][name="estate_class"]').filter('[value="' + parseInt(estateClass) + '"]').prop('checked', true);
								basicSetting.updateCurrentBaseSetting();
								basicSetting.renderEstateType();
								if (shomuku.length > 0) {
									for (i=0,l=shomuku.length;i< l;i++) {
										$('#enabled_estate_type .shumoku_shosai_box .shumoku_shosai').filter('[value="' + parseInt(shomuku[i]) + '"]').prop('checked', true);
									}
								}
								return;
							} else {
								basicSetting.updateCurrentBaseSetting();
								basicSetting.renderEstateType();
								changeNavigation(1);
								resetSettingSpecial();
								currentBaseSetting.methodSetting = 1;
								$('#method_setting input[type="radio"][name="method_setting"]').filter('[value="1"]').prop('checked', true);
							}
						})
					} else {
						resetSettingSpecial();
						basicSetting.updateCurrentBaseSetting();
					}
				});
				var oldSetting = $('#method_setting input[type="radio"][name="method_setting"]:checked').val();
				$('#method_setting input[type="radio"][name="method_setting"]').change(function () {
					if (resetSetting == 1) {
						var msg = 'ページの基本設定以外は初期化されますがよろしいですか？';
						app.modal.confirm('確認', msg, function (res) {
							if (!res) {
								$('#method_setting input[type="radio"][name="method_setting"]').filter('[value="' + parseInt(oldSetting) + '"]').prop('checked', true);
								return;
							} else {
								oldSetting =  $('#method_setting input[type="radio"][name="method_setting"]:checked').val()
								changeNavigation(oldSetting);
								$('#enabled_estate_type input[type="radio"][name="estate_class"]').filter('[value="1"]').prop('checked', true);
								basicSetting.updateCurrentBaseSetting();
								resetSettingSpecial();
								setting.estate_class = null;
								setting.enabled_estate_type = [];
								basicSetting.$estateType.prop('checked', false).change();
							}
						})
					} else {
						resetHouse = 2;
						resetSettingSpecial();
						basicSetting.updateCurrentBaseSetting();
						setting.method_setting = $(this).val();
						changeNavigation($(this).val());
					}
				});
				basicSetting.beforeNext = function () {
					var self = this;
					var $container = self.$element.find('#display_freeword');
					var $checked = $container.is(":checked");
					setting.display_freeword  = $checked ? 1 : 0;

					setting.owner_change =  $('#enabled_estate_type input[type="radio"][name="owner_change"]:checked').val();
				};

				//-----------------
				// 物件の設定
				//-----------------
				var methodSetting = app.inherits(app.estate.StepContentsMethod, function () {
					app.estate.StepContentsMethod.apply(this, arguments);
					this.$note = this.$element.find('.js-note');
				},
				{
					show: function () {
						var self = this;
						self.toggleEstateType();
						self.basic.renderEstateType();
						var $searchType = this.$element.find('#has_search_page_method').find('input[name="search_type[]"]');
						$searchType.prop('checked', false);
						this.renderMethod();
						app.estate.StepContents.prototype.show.call(this);
					},
					next: function () {
						var self = this;
						this.$next = this.$element.find('.js-next-step');
						if (this.$next.hasClass('is-disable')) {
							return;
						}

						// loading on
						this.$next.addClass('is-disable');
						this.$form = this.$element.find('form');
						var params = this.$form.serialize();
						params += '&method=' + setting.method_setting;
						params += '&search_page=' + setting.area_search_filter.has_search_page;
						app.api('/estate-special/api-validate-method', params, function (res) {
							self.$form.find('.is-error').removeClass('is-error');
							self.$form.find('.errors').empty();
							if (res.errors) {
								// レスポンスのエラー内容を表示
								app.setErrors(self.$form, res.errors);
								// 選択できなくなりましたエラーを表示
								self.basic.renderCantUse();

								var $errorInput = $('.is-error:not(:hidden)');
								var $error = $('.errors p');
								var $target;
								if ($errorInput.length) {
									$target = $errorInput.eq(0);
								}
								else if ($error.length) {
									$target = $error.eq(0);
								}
								else {
									return;
								}

								app.scrollTo($target.offset().top - 50);
								return;
							}
							self.updateSetting();
							if (setting.method_setting == 3) {
								self.step.next(5);
							} else {
								var nextVal;
								if (needShikugunSetting()) {
									nextVal = 2;
								} else if (needEnsenSetting()) {
									nextVal = 3;
								} else {
									nextVal = 4;
								}
								self.step.next(nextVal);
							}
						})
						.always(function () {
							self.$next.removeClass('is-disable');
						});
					},
					getSearchType: function () {
						var searchTypes = this.$searchType.filter(':checked').map(function () {
							return this.value;
						}).get();
						if (searchTypes.length > 0) {
							return searchTypes;
						}
						return ["1"];
					},
					hasSearchType: function(type) {
						return $.inArray(''+type, setting.area_search_filter.search_type) > -1;
					},
					needShikugunSetting: function() {
						return hasSearchType(Master.searchTypeConst.TYPE_AREA) || hasSearchType(Master.searchTypeConst.TYPE_SPATIAL);
					},
					needEnsenSetting: function() {
						return hasSearchType(Master.searchTypeConst.TYPE_ENSEN);
					},
					getSetting: function () {
						return setting;
					},
					getAllowed: function () {
						return {
							pref: currentBaseSetting.area_search_filter.area_1,
							ensens: currentBaseSetting.area_search_filter.area_3,
							ekis: currentBaseSetting.area_search_filter.area_4
						};
					},
					getElement: function () {
						return basicSetting;
					},
					getPref: function () {
						return this.$prefContainer.find(':checked').map(function () {
							return this.value;
						}).get();
					},
					updateSetting: function () {
						var self = this;
						if (setting.area_search_filter.has_search_page == 0) {
							setting.area_search_filter.search_type = self.getSearchType();
							setting.area_search_filter.choson_search_enabled = self.getChosonSearchEnabled();
						}
						setting.area_search_filter.area_1 = self.getPref();
						this.$publishEstate.each(function () {
							var $this = $(this);
							setting[$this.val()] = ~~$this.prop('checked');
						});
					},
					getChosonSearchEnabled: function () {
						var $checked = this.$searchType.filter('[data-choson-search]:checked');
						if ($checked.length) {
							return $checked.eq(0).attr('data-choson-search');
						}
						return 0;
					},
					toggleEstateType: function () {
						$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").closest('span').hide();
					},
					renderSearchTypeMethod: function () {
						var i,l;
						this.$searchType.each(function () {
							var $this = $(this);

							if ($.inArray(this.value, currentBaseSetting.area_search_filter.search_type) < 0) {
								$this.prop('checked', false).parent().hide();
							}else if (this.value == 3 && !currentBaseSetting.mapOption){
								$this.prop('checked', false).parent().hide();
							}else {
								$this.parent().show();
							}
						});
						for (i=0,l=currentBaseSetting.area_search_filter.search_type;i< l;i++) {
							this.$searchType.filter('[value=""]').show();
						}

						// 町名検索設定非表示
						var chosonSearchEnabled = currentBaseSetting.area_search_filter.choson_search_enabled == 1;
						var $onlyChosonSearchEnabled = $('.js-only-choson-search-enabled');
						$onlyChosonSearchEnabled.toggleClass('is-hide', !chosonSearchEnabled);
						if (chosonSearchEnabled) {
							$onlyChosonSearchEnabled.prop('disabled', false);
						} else {
							$onlyChosonSearchEnabled.prop('checked', false);
							$onlyChosonSearchEnabled.prop('disabled', true);
						}

					},
					renderMethod: function () {
						var self = this;
						if (setting.area_search_filter.has_search_page == 0) {
							// 検索ページなしの場合
							var i, l;
							var isEnabled = true;
							this.$chosonSearchInput = this.$element.find('#has_search_page_method');
							this.$element.find('.errors').empty();
							this.$chosonSearchInput.parent().show();
							this.$searchType = this.$element.find('#has_search_page_method input[name="search_type[]"]');

							if (setting.area_search_filter.search_type.length > 0) {
								for (i=0,l=setting.area_search_filter.search_type.length;i< l;i++) {
									if (setting.area_search_filter.search_type[i] == 1) {
										// 地域から探すが選択されている場合、町名検索設定
										this.$searchType.filter('[value="'+setting.area_search_filter.search_type[i]+'"][data-choson-search="'+setting.area_search_filter.choson_search_enabled+'"]').prop('checked', true);
									} else {
										this.$searchType.filter('[value="'+setting.area_search_filter.search_type[i]+'"]').prop('checked', true);
									}
								}
							} else {
								this.$searchType.eq(0).prop('checked', true);
							}
						}
						if (setting.area_search_filter.has_search_page == 1) {
							this.$chosonSearchInput = this.$element.find('#has_search_page_method');
							this.$element.find('.errors').empty();
							this.$chosonSearchInput.parent().hide();
						}
						this.$prefContainer = this.$element.find('#pref');
						this.basic.$prefContainer = this.$prefContainer;
						this.$prefContainer.find('label').remove();
						this.basic.renderPref();
						this.$searchType = this.$element.find('#has_search_page_method').find('input[name="search_type[]"]');

						this.$publishEstate = this.$element.find('#publish_estate input');
						this.$tesuryoKokokuhi = this.$element.find('.tesuryo_kokokuhi input');

						self.renderSearchTypeMethod();

						this.$publishEstate.each(function () {
							var $this = $(this);
							$this.prop('checked', !!setting[$this.val()]);
						});
						this.$tesuryoKokokuhi.each(function () {
							var $this = $(this);
							$this.prop('checked', !!setting[$this.val()]);
						});

						this.basic.publishEstateCheckCtl();
						$(document).on('change', '#publish_estate input', function() {
							self.basic.publishEstateCheckCtl();
							self.$publishEstate.each(function () {
								var $this = $(this);
								setting[$this.val()] = ~~$this.prop('checked');
							});
						});

						$(document).on('change', '#tesuryo_check', function() {
							self.basic.tesuryoCheckCtl();
						});

						$(document).on('change', '#pref input', function() {
							setting.area_search_filter.area_1 = self.getPref();
						});

						$('.tesuryo_kokokuhi input[type=radio]').each(function() {
							if($(this).prop('checked')) {
								$('#tesuryo_check').prop('checked', true);
							}
						});

						if($('#tesuryo_check').prop('checked') == false) {
							$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").attr('disabled', 'disabled');
						}
						this.basic.tesuryoCheckCtl();
						$(document).on('change', '.tesuryo_kokokuhi input', function() {
							self.$tesuryoKokokuhi.each(function () {
								var $this = $(this);
								setting[$this.val()] = ~~$this.prop('checked');
							});
						});

						$(document).on('change', '#has_search_page_method input:radio[name="search_type[]"]', function () {
							setting.area_search_filter.search_type = self.getSearchType();
							setting.area_search_filter.choson_search_enabled = self.getChosonSearchEnabled();
						});

						if (setting.method_setting == 3) {
							setting['niji_kokoku_jido_kokai'] = 0;
							this.$element.find('#publish_estate #publish_estate-niji_kokoku_jido_kokai').parent().hide();
							this.$element.find("#publish_estate .notes-allowed-second-estate").hide();
							$("td.tesuryo_kokokuhi input:checkbox[name='tesuryo_kokokuhi[]']").attr('disabled', 'disabled');
							$(".search_type_method  input:radio[name='search_type[]']").attr('disabled', 'disabled');
							$("td.tesuryo_kokokuhi #tesuryo_check").attr('disabled', 'disabled');
							$("#pref input:checkbox[name='pref[]']").attr('disabled', 'disabled');
							$("td.tesuryo_kokokuhi").parent().hide();
							$(".search_type_method").parent().parent().hide();
							$("#pref").parent().hide();
							
						}
						if (setting.method_setting == 1) {
							this.$element.find('#publish_estate #publish_estate-niji_kokoku_jido_kokai').parent().show();
							this.$element.find("#publish_estate .notes-allowed-second-estate").show();
							$("td.tesuryo_kokokuhi input:checkbox[name='tesuryo_kokokuhi[]']").removeAttr("disabled")
							if (setting.area_search_filter.has_search_page == 0) {
								$(".search_type_method  input:radio[name='search_type[]']").removeAttr("disabled");
								$(".search_type_method").parent().parent().show();
							}
							$("td.tesuryo_kokokuhi #tesuryo_check").removeAttr("disabled")
							$("#pref input:checkbox[name='pref[]']").removeAttr("disabled")
							$("td.tesuryo_kokokuhi").parent().show();
							$("#pref").parent().show();
							
						}
					}
				});
				step.addContents(new methodSetting(step, $contents.eq(1), Master));

				//-----------------
				// 物件の個別設定
				//-----------------
				var invidialSetting = app.inherits(app.estate.StepContentsInvidial, function () {
					app.estate.StepContentsInvidial.apply(this, arguments);
					this.$note = this.$element.find('.js-note');
					this.$searchType = $('#template_modal .js-method-search input[name="search_type[]"]');
				},
				{
					show: function () {
						this.$note.html(
							setting.area_search_filter.has_search_page ?
								'ここで設定した市区郡が特集の検索画面で表示されます。<span style="display:none" class="bothAreaSetting"><br>※「地域から探す」と「地図から探す」で共通の取り扱いエリアとなります。別々で取り扱いエリアを設定することはできません。</span><span><br>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>':
								'ここで設定した市区郡で物件を抽出します。<span><br>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>'
						);
						this.toggleEstateType();
						this.basic.renderEstateType();
						this.renderSearchTypeCondition();
						app.estate.StepContentsInvidial.prototype.show.call(this);
					},
					setSetting: function(value) {
						setting = value;
					},
					getSetting: function () {
						return setting;
					},
					getElement: function () {
						return basicSetting;
					},
					getAllowed: function () {
						return {
							pref: currentBaseSetting.area_search_filter.area_1,
							shikuguns: currentBaseSetting.area_search_filter.area_2,
							ensens: currentBaseSetting.area_search_filter.area_3,
							ekis: currentBaseSetting.area_search_filter.area_4,
							chosons: currentBaseSetting.area_search_filter.area_5,
							choazas: currentBaseSetting.area_search_filter.area_6
						};
					},
					toggleEstateType: function () {
						$(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").closest('span').hide();
					},
					renderSearchTypeCondition: function() {
						this.$searchType.each(function () {
							$(this).prop('disabled', false).parent().parent().show();
						});
						if (currentBaseSetting.area_search_filter.search_type.length == 1 && currentBaseSetting.area_search_filter.search_type[0] == "3") {
							this.$searchType.filter('[value="2"]').prop('checked', false).prop('disabled', true).parent().parent().hide();
							this.$searchType.filter('[value="3"]').prop('checked', false).prop('disabled', true).parent().parent().hide();
						} else {
							if (($.inArray("1", currentBaseSetting.area_search_filter.search_type)) < 0) {
								this.$searchType.filter('[value="1"]').prop('checked', false).prop('disabled', true).parent().parent().hide();
								this.$searchType.filter('[value="2"]').prop('checked', false).prop('disabled', true).parent().parent().hide();
							} else {
								var chosonSearchEnabled = currentBaseSetting.area_search_filter.choson_search_enabled == 1;
								if (!chosonSearchEnabled) {
									this.$searchType.filter('[value="2"]').prop('checked', false).prop('disabled', true).parent().parent().hide();
								}
							}

							if (($.inArray("2", currentBaseSetting.area_search_filter.search_type)) < 0) {
								this.$searchType.filter('[value="3"]').prop('checked', false).prop('disabled', true).parent().parent().hide();
							}
						}
					}
				});
				step.addContents(new invidialSetting(step, $contents.eq(2), Master));
				//-----------------
				// 市区郡選択
				//-----------------
				var shikugunSetting = app.inherits(app.estate.StepContentsShikugun, function () {
					app.estate.StepContentsShikugun.apply(this, arguments);
					this.$note = this.$element.find('.js-note');
				},
				{
					show: function () {
						this.$note.html(
							setting.area_search_filter.has_search_page ?
								'ここで設定した市区郡が特集の検索画面で表示されます。<span style="display:none" class="bothAreaSetting"><br>※「地域から探す」と「地図から探す」で共通の取り扱いエリアとなります。別々で取り扱いエリアを設定することはできません。</span><span><br>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>':
								'ここで設定した市区郡で物件を抽出します。<span><br>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>'
						);
						app.estate.StepContentsShikugun.prototype.show.call(this);
					},
					getSetting: function () {
						return setting;
					},
					getAllowed: function () {
						return {
							pref: currentBaseSetting.area_search_filter.area_1,
							shikuguns: currentBaseSetting.area_search_filter.area_2,
							chosons: currentBaseSetting.area_search_filter.area_5,
							choazas: currentBaseSetting.area_search_filter.area_6
						};
					}
				});
				step.addContents(new shikugunSetting(step, $contents.eq(3), Master));

				//-----------------
				// 沿線・駅選択
				//-----------------
				var ensenSetting = app.inherits(app.estate.StepContentsEnsen, function () {
					app.estate.StepContentsEnsen.apply(this, arguments);
					this.$note = this.$element.find('.js-note');
				},
				{
					show: function () {
						this.$note.text(
							setting.area_search_filter.has_search_page ?
								'ここで設定した沿線・駅が特集の検索画面で表示されます。':
								'ここで設定した沿線・駅で物件を抽出します。'
						);
						app.estate.StepContentsEnsen.prototype.show.call(this);
					},
					getSetting: function () {
						return setting;
					},
					getAllowed: function () {
						return {
							pref: currentBaseSetting.area_search_filter.area_1,
							ensens: currentBaseSetting.area_search_filter.area_3,
							ekis: currentBaseSetting.area_search_filter.area_4
						};
					}
				});
				step.addContents(new ensenSetting(step, $contents.eq(4), Master));

				//-----------------
				// 検索条件設定
				//-----------------
				var searchSetting = app.inherits(app.estate.StepContentsSpecialSearchFilter, function () {
					app.estate.StepContentsSpecialSearchFilter.apply(this, arguments);
				},
				{
					getSetting: function () {
						return setting;
					},
					next: function () {
						$('.kakaku-error').remove();
						$('.rimawari-error').remove();
						$('.menseki-error').remove();
						$('.chikunensu-error').remove();

						var self = this;
						var kakaku1 = self.$element.find('.js-search-filter-item-kakaku-1');
						var kakaku2 = self.$element.find('.js-search-filter-item-kakaku-2');
						if (kakaku1 && kakaku2) {
							var min = parseInt(kakaku1.val());
							var max = parseInt(kakaku2.val());
							if ((max > 0) && (min > max)) {
								kakaku2.next().after('<div class="errors kakaku-error">下限は上限以下を設定して下さい。</div>');
								app.scrollTo(0);
								return;
							}
						}

						var rimawari1 = self.$element.find('.js-search-filter-item-rimawari-1');
						var rimawari2 = self.$element.find('.js-search-filter-item-rimawari-2');
						if (rimawari1 && rimawari2) {
							var min = parseInt(rimawari1.val());
							var max = parseInt(rimawari2.val());
							if ((max > 0) && (min > max)) {
								rimawari2.next().after('<div class="errors rimawari-error">下限は上限以下を設定して下さい。</div>');
								app.scrollTo(0);
								return;
							}
						}

						var menseki1 = self.$element.find('.js-search-filter-item-menseki-1');
						var menseki3 = self.$element.find('.js-search-filter-item-menseki-3');
						if (menseki1 && menseki3) {
							var min = parseInt(menseki1.val());
							var max = parseInt(menseki3.val());
							if ((max > 0) && (min > max)) {
								$(menseki1).closest("td").append('<div class="errors menseki-error">下限は上限以下を設定して下さい。</div>');
								app.scrollTo(0);
								return;
							}
						}

						var menseki2 = self.$element.find('.js-search-filter-item-menseki-2');
						var menseki4 = self.$element.find('.js-search-filter-item-menseki-4');
						if (menseki2 && menseki4) {
							var min = parseInt(menseki2.val());
							var max = parseInt(menseki4.val());
							if ((max > 0) && (min > max)) {
								$(menseki1).closest("td").append('<div class="errors menseki-error">下限は上限以下を設定して下さい。</div>');
								app.scrollTo(0);
								return;
							}
						}

						var chikunensu2 = self.$element.find('.js-search-filter-item-chikunensu-2');
						var chikunensu3 = self.$element.find('.js-search-filter-item-chikunensu-3');
						if (chikunensu2 && chikunensu3) {
							var min = parseInt(chikunensu2.val());
							var max = parseInt(chikunensu3.val());
							if ((max > 0) && (min > max)) {
								$(chikunensu2).closest("td").append('<div class="errors chikunensu-error">下限は上限以下を設定して下さい。</div>');
								app.scrollTo(0);
								return;
							}
						}

						self.step.next(1);
					}
				});
				step.addContents(new searchSetting(step, $contents.eq(5), Master));


				//-----------------
				// 設定確認
				//-----------------
				var confirmSetting = step.createContents($contents.eq(6), '設定確認', function () {
					this.$buttons = this.$element.find('.js-prev-step,.js-next-step');

					this.pageBasicSetting = new app.estate.ConfirmSpecialPageBasicView(publishStatus);
					this.$element.find('.js-confirm-page-basic-setting').append(this.pageBasicSetting.$element);

					this.specialBasicSetting = new app.estate.ConfirmSpecialBasicView(Master);
					this.$element.find('.js-confirm-special-basic-setting').append(this.specialBasicSetting.$element);

					this.confirmShikugun = new app.estate.ConfirmShikugunView(Master);
					this.$confirmShikugun = this.$element.find('.js-confirm-shikuguns').append(this.confirmShikugun.$element);

					this.confirmEnsen = new app.estate.ConfirmEnsenView(Master);
					this.$confirmEnsen = this.$element.find('.js-confirm-ensens').append(this.confirmEnsen.$element);

					this.specialSearchFilter = new app.estate.ConfirmSpecialSearchFilterView();
					this.$element.find('.js-confirm-special-search-filter').append(this.specialSearchFilter.$element);
					
					this.specialHousesList = new app.estate.ConfirmSpecialHousesListView();
					this.$element.find('.js-confirm-special-houses-list').append(this.specialHousesList.$element);

					this.specialMethod = new app.estate.ConfirmHouseSpecialBasicView(Master);
					this.$element.find('.js-confirm-method-special-setting').append(this.specialMethod.$element);

					this.specialSecond = new app.estate.ConfirmSecondHouseSpecialView(Master, setting.method_setting);
					this.$element.find('.js-confirm-second-special-setting').append(this.specialSecond.$element);

				},
				{
					show: function () {
						this.pageBasicSetting.render(setting);
						this.specialBasicSetting.render(setting);

						if (setting.method_setting == 1) {
							this.specialMethod.render(setting);
							this.renderShikugun();
							this.renderEnsen();
							this.$element.find('.js-confirm-special-search-filter').show();
							this.$element.find('.js-confirm-special-houses-list').hide();
							this.specialSearchFilter.render(setting);
							this.$element.find('.js-confirm-method-special-setting').show();
							this.$element.find('.js-confirm-second-special-setting').hide();
						} else if (setting.method_setting == 2) {
							this.$confirmShikugun.hide();
							this.$confirmEnsen.hide();
							this.$element.find('.js-confirm-special-search-filter').hide();
							this.$element.find('.js-confirm-special-houses-list').show();
							this.specialHousesList.render(setting);
							this.setHousesNo();
							this.setting = setting;
							this.houses_id = setting.houses_id;
							this.initCheckAll();
							this.$element.find('.js-confirm-method-special-setting').hide();
							this.$element.find('.js-confirm-second-special-setting').hide();
						} else {
							this.specialSecond.render(setting);
							this.$confirmShikugun.hide();
							this.$confirmEnsen.hide();
							this.$element.find('.js-confirm-special-search-filter').hide();
							this.$element.find('.js-confirm-special-houses-list').hide();
							this.$element.find('.js-confirm-method-special-setting').hide();
							this.$element.find('.js-confirm-second-special-setting').show();
						}

						app.estate.StepContents.prototype.show.call(this);
					},
					setHousesNo: function () {
						this.houses_id = setting.houses_id;
					},
					renderShikugun: function () {
						if (!hasSearchType(Master.searchTypeConst.TYPE_AREA) && !hasSearchType(Master.searchTypeConst.TYPE_SPATIAL)) {
							this.$confirmShikugun.hide();
							return;
						}

						this.confirmShikugun.render(setting);

						this.$confirmShikugun.show();
					},
					renderEnsen: function () {
						if (!hasSearchType(Master.searchTypeConst.TYPE_ENSEN)) {
							this.$confirmEnsen.hide();
							return;
						}

						this.confirmEnsen.render(setting);

						this.$confirmEnsen.show();
					},
					prev: function () {
						if (this.$buttons.hasClass('is-disable')) {
							return;
						}
						if (setting.method_setting == 3) {
							this.step.prev(5);
						} else if (setting.method_setting == 2) {
							this.step.setNextStep(false);
							this.step.prev(4);
						} else {
							this.step.prev();
						}
					},
					next: function () {
						if (this.$buttons.hasClass('is-disable')) {
							return;
						}
						this.$buttons.addClass('is-disable');
						this.step.lock(true);

						var self = this;

						if (setting.method_setting != 2) {
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
						}
						setting.display_freeword = $('input[id="display_freeword"]:checked').length;

						setting.owner_change = $('input[name="owner_change"]:checked').val();

						var saveData = $.extend({}, setting);
						saveData.area_search_filter = JSON.stringify(setting.area_search_filter);
						saveData.search_filter      = JSON.stringify(setting.search_filter);

						if(saveData.enabled_estate_type.length != 1 || saveData.enabled_estate_type[0] != 12) {
							saveData.owner_change = 0;
						}

						app.api('/estate-special/api-save', saveData, function () {
							self.step.lock(false);
							self.step.next();
							self.step.lock(true);
							app.updateAlertPublish();
						})
						.fail(function () {
							self.step.lock(false);
						})
						.always(function () {
							self.$buttons.removeClass('is-disable');
						});
					}
				});

				//-----------------
				// 設定完了
				//-----------------
				var completeSetting = step.createContents($contents.eq(7), '設定完了');
				function resetSettingSpecial() {
					resetSetting = 0;
					setting.only_er_enabled = null;
					setting.second_estate_enabled = null;
					setting.end_muke_enabled = 0;
					setting.only_second = 0;
					setting.exclude_second = 0;
					setting.area_search_filter.has_search_page = null;
					setting.area_search_filter.search_type = [];
					setting.area_search_filter.choson_search_enabled = 0;
					setting.area_search_filter.area_1 = [];
					setting.area_search_filter.area_2 = {};
					setting.area_search_filter.area_3 = {};
					setting.area_search_filter.area_4 = {};
					setting.area_search_filter.area_5 = {};
					setting.area_search_filter.area_6 = {};
					setting.area_search_filter.search_condition = {"type":0,"count":0};
					setting.search_filter.categories = [];
					setting.display_freeword = null;
					setting.owner_change = null;
					setting.jisha_bukken = null;
					setting.niji_kokoku = null;
					setting.niji_kokoku_jido_kokai = null;
					setting.tesuryo_ari_nomi = null;
					setting.tesuryo_wakare_komi = null;
					setting.kokokuhi_joken_ari = null;
					setting.on = 0;
					setting.houses_id = [];
					basicSetting.$searchType.prop('checked', false).change();
					basicSetting.$hasSearchPage.prop('checked', false).change();
					basicSetting.$hasSearchPage.eq(0).prop('checked', true).change();
					$('#display_freeword').prop('checked', false);
					basicSetting.$element.find('.errors').empty();
				}
				function changeNavigation(method) {
					switch(parseInt(method)) {
						case 1:
							$('.js-step').find('img').eq(0).attr("src", "/images/article/condition/1.png?v=20200313");
							$('.js-step').find('img').eq(1).attr("src", "/images/article/condition/2.png?v=20200313");
							$('.js-step').find('img').eq(6).attr("src", "/images/article/condition/6.png?v=20200313");
							$('.js-step').find('img').eq(7).attr("src", "/images/article/condition/7.png?v=20200313");
							break;
						case 2:
							$('.js-step').find('img').eq(0).attr("src", "/images/article/individually/1.png?v=20200313");
							$('.js-step').find('img').eq(6).attr("src", "/images/article/individually/3.png?v=20200313");
							$('.js-step').find('img').eq(7).attr("src", "/images/article/individually/4.png?v=20200313");
							break;
						case 3:
							$('.js-step').find('img').eq(0).attr("src", "/images/article/recommended/1.png?v=20200313");
							$('.js-step').find('img').eq(1).attr("src", "/images/article/recommended/2.png?v=20200313");
							$('.js-step').find('img').eq(6).attr("src", "/images/article/recommended/3.png?v=20200313");
							$('.js-step').find('img').eq(7).attr("src", "/images/article/recommended/4.png?v=20200313");
							break;
					}
				}
				function resetHouseType() {
					setting.houses_id = [];
					setting.area_search_filter.search_condition['count'] = 0;
					resetHouse = 2;
				}
				function resetTesuryo() {
					setting.on = 0;
					setting.tesuryo_ari_nomi = 0;
					setting.tesuryo_wakare_komi = 0;
				}
			});
		})();
	</script>
	@stop
	@section('title')特集設定 @stop
<div class="main-contents article-search">
	<h1>物件特集の<span class="js-h1-title">作成/更新（基本設定）</span></h1>
	<div class="main-contents-body">

		<div class="setting-flow js-step">
            <p><img src="/images/article/condition/1.png?v=20200313" alt="基本設定"></p>
            <p style="display:none;"><img src="/images/article/condition/2.png?v=20200313" alt="物件の設定"></p>
            <p style="display:none;"><img src="/images/article/individually/2.png?v=20200313" alt="物件の個別設定"></p>
            <p style="display:none;"><img src="/images/article/condition/3.png?v=20200313" alt="市区郡"></p>
            <p style="display:none;"><img src="/images/article/condition/4.png?v=20200313" alt="沿線駅"></p>
            <p style="display:none;"><img src="/images/article/condition/5.png?v=20200313" alt="絞り込み条件"></p>
            <p style="display:none;"><img src="/images/article/condition/6.png?v=20200313" alt="設定確認"></p>
            <p style="display:none;"><img src="/images/article/condition/7.png?v=20200313" alt="設定完了"></p>
		</div>


		<div class="js-step-contents">
			<h2>ページの基本設定</h2>
			<form>
				@csrf
				<?php if(isset($view->special) && $view->special):?>
				<input type="hidden" name="id" value="<?php echo $view->special->id?>">
				<?php endif;?>

				<div class="section">
					<table class="form-basic">
						<?php $name = 'title'?>
						<tr class="is-require">
							<th><span>特集名<?php echo $view->toolTip('estate_special-'.$name)?></span></th>
							<td>
								<div class="mb10 w40per">
									<?php $view->form->form($name)?>
								</div>
								<div class="errors"></div>
							</td>
						</tr>

						<?php /* tdk?>
						<?php $name = 'description'?>
						<tr class="is-require">
							<th>ページの説明</th>
							<td>
								<?php $view->form->form($name)?>
								<span class="input-count"></span>
								<div class="errors"></div>
							</td>
						</tr>
						<tr class="is-require">
							<th>キーワード</th>
							<td>
								<?php for($i=1;$i<=3;$i++):?>
								<?php $view->form->form('keyword'.$i)?>
								<span class="input-count"></span>
								<?php endfor?>
								<div class="errors"></div>
							</td>
						</tr>
						*/?>

						<?php $name = 'filename'?>
						<tr class="is-require">
							<th><span>ページ名（英語表記）<?php echo $view->toolTip('tdk_filename_special')?></span></th>
							<td>
								<div class="mb10 w40per">
									<span>sp-</span><?php $view->form->form($name)?>
								</div>
								<div class="errors"></div>
							</td>
						</tr>
						<?php $name = 'comment'?>
						<tr>
							<th><span>特集ページの紹介コメント<?php echo $view->toolTip('estate_special-'.$name)?></span></th>
							<td>
								<div class="mb10">
									<?php $view->form->form($name)?>
									<span class="input-count"></span>
								</div>
								<div class="errors"></div>
							</td>
						</tr>
					</table>
				</div>

				<h2>特集の基本設定</h2>
				<div class="section">
					<table class="form-basic">
						<tr class="is-require">
							<th><span>物件種目</span></th>
							<td id="enabled_estate_type">
								<?php $baseEstateTypes = $view->form->getElement('enabled_estate_type')->getValueOptions();?>
								<?php 
									$name ='estate_class';
									$estateClassRadios = explode('<br>',$view->form->form($name, false))?>
								<ul>
									<?php $i = 0;?>
									<?php foreach ($view->form->getElement($name)->getValueOptions() as $estateClass => $estateClassLabel):?>
										
										<li class="<?php if($i != 0):?>mt10<?php endif;?>">
											<?php echo $estateClassRadios[$i++]?>
											<ul class="ml20">
												<?php foreach (TypeList::getInstance()->getByClass($estateClass) as $estateType => $estateTypeName):?>
													<?php if (!isset($baseEstateTypes[ $estateType ])) continue?>
													<li style="display: inline-block">
														<label>
															<input data-estate-class="<?php echo $estateClass?>" type="checkbox" name="enabled_estate_type[]" value="<?php echo $estateType?>">
															<?php echo h($estateTypeName)?>
														</label>
														<?php if(isset($view->shumokuTypeMaster[ $estateType ])) {?>
															<a style="margin-left:-43px; margin-right:10px;">詳細な種目を選ぶ</a>
														<?php }?>
														<?php if(isset($view->shumokuTypeMaster[ $estateType ])) {?>
														<div class="shumoku_shosai_box" style="nowrap">
															<?php
															$cnt = 0;
															foreach($view->shumokuTypeMaster[ $estateType ] as $item) {
															?>

															<?php if(gettype($item) == 'string') {
																echo $item;
															} else { ?>
															<label style="display:block; float: left;">
																<input class="shumoku_shosai" type="checkbox" value="<?php echo $item['item_id'];?>"<?php print ' initialck="'.$item['checked'].'"';?> label_val="<?php echo $item['label'];?>">
																<?php echo $item['label'];?>
															</label>
															<?php } ?>
															<?php } ?>
															<?php if($estateType == 12) {?>
															<br style="clear:both;"/>
																<label style="display: block;clear: both;"><b>オーナーチェンジ</b></label>

																<label style="display: block;float: left;">
																<input type="radio" name="owner_change" value="0" checked>オーナーチェンジを含む
																</label>
																<label style="display: block;float: left;">
																<input type="radio" name="owner_change" value="2">オーナーチェンジを除く
																</label>
																<label style="display: block;float: left;">
																<input type="radio" name="owner_change" value="1">オーナーチェンジのみ
																</label>
															<?php } ?>
														</div>

														<?php }?>
													</li>
												<?php endforeach;?>
											</ul>
										</li>
									<?php endforeach;?>
								</ul>
								<div class="errors"></div>
							</td>
						</tr>

						<?php $name = 'method_setting'?>
						<tr class="is-require">
							<th><span>特集の設定方法</span></th>
							<td id="method_setting">
								<?php $view->form->form($name);?>
								<div class="errors"></div>
							</td>
						</tr>

						<?php $name = 'has_search_page'?>
						<tr class="is-require">
							<th><span>ホームページ上の検索方法</span></th>
							<td id="has_search_page">
								<?php $radios = explode('<br>', $view->form->form($name, false))?>						
								<?php $name = 'search_type'?>
								<ul>
									<li id="search_type" class="mb10 js-has-search-page-container">
										<?php echo $radios[0]?>
										<ul class="ml20 js-search-type">
											<li>
												<label><input type="checkbox" name="search_type[]" id="search_type-1" value="1">地域から探す</label>
												<ul class="choson-search-input js-only-choson-search-enabled">
													<li><label><input type="radio" name="choson_search_enabled" value="0">市区郡まで検索させる</label></li>
													<li><label><input type="radio" name="choson_search_enabled" value="1">町名まで検索させる</label></li>
												</ul>
											</li>
											<li><label><input type="checkbox" name="search_type[]" id="search_type-2" value="2">沿線・駅から探す</label></li>
											<li><label><input type="checkbox" name="search_type[]" id="search_type-3" value="3">地図から探す</label></li>
										</ul>
									</li>
									<li class="js-has-search-page-container no_search_page">
										<?php  echo $radios[1] ?>
									</li>
								</ul>
								<div class="errors"></div>
							</td>
						</tr>

						<tr>
							<th><span>フリーワード検索</span></th>
							<td>
								<label>
									<input type="checkbox" name="display_freeword" id="display_freeword" value="1">利用する
								</label>
							</td>
						</tr>

					</table>
				</div>
			</form>
			
			<div class="section btn-area">
				<a href="<?php echo route('default.estatespecial.index')?>" class="btn-t-gray js-confirm-leave-edit">設定トップに戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">次の設定に進む</a>
			</div>
        </div>
        <div class="js-step-contents" style="display:none;">
            <h2>物件の設定</h2>
            <div class="section">
                <form>
					@csrf
                    <table class="form-basic page_method">
                        <tr class="is-line"><th></th><td></td></tr>
                        <tr class="is-require">
                            <th><span>物件の指定方法</span></th>
                            <td id="has_search_page_method">
                                <li class="js-has-search-page-container no_search_page search_type_method">
                                    <ul id="search_type_method" class="js-search-type">
                                        <li><label><input type="radio" name="search_type[]" id="search_type-1-0" value="1" data-choson-search="0">地域から探す（市区郡から指定する）</label></li>
                                        <li class="js-only-choson-search-enabled"><label><input type="radio" name="search_type[]" id="search_type-1-1" value="1" data-choson-search="1">地域から探す（町名から指定する）</label></li>
                                        <li><label><input type="radio" name="search_type[]" id="search_type-2" value="2" data-choson-search="0">沿線・駅から探す（沿線・駅で指定する）</label></li>
                                        <li><label><input type="radio" name="search_type[]" id="search_type-3" value="3" data-choson-search="0">地図から探す（地図で指定する）</label></li>
                                    </ul>
                                    <div class="errors"></div>
                                </li>
                            </td>
                        </tr>
                        <?php $name = 'pref'?>
                        <tr class="is-require">
                            <th><span>都道府県</span></th>
                            <td id="pref" class="prefectures">
                                <?php /*$view->form->form($name)*/?>
                                <div class="errors"></div>
                            </td>
                        </tr>
						
                        <tr class="is-require">
                            <?php $name = 'publish_estate'?>
                            <th><span>公開する物件</span></th>
                            <td id="publish_estate">
                                <p class="list-heading">公開する物件の種類</p>
                                <ul class="list-radio-block">
                                    <?php $checks = explode('<br>', $view->formMethod->form($name, false))?>
									
                                    <li><?php echo $checks[0]?></li>
                                    <li><?php echo preg_replace('/\<\/label\>$/', '<span class="fs-small" style="display:inline;">※ATBBの物件情報入手にて「取込み」し公開した物件</span></label>', $checks[1])?></li>
                                    <?php $isAllowedSecondEstate = getInstanceUser('cms')->isAvailableSecondEstate() && $view->acl()->isAllowed('index', 'second-estate-search-setting')?>
                                    <?php if($isAllowedSecondEstate):?>
                                    <li><?php echo $checks[2]?></li>
                                    <?php endif?>
                                </ul>

                                <p class="list-heading">公開する物件の絞り込みオプション<?php if($isAllowedSecondEstate):?><br><span class="fs-small notes-allowed-second-estate">※2次広告自動公開の物件が選択されている場合はこのオプションは利用できません。</span><?php endif?></p>
                                <ul class="list-radio-block">
                                    <li><?php echo $checks[3]?></li>
                                </ul>
                                <div class="errors"></div>
                            </td>
                        </tr>
                        <tr>
                            <th><span>手数料/広告費</span></th>
                            <td class="tesuryo_kokokuhi">
                                <?php $name = 'tesuryo_kokokuhi'?>
                                <?php $checks = explode('<br>', $view->formMethod->form($name, false))?>
                                <div><?php echo $checks[0]?></div>
                                <div class="sp-basic-tesuryo">
                                    <label><input type="checkbox" id="tesuryo_check"/>手数料ありの物件だけ表示する</label>
                                    <span>(
                                    <?php
                                        $radio1 =  preg_replace('/checkbox/', 'radio', $checks[1]);
                                        $radio1 =  preg_replace('/手数料ありの物件だけ表示する（(.*)）/', "$1", $radio1);
                                        $radio2 =  preg_replace('/checkbox/', 'radio', $checks[2]);
                                        $radio2 =  preg_replace('/手数料ありの物件だけ表示する（(.*)）/', "$1", $radio2);
                                        echo $radio1;
                                        echo $radio2;
                                    ?>)</span>
                                </div>
                                <div><?php echo $checks[3]?></div>
                                <div class="errors"></div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="section handing-area">
                <p class="mb10 js-note"></p>
                <div class="errors"></div>
                <div class="js-table-container"></div>
            </div>
            <div class="section btn-area">
                <a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
                <a href="javascript:;" class="btn-t-blue size-l js-next-step">次の設定に進む</a>
            </div>
        </div>
		
        <div class="js-step-contents" style="display:none;">
			<h2>物件の個別設定</h2>
			<div class="section individual-setting ">
                <div class="btn-all-house">
                    <a href="javascript:;" class="btn-t-blue">物件を一括で表示する</a>
                    <div>特集で公開する物件を下記の条件か物件番号から検索してください</div>
                </div>
                <div class="condition-search-house">
                    <div class="set-condition-container">
                        <table class="tb-basic">
                            <thead>
                                <tr>
                                    <th class="alL">条件で探す</th>
                                </tr>
                            </thead>
                            <tr>
                                <td>条件を指定して検索します。</td>
                            </tr>
                            <tr>
                                <td><a href="javascript:;" class="btn-t-blue btn-search-condition">条件設定</a></td>
                            </tr>
                        </table>
                    </div>
                    <div class="search-house-container">
                        <table class="tb-basic">
                            <thead>
                                <tr>
                                    <th class="alL">物件番号で探す</th>
                                </tr>
                            </thead>
                            <tr>
                                <td class="errors"><p>物件が見つかりません</p></td>
                            </tr>
                            <tr>
                                <td>物件番号は８桁・10桁・11桁で入力してください。</td>
                            </tr>
                            <tr>
                                <td class="house-no-input">
                                    <span><input type="text" name=house_no[] autocomplete="nope"></span>
                                    <span><input type="text" name=house_no[] autocomplete="nope"></span>
                                    <span><input type="text" name=house_no[] autocomplete="nope"></span>
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <a href="javascript:;" class="btn-t-blue btn-search-house is-disable">検索</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="errors"></div>
                <div class="section-house-list hide">
                    <p>特集で公開する物件を選択して下さい</p>
                    <a href="javascript:;" class="btn-t-result-condition btn-t-blue">検索結果を開く</a>
                    <div class="section house-list hide">
                    </div>
                </div>

			</div>
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">設定確認に進む</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<h2>エリア一覧</h2>
			<div class="section handing-area">
				<p class="mb10 js-note"></p>

				<div class="errors"></div>
				<div class="js-table-container"></div>

			</div>
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">次の設定に進む</a>
			</div>
        </div>

		<div class="js-step-contents" style="display:none;">
			<h2>沿線一覧</h2>
			<div class="section handing-area">
				<p class="mb10 js-note"></p>

				<div class="errors"></div>
				<div class="js-table-container"></div>

			</div>
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">次の設定に進む</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<h2>絞り込み条件</h2>
			<div class="section">
				<p class="mb10"></p>

				<div class="errors"></div>
				<div class="js-table-container"></div>

			</div>
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">設定確認に進む</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<div class="section js-confirm-page-basic-setting">
				<h2>ページの基本設定</h2>
			</div>

			<div class="section js-confirm-special-basic-setting">
				<h2>特集の基本設定</h2>
			</div>

            <div class="section js-confirm-method-special-setting">
                <h2>物件の設定</h2>
            </div>

            <div class="section js-confirm-second-special-setting">
                <h2>物件の設定</h2>
            </div>

			<div class="section confirm-area js-confirm-shikuguns">
				<h2>市区郡</h2>
				<span>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>
			</div>

			<div class="section confirm-station js-confirm-ensens">
				<h2>沿線・駅</h2>
			</div>

			<div class="section js-confirm-special-search-filter">
				<h2>絞り込み条件</h2>
            </div>
            <div class="section js-confirm-special-houses-list">
				<h2>公開する物件の個別設定一覧</h2>
			</div>

			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">設定を保存する</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			<div class="section setting-finish">
				<h2>設定を保存しました。</h2>
				<p>特集設定が完了いたしました。</p>

				<p>本設定だけではホームページに公開されません。</p>

				<p>作成した特集をホームページに公開するには「ページの作成/更新」（上部メニューまたは下部リンク）より設定が必要です。<br>
				　1. 作成した特集は「階層外のページ」に配置されます。<br>
				　2.「メインメニュー」の中で配置したいページの「+追加」より登録します。<br>
				　3.「サイトの公開/更新」より公開処理をしてください。<br>
				<br>
				　また、物件コマとしてページへ挿入することも出来ます。
				</p>

				<div class="link-pageend">
					<ul>
						<li><a href="<?php echo $view->route('index', 'site-map')?>" class="i-s-link">ページの作成/更新</a></li>
						<li><a href="/" class="i-s-link">ホームへ</a></li>
					</ul>
				</div>
			</div>

			<div class="section btn-area">
				<a href="<?php echo $view->route('index', 'estate-special')?>" class="btn-t-blue size-l">特集トップへ</a>
			</div>
		</div>
		
    </div>
    <div id="template_modal" style="display: none">
        <h2 class="individual-title">設定方法を選択してください。</h2>
        <div class="js-method-search">
            <ul class="is-required">
                <?php foreach ($view->searchTypeConditionMaster as $key=>$searchType) :?>
                <li><label><input type="radio" name="search_type[]" value="<?php echo $key;?>"><?php echo $searchType;?></label></li>
                <?php endforeach; ?>
            </ul>
            <div class="errors"></div>
        </div>
        <h2 class="individual-title">都道府県を選択してください。</h2>
        <div id="pref" class="prefectures">
            <div class="errors"></div>
        </div>
        <h2 class="individual-title">公開する物件の種類を選択してください。</h2>
        <div class="js-type-publish">
            <div class="is-required" id="publish_estate">
                <p class="list-heading">公開する物件の種類<br>
                <ul class="list-radio-block">
                    <?php $checks = explode('<br>', $view->formMethod->form('publish_estate', false))?>
                    <li>
                        <?php echo $checks[0]?>
                    </li>
                    <li>
                        <?php echo preg_replace('/\<\/label\>$/', '<span class="fs-small" style="display:inline;">※ATBBの物件情報入手にて「取込み」し公開した物件</span></label>', $checks[1])?>
                    </li>
                    <?php $isAllowedSecondEstate = getInstanceUser('cms')->isAvailableSecondEstate() && $view->acl()->isAllowed('index', 'second-estate-search-setting')?>
                    <?php if($isAllowedSecondEstate):?>
                    <li>
                        <?php echo $checks[2]?>
                    </li>
                    <?php endif; ?>
                </ul>
                <p class="list-heading">公開する物件の絞り込みオプション 
                    <?php if($isAllowedSecondEstate):?>
                    <br>
                    <span class="fs-small">※2次広告自動公開の物件が選択されている場合はこのオプションは利用できません。</span>
                    <?php endif; ?>
                </p>
                <ul class="list-radio-block">
                    <li><?php echo $checks[3]?></li>
                </ul>
                <div class="errors ml0"></div>
            </div>
        </div>
        <h2 class="individual-title">手数料/広告費を選択してください。</h2>
        <div class="tesuryo_kokokuhi">
            <?php $name = 'tesuryo_kokokuhi'?>
            <?php $checks = explode('<br>', $view->formMethod->form($name, false))?>
            <div>
            <?php echo $checks[0]?>
            </div>

            <div class="sp-basic-tesuryo">
                <label><input type="checkbox" id="tesuryo_check"/>手数料ありの物件だけ表示する</label>
                <span>
                (
                <?php
                $radio1 =  preg_replace('/checkbox/', 'radio', $checks[1]);
                $radio1 =  preg_replace('/手数料ありの物件だけ表示する（(.*)）/', "$1", $radio1);
                $radio2 =  preg_replace('/checkbox/', 'radio', $checks[2]);
                $radio2 =  preg_replace('/手数料ありの物件だけ表示する（(.*)）/', "$1", $radio2);
                echo $radio1;
                echo $radio2;
                ?>)
                </span>
            </div>

            <div>
            <?php echo $checks[3]?>
            </div>
            <div class="errors"></div>
        </div>
    </div>
</div>
@endsection