@extends('layouts.default')
@section('title', __('2次広告自動公開設定'))
@section('slyte')
<link rel="stylesheet" href="/css/estate_extension.css"> 
@endsection

@section('script')
<script type="text/javascript" src="/js/app.estate.js?v=20171213"> </script>
<script type="text/javascript">
(function () {
	
	'use strict';
	
	$(function () {
	
		var Master = {
			prefMaster      :<?php echo json_encode($view->prefMaster)?>,
			searchTypeMaster: <?php echo json_encode($view->searchTypeMaster)?>,
			searchTypeConst : <?php echo json_encode($view->searchTypeConst)?>,
			estateTypeMaster: <?php echo json_encode($view->estateTypeMaster)?>,
			secondEnabledMaster: <?php echo json_encode($view->secondEnabledMaster)?>
		};
		
		var EstateMaster = app.estate.EstateMaster;
		var setting = <?php echo json_encode($view->setting)?>;
		var baseSetting = <?php echo json_encode($view->baseSetting)?>;
		
		function hasSearchType(type) {
			return ''+type === ''+setting.area_search_filter.search_type;
		}
		function needShikugunSetting() {
			return hasSearchType(Master.searchTypeConst.TYPE_AREA);
		}
		function needEnsenSetting() {
			return hasSearchType(Master.searchTypeConst.TYPE_ENSEN);
		}
		
		var step = new app.estate.Step($('.js-step'), $('#topicpath'));
		var $contents = $('.js-step-contents');
		//-----------------
		// 基本設定
		//-----------------
		var basicSetting = step.createContents($contents.eq(0), $('#topicpath .last').html(), function () {
			var i,l;
			
			// 2次広告自動公開
			try {
				this.$element
					.find('#enabled input[value="'+(setting.enabled === null ? 1 : setting.enabled)+'"]')
					.prop('checked', true);
			}catch(err) {}
			
			
			// 種目
			var $estateType = this.$element.find('#enabled_estate_type input');
			$estateType.prop('checked', false);
			for (i=0,l=setting.enabled_estate_type.length;i< l;i++) {
				$estateType.filter('[value="'+setting.enabled_estate_type[i]+'"]').prop('checked', true);
			}
			
			// 探し方
			try {
				this.$element
					.find('#search_type input[value="'+(setting.area_search_filter.search_type || 1)+'"][data-choson_search_enabled="'+setting.area_search_filter.choson_search_enabled+'"]')
					.prop('checked', true);
			}catch(err) {}
			
			// 都道府県
			var $pref = this.$element.find('#pref input');
			$pref.prop('checked', false);
			for (i=0,l=setting.area_search_filter.area_1.length;i< l;i++) {
				$pref.filter('[value="'+setting.area_search_filter.area_1[i]+'"]').prop('checked', true);
			}
			
			// 非活性処理
			var self = this;
			this.$element.on('change', '#enabled input', function () {
				var isDisabled = self.$element.find('#enabled input[value="0"]').prop('checked');
				self.$element.find('#pref,#enabled_estate_type,#search_type')
					.toggleClass('is-disable', isDisabled)
					.find('input').prop('disabled', isDisabled);
				
				if (isDisabled) {
					self.$element.find('.errors').empty();
				}
			})
			.find('#enabled input').eq(0).change();
		});
		basicSetting.beforeNext = function () {
			var self = this;
			var checks = {};
			
			// 設定しない場合
			var isEnabled = !(this.$element.find('#enabled input:checked').val() === '0');
			
			$.each(['enabled', 'search_type', 'enabled_estate_type', 'pref'], function (i, id) {
				var $container = self.$element.find('#' + id);
				var $checked = $container.find('input:checked');
				$container.find('.errors').empty();
				if ($checked.length) {
					checks[id] = $checked.map(function () {
						return this.value;
					}).get();
				}
				else {
					checks[id] = [];
					if (isEnabled) {
						$container.find('.errors').html('<p>値は必須です。</p>');
					}
				}
			});
			
			if (
				isEnabled &&
				(
					!checks.enabled.length ||
					!checks.search_type.length ||
					!checks.enabled_estate_type.length ||
					!checks.pref.length
				)
			) {
				return false;
			}
			
			// データ更新
			setting.enabled                        = ~~isEnabled;
			setting.enabled_estate_type            = checks.enabled_estate_type;
			setting.area_search_filter.search_type = checks.search_type[0] || null;
            setting.area_search_filter.choson_search_enabled = this.$element.find('#search_type input:checked').attr('data-choson_search_enabled') || 0
			setting.area_search_filter.area_1      = checks.pref;
		};
		basicSetting.next = function () {
			var nextVal;
			if (setting.enabled) {
				nextVal = needShikugunSetting() ? 1 : 2;
			}
			else {
				nextVal = 4;
			}
			this.step.next(nextVal);
		};
		
		//-----------------
		// 市区郡選択
		//-----------------
		var shikugunSetting = app.inherits(app.estate.StepContentsShikugun, function () {
			app.estate.StepContentsShikugun.apply(this, arguments);
		},
		{
			getSetting: function () {
				return setting;
			},
			hasSearchType: function (type) {
				return hasSearchType(type);
			}
		});
		step.addContents(new shikugunSetting(step, $contents.eq(1), Master, '市区郡選択'));
		
		//-----------------
		// 沿線・駅選択
		//-----------------
		var ensenSetting = app.inherits(app.estate.StepContentsEnsen, function () {
			app.estate.StepContentsEnsen.apply(this, arguments);
		},
		{
			getSetting: function () {
				return setting;
			},
			hasSearchType: function (type) {
				return hasSearchType(type);
			}
		});
		step.addContents(new ensenSetting(step, $contents.eq(2), Master));
		
		//-----------------
		// 検索条件設定
		//-----------------
		var searchSetting = app.inherits(app.estate.StepContentsSecondSearchFilter, function () {
			app.estate.StepContentsSecondSearchFilter.apply(this, arguments);
		},
		{
			getSetting: function () {
				return setting;
			},
			next: function () {
				var self = this;
				var $sections,$section,secNo,i,len;
				var item1,item2;
				var errFlg = false;
				$('.kakaku-error').remove();
				$sections = $('.js-filters-container').children();
				for (i=0,len=$sections.length;i<len;i++) {
					secNo= $sections[i].id.replace( /section-/g , "" ) ;
					$section = self.$element.find('#section-'+secNo);
					// 価格
					item1 = $section.find('.js-search-filter-item-kakaku-1');
					item2 = $section.find('.js-search-filter-item-kakaku-2');
					if (item1 && item2) {
						var min = parseInt(item1.val());
						var max = parseInt(item2.val());
						if ((max > 0) && (min > max)) {
							item2.next().after('<div class="errors kakaku-error">下限は上限以下を設定して下さい。</div>');
							errFlg=true;
						}
					}
					// 使用部分面積
					item1 = $section.find('.js-search-filter-item-tatemono_ms-1');
					item2 = $section.find('.js-search-filter-item-tatemono_ms-2');
					if (item1 && item2) {
						var min = parseInt(item1.val());
						var max = parseInt(item2.val());
						if ((max > 0) && (min > max)) {
							item2.next().after('<div class="errors kakaku-error">下限は上限以下を設定して下さい。</div>');
							errFlg=true;
						}
					}
					// 土地面積
					item1 = $section.find('.js-search-filter-item-tochi_ms-1');
					item2 = $section.find('.js-search-filter-item-tochi_ms-2');
					if (item1 && item2) {
						var min = parseInt(item1.val());
						var max = parseInt(item2.val());
						if ((max > 0) && (min > max)) {
							item2.next().after('<div class="errors kakaku-error">下限は上限以下を設定して下さい。</div>');
							errFlg=true;
						}
					}
				}
				if(errFlg){
					app.scrollTo(0);
					return;
				}

				self.step.next(1);
			}
		});
		step.addContents(new searchSetting(step, $contents.eq(3), Master, '絞り込み条件選択'));
		
		//-----------------
		// 設定確認
		//-----------------
		var confirmSetting = step.createContents($contents.eq(4), '設定確認', function () {
			this.$buttons = this.$element.find('.js-prev-step,.js-next-step');
			
			this.basicSetting = new app.estate.ConfirmSecondBasicView(Master);
			$('.js-confirm-basic-setting').append(this.basicSetting.$element);
			
			this.confirmShikugun = new app.estate.ConfirmShikugunView(Master);
			this.$confirmShikugun = $('.js-confirm-shikuguns').append(this.confirmShikugun.$element);
			
			this.confirmEnsen = new app.estate.ConfirmEnsenView(Master);
			this.$confirmEnsen = $('.js-confirm-ensens').append(this.confirmEnsen.$element);
			
			this.searchFilter = new app.estate.ConfirmSecondSearchFilterView();
			this.$element.find('.js-confirm-search-filter').append(this.searchFilter.$element);
		});
		confirmSetting.show = function () {
			this.basicSetting.render(setting);
			app.estate.StepContents.prototype.show.call(this);
			
			this.renderShikugun();
			this.renderEnsen();
			
			this.searchFilter.render(setting);
			
			app.estate.StepContents.prototype.show.call(this);
		};
		confirmSetting.renderShikugun = function () {
			if (!hasSearchType(Master.searchTypeConst.TYPE_AREA)) {
				this.$confirmShikugun.hide();
				return;
			}
			
			this.confirmShikugun.render(setting);
			
			this.$confirmShikugun.show();
		};
		confirmSetting.renderEnsen = function () {
			if (!hasSearchType(Master.searchTypeConst.TYPE_ENSEN)) {
				this.$confirmEnsen.hide();
				return;
			}
			
			this.confirmEnsen.render(setting);
			
			this.$confirmEnsen.show();
		};
		confirmSetting.prev = function () {
			if (this.$buttons.hasClass('is-disable')) {
				return;
			}
			this.step.prev(setting.enabled ? 1 : 4);
		};
		confirmSetting.next = function () {
			if (this.$buttons.hasClass('is-disable')) {
				return;
			}
			this.$buttons.addClass('is-disable');
			this.step.lock(true);
			
			var self = this;
			
			if (!hasSearchType(Master.searchTypeConst.TYPE_AREA)) {
				setting.area_search_filter.area_2 = {};
                setting.area_search_filter.area_5 = {};
			}
			if (!hasSearchType(Master.searchTypeConst.TYPE_ENSEN)) {
				setting.area_search_filter.area_3 = {};
				setting.area_search_filter.area_4 = {};
			}
            if (setting.area_search_filter.choson_search_enabled != 1) {
                setting.area_search_filter.area_5 = {};
            }

			var saveData = $.extend({}, setting);
			saveData.area_search_filter = JSON.stringify(setting.area_search_filter);
			
			app.api('/second-estate-search-setting/api-save', saveData, function () {
				self.step.lock(false);
				self.step.next();
				self.step.lock(true);
			})
			.fail(function () {
				self.step.lock(false);
			})
			.always(function () {
				self.$buttons.removeClass('is-disable');
			});
		};
		
		//-----------------
		// 設定完了
		//-----------------
		var completeSetting = step.createContents($contents.eq(5), '設定完了');


		$(document).on('change', ".tesuryo_box input[type=checkbox]", function() {
			if($(this).prop('checked') === false) {
				$(this).closest('div').find('input[type=radio]').prop('checked', false);
				$(this).closest('div').find('input[type=radio]').attr('disabled', 'disabled');
			} else {
				$(this).closest('div').find('input[type=radio]').eq(0).prop('checked', true);

				if($(".main-contents h1").attr('estateclass') == 1 || $(".main-contents h1").attr('estateclass') == 2) {
					$(this).closest('div').find('input[type=radio]').eq(0).attr('disabled', false);
				} else {
					$(this).closest('div').find('input[type=radio]').attr('disabled', false);
				}
			}
		});
		$(document).on('change', ".tesuryo_box input[type=radio]", function() {
			$(this).closest('div').find('input[type=checkbox]').prop('checked', true);
		});
	});

})();
</script>
@endsection

@section('content')

<div class="main-contents article-search">
	<h1 estateclass="<?php echo $view->estateClassNo;?>">2次広告自動公開設定（<?php echo $view->estateClassName?>）：<span class="js-h1-title">基本設定</span></h1>
	<div class="main-contents-body">
	
		<div class="setting-flow js-step">
			<p><img src="/images/article/bg_ad_flow1.png?v=20171213" alt="基本設定"></p>
			<p style="display:none;"><img src="/images/article/bg_ad_flow2.png?v=20171213" alt="市区郡選択※「市区郡を対象」選択時"></p>
			<p style="display:none;"><img src="/images/article/bg_ad_flow3.png?v=20171213" alt="沿線・駅選択※「沿線・駅を対象」選択時"></p>
			<p style="display:none;"><img src="/images/article/bg_ad_flow4.png?v=20171213" alt="絞り込み条件選択"></p>
			<p style="display:none;"><img src="/images/article/bg_ad_flow5.png?v=20171213" alt="設定確認"></p>
			<p style="display:none;"><img src="/images/article/bg_ad_flow6.png?v=20171213" alt="設定完了"></p>
		</div>
		
		<div class="js-step-contents">
			<h2>2次広告自動公開の基本設定</h2>
			<div class="section">
				<table class="form-basic">
					<?php foreach ($view->form->getElements() as $name => $elem):?>
					<tr class="is-require">
						<th><span><?php echo $view->form->getElement($name)->getLabel()?><?php echo $view->toolTip('second_estate_search_setting-'.$name)?></span></th>
						<td id="<?php echo $name ?>"<?php if($name=='pref'):?> class="prefectures"<?php endif;?>>
                            <?php if ($name=='search_type'):?>
                                <label><input type="radio" name="search_type" id="search_type-1" value="1" data-choson_search_enabled="0">市区郡を対象にする</label>
                                <label><input type="radio" name="search_type" id="search_type-2" value="2" data-choson_search_enabled="0">沿線・駅を対象にする</label>
                            <?php else:?>
                                <?php echo $view->form->form($name)?>
                            <?php endif;?>
							<div class="errors"></div>
						</td>
					</tr>
					<?php endforeach;?>
				</table>
			</div>
			
			<div class="section btn-area">
				<a href="<?php echo route('default.secondestatesearchsetting.index') ?>" class="btn-t-gray js-confirm-leave-edit">設定トップに戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">次の設定に進む</a>
			</div>
		</div>
		
		<div class="js-step-contents" style="display:none;">
			<h2>市区郡選択</h2>
			<div class="section handing-area">
				<p class="mb10">
					ここで設定した市区郡が2次広告自動公開物件の対象範囲となります。
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
			<h2>沿線・駅選択</h2>
			<div class="section handing-area">
				<p class="mb10">
					ここで設定した沿線・駅が2次広告自動公開物件の対象範囲となります。
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
			<h2>絞り込み条件選択</h2>
			<div class="section">
				<p class="mb10">
					ここで設定した絞り込み条件で2次広告自動公開物件が絞り込まれます。
				</p>
				
				<div class="errors"></div>
				<div class="js-filters-container"></div>

			</div>
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">設定確認に進む</a>
			</div>
		</div>

		<div class="js-step-contents" style="display:none;">
			
			
			<div class="section js-confirm-basic-setting">
				<h2>2次広告自動公開の基本設定</h2>
			</div>
			
			<div class="section confirm-area js-confirm-shikuguns">
				<h2>市区郡</h2>
			</div>
			
			<div class="section confirm-station js-confirm-ensens">
				<h2>沿線・駅</h2>
			</div>
			
			<div class="section js-confirm-search-filter">
				<h2>絞り込み条件</h2>
			</div>
			
			<div class="section btn-area">
				<a href="javascript:;" class="btn-t-gray js-prev-step">前の画面に戻る</a>
				<a href="javascript:;" class="btn-t-blue size-l js-next-step">設定を保存する</a>
			</div>
		</div>
		
		<div class="js-step-contents" style="display:none;">
			<div class="section setting-finish">
				<h2>設定を保存しました。</h2>
				<p>2次広告自動公開設定が完了いたしました。</p>
				<p>
					特集に2次広告自動公開の物件を含める場合は、<br>
					特集の設定画面にて特集ごとに「2次広告自動公開の物件を含める」にチェックを入れてください。
				</p>
				
				<div class="link-pageend">
					<ul>
						<li><a href="/" class="i-s-link">ホームへ</a></li>
					</ul>
				</div>
			</div>
			
			<div class="section btn-area">
				<a href="<?php echo route('default.secondestatesearchsetting.index') ?>" class="btn-t-blue size-l">2次広告自動公開設定トップへ</a>
			</div>
		</div>
	</div>
</div>
@endsection