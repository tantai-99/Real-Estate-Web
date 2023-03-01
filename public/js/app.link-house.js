(function (app) {
    'use strict';

    var error_house = '以下の理由で該当物件がありません。' +
    　　　　　　　　　　'<p>・物件番号が間違っている。</p>' +
     　　　　　　　　　 '<p>・物件が公開されていない。</p>' +
     　　　　　　　　　 '<p>・物件検索設定や2次広告自動公開設定にて該当物件が対象に含まれていない。</p>';
    
	app.LinkHouse = function () {
    };
    app.LinkHouse.prototype = {
		init: function (baseSettings, Master, setting, isLite) {
            var self = this;
            this.settingAll = baseSettings;
            this.Master = Master;
            this.houses_id = [];
            this.housesRemove = [];
            this.setting = setting;
            this.OldModal = false;

            $('body').on('click', 'li:not(.is-disable) .btn-search-all-house', function () {
                self.houses_id = [];
                self.setting = self.resetSetting(setting);
                self.setContainer($(this).closest('.link-house-module'));
                self.setOldModal($(this));
                self.hideModal();
                new app.estate.conditionHouseListModal(self);
            });

            $('body').on('click', 'li:not(.is-disable) .btn-search-house-no', function () {
                var container = $(this).closest('.link-house-module');
                self.setContainer(container);
                self.setOldModal($(this));
                self.setting = self.resetSetting(setting);
                var houses_no = container.find('input.input-house-no').val().toHalfWidth();
                if (self.checkInputHouseNo(houses_no)) {
                    self.hideModal();
                    new app.estate.HouseListModal(self, [houses_no], 1);
                }
            });
            $('body').on('keypress', 'input.input-house-no', function(e) {
                if (e.charCode == 13) {
                    $('li:not(.is-disable) .btn-search-house-no').trigger('click');
                }
                var charCode = (e.which) ? e.which : e.keyCode;
                if (charCode > 31 && (charCode < 48 || charCode > 57))
                    return false;
                return true;
            })

            $('body').on('click', 'a.btn-preview-link-house:not(.is-disable)', function () {
                var url = $(this).attr('data-href');
                if (url) {
                    window.open(url, '_blank');
                }
                return;
            });
            $('body').on('mouseenter', '.link-house-module a.tooltip', function () {
                if ($(this).hasClass('is-disable')) {
                    $(this).find('.tooltip-body').css('display', 'none');
                } else {
                    $(this).find('.tooltip-body').removeAttr('style');
                }
            })
            $(document).ready(function() {
                if (!isLite) {
                    $('body').find('.input-img-wrap').each(function () {
                        $(this).find('.search-house-method input:radio').eq(0).prop('checked', true).change();
                    });
                    if (self.settingAll) {
                        self.showInfoHouseLink();
                    }
                }
            });
            app.setInputFilter(document.getElementsByClassName('input-house-no'), function(value) {
                return /^[0-9０-９]*$/.test(value); 
            });

            String.prototype.toHalfWidth = function() {
                return this.replace(/[Ａ-Ｚａ-ｚ０-９]/g, 
                  function(s) {return String.fromCharCode(s.charCodeAt(0) - 0xFEE0)});
            };
            
			
			return this;
        },
        // Restricts input for the given textbox to the given inputFilter.
        setInputFilter: function(textbox, inputFilter) {
            ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
                for (var i = 0; i < textbox.length; i++) {
                    textbox[i].addEventListener(event, function() {
                        if (inputFilter(this.value)) {
                            this.oldValue = this.value;
                            this.oldSelectionStart = this.selectionStart;
                            this.oldSelectionEnd = this.selectionEnd;
                        } else if (this.hasOwnProperty("oldValue")) {
                            this.value = this.oldValue;
                            this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                        }
                    });
                }
            });
        },
        resetSetting: function(setting) {
            setting.estate_class = null;
            setting.enabled_estate_type = [];
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
            setting.area_search_filter.search_condition = {"type":1,"count":0};
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
            return setting;
        },
        initAllowed: function (estateClass) {
            var currentBaseSetting = this.settingAll[estateClass];
            var allowed = {
                pref: currentBaseSetting.area_search_filter.area_1,
                shikuguns: currentBaseSetting.area_search_filter.area_2,
                ensens: currentBaseSetting.area_search_filter.area_3,
                ekis: currentBaseSetting.area_search_filter.area_4,
                chosons: currentBaseSetting.area_search_filter.area_5,
                choazas: currentBaseSetting.area_search_filter.area_6
            };
            this.allowed = {};
    
            if (!allowed) {
                return;
            }
            var key, _key, codes, _codes, i, l;
            for (key in allowed) {
                if(key == 'choazas') {
                    continue;
                }
    
                codes = allowed[key];
                this.allowed[key] = {};
                if ($.isArray(codes)) {
                    for (i=0,l=codes.length;i<l;i++) {
                        this.allowed[key][codes[i]] = true;
                    }
                }
                else {
                    _codes = codes;
                    for (_key in _codes) {
                        codes = _codes[_key];
                        if ($.isArray(codes)) {
                            for (i=0,l=codes.length;i<l;i++) {
                                this.allowed[key][codes[i]] = true;
                            }
                        } else {
                            /**
                             * 町
                             * key = 'chosons', _key = pref, codes = { shikugun_cd : [chosonCode...] }
                             * codeが一意ではないため使用不可
                             */
                            for (var shikugun_cd in codes) {
                                for (i=0,l=codes[shikugun_cd].length;i<l;i++) {
                                    this.allowed[key][ codes[shikugun_cd][i] ] = true;
                                }
                            }
                        }
                    }
                }
            }
            this.allowed['pref_ensens'] = {};
            for (key in allowed['ensens']) {
                codes = allowed['ensens'][key];
                this.allowed['pref_ensens'][key] = {};
                for (_key in codes) {
                    this.allowed['pref_ensens'][key][codes[_key]] = true;
                }
            }
            this.allowed['pref_ekis'] = {};
            for (key in allowed['ekis']) {
                codes = allowed['ekis'][key];
                this.allowed['pref_ekis'][key] = {};
                for (_key in codes) {
                    this.allowed['pref_ekis'][key][codes[_key]] = true;
                }
            }
            this.allowed['shikugun_chosons'] = {};
            if (allowed['chosons']) {
                for (var pref in allowed['chosons']) {
                    var shikuguns = allowed['chosons'][pref];
                    for (var shikugunCd in shikuguns) {
                        var chosons = shikuguns[shikugunCd];
                        if (chosons.length) {
                            this.allowed['shikugun_chosons'][shikugunCd] = {};
                            var i = 0;
                            var choson;
                            for (;choson = chosons[i];i++) {
                                this.allowed['shikugun_chosons'][shikugunCd][choson] = true;
                            }
                        }
                    }
                }
            }
            this.allowed['choazas'] = {};
            if(allowed['choazas']) {
                for (var pref in allowed['choazas']) {
                    var shikuguns = allowed['choazas'][pref];
                    for (var shikugunCd in shikuguns) {
                        this.allowed['choazas'][shikugunCd] = shikuguns[shikugunCd]; 
                    }
                }
            }
            return this.allowed;
        },
        clearError: function ($container) {
            var container;
            if (typeof $container != 'undefined') {
                container = $container;
            } else {
                container = this.Container;
            }
            container.closest('.input-img-wrap, .modal-sitemap-link').find('.is-error').removeClass('is-error');
            container.closest('.input-img-wrap, .modal-sitemap-link').find('.error, .errors').html('');
        },
        clearErrorHouseNo: function() {
            this.Container.closest('.input-img-wrap, .modal-sitemap-link').find('.is-error').removeClass('is-error');
            this.Container.closest('.input-img-wrap, .modal-sitemap-link').find('.error, .errors').html('');
        },
        clearInfoLinkHouse: function($container) {
            this.clearError($container);
            $container.find('.house-title label').html('');
            $container.find('.member-no-info').toggleClass('is-hide', true);
			$container.find('.display-house-title').css('display', 'none');
            $container.find('.house-title a').removeAttr('data-href');
            $container.find('.member-no-info label').html('');
            $container.find('input[name="link_house"]').val('').change();
            $container.find('input[name="title_house"]').val('').change();
            $container.find('input.input-house-no').val('');
        },
        showError: function() {
            if (this.OldModal) {
                this.showModal();
            }
            this.Container.find('div.error').last().html(error_house);
        },
        setHousesNo: function (houses_id) {
            this.houses_id = houses_id;
        },
        actionListHouse: function (dataLinkHouse) {
            var self = this;
            if (typeof dataLinkHouse != 'undefined') {
                var link = dataLinkHouse;
                if (this.isJSON(dataLinkHouse)) {
                    var linkJson = JSON.parse(dataLinkHouse);
                    self.Container.find('.search-house-method .search-method').eq(linkJson.search_type).prop('checked', true).change();
                    if (linkJson.search_type == 1 ) {
                        self.Container.find('.input-house-no').val(linkJson.house_no);
                    }
                    link = linkJson.url
                }
                var array = link.split('/');
                this.houses_id = [array[2].replace('detail-', '').replace(/[^a-zA-Z0-9]/g, '')];
                var shumokuAll = this.shumokuAll().map(function(value) {
                    return value.replace(/[^a-zA-Z0-9]/g, '');
                });
                this.setting.enabled_estate_type = [shumokuAll.indexOf(array[1].replace(/[^a-zA-Z0-9]/g, '')) + 1];
            }
            var params = {
                is_title: true,
                page: 1,
                link_page: true,
                houses_id: this.houses_id,
                estateClass: this.setting.enabled_estate_type
            }
			self.Container.find('.display-house-title').css('display', 'block');
            var eleMemberNo = self.Container.find('.member-no-info');
            eleMemberNo.find('label').text('');
            var eleTitle = self.Container.find('.house-title');
			eleTitle.find('.btn-preview-link-house').toggleClass('is-hide', true);
            eleTitle.find('label').addClass('searching').text('検索中...');
            app.estate.EstateMaster.getHouseAll(params).done(function (data) {
                eleTitle.find('label').removeClass('searching').text(data.content.title);
                if (!data.content.success) {
                    self.Container.find('div.error').last().html(error_house);
                } else {
                    eleMemberNo.toggleClass('is-hide', false);
                    eleMemberNo.find('label').eq(0).text('物件番号');
					eleTitle.find('.btn-preview-link-house').toggleClass('is-hide', false);
                }
                eleTitle.find('input').val(data.content.url);
                eleTitle.find('a').attr('data-href', data.content.domain + data.content.url);
                eleMemberNo.find('input').val(data.content.house_type.length == 1 ? data.content.house_type : data.content.house_type[0]);
                eleMemberNo.find('label.display-house-no').text(data.content.bukken_no);
                var houseTitle = self.Container.find('input[name="link_house_title"]');
                if (houseTitle.hasClass('not-edit')) {
                    houseTitle.val(data.content.title);
                }
                if ($('.hide-msg-div').length > 0) {
                    $('.hide-msg-div').css('height', $("#section-side").height());
                }
            });
            var searchType = 0, houseNo = '';
            if (typeof linkJson != 'undefined') {
                searchType = linkJson.search_type;
                houseNo = typeof linkJson.house_no != 'undefined'? linkJson.house_no: '';
            }
            if (typeof $before_form != 'undefined') {
                $($before_form).find("[name='" + self.Container.find('.search-house-method .search-method').attr('name') + "']").eq(searchType).prop('checked', true);
                $($before_form).find("[name='" + eleTitle.find('input').attr('name') + "']").val(link);
                $($before_form).find("[name='" + self.Container.find('.content-search-method .input-house-no').attr('name') + "']").val(houseNo);
            }
        },
        showInfoHouseLink: function () {
            var self = this;
            $('.house-title input[type="hidden"]').each(function() {
                if ($(this).val() != '') {
                    self.Container = $(this).closest('.link-house-module');
                    self.actionListHouse($(this).val());
                }
            })
        },
        shumokuAll: function() {
            return [
                'chintai',
                'kasi-tenpo',
                'kasi-office',
                'parking',
                'kasi-tochi',
                'kasi-other',
                'mansion',  
                'kodate',
                'uri-tochi',
                'uri-tenpo',
                'uri-office',
                'uri-other'
            ]
        },
        showModal: function() {
            if ($('.link-house-module').closest('.modal-set:not(.is-hide)').length) {
                $('.link-house-module').closest('.modal-set:not(.is-hide)').removeAttr('style');
            }
        },
        hideModal: function() {
            if ($('.link-house-module').closest('.modal-set:not(.is-hide)').length) {
                $('.link-house-module').closest('.modal-set:not(.is-hide)').hide();
            }
        },
        setOldModal: function($this) {
            if ($this.closest('.modal-set:not(.is-hide)').length) {
                this.OldModal = true;
            }else {
                this.OldModal = false;
            }
        },
        setContainer: function(container) {
            this.Container = container;
        },
        checkInputHouseNo: function(val) {
            this.clearError(this.Container);
            var numbers = /^[0-9]+$/;
            var checkLen = (val.length == 8 || val.length == 10 || val.length == 11);
            if (!checkLen || !val.match(numbers)) {
                this.Container.find('li.content-search-method .error').html('<p>有効な物件番号を入力してください。</p>');
                return false;
            }
            return true;
        },
        modalConditionLinkHouseInit: function($main) {
            var self = this;
            this.$main = $main;
            this.$estateClass = $main.find('#enabled_estate_type input[name="estate_class"]');
            this.$estateType = $main.find('#enabled_estate_type input[name="enabled_estate_type[]"]');
            this.$searchType = $main.find('input[name="search_type[]"]:not(:disabled)');
            this.$prefContainer = $main.find('#pref');
            this.$shumokuShosai = $main.find('input.shumoku_shosai');
            this.$estateClass.on('change', function() {
                self.setting.area_search_filter.search_condition = {"type":1,"count":0};
                self.setting.area_search_filter.area_1 = [];
                self.setting.area_search_filter.area_2 = {};
                self.setting.area_search_filter.area_3 = {};
                self.setting.area_search_filter.area_4 = {};
                self.setting.area_search_filter.area_5 = {};
                self.setting.area_search_filter.area_6 = {};
                self.setting.search_filter.categories = [];
                var estateClass = $(this).val();
                var currentBaseSetting = self.settingAll[estateClass];
                self.renderPref(currentBaseSetting, this);
                self.renderEstateType(currentBaseSetting);
                self.renderSearchTypeCondition(currentBaseSetting, this);
                self.$estateClass.each(function() {
                    if (!$(this).prop('checked')) {
                        $(this).closest('li').find('input[name="enabled_estate_type[]"]').prop('checked', false);
                    }
                });

                self.allowed = self.initAllowed(estateClass);
            });
            if (this.setting.estate_class) {
                this.$estateClass.filter('[value="'+this.setting.estate_class+'"]').prop('checked', true);
                var currentBaseSetting = self.settingAll[this.setting.estate_class];
                this.renderSearchTypeCondition(currentBaseSetting);
                this.renderPref(currentBaseSetting);
                this.renderEstateType(currentBaseSetting);
            } else {
                this.$estateClass.eq(0).prop('checked', true).change();
            }

            var estateTypes = this.setting.enabled_estate_type;
            for(var i = 0; i < estateTypes.length; i++) {
                this.$estateType.filter('[value="' + estateTypes[i] + '"]').prop('checked', true);
            }

            var shumokuShosai = this.setting.search_filter.categories;
            for(var i = 0; i < shumokuShosai.length; i++) {
                if (shumokuShosai[i]['category_id'] == 'shumoku') {
                    for(var j = 0; j < shumokuShosai[i]['items'].length; j++) {
                        if (shumokuShosai[i]['items'][j]['item_value'] == 1) {
                            this.$shumokuShosai.filter('[value="' + shumokuShosai[i]['items'][j]['item_id'] + '"]').prop('checked', true);
                        }
                    }
                }
            }

            switch(this.setting.owner_change) {
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
                    $(this).closest('li').find('.shumoku_shosai_box').css('left', 68);

                    var shosaiWidth = $(this).closest('div#enabled_estate_type').css('width').replace('px', '') - 100;

                    $(this).closest('li').find('.shumoku_shosai_box').css('width', shosaiWidth + 'px');
                    $(this).closest('li').find('.shumoku_shosai_box').show();

                    var estateClass = $(this).closest('li').find("input[name='enabled_estate_type[]']").attr('data-estate-class');
                    if (estateClass == 3){
                        $(this).closest('li').css('margin-bottom', 43);
                    } else {
                        $(this).closest('li').css('margin-bottom', $(this).closest('li').find('.shumoku_shosai_box').css('height'));
                    }

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
        },
        renderSearchTypeCondition: function(currentBaseSetting, element) {
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
            if (typeof element != 'undefined') {
                var typeCheck = $(element).closest('.modal-set').find('.js-method-search input[name="search_type[]"]').filter(':checked').val();
                if (typeof typeCheck == 'undefined') {
                    typeCheck = $(element).closest('.modal-set').find('.js-method-search li:visible').first().find('input').val();
                }
            }
            if (typeof typeCheck != 'undefined' && this.$searchType.filter('[value="'+typeCheck+'"]').closest('li').is(":visible")) {
                this.$searchType.filter('[value="'+typeCheck+'"]').prop('checked', true).change();
            } else {
                this.$searchType.filter('[value="'+this.setting.area_search_filter.search_condition['type']+'"]').prop('checked', true).change();
            }
        },
        renderEstateType: function (currentBaseSetting) {
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
        renderPref: function (currentBaseSetting, element) {
            var prefChecks = [];
            if (typeof element != 'undefined') {
                prefChecks = $(element).closest('.modal-set').find('#pref input[name="pref[]"]').filter(':checked').map(function() {
                    return this.value;
                }).get();
            }
            this.$prefContainer.find('div:not(.errors)').remove();

            var prefCodes = currentBaseSetting.area_search_filter.area_1;
            var prefCode;
            var prefHtml = '<div class="is-required">';
            var checked = '';
            var i,l;
            for (i=0,l=prefCodes.length;i< l;i++) {
                prefCode = prefCodes[i];
                checked = $.inArray(prefCode, prefChecks) > -1 ? ' checked="checked"' : '';
                prefHtml += ''+
                    '<label>'+
                        '<input type="checkbox" name="pref[]" value="'+prefCode+'"'+checked+'>'+
                        app.h(this.Master.prefMaster[prefCode])+
                    '</label>';
            }
            prefHtml += '</div>';
            this.$prefContainer.find('.errors').before(prefHtml);

        },
        getEstateClass: function () {
            return this.$estateClass.filter(':checked').val() || null;
        },
        getEstateType: function () {
            return this.$estateType.filter('[name="enabled_estate_type[]"]:checked').map(function() {
                return this.value;
            }).get();
        },
        updateSettingLinkHouse: function(self) {
            self.setting.estate_class = this.getEstateClass();
            self.setting.enabled_estate_type = this.getEstateType();
            self.setting.owner_change = this.$main.find('input[name="owner_change"]:checked').val();
            if(typeof self.setting['search_filter'] == 'undefined') {
                self.setting['search_filter'] = { 'categories' : Array() };
            }
            if(typeof self.setting['search_filter']['categories'] == 'undefined') {
                self.setting['search_filter']['categories'] = Array();
            }

            var shumoku_cno = -1;
            for(var cno=0; cno < self.setting['search_filter']['categories'].length; cno++) {
                if(self.setting['search_filter']['categories'][cno]['category_id'] == 'shumoku') {
                    shumoku_cno = cno;
                }
            }
            if(shumoku_cno == -1) {
                shumoku_cno = self.setting['search_filter']['categories'].length;
                self.setting['search_filter']['categories'].push({ 'category_id' : 'shumoku', 'items' : Array() });
            } else {
                self.setting['search_filter']['categories'][ shumoku_cno ]['items'] = null;
                self.setting['search_filter']['categories'][ shumoku_cno ]['items'] = Array();
            }

            //
            // setting['search_filter']['categories'][ shumoku_cno ]['items'].push({'item_id' : '27', 'item_value' : 1 });
            this.$main.find("#enabled_estate_type input[type=radio]:checked").eq(0).closest('li').find("input[type=checkbox]:checked").each(function() {
                if(typeof $(this).attr('name') === 'undefined') {
                    self.setting['search_filter']['categories'][ shumoku_cno ]['items'].push({'item_id' : $(this).val(), 'item_value' : 1 });
                }
            });
            self.setData(
                self.setting.area_search_filter.area_2 || {},
                self.setting.area_search_filter.area_3 || [],
				self.setting.area_search_filter.area_4 || {},
				self.setting.area_search_filter.area_5 || {},
				self.setting.area_search_filter.area_6 || {},
				self.setting.area_search_filter.choson_search_enabled == 1);
        },
        isJSON: function(text) {
            if ( /^\s*$/.test(text) ) return false;
            text = text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@');
            text = text.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
            text = text.replace(/(?:^|:|,)(?:\s*\[)+/g, '');
            return (/^[\],:{}\s]*$/).test(text);
        }
    };
	app.LinkHouse.instance = null;
	app.LinkHouse.getInstance = function () {
		if (!app.LinkHouse.instance) {
            app.LinkHouse.instance = new app.LinkHouse();
		}
		return app.LinkHouse.instance;
    };
    app.LinkHouse.init = function (baseSettings, Master, setting, isLite) {
		return app.LinkHouse.getInstance().init(baseSettings, Master, setting, isLite);
    };
    app.LinkHouse.clearInfoLinkHouse = function($container) {
        return app.LinkHouse.getInstance().clearInfoLinkHouse($container);
    }
    app.LinkHouse.toolTip = function(key) {
        var message = {
            display_house_title: '現在設定されている物件の建物名、間取り、所在地等が表示されます。<br>※物件種目や物件登録の内容によって表示される内容は異なります。',
        }
        var tooltip = '<a class="tooltip" href="javascript:;">' +
                        '<i class="i-s-tooltip"></i>' +
                        '<div class="tooltip-body">' +
                            message[key] +
                        '</div>' +
                    '</a>';
        return tooltip;
    }
    
})(app);