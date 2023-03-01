(function () {
	'use strict';

	var estate = app.estate = {};
	var connection_failure_msg = '通信に失敗しました。\n 画面を更新して初めから設定をやり直して下さい。';
	var Step = estate.Step = function ($step, $topicpath, titles) {
		var self = this;

		this.currentIndex = 0;
		this.locked = false;

		this.$step = $step;
		this.$steps = $step.children();
		this.contents = [];
		this.titles   = titles || {};
		this.history = [0];
        this.nextStep = true;

		this.$topicpath = $topicpath;
		if ($topicpath) {
			this.$basepath = $topicpath.find('.last');
			this.$topicpath.on('click', '[data-step-index]', function () {
				var index = parseInt($(this).attr('data-step-index'));
				// 確認画面まで遷移している場合は基本設定のリンクでリロード
				if (self.isComplete() && index === 0) {
					location.reload();
				}
				else {
					self.go(index);
				}
				return false;
			});
		}
	};

	Step.STEP_ACTIVE_CLASS = 'is-active';

	Step.prototype.lock = function (isLock) {
		this.locked = isLock;
	};
	Step.prototype.getCurrentIndex = function () {
		return this.currentIndex;
	};
	Step.prototype.isComplete = function () {
		return this.currentIndex === this.contents.length - 1;
	};
	Step.prototype.createContents = function ($element, name, init, props) {
		props = props || {};
		if (init) {
			props.init = init;
		}

		var Contents = app.inherits(StepContents, function () {
			StepContents.apply(this, arguments);
			this.init && this.init();
		}, props);

		var contents = new Contents(this, $element, name);

		this.addContents(contents);
		return contents;
	};
	Step.prototype.addContents = function (contents) {
		this.contents.push(contents);
	};
	Step.prototype.next = function (num) {
		this.go(this.currentIndex + (num || 1));
	};
	Step.prototype.prev = function (num) {
		this.go(this.currentIndex - (num || 1));
	};
	Step.prototype.go = function (index) {
		var newIndex;

		if (this.locked) {
			return;
		}

		newIndex = Math.min(this.contents.length - 1, Math.max(0, index));
		if (newIndex === this.currentIndex) {
			return;
		}

		if (newIndex < this.currentIndex) {
			for (var i=this.history.length;i--;) {
				if (this.history[i] < newIndex) {
					break;
				}
				this.history.pop();
			}
		}

		this.$steps.hide();
		this.contents[this.currentIndex].hide();

		this.currentIndex = newIndex;
		this.history.push(newIndex);
		this.renderTopicpath();

		this.$steps.eq(this.currentIndex).show();
		this.contents[this.currentIndex].show();

		$('.js-h1-title').text((this.titles[newIndex]) || (newIndex === 0 ? '基本設定' : this.contents[this.currentIndex].name));

		var contentsTop = $('#contents').offset().top;
		if ($(window).scrollTop() > contentsTop) {
			$('html,body').animate({scrollTop: contentsTop + 'px'});
		}
	};
	Step.prototype.renderTopicpath = function () {
		if (!this.$basepath) {
			return;
		}
		this.$basepath.nextAll().remove();
		var baseHtml = this.$basepath.text();
		if (this.history.length > 1) {
			baseHtml = '<a data-step-index="0" href="javascript:;">' + baseHtml + '</a>';
		}
		this.$basepath.html(baseHtml);

		var index;
		var pathHtmls = '';
		var pathHtml;
		for (var i=1,l=this.history.length;i<l;i++) {
			index = this.history[i];
			// 現在のインデックスが完了画面の場合、最後のリンク以外表示しない
			if (this.isComplete() && index !== this.currentIndex) {
				continue;
			}
			pathHtml = this.contents[index].name;
			if (i < l - 1) {
				pathHtml = '<li><a data-step-index="'+index+'" href="javascript:;">' + pathHtml + '</a></li>';
			}
			else {
				pathHtml = '<li>' + pathHtml + '</li>';
			}
			pathHtmls += pathHtml;
		}
		this.$basepath.after(pathHtmls);
		this.$topicpath.find('.last').removeClass('last');
		this.$topicpath.find('li:last').addClass('last');
	};
    Step.prototype.setNextStep = function(next) {
        this.nextStep = next;
    }

	var StepContents = estate.StepContents = function (step, $element, name) {
		this.step = step;
		this.$element = $element;
		this.name = name;
        this.templates = {};
        
        this.page = null;
        this.sort = null;

		var self = this;
		this.$element.on('click', '.js-next-step,.js-prev-step', function () {
			if ($(this).hasClass('js-next-step')) {
				if (false === self.beforeNext()) {
					return false;
				}
				self.next();
			}
			else {
				if (false === self.beforePrev()) {
					return false;
				}
				self.prev();
			}
			return false;
        });
        this.$element.on('click', '.sort-table a, .paging li:not(.is-active) a', function (e) {
            e.preventDefault();
            if ($(e.target).closest('.sort-table').length > 0) {
                self.sort = $(this).parent().data('value');
            }
            if ($(e.target).closest('.paging').length > 0) {
                self.page = $(this).data('page');
            }
            self.actionListHouse();
        });
		$(window).on('unload', function _onUnload() {
			$(window).off('unload', _onUnload);
			$element.off();
			self.onUnload();
		});
	};
	StepContents.prototype.loadTemplate = function (templateIds) {
		var id;
		for (var i=0,l=templateIds.length;i< l;i++) {
			id = templateIds[i];
			this.templates[ id ] = $('#template-'+id).html();
		}
	};
	StepContents.prototype.onUnload = function () {

	};
	StepContents.prototype.beforePrev = function () {
		return true;
	};
	StepContents.prototype.beforeNext = function () {
		return true;
	};
	StepContents.prototype.prev = function () {
		this.step.prev();
	};
	StepContents.prototype.next = function () {
		this.step.next();
	};
	StepContents.prototype.show = function () {
		this.$element.show();
	};
	StepContents.prototype.hide = function () {
		this.$element.hide();
    };
    StepContents.prototype.actionListHouse = function () {
        this.$sectionList = this.$element.find('.section-house-list');
        var self = this;
        if (this.houses_id.length == 0) {
            self.$sectionList.addClass('hide');
            return;
        }
        // loading on
        var closer = app.loading();
        var params = {estateClass: this.setting.enabled_estate_type, page: this.page, sort: this.sort, houses_id: this.houses_id, setting: this.setting};
        if (self.step.currentIndex == 6) {
            params.isConfirm = true;
        }
        EstateMaster.getHouseAll(params).done(function (data) {
            // loading off
            closer();
            self.$sectionList.removeClass('hide').find('div.house-list').replaceWith(data.content);
            if (self.step.currentIndex == 6) {
                self.$sectionList.find('div.house-list input').prop('disabled', true).prop('checked', true);
            }
            if (self.step.currentIndex == 2) {
                self.checkedInputHouse();
            }
        });
    };
    StepContents.prototype.initCheckAll = function () {
        var self = this;
        this.$element.on('change', '.js-estate-select-group-check input', function () {
            var $this = $(this);
            var input = $this.closest('.js-estate-select-group').find('.js-estate-select-group-container input:not(:disabled)');
            input.prop('checked', $this.prop('checked'));
            if ($this.closest('.js-estate-select-group').hasClass('house-list')) {
                input.each(function() {
                    if($(this).attr('name') != 'from-cms') {
                        if ($this.prop('checked')) {
                            self.houses_id = self.houses_id.concat([$(this).val()].filter(function(val) {
                                return self.houses_id.indexOf(val) === -1;
                            }));
                            var index = self.housesRemove.indexOf($(this).val());
                            self.housesRemove.splice(index, 1);
                        } else {
                            self.housesRemove = self.housesRemove.concat([$(this).val()].filter(function(val) {
                                return self.housesRemove.indexOf(val) === -1;
                            }));
                        }
                    }
                });
            }
        });
        this.$element.on('change', '.js-estate-select-group-container input', function () {
            var $group = $(this).closest('.js-estate-select-group');
            var $all = $group.find('.js-estate-select-group-check input');
            $all.prop('checked', !$group.find('.js-estate-select-group-container input:not(:disabled):not(:checked)').length);
            if ($group.hasClass('house-list')) {
                if ($(this).prop('checked')) {
                    self.houses_id = self.houses_id.concat([$(this).val()].filter(function(val) {
                        return self.houses_id.indexOf(val) === -1;
                    }));
                    var index = self.housesRemove.indexOf($(this).val());
                    self.housesRemove.splice(index, 1);
                } else {
                    self.housesRemove = self.housesRemove.concat([$(this).val()].filter(function(val) {
                        return self.housesRemove.indexOf(val) === -1;
                    }));
                }
            }
        });
    };
    var StepContentsMethod = estate.StepContentsMethod = app.inherits(StepContents, function (step, $element, Master, name) {
        StepContents.call(this, step, $element, name || '物件の設定');
        this.Master = Master;
        this.basic = this.getElement();
        this.init();
    },
    {
        init: function () {
            var self = this;
        },
        getSetting: function () {
            throw new Error('override!');
        },
        clearError: function () {
            this.editShikugun.clearError();
            this.$errors.empty();
        },
        beforeNext: function () {
        },
        hasSearchType: function (type) {
            return $.inArray(''+type, this.getSetting().area_search_filter.search_type) > -1;
        },
        needShikugunSetting: function () {
            return this.hasSearchType(this.Master.searchTypeConst.TYPE_AREA) || this.hasSearchType(this.Master.searchTypeConst.TYPE_SPATIAL);
        },
        needEnsenSetting: function() {
            return this.hasSearchType(this.Master.searchTypeConst.TYPE_ENSEN);
        }
    });
    var StepContentsInvidial = estate.StepContentsInvidial = app.inherits(StepContents, function (step, $element, Master, name) {
		StepContents.call(this, step, $element, name || '物件の個別設定');
        this.Master = Master;
        this.basic = this.getElement();
		this.init();
	},
	{
		init: function () {
            var self = this;
            var isIMEOn = true;
            this.setting = this.getSetting();
            this.houses_id = [];
            this.housesRemove = [];
            if (this.setting.houses_id.length > 0) {
                this.houses_id = this.setting.houses_id;
            }
            this.$element.on('click', '.btn-all-house a', function() {
                new estate.HouseListModal(self, null, 2);
            });
            this.$element.on('click', '.btn-search-house:not(.is-disable)', function() {
                var error = true;
                var houses_no = [];
                var listError = [];
                self.$element.find('.house-no-input input').each(function () {
                    var val = $(this).val();
                    var checkHouse = self.checkInputHouseNo(val);
                    if (val.length > 0) {
                        if (checkHouse == true) {
                            houses_no.push($(this).val());
                            $(this).removeClass('is-error');
                        } else {
                            $(this).addClass('is-error');
                            error = checkHouse;
                            if (listError.indexOf(checkHouse) == -1) {
                                listError.push(checkHouse);
                            }
                        }
                    } else {
                        $(this).removeClass('is-error');
                    }
                    
                });
                if (error == true) {
                    new estate.HouseListModal(self, houses_no, 1);
                    listError = [];
                } else {
                    if (listError.length == 1) {
                        switch(error) {
                          case 2:
                            self.showErrorHouseNo('物件番号は半角数字で入力してください。');
                            break;
                          case 3:
                            self.showErrorHouseNo('物件番号は8桁、10桁、11桁のいずれかで入力してください。');
                            break;
                          default:
                            self.showErrorHouseNo('物件番号は半角数字で入力してください。物件番号は8桁、10桁、11桁のいずれかで入力してください。');
                        }
                    } else {
                        self.showErrorHouseNo('物件番号は半角数字で入力してください。物件番号は8桁、10桁、11桁のいずれかで入力してください。');
                    }
                }
            });
            this.$element.on('click', '.btn-t-result-condition', function() {
                new estate.HouseListModal(self, null, 3);
            });
            this.$element.on('click', '.btn-search-condition', function() {
                new estate.conditionHouseListModal(self);
            });
            this.$element.on('compositionupdate', 'input[name="house_no[]"]', function(ev) {
                isIMEOn = false;
                var ua = navigator.userAgent;
                var isIE = ua.indexOf("MSIE ") > -1 || ua.indexOf("Trident/") > -1;
                var isFirefox = ua.toLowerCase().indexOf('firefox') > -1;
                var isSafari = !!ua.match(/Version\/[\d\.]+.*Safari/);
                if (!(isIE || isFirefox || isSafari)) {
                    isIMEOn = true;
                }
            })
            this.$element.on('keyup input', 'input[name="house_no[]"]', function(e) {
                var empty = true;
                self.$element.find('.house-no-input input').each(function () {
                    if ($(this).val().length > 0) {
                        empty = false;
                        return;
                    }
                });
                if (empty) {
                    self.$element.find('.btn-search-house').addClass('is-disable');
                    self.clearErrorHouseNo();
                    self.$element.find('.house-no-input input').removeClass('is-error');
                } else {
                    self.$element.find('.btn-search-house').removeClass('is-disable');
                }
                if (e.keyCode == 13) {
                    if (isIMEOn) {
                        self.$element.find('.btn-search-house:not(.is-disable)').trigger('click');
                    } else {
                        isIMEOn = true;
                    }
                }
            })
            this.$errors = this.$element.find('div.errors');
            this.$btnResultCondition = this.$element.find('.btn-t-result-condition');
            this.initCheckAll();
        },
        initAllowed: function (allowed) {
            this.allowed = {};
            this.denied  = {};
    
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
        },
        setHousesNo: function (houses_id) {
            this.houses_id = houses_id;
        },
		getSetting: function () {
			throw new Error('override!');
        },
        setSetting: function() {
            throw new Error('override!');
        },
        setCurrentSetting: function(setting) {
            this.setting = setting;
        },
		getAllowed: function () {
			return null;
		},
		show: function () {    
            this.allowed = {};
            this.denied  = {};
            this.initAllowed(this.getAllowed());
            this.setting = this.getSetting();
            this.houses_id = this.setting.houses_id.concat(this.housesRemove);
            if (this.step.nextStep) {
                this.actionListHouse();
            }
            this.renderBtnResultCondition();
			app.estate.StepContents.prototype.show.call(this);
        },
		clearError: function () {
			this.$errors.empty();
        },
        showErrorHouseNo: function(mesg) {
            this.$element.find('.search-house-container .errors p').show().html(mesg);
        },
        clearErrorHouseNo: function() {
            this.$element.find('.search-house-container .errors p').hide();
        },
		beforeNext: function () {
            var self = this;
            if (this.houses_id.length > 0 && this.$element.find('div.section.house-list').children().length > 0) {
                this.setting.houses_id = this.houses_id.filter(function(value) {
                    if (!($.inArray(value, self.housesRemove) > -1)) {
                        return value;
                    }
                });
                if(this.setting.houses_id.length == 0) {
                    app.modal.alert('', '物件が選択されていません。');
                    return false;
                }
                this.setSetting(this.setting);
                return true;
            }
            this.$errors.html('条件が設定されていません。');
            return false;
		},
		next: function () {
            this.step.setNextStep(true);
			this.step.next(4);
        },
        renderBtnResultCondition: function() {
            var self = this;
            if(this.setting.area_search_filter.search_condition['type'] == 0){
                return;
            }
            if (this.setting.area_search_filter.search_condition['count'] > 0) {
                self.$btnResultCondition.addClass('show');
                return false;
            }
        },
        checkInputHouseNo: function(val) {
            var numbers = /^[0-9]+$/;
            var checkLen = (val.length == 8 || val.length == 10 || val.length == 11);
            if (checkLen) {
                if (val.match(numbers)) {
                    return true;
                } else {
                    return 2;
                }
            } else {
                if (val.match(numbers)) {
                    return 3;
                } else {
                    return 4;
                }
            }
        },
        prev: function () {
            this.step.setNextStep(false);
            this.getSetting().houses_id = this.houses_id;
            this.step.prev(2);
        },
        checkedInputHouse: function () {
            var self = this;
            if (self.housesRemove.length == 0) {
                this.$element.find('.js-estate-select-group-check input').prop('checked', true).change();
                return
            }
            var check = true;
            this.$element.find('input[name="houses_id[]"]').each(function() {
                var val = $(this).val();
                if ($.inArray(val, self.housesRemove) > -1) {
                    check = false;
                } else {
                    $(this).prop('checked', true);
                }
            });
            if (check) {
                this.$element.find('.js-estate-select-group-check input').prop('checked', true);
            }
        }
	});

	/**
	 * 外部スコープの変数にアクセスさせるために、
	 * getSetting、getAllowedをoverrideする
	 */
	var StepContentsShikugun = estate.StepContentsShikugun = app.inherits(StepContents, function (step, $element, Master, name) {
		StepContents.call(this, step, $element, name || '市区郡・町名選択');
		this.Master = Master;
		this.init();
	},
	{
		init: function () {
			var self = this;

			this.$errors = this.$element.find('.errors');
			this.editShikugun = new app.estate.EditShikugunView(this.Master, function (shikuguns, chosons, choazas) {
				self.getSetting().area_search_filter.area_2 = shikuguns;
				self.getSetting().area_search_filter.area_5 = chosons;
				self.getSetting().area_search_filter.area_6 = choazas;
				self.$errors.empty();
			});
			this.editShikugun.setSelectItemBtnLabel(this.name);
			this.$element.find('.js-table-container').append(this.editShikugun.$element);
		},
		getSetting: function () {
			throw new Error('override!');
		},
		getAllowed: function () {
			return null;
		},
		show: function () {
			var setting = this.getSetting();

			if (this.bothAreaSetting() && $('.bothAreaSetting')) {
				$('.bothAreaSetting').show();
			} else {
				$('.bothAreaSetting').hide();
			}

			this.clearError();
			this.editShikugun.initAllowed(this.getAllowed());
			this.editShikugun.setData(
				setting.area_search_filter.area_1 || [],
				setting.area_search_filter.area_2 || {},
				setting.area_search_filter.area_5 || {},
				setting.area_search_filter.area_6 || {},
				setting.area_search_filter.choson_search_enabled == 1);

			this.editShikugun.render();

			app.estate.StepContents.prototype.show.call(this);
		},
		clearError: function () {
			this.editShikugun.clearError();
			this.$errors.empty();
		},
		beforeNext: function () {
			var setting = this.getSetting();
			var data    = this.editShikugun.getData();
			var empties = [];
			var shikuguns = {};
			var chosons = {};
			var choazas = {};

			this.clearError();
			var prefCode;
			for (var i=0,l=setting.area_search_filter.area_1.length;i< l;i++) {
				prefCode = setting.area_search_filter.area_1[i];
				if (!data.shikuguns[ prefCode ] || !data.shikuguns[ prefCode ].length) {
					empties.push(prefCode);
				}
				else {
					shikuguns[prefCode] = data.shikuguns[prefCode];
					if (data.chosons[prefCode]) {
						var _chosons = {};
						var hasChoson = false;
						$.each(shikuguns[prefCode], function (i, shikugunCd) {
							if (data.chosons[prefCode][shikugunCd] && data.chosons[prefCode][shikugunCd].length) {
								hasChoson = true;
								_chosons[shikugunCd] = data.chosons[prefCode][shikugunCd];
							}
						});
						if (hasChoson) {
							chosons[prefCode] = _chosons;
						}
					}
					// 町村編集画面の完了で、町字選択時はその上位も選択するようにしている

                    if(data.choazas[prefCode] !== undefined) {

                        if(Object.keys(data.choazas[prefCode]).length) {
                            for(var shikugun_cd in data.choazas[prefCode] ) {
                                if(Object.keys(data.choazas[prefCode][shikugun_cd]).length > 0) {
                                    if(choazas[prefCode] === undefined) {
                                        choazas[prefCode] = {};
                                    }
                                    choazas[prefCode][shikugun_cd] = data.choazas[prefCode][shikugun_cd];
                                }
                            } 
                        }
                    }
					// choazas[prefCode] = data.choazas[prefCode];
				}
			}

			// パラメータ数カウント
			// 町村選択の場合は町村数追加、町村未選択は市区郡数追加
			var paramCount = 0;
			for (var _pref in shikuguns) {
				$.each(shikuguns[_pref], function (i, _shikugun) {
					if (chosons[_pref] && chosons[_pref][_shikugun]) {
						for(var cno = 0; cno < chosons[_pref][_shikugun].length; cno++) {
							var choson_cd = chosons[_pref][_shikugun][cno];

							if(choazas[_pref] !== undefined && choazas[_pref][_shikugun] !== undefined && choazas[_pref][_shikugun][choson_cd] !== undefined) {
								if(choazas[_pref][_shikugun][choson_cd].length == 0) {
									paramCount += 1;
								} else {
									paramCount += choazas[_pref][_shikugun][choson_cd].length;
								}
							} else {
								paramCount += 1;
							}
						}
					} else {
						paramCount += 1;
					}
				});
				console.log("_pref=" + _pref + "/paramCount=" + paramCount);
			}

			if (empties.length) {
				app.modal.alert('', '未選択の都道府県があります。');
				this.editShikugun.setError(empties);
				return false;
			}
			else if (paramCount > 5000) {
				app.modal.alert('', '設定している条件数が多いため、登録ができません。\n条件数を減らして登録してください。');
				return false;
			}
			else {
				setting.area_search_filter.area_2 = shikuguns;
				setting.area_search_filter.area_5 = chosons;
				setting.area_search_filter.area_6 = choazas;
				return true;
			}
		},
		next: function () {
			this.step.next(this.needEnsenSetting() ? 1 : 2);
		},
		hasSearchType: function (type) {
			return $.inArray(''+type, this.getSetting().area_search_filter.search_type) > -1;
		},
		needShikugunSetting: function () {
			return this.hasSearchType(this.Master.searchTypeConst.TYPE_AREA) || this.hasSearchType(this.Master.searchTypeConst.TYPE_SPATIAL);
		},
		needEnsenSetting: function() {
			return this.hasSearchType(this.Master.searchTypeConst.TYPE_ENSEN);
		},
		bothAreaSetting: function() {
			return this.hasSearchType(this.Master.searchTypeConst.TYPE_AREA) && this.hasSearchType(this.Master.searchTypeConst.TYPE_SPATIAL);
		},
        prev: function () {
            this.step.prev(2);
        }
	});

	/**
	 * 外部スコープの変数にアクセスさせるために、
	 * getSetting、getAllowedをoverrideする
	 */
	var StepContentsEnsen = estate.StepContentsEnsen = app.inherits(StepContents, function (step, $element, Master, name) {
		StepContents.call(this, step, $element, name || '沿線・駅選択');
		this.Master = Master;
		this.init();
	},
	{
		init: function () {
			var self = this;
			this.$errors = this.$element.find('.errors');
			this.editEnsen = new app.estate.EditEnsenView(this.Master, function (ensens, ekis) {
				var setting = self.getSetting();
				setting.area_search_filter.area_3 = ensens;
				setting.area_search_filter.area_4 = ekis;
				self.$errors.empty();
			});
			this.$element.find('.js-table-container').append(this.editEnsen.$element);
		},
		getSetting: function () {
			throw new Error('override!');
		},
		getAllowed: function () {
			return null;
		},
		show: function () {
			var setting = this.getSetting();

			this.clearError();
			this.editEnsen.initAllowed(this.getAllowed());
			this.editEnsen.setData(
				setting.area_search_filter.area_1 || [],
				setting.area_search_filter.area_3 || {},
				setting.area_search_filter.area_4 || {});

			this.editEnsen.render();

			StepContents.prototype.show.call(this);
		},
		clearError: function () {
			this.editEnsen.clearError();
			this.$errors.empty();
		},
		beforeNext: function () {
			var setting = this.getSetting();

			var empties = [];

			var data = this.editEnsen.getData();
			var updatedEnsen = {};
			var updatedEki   = {};

			this.clearError();
			var prefCode;
			for (var i=0,l=setting.area_search_filter.area_1.length;i< l;i++) {
				prefCode = setting.area_search_filter.area_1[i];
				if (
					!data.ensens[ prefCode ] ||
					!data.ensens[ prefCode ].length ||
					!data.ekis[ prefCode ] ||
					!data.ekis[ prefCode ].length
				) {
					empties.push(prefCode);
				}
				else {
					updatedEnsen[prefCode] = data.ensens[ prefCode ];
					updatedEki[prefCode] = data.ekis[ prefCode ];
				}
			}
			var paramCount = 0;
            // 都道府県ごとに沿線数を加算
            for (var _pref in updatedEnsen) {
                paramCount += updatedEnsen[ _pref ].length;
            }
            // 都道府県ごとに駅数を加算
            for (var _pref in updatedEki) {
                paramCount += updatedEki[ _pref ].length;
            }
            console.log("paramCount=" + paramCount);

			if (empties.length) {
				this.editEnsen.setError(empties);
				app.modal.alert('', '未選択の沿線・駅があります。');
				return false;
			}
			else if (paramCount > 5000) {
		app.modal.alert('', '設定している条件数が多いため、登録ができません。\n条件数を減らして登録してください。');
		return false;
			}
			else {
				setting.area_search_filter.area_3 = updatedEnsen;
				setting.area_search_filter.area_4 = updatedEki;
				return true;
			}
		},
		prev: function () {
			this.step.prev(this.needShikugunSetting() ? 1 : 3);
		},
		hasSearchType: function (type) {
			return $.inArray(''+type, this.getSetting().area_search_filter.search_type) > -1;
		},
		needShikugunSetting: function () {
			return this.hasSearchType(this.Master.searchTypeConst.TYPE_AREA) || this.hasSearchType(this.Master.searchTypeConst.TYPE_SPATIAL);
		},
		needEnsenSetting: function() {
			return this.hasSearchType(this.Master.searchTypeConst.TYPE_ENSEN);
		}
	});

	/**
	 * 外部スコープの変数にアクセスさせるために、
	 * getSetting、getAllowedをoverrideする
	 */
	var StepContentsSearchFilter = estate.StepContentsSearchFilter = app.inherits(StepContents, function (step, $element, Master, name) {
		StepContents.call(this, step, $element, name || '絞り込み条件');
		this.Master = Master;
		this.init();

	}, {
		init: function () {
			this.$table = $('<table class="tb-basic ad-terms"></table>');
			this.$element.find('.js-table-container').append(this.$table);

			this.deferred = null;
		},

		getSetting: function () {
			throw new Error('override!');
		},
		getAllowed: function () {
			return null;
		},

		show: function () {
			var setting = this.getSetting();

			var self = this;
			if (!this.beforeRerender()) {
				app.estate.StepContents.prototype.show.call(this);
				return;
			}

			var def = this.deferred = this.getSearchFilterMaster();  
			def.done(function (data) {
				if (def !== self.deferred) {
					return;
				}
				if (!data) {
					return;
				}
				self.render(data);
			});
			app.estate.StepContents.prototype.show.call(this);
		},

		beforeRerender: function () {
			throw Error('override!');
		},

		getSearchFilterMaster: function () {
			throw Error('override!');
		},

		render: function (data) {
			var tableHtml = '';
			var tdClassHtml = '';
			var i,il, category, $tr;
			for (i=0,il=data.categories.length;i< il;i++) {
				category = data.categories[i];
				tdClassHtml = '';
				if (category.category_id === 'madori') {
					tdClassHtml = ' class="floor-plans"';
				} else if (category.category_id === 'kitchen') {
					tdClassHtml = ' class="list-more"';
				}
                if (category.category_id === 'reform_renovation') {
                    category.label = 'リフォーム・リノベーション済/予定含む';
                }
				if (category.category_id === 'shumoku') {
					continue;
				}

				switch (category.category_id){
					case 'madori':
						tdClassHtml = ' class="floor-plans"';
						break;
					case 'kitchen':
					case 'bath_toilet':
					case 'reidanbo':
					case 'shuno':
					case 'tv_tsusin':
					case 'security':
					case 'ichi':
					case 'joken':
					case 'kyouyu_shisetsu':
					case 'setsubi_kinou':
					case 'tokucho':
					case 'koho_kozo':
					case 'other':
						tdClassHtml = ' class="list-more"';
						break;
					case 'shumoku':
						tdClassHtml = ' class="list-more" style="display:block"';
						break;
				}

				tableHtml += '<tr data-category-id="'+category.category_id+'">';
				tableHtml += '<th>'+category.label+'</th>';
				tableHtml += '<td'+tdClassHtml+'>';

				if (category.description) {
					tableHtml += '<p class="tx-annotation">'+category.description+'</p>';
				}

				if (category.category_id === 'kakaku') {
					tableHtml += this.renderKakaku(category);
				}
				else if (category.category_id === 'rimawari') {
					tableHtml += this.renderRimawari(category);
				}
				else if (category.category_id === 'menseki') {
					tableHtml += this.renderMenseki(category);
				}
				else if (category.category_id === 'reformable_parts') {
					tableHtml += this.renderReformableParts(category);
				}
				else if (category.category_id === 'chikunensu') {
					tableHtml += this.renderChikunensu(category);
				}
				else {
					tableHtml += this.renderCategory(category);
				}

				tableHtml += '</td>';
				tableHtml += '</tr>';
			}
			this.$table.html(tableHtml);

			// 価格上限の初期値を設定
			var $kakaku2 = this.$table.find('.js-search-filter-item-kakaku-2');
			$kakaku2[0].selectedIndex = $kakaku2.find('option').length - 1;

			// 面積・土地面積上限の初期値を設定
			var $menseki3 = this.$table.find('.js-search-filter-item-menseki-3');
			if($menseki3.length > 0) {
				$menseki3[0].selectedIndex = $menseki3.find('option').length - 1;
			}
			var $menseki4 = this.$table.find('.js-search-filter-item-menseki-4');
			if($menseki4.length > 0) {
				$menseki4[0].selectedIndex = $menseki4.find('option').length - 1;
			}

			// 利回り上限の初期値を設定
			var $rimawari2 = this.$table.find('.js-search-filter-item-rimawari-2');
			if($rimawari2.length > 0) {
                $rimawari2.val('0');
			}

			// 築年数上限の初期値を設定
			var $chikunensuTo = $("#chikunensuTo");
			if($chikunensuTo.length > 0) {
				$chikunensuTo.val('0');
			}

			//契約条件初期値設定
			var $keiyaku_joken = this.$table.find('.js-search-filter-item-keiyaku_joken-1');
			if ($keiyaku_joken[0]) {
				$keiyaku_joken.val(0);
			}

			// ラジオの初期値を設定
			this.$table.find('input:radio[value="0"]').prop('checked', true);
			this.restore(data);
		},

		restore: function (data) {
			var setting = this.getSetting();
			var i,il, category, categoryMaster;
			var j,jl, item, itemMaster, $item;
			var k,kl, val;
			for (i=0,il=setting.search_filter.categories.length;i< il;i++) {
				category = setting.search_filter.categories[i];
				categoryMaster = data.categoryMap[category.category_id];
				if (!categoryMaster) {
					continue;
				}

				for (j=0,jl=category.items.length;j< jl;j++) {
					item = category.items[j];
					itemMaster = categoryMaster.itemMap[item.item_id];
					if (!itemMaster) {
						continue;
					}

					$item = this.$table.find('.js-search-filter-item-'+category.category_id+'-'+item.item_id);
					if (itemMaster.type === 'list') {
						if (itemMaster.elementType === 'radio') {
							$item.filter('[value="'+(item.item_value || 0)+'"]').prop('checked', true);
						}
						else {
							$item.val(item.item_value);
						}
					}
					else if (itemMaster.type === 'multi') {
						for (k=0,kl=item.item_value.length;k< kl;k++) {
							val = item.item_value[k];
							try {
								$item.filter('[value="'+val+'"]').prop('checked', true).trigger("change", [true]);;
							}
							catch (err) {}
						}
					}
					else if (itemMaster.type === 'radio') {
						try {
							$item.filter('[value="'+(item.item_value || 0)+'"]').prop('checked', true);
						}
						catch (err) {}
					}
					else {
						$item.prop('checked', true);
					}
				}
			}
			// 築年数上限の初期値を設定
			var $chikunensuTo = $("#chikunensuTo");
			if($chikunensuTo.length > 0) {
				$chikunensuTo.closest('.chikunensu_block').find("input[name='chikunensu_1[]']").each(function() {
					if($(this).parent().text().match(/\d{1,2}/)) {
						if($(this).prop('checked')) {
							$chikunensuTo.val($(this).val());
						}
						
					}
				});
			}
		},

		renderKakaku: function (category) {
			var kakakuHtml = '';
			kakakuHtml += this.renderListItem(category, category.items[0]);
			kakakuHtml += ' ～ ';
			kakakuHtml += this.renderListItem(category, category.items[1]);

			kakakuHtml += '<br>';

			for (var i=2,l=category.items.length;i< l;i++) {
				kakakuHtml += this.renderFlagItem(category, category.items[i]);
				kakakuHtml += '<br>';
			}

			return kakakuHtml;
		},

        renderRimawari: function (category) {
			return this.renderKakaku(category);
		},

		renderMenseki: function (category) {
			var mensekiHtml = '<dl class="dl-inlineb">';

			var mensekiHtml1 = '';      //専有面積
			var mensekiHtml2 = '';      //土地

			if (category.items[1]) {
				for (var i=0,l=category.items.length;i< l;i++) {
					var itemId = category.items[i].item_id;

					if(itemId == "1") {
						mensekiHtml1 += '<dt>面積<span class="tx-annotation">（建物面積・専有面積・使用部分面積）</span></dt>';
						mensekiHtml1 += '<dd>' + this.renderItem(category, category.items[i]) + '</dd>';
					} else if(itemId == "3") {
						mensekiHtml1 += '<dd>&nbsp;～&nbsp;' + this.renderItem(category, category.items[i]) + '</dd>';
					} else if(itemId == "2") {
						mensekiHtml2 += '<dt>' + category.items[i].label + '　</dt>';
						mensekiHtml2 += '<dd>' + this.renderItem(category, category.items[i]) + '</dd>';
					} else {
						mensekiHtml2 += '<dd>&nbsp;～&nbsp;' + this.renderItem(category, category.items[i]) + '</dd>';
					}
				}

				mensekiHtml += mensekiHtml1;
				if(mensekiHtml1 != "" && mensekiHtml2 != "") {
					mensekiHtml += "　/　";
				}
				mensekiHtml += mensekiHtml2;

				mensekiHtml += '</dl>';
			} else {
				mensekiHtml += this.renderItem(category, category.items[0]);
			}
			return mensekiHtml;
		},

		renderTesuryo: function (category) {
			var category2 = JSON.parse(JSON.stringify(category));

			if((category2.items.length) == 1) {
				var tesuryoHtml = this.renderCategory(category);
				tesuryoHtml = tesuryoHtml.replace(/（\S+）/, '');
				return tesuryoHtml;
			}

			var regexp = /（(\S+)）$/;
            for(var i=0; i<category2.items.length;i++) {
                var tmpLabel = category2.items[i].label;
                var match = regexp.test(tmpLabel);
                category2.items[i].label = RegExp.$1;
            }

            var tesuryoHtml = this.renderCategory(category2);
            tesuryoHtml = tesuryoHtml.replace(/checkbox/g, 'radio');
            tesuryoHtml = '<div class="tesuryo_box"><label><input type="checkbox" name="tesuryo_use"/>手数料ありのみ</label>' + ' ( ' + tesuryoHtml + ' )</div>';

            var classNo = $(".main-contents h1").attr('estateclass');
            var elem = $(tesuryoHtml);
            $(elem).find("input:radio").attr('disabled', 'disabled');
            if(classNo == 1 || classNo == 2) {  // 賃貸の時は分かれを非活性するだけ
                $(elem).find("label").eq(1).addClass('is-disable');
            }
            tesuryoHtml = jQuery('<div>').append(elem.clone(true)).html();

            return tesuryoHtml;
		},

		renderCategory: function (category) {
			var categoryHtml = '';
			for (var i=0,l=category.items.length;i< l;i++) {
				categoryHtml += this.renderItem(category, category.items[i]);
			}
			return categoryHtml;
		},

		renderReformableParts: function (category) {
			// リフォーム箇所選択有無チェック
			var setting = this.getSetting();
			var selectFlg = false;
			for(var i=0; i<setting.search_filter.categories.length;i++) {
 				if(setting.search_filter.categories[i].category_id == 'reformable_parts') {
					selectFlg = true;
				}
			}

			// フォーム書き出し
			var reformablePartsHtml = '';

			reformablePartsHtml += '<div class="reformable_parts_block">';

			// 指定有無のradio
			if(selectFlg) {
				reformablePartsHtml += '<input type="radio" name="rp_use" value="1">指定なし' + "　　";
				reformablePartsHtml += '<input type="radio" name="rp_use" value="2" checked>リフォーム可能箇所' + "　　";
				reformablePartsHtml += '<a class="rp_detail_display" style="cursor: pointer;">詳細な設定を選ぶ</a>';
			} else {
				reformablePartsHtml += '<input type="radio" name="rp_use" value="1" checked>指定なし' + "　　";
				reformablePartsHtml += '<input type="radio" name="rp_use" value="2">リフォーム可能箇所' + "　　";
				reformablePartsHtml += '<a class="rp_detail_display is-disable">詳細な設定を選ぶ</a>';
			}

			// 指定箇所詳細:Start
			reformablePartsHtml += '<div class="rp_detail_block" style="display:none;">';

			for (var i=0,l=category.items.length;i< l;i++) {
				reformablePartsHtml += '<div class="rp_cat_block">';
				// サマリのcheckbox作成
				var rpCb = $('<input>').attr({ 'type':'checkbox', 'class':'rp_cb_summary' });
				reformablePartsHtml += '<b>' +  $(rpCb).prop('outerHTML') + category.items[i]['label'] + '</b>';
				reformablePartsHtml += '<br/>';
				if(category.items[i].options.length == 1) {
					reformablePartsHtml += '<div style="display:none;">';
					reformablePartsHtml += this.renderItem(category, category.items[i]);
					reformablePartsHtml += '</div>';
				} else {
					reformablePartsHtml += this.renderItem(category, category.items[i]);
				}
				reformablePartsHtml += '</div>';
			}

			reformablePartsHtml += '</div>';
			// 指定箇所詳細:End

			reformablePartsHtml += '</div>';
			return reformablePartsHtml;
		},

		renderChikunensu: function (category) {

			var chikunensuHtml = '';
			var chikunensuHtmlLast = '';
			var chikunensuLabels = '';

			chikunensuHtml += '<div class="chikunensu_block">';
			for (var i=0,l=category.items.length;i< l;i++) {
				var item_id = category.items[i].item_id;
				switch(item_id) {
					case 1:
						chikunensuLabels = this.renderItem(category, category.items[i]);
						$('<div>').append($(chikunensuLabels)).find('label').each(function() {
							if($(this).find('input').eq(0).val() == "0") {
								$(this).css('display', 'none');
							} else if($(this).text().match(/\d{1,2}/)) {
								$(this).css('display', 'none');
							}
							if($(this).text() == "新築を除く") {
								chikunensuHtmlLast += jQuery('<div>').append($(this)).html();
							} else {
								chikunensuHtml += jQuery('<div>').append($(this)).html();
							}
						});
						break;
					case 2:
						chikunensuHtml += '<div class="chikunensu_sel">';
						chikunensuHtml += this.renderItem(category, category.items[i]);
						break;

				}
			}
			chikunensuHtml += ' ～ ';

			var chikunensuTo = $('<select>').attr({
				id: 'chikunensuTo',
				class: "select-inlineb"
			});
			$('<div>').append($(chikunensuLabels)).find('label').each(function() {
				var str = $(this).text();
				var val = $(this).find('input').eq(0).val();
				if(str.match(/\d{1,2}/)) {
					$(chikunensuTo).append($('<option>').attr({value: val}).text(str));
				}
			});
			$(chikunensuTo).append($('<option>').attr({value:"0"}).text('上限なし'));

			chikunensuHtml += jQuery('<div>').append(chikunensuTo).html();

			chikunensuHtml += '</div>';

			chikunensuHtml += chikunensuHtmlLast;

			chikunensuHtml += '</div>';
			return chikunensuHtml;
		},

		renderItem: function (category, item) {
			switch (item.type) {
				case 'list':
					return this.renderListItem(category, item);
				case 'radio':
					return this.renderRadioItem(category, item);
				case 'multi':
					return this.renderMultiItem(category, item);
				default:
					return this.renderFlagItem(category, item);
			}
		},

		renderListItem: function (category, item) {
			var itemHtml = '';
			var opt;

			itemHtml += '<select class="select-inlineb js-search-filter-item js-search-filter-item-'+category.category_id+'-'+item.item_id+'" data-type="'+item.type+'" data-item-id="'+item.item_id+'">';
			for (var i=0,l=item.options.length;i< l;i++) {
				opt = item.options[i];

				if(category.category_id == 'menseki') {
					var dispay_label = '';
					dispay_label = app.h(opt.label);
					if(item.item_id == 1 || item.item_id == 2) {
						dispay_label = dispay_label.replace('以上', '');
						dispay_label = dispay_label.replace('指定なし', '下限なし');
					} else if(item.item_id == 3 || item.item_id == 4) {
						dispay_label = dispay_label.replace('指定なし', '上限なし');
					}
					itemHtml += '<option value="'+app.h(opt.value)+'">'+dispay_label+'</option>';
				} else {
					itemHtml += '<option value="'+app.h(opt.value)+'">'+app.h(opt.label)+'</option>';
				}
			}
			itemHtml += '</select>';
			return itemHtml;
		},
		renderRadioItem: function (category, item) {
			var itemHtml = '';
			var opt;

			for (var i=0,l=item.options.length;i< l;i++) {
				opt = item.options[i];
				itemHtml += this._renderCheckItem(category, item, 'radio', opt);
			}
			return itemHtml;
		},
		renderMultiItem: function (category, item) {
			var itemHtml = '';
			var opt;
			for (var i=0,l=item.options.length;i< l;i++) {
				opt = item.options[i];
				itemHtml += this._renderCheckItem(category, item, 'checkbox', opt, true);
			}
			return itemHtml;
		},
		renderFlagItem: function (category, item) {
			return this._renderCheckItem(category, item, 'checkbox', {value:1,label:item.label});
		},
		_renderCheckItem: function (category, item, type, opt, isMulti) {
			var nameSuffix = isMulti ? '[]' : '';
			return ''+
				'<label>'+
					'<input data-type="'+item.type+'" type="'+type+'" value="'+opt.value+'" '+
						'class="js-search-filter-item js-search-filter-item-'+category.category_id+'-'+item.item_id+'" '+
						'data-item-id="'+item.item_id+'" '+
						'name="'+category.category_id+'_'+item.item_id+nameSuffix+'" '+
						'>'+
						app.h(opt.label)+
				'</label>';
		},

		beforeNext: function () {
			// マスタ取得中=選択・解除していないので更新しない
			if (!this.deferred || this.deferred.state() === 'pending') {
				return;
			}

			var categories = [];

			// 種目は基本設定で指定済み
			var bsetting = this.getSetting().search_filter.categories;
			for(var cno = 0; cno < bsetting.length; cno++) {
				if(bsetting[cno]['category_id'] == 'shumoku') {
					categories.push(bsetting[cno]);
				}
			}

			this.$table.find('tr').each(function () {
				var $row = $(this);

				if($row.attr('data-category-id') == 'shumoku') {
					return true;
				}
				var category = {
					category_id: $row.attr('data-category-id'),
					items: []
				};

				var multiItemMap = {};
				$row.find('.js-search-filter-item').each(function () {
					var $this = $(this);
					var type = $this.attr('data-type');
					var itemId = $this.attr('data-item-id');
					if (type === 'list') {
						if (!parseInt($this.val())) {
							return;
						}
						category.items.push({
							item_id: itemId,
							item_value: $this.val()
						});
					}
					else if (type === 'multi') {
						if (!$this.prop('checked')) {
							return;
						}
						if (!multiItemMap[itemId]) {
							multiItemMap[itemId] = {
								item_id: itemId,
								item_value: []
							};
							category.items.push(multiItemMap[itemId]);
						}
						multiItemMap[itemId].item_value.push($this.val());
					}
					else if (type === 'radio') {
						if (!$this.prop('checked') || !parseInt($this.val())) {
							return;
						}
						category.items.push({
							item_id: itemId,
							item_value: $this.val()
						});
					}
					else {
						if (!$this.prop('checked')) {
							return;
						}
						category.items.push({
							item_id: itemId,
							item_value: 1
						});
					}
				});

				if (category.items.length) {
					categories.push(category);
				}
			});
			this.getSetting().search_filter.categories = categories;
		},

		hasSearchType: function (type) {
			throw new Error('override!');
		},

		needEnsenSetting: function () {
			return this.hasSearchType(this.Master.searchTypeConst.TYPE_ENSEN);
		},

		needShikugunSetting: function () {
			return this.hasSearchType(this.Master.searchTypeConst.TYPE_AREA);
		},

		prev: function () {
			var prevValue;
			if (this.needEnsenSetting()) {
				prevValue = 1;
			}
			else if (this.needShikugunSetting()) {
				prevValue = 2;
			}
			else {
				prevValue = 4;
			}
			this.step.prev(prevValue);
		}
	});

	var StepContentsSpecialSearchFilter = estate.StepContentsSpecialSearchFilter = app.inherits(StepContentsSearchFilter, function () {
		StepContentsSearchFilter.apply(this, arguments);
	}, {
		beforeRerender: function () {
			var estateType = this.getSetting().enabled_estate_type.join('-');
			var doRender = this.$table.attr('data-estate-type') !== estateType;
			if (doRender) {
				this.$table.empty();
				this.$table.attr('data-estate-type', estateType);
			}
			return doRender;
		},

		getSearchFilterMaster: function () {
			return EstateMaster.getSpecialSearchFilter( this.getSetting().enabled_estate_type );
		},

		hasSearchType: function (type) {
			return $.inArray(''+type, this.getSetting().area_search_filter.search_type) > -1;
		}
	});

	var StepContentsSecondSearchFilter = estate.StepContentsSecondSearchFilter = app.inherits(StepContentsSearchFilter, function () {
		StepContentsSearchFilter.apply(this, arguments);
	}, {

		// SecondSearchFilte init
		init: function () {
			this.$table = $('<table class="tb-basic ad-terms"></table>');
			this.$element.find('.js-table-container').append(this.$table);

			//this.$filters = $('<table class="tb-basic ad-terms"></table>');
			//this.$element.find('.js-filters-container').append(this.$filters);

			this.deferred = null;
		},

		// SecondSearchFilte show
		show: function () {
			var setting = this.getSetting();

			var self = this;
			if (!this.beforeRerender()) {
				app.estate.StepContents.prototype.show.call(this);
				return;
			}

			var def = this.deferred = this.getSearchFilterMaster();
			def.done(function (data) {
				if (def !== self.deferred) {
					return;
				}
				if (!data) {
					return;
				}
				self.render(data);
			});
			app.estate.StepContents.prototype.show.call(this);
		},

		// SecondSearchFilte beforeRerender
		beforeRerender: function () {
			return true;
		},

		getSearchFilterMaster: function () {
			return EstateMaster.getSecondSearchFilter( this.getSetting().estate_class );
		},

		// SecondSearchFilte render
		render: function (data) {
			var tableHtml = '';
			var tdClassHtml = '';
			var typeIdx,typeIdxl,estateType,estateTypeCategories;
			var i,il, category, $tr;
			var j,jl,item, itemMaster;
			var restoreEstateTypes=[];

			var setting = this.getSetting();

			for (typeIdx=0;typeIdx<data.estate_types.length;typeIdx++) {
				estateType = data.estate_types[typeIdx].estate_type;
				estateTypeCategories = data.estate_types[typeIdx].categories;

				if (setting.enabled_estate_type.indexOf(String(estateType)) == -1){
					var sectionId = 'section-'+estateType;
					var $section = this.$element.find('#'+sectionId);
					if ($section.length>=1){
						$section.remove();
					}
					continue;
				}

				tableHtml = '';
				var $tabele = $('<table class="tb-basic ad-terms"></table>');
				for (i = 0, il = estateTypeCategories.length; i < il; i++) {
					category = estateTypeCategories[i];
					tdClassHtml = '';
					if (category.category_id === 'madori') {
						tdClassHtml = ' class="floor-plans"';
					} else if (category.category_id === 'kitchen') {
						tdClassHtml = ' class="list-more"';
					}
					switch (category.category_id) {
						case 'madori':
							tdClassHtml = ' class="floor-plans"';
							break;
						case 'kitchen':
						case 'bath_toilet':
						case 'reidanbo':
						case 'shuno':
						case 'tv_tsusin':
						case 'security':
						case 'ichi':
						case 'joken':
						case 'kyouyu_shisetsu':
						case 'setsubi_kinou':
						case 'tokucho':
						case 'koho_kozo':
						case 'other':
							tdClassHtml = ' class="list-more"';
							break;
					}

					tableHtml += '<tr data-category-id="' + category.category_id + '">';
					tableHtml += '<th>' + category.label + '</th>';
					tableHtml += '<td' + tdClassHtml + '>';

					if (category.description) {
						tableHtml += '<p class="tx-annotation">' + category.description + '</p>';
					}

					if (category.category_id === 'kakaku'
						|| category.category_id === 'tochi_ms' || category.category_id === 'tatemono_ms')  {
						tableHtml += this.renderKakaku(category);
					}
					else if (category.category_id === 'menseki') {
						tableHtml += this.renderMenseki(category);
					}
					else if (category.category_id === 'tesuryo') {
						tableHtml += this.renderTesuryo(category);
					}
					else {
						tableHtml += this.renderCategory(category);
					}

					tableHtml += '</td>';
					tableHtml += '</tr>';
				}


				$tabele.html(tableHtml);


				// 価格上限の初期値を設定
				var $kakaku2 = $tabele.find('.js-search-filter-item-kakaku-2');
				$kakaku2[0].selectedIndex = $kakaku2.find('option').length - 1;

                // 土地面積の初期値を設定
                var $tochi2 = $tabele.find('.js-search-filter-item-tochi_ms-2');
                if($tochi2.find('option').length>=1) {
                    $tochi2[0].selectedIndex = $tochi2.find('option').length - 1;
                }

                // 建物面積の初期値を設定
                var $tatemono2 = $tabele.find('.js-search-filter-item-tatemono_ms-2');
                if($tatemono2.find('option').length>=1) {
                    $tatemono2[0].selectedIndex = $tatemono2.find('option').length - 1;
                }

                //契約条件初期値設定
				var $keiyaku_joken = $tabele.find('.js-search-filter-item-keiyaku_joken-1');
				if ($keiyaku_joken[0]) {
					$keiyaku_joken.val(0);
				}

				// ラジオの初期値を設定
				$tabele.find('input:radio[value="0"]').prop('checked', true);

				// 築年数の初期設定
				var $chikunensu_radio = $tabele.find('tr[data-category-id=chikunensu] input:radio');
				if ($chikunensu_radio[0]){
					$chikunensu_radio.attr('name', estateType+'_'+$chikunensu_radio[0].name);
				}

				// 手数料の初期設定
				var $tesuryo_radio = $tabele.find('tr[data-category-id=tesuryo] input:radio');
				if ($tesuryo_radio[0]){
					$tesuryo_radio.attr('name', estateType+'_'+$tesuryo_radio[0].name);
				}

                var sectionId = 'section-'+estateType;
				var $filtersContainer = this.$element.find('.js-filters-container');
				var $section = $filtersContainer.find('#'+sectionId);
				if ($section.length<=0){
					var sectionDiv = '<div id="'+sectionId+'" class="section"></div>';
					var $sectionList = $filtersContainer.find('.section');
					if ($sectionList.length<=0){
						$filtersContainer.append(sectionDiv);
					}else{
						var secIdx, secl, preSecIdx, secEstateType;
						for(secIdx = 0, secl=$sectionList.length; secIdx < secl; secIdx++){
							secEstateType = $sectionList[secIdx].id.replace( /section-/g , "" ) ;
							if (estateType < secEstateType ){
								break;
							}
						}
						if ($sectionList.length==secIdx){
							$section = $filtersContainer.find('#'+$sectionList[secIdx-1].id);
							$section.after(sectionDiv);
						}else{
							$section = $filtersContainer.find('#'+$sectionList[secIdx].id);
							$section.before(sectionDiv);
						}
					}

					$section = $filtersContainer.find('#'+sectionId);
					$section.append("<h3>"+data.estateTypeMaster[estateType]+"</h3>");
					$section.append($tabele);
					restoreEstateTypes.push(estateType);
				}
			}

			if (restoreEstateTypes.length >= 1){
				data.restoreEstateTypes = restoreEstateTypes;
				this.restore(data);
			}
		},

		// SecondSearchFilte restore
		restore: function (data) {

			var setting = this.getSetting();
			var $section;
            var typeIdx,typeIdxl,estate_type_filter,estate_type,categories;
			var i,il, category, categoryMaster;
			var j,jl, item, itemMaster, $item;
			var k,kl, val;

			if (setting.search_filter.estate_types==null){
				return;
			}

			var categoryMap=[];
			for (typeIdx=0,typeIdxl=data.estate_types.length;typeIdx<typeIdxl;typeIdx++) {
				estate_type = data.estate_types[typeIdx].estate_type;
				categoryMap[estate_type] = data.categoryMap[typeIdx];
			}


			for (typeIdx=0,typeIdxl=setting.search_filter.estate_types.length;typeIdx<typeIdxl;typeIdx++) {
				estate_type_filter = setting.search_filter.estate_types[typeIdx];
				estate_type = estate_type_filter['estate_type'];

				if(!data.restoreEstateTypes.indexOf(estate_type)){
					continue;
				}

				categories  = estate_type_filter['categories'];
				for (i = 0, il = categories.length; i < il; i++) {
					category = categories[i];
					categoryMaster = categoryMap[estate_type][category.category_id];
					if (!categoryMaster) {
						continue;
					}

					for (j = 0, jl = category.items.length; j < jl; j++) {
						item = category.items[j];
						itemMaster = categoryMaster.itemMap[item.item_id];
						if (!itemMaster) {
							continue;
						}

						$section = this.$element.find('#section-' + estate_type);
						$item = $section.find('.js-search-filter-item-' + category.category_id + '-' + item.item_id);
						if (itemMaster.type === 'list') {
							if (itemMaster.elementType === 'radio') {
								$item.filter('[value="' + (item.item_value || 0) + '"]').prop('checked', true);
							}
							else {
								$item.val(item.item_value);
							}
						}
						else if (itemMaster.type === 'multi') {
							for (k = 0, kl = item.item_value.length; k < kl; k++) {
								val = item.item_value[k];
								try {
									$item.filter('[value="' + val + '"]').prop('checked', true);
								}
								catch (err) {
								}
							}
						}
						else if (itemMaster.type === 'radio') {
							try {
								$item.filter('[value="' + (item.item_value || 0) + '"]').prop('checked', true);
							}
							catch (err) {
							}
						}
						else {
							if(category.category_id === 'tesuryo') {
								$item.prop('checked', true);
								var classNo = $(".main-contents h1").attr('estateclass');
								if(classNo != 1 && classNo != 2) {
									$item.parent().parent().find("input:radio").attr('disabled', false);
									$item.parent().parent().find("input:checkbox").attr('checked', true);
								}
							} else {
								$item.prop('checked', true);
							}
						}
					}
				}
			}
		},

		// SecondSearchFilte
		beforeNext: function () {

			// マスタ取得中=選択・解除していないので更新しない
			if (!this.deferred || this.deferred.state() === 'pending') {
				return;
			}

			var estate_types= [];
            var $filtersContainer = this.$element.find('.js-filters-container');

			$filtersContainer.find('.section').each(function () {
				var $section = $(this);
				var sectionname = $(this).attr("id");
				var estateType = sectionname.replace( /section-/g , "" ) ;
				$section.find('table').each(function () {
					var $table = $(this);

					var categories = [];
					$table.find('tr').each(function () {
						var $row = $(this);
						var category = {
							category_id: $row.attr('data-category-id'),
							items: []
						};

						var multiItemMap = {};
						$row.find('.js-search-filter-item').each(function () {
							var $this = $(this);
							var type = $this.attr('data-type');
							var itemId = $this.attr('data-item-id');
							if (type === 'list') {
								if (!parseInt($this.val())) {
									return;
								}
								category.items.push({
									item_id: itemId,
									item_value: $this.val()
								});
							}
							else if (type === 'multi') {
								if (!$this.prop('checked')) {
									return;
								}
								if (!multiItemMap[itemId]) {
									multiItemMap[itemId] = {
										item_id: itemId,
										item_value: []
									};
									category.items.push(multiItemMap[itemId]);
								}
								multiItemMap[itemId].item_value.push($this.val());
							}
							else if (type === 'radio') {
								if (!$this.prop('checked') || !parseInt($this.val())) {
									return;
								}
								category.items.push({
									item_id: itemId,
									item_value: $this.val()
								});
							}
							else {
								if (!$this.prop('checked')) {
									return;
								}
								category.items.push({
									item_id: itemId,
									item_value: 1
								});
							}
						});

						if (category.items.length) {
							categories.push(category);
						}
					});
					if (categories.length) {
						var estateTypeFiletr = {
							estate_type: estateType,
							categories: categories
						};
						estate_types.push(estateTypeFiletr);
					}

				});
			});
			//this.getSetting().search_filter.categories = categories;
			this.getSetting().search_filter.estate_types = estate_types;

		},

		hasSearchType: function (type) {
			return ''+type === ''+this.getSetting().area_search_filter.search_type;
		}
	});

	var EstateMaster = estate.EstateMaster = {
		current_pref : '',
		_master: {
			shikugun: {},
			choson: {},
			ensen: {},
			eki: {},
			specialSearchFilter: {},
            secondSearchFilter: {},
            houseAll: {},
            searchHouse: {},
		},

		_get: function (masterName, requestMethod, codes, multiCodeMax) {
			var MULTI_CODE_MAX = multiCodeMax || 5;
			var dfds = [];
			var requests = [];
			var i, l;
			var code;

			if (!$.isArray(codes)) {
				codes = [codes];
            }
			for (i=0,l=codes.length;i< l;i++) {
				code = codes[i];

				if (!this._master[masterName][code] || this._master[masterName][code].state() === 'rejected') {
					this._master[masterName][code] = $.Deferred();
					requests.push(code);
				}
				dfds.push(this._master[masterName][code]);

				if (requests.length >= MULTI_CODE_MAX) {
					this[requestMethod](requests);
					requests = [];
				}
			}

			if (requests.length) {
				this[requestMethod](requests);
			}

			return $.when.apply($, dfds);
        },
		setPref: function (ken_cds) {
			this.current_pref = ken_cds;
		},

		getShikugun: function (ken_cds) {
            if ($.isArray(ken_cds)) {
                ken_cds = ken_cds.join(',');
            }
			return this._get('shikugun', 'requestShikugun', ken_cds);
		},

        getChoson: function (shikugun_cds) {
            return this._get('choson', 'requestChoson', shikugun_cds);
        },

        getEnsen: function (ken_cds) {
            if ($.isArray(ken_cds)) {
                ken_cds = ken_cds.join(',');
            }
			return this._get('ensen', 'requestEnsen', ken_cds);
		},

		getEki: function (ensen_cds) {
			return this._get('eki', 'requestEki', ensen_cds);
		},

		getSpecialSearchFilter: function (estateType) {
			return this._get('specialSearchFilter', 'requestSpecialSearchFilter', estateType.join(','), 1);
		},

		getSecondSearchFilter: function (estateClass) {
			return this._get('secondSearchFilter', 'requestSecondSearchFilter', estateClass, 1);
        },
        
        getHouseAll: function (params) {
			return this._get('houseAll', 'requestHouseAll', JSON.stringify(params), 1);
        },

		requestShikugun: function (codes) {
			var self = this;
			app.api('/api-estate/shikugun', {ken_cd: codes[0]}, function (data) {
				var x, xl, kenData = {};
				var y, yl, locateGroupData;
				var z, zl, shikugunData;

				for (x=0,xl=data.shikuguns.length;x< xl;x++) {
					kenData[x] = data.shikuguns[x];
					kenData[x].shikugunMap = {};

					for (y=0,yl=kenData[x].locate_groups.length;y< yl;y++) {
						locateGroupData = kenData[x].locate_groups[y];

						for (z=0,zl=locateGroupData.shikuguns.length;z< zl;z++) {
							shikugunData = locateGroupData.shikuguns[z];
							kenData[x].shikugunMap[shikugunData.code] = shikugunData;
						}
                    }
                }
                if (!self._master.shikugun[codes[0]]) {
                    return;
                }
                self._master.shikugun[codes[0]].resolve(kenData);
			},connection_failure_msg)
				.fail(function () {
                    if (!self._master.shikugun[codes[0]]) {
						return;
                    }
                    self._master.shikugun[codes[0]].reject();
			});
        },

        requestChoson: function (codes) {
            var self = this;
            app.api('/api-estate/choson', {shikugun_cd: codes.join(',')}, function (data) {
                var x, xl, shikugunData;
                var y, yl, chosonData;

                for (x=0,xl=data.shikuguns.length;x< xl;x++) {
                    shikugunData = data.shikuguns[x];
                    if (!self._master.choson[shikugunData.shikugun_cd]) {
                        continue;
                    }
                    shikugunData.chosonMap = {};

                    for (y=0,yl=shikugunData.chosons.length;y< yl;y++) {
                        chosonData = shikugunData.chosons[y];
                        shikugunData.chosonMap[chosonData.code] = chosonData;
                    }
                    self._master.choson[shikugunData.shikugun_cd].resolve(shikugunData);
                }
            },connection_failure_msg)
                .fail(function () {
                    for (var i=0,l=codes.length;i< l;i++) {
                        if (!self._master.choson[codes[i]]) {
                            continue;
                        }
                        self._master.choson[codes[i]].reject();
                    }
                });
        },

		requestEnsen: function (codes) {
			var self = this;
			app.api('/api-estate/ensen', {ken_cd: codes.join(',')}, function (data) {
				var x, xl, kenData = {};
				var y, yl, ensenGroupData;
				var z, zl, ensenData;

				for (x=0,xl=data.ensens.length;x< xl;x++) {
					kenData[x] = data.ensens[x];
					kenData[x].ensenMap = {};

					for (y=0,yl=kenData[x].ensen_groups.length;y< yl;y++) {
						ensenGroupData = kenData[x].ensen_groups[y];

						for (z=0,zl=ensenGroupData.ensens.length;z< zl;z++) {
							ensenData = ensenGroupData.ensens[z];
							kenData[x].ensenMap[ensenData.code] = ensenData;
						}
					}
                }
                if (!self._master.ensen[codes[0]]) {
                    return;
                }
                self._master.ensen[codes[0]].resolve(kenData);
			},connection_failure_msg)
				.fail(function () {
                if (!self._master.ensen[codes[0]]) {
                    return;
                }
                self._master.ensen[codes[0]].reject();
			});
		},

		requestEki: function (codes) {
			var self = this;
			app.api('/api-estate/eki', {ensen_cd: codes.join(','), ken_cd: self.current_pref}, function (data) {
				var x, xl, ensenData;
				var y, yl, ekiData;

				for (x=0,xl=data.ensens.length;x< xl;x++) {
					ensenData = data.ensens[x];
					if (!self._master.eki[ensenData.code]) {
						continue;
					}
					ensenData.ekiMap = {};

					for (y=0,yl=ensenData.ekis.length;y< yl;y++) {
						ekiData = ensenData.ekis[y];
						ensenData.ekiMap[ekiData.code] = ekiData;
					}
					self._master.eki[ensenData.code].resolve(ensenData);
				}
			},connection_failure_msg)
				.fail(function () {
				for (var i=0,l=codes.length;i< l;i++) {
					if (!self._master.eki[codes[i]]) {
						continue;
					}
					self._master.eki[codes[i]].reject();
				}
			});
		},

		requestSpecialSearchFilter: function (codes) {
			var estateType = codes[0];
			var self = this;
			app.api('/api-estate/special-search-filter', {estate_type: estateType}, function (data) {
				if (!self._master.specialSearchFilter[estateType]) {
					return;
				}
				data = self._parseSearchFilterResponse(data);
				self._master.specialSearchFilter[estateType].resolve(data);
			},connection_failure_msg)
			.fail(function () {
				if (self._master.specialSearchFilter[estateType]) {
					self._master.specialSearchFilter[estateType].reject();
				}
			});
		},

		requestSecondSearchFilter: function (codes) {
			var estateClass = codes[0];
			var self = this;
			app.api('/api-estate/second-search-filter', {estate_class: estateClass}, function (data) {
				if (!self._master.secondSearchFilter[estateClass]) {
					return;
				}
				data = self._parseSecondSearchFilterResponse(data);
				self._master.secondSearchFilter[estateClass].resolve(data);
			},connection_failure_msg)
			.fail(function () {
				if (self._master.secondSearchFilter[estateClass]) {
					self._master.secondSearchFilter[estateClass].reject();
				}
			});
		},

		_parseSearchFilterResponse: function (data) {
			data.categoryMap = {};
			var i,il,category;
			var j,jl,item;
			var x,xl,opt;
			for (i=0,il=data.categories.length;i<il;i++) {
				category = data.categories[i];
				data.categoryMap[category.category_id] = category;
				category.itemMap = {};
				for (j=0,jl=category.items.length;j<jl;j++) {
					item = category.items[j];
					category.itemMap[item.item_id] = item;
					if (item.options) {
						item.optionMap = {};
						for (x=0,xl=item.options.length;x<xl;x++) {
							opt = item.options[x];
							item.optionMap[opt.value] = opt.label;
						}
					}
				}
			}
			return data;
		},

		_parseSecondSearchFilterResponse: function (data) {
			data.categoryMap = {};
			var cnt,cntl,estate_type_filter;
			var categories;
			var i,il,category;
			var j,jl,item;
			var x,xl,opt;

			for (cnt=0,cntl=data.estate_types.length;cnt<cntl;cnt++) {
				estate_type_filter = data.estate_types[cnt];
				data.categoryMap[cnt] = {};
				for (i=0,il=estate_type_filter.categories.length;i<il;i++) {
					category = estate_type_filter.categories[i];
					data.categoryMap[cnt][category.category_id] = category;
					category.itemMap = {};

					for (j=0,jl=category.items.length;j<jl;j++) {
						item = category.items[j];
						category.itemMap[item.item_id] = item;
						if (item.options) {
							item.optionMap = {};
							for (x=0,xl=item.options.length;x<xl;x++) {
								opt = item.options[x];
								item.optionMap[opt.value] = opt.label;
							}
						}
					}
				}
			}
			return data;
        },
        requestHouseAll: function (codes) {
            var self = this;
			app.api('/api-estate/house-all', JSON.parse(codes[0]) , function (data) {
                if (!self._master.houseAll[codes[0]]) {
					return;
                }
                self._master.houseAll[codes[0]].resolve(data);
            },connection_failure_msg)
            .fail(function () {
                if (self._master.houseAll[codes[0]]) {
                    self._master.houseAll[codes[0]].reject();
                }
            });
        },

    };

    var ModalInvidial = estate.ModalInvidial = function(invidial, houses, type) {
        this._deferred = $.Deferred();
		this.promise = this._deferred.promise();

        this.invidial = invidial;
        this.setting = this.invidial.setting;
        this.isLinkHouse = typeof this.invidial.Container != 'undefined';
        this.type = type;
        this.housesNo = houses;
        this.houseSelect = this.invidial.houses_id.concat([]);
        this.shikuguns = {};
		this.chosons = {};
        this.searchChosonEnabled = false;

        this.checks = {
            shikuguns: {},
            chosons: {},
            choazas: {},
            ensens: {},
        };
        
        this.allowed = {};
		this.denied  = {};
        this.invidial.clearError();
		this.$element = $(this.template);
		this.modal = app.modal(this.$element);
        this.modal.show();
        
        this.initClose();
		this.initCheckAll();
        this.init(this.houseSelect);
    }
    ModalInvidial.prototype.template = '<div/>';
    ModalInvidial.prototype.init = function () {};
	ModalInvidial.prototype.setTitle = function (title) {
		this.$element.find('.js-modal-title').text(title);
    };
    ModalInvidial.prototype.initClose = function () {
		var self = this;
		this.$element.on('click', '.js-modal-close', function () {
			self._deferred.reject();
            self.close();
            if (self.isLinkHouse && self.invidial.OldModal) {
                self.invidial.showModal();
            }
			return false;
		});
	};
	ModalInvidial.prototype.initCheckAll = function () {
        var self = this;
		this.$element.on('change', '.js-estate-select-group-check input', function () {
            var $this = $(this);
            var input = $this.closest('.js-estate-select-group').find('.js-estate-select-group-container input:not(:disabled)');
            input.prop('checked', $this.prop('checked'));
            if ($this.closest('.js-estate-select-group').hasClass('house-list')) {
                if ($this.prop('checked')) {
                    input.each(function() {
                        if($(this).attr('name') != 'from-cms') {
                        	self.houseSelect = self.houseSelect.concat([$(this).val()].filter(function(val) {
                            	return self.houseSelect.indexOf(val) === -1;
                        	}));
                        	self.checks[ $(this).val() ] = true;
						}
                    });
                } else {
                    input.each(function() {
                        var index = self.houseSelect.indexOf($(this).val());
                        self.houseSelect.splice(index, 1);
                        self.checks[ $(this).val() ] = false;
                    });
                }
            }
		});
		this.$element.on('change', '.js-estate-select-group-container input', function () {
			var $group = $(this).closest('.js-estate-select-group');
			var $all = $group.find('.js-estate-select-group-check input');
            $all.prop('checked', !$group.find('.js-estate-select-group-container input:not(:disabled):not(:checked)').length);
            if ($group.hasClass('house-list')) {
                if ($(this).prop('checked')) {
                    if (self.isLinkHouse) {
                        self.houseSelect = [$(this).val()];
                    } else {
                        self.houseSelect = self.houseSelect.concat([$(this).val()].filter(function(val) {
                            return self.houseSelect.indexOf(val) === -1;
                        }));
                        self.checks[ $(this).val() ] = true;
                    }
                } else {
                    var index = self.houseSelect.indexOf($(this).val());
                    self.houseSelect.splice(index, 1);
                    self.checks[ $(this).val() ] = false;
                }
            }
        });
    };
    ModalInvidial.prototype.showError = function (message) {
		app.modal.alert('', message);
	};
	ModalInvidial.prototype.clearError = function () {
	};
	ModalInvidial.prototype.close = function () {
		$(window).off('resize', this.updateSize);
        this.$element.off();
		this.modal.close();
		this.modal = null;
	};
	ModalInvidial.prototype.createLoading = function () {
		return $('<div class="loading"><p><img alt="" src="/images/common/loading.gif"></p></div>');
    };
    ModalInvidial.prototype.setData = function (shikuguns, ensens, ekis, chosons, choazas, searchChosonEnabled) {
        this.searchChosonEnabled = searchChosonEnabled;
        var ret1 = this.setShikuguns(shikuguns);
        var ret2 = this.setChosons(chosons);
        var ret3 = this.setChoazas(choazas);
        var ret4 = this.setEnsens(ensens);
        var ret5 = this.setEkis(ekis);
        return ret1 && ret2 && ret3 && ret3 && ret4 && ret5;
    },
    ModalInvidial.prototype.setShikuguns = function (codes) {
        this._setCodes('shikuguns', codes);
    },
    ModalInvidial.prototype.setChosons = function (codes) {
        var name = 'chosons';
        this[name] = $.extend({}, codes);
        for (var shikugun_cd in codes) {
            this.checks[name][shikugun_cd] = {};
            for (var i=0,l=codes[shikugun_cd].length;i<l;i++) {
                this.checks[name][shikugun_cd][ codes[shikugun_cd][i] ] = true;
            }
        }
    },
    ModalInvidial.prototype.setEnsens = function (codes) {
        var name = 'ensens'
        this[name] = $.extend({}, codes);
        // this._setCodes('ensens', codes);
    },
    ModalInvidial.prototype.setEkis = function (codes) {
        var name = 'ekis'
        this[name] = $.extend({}, codes);
        // this._setCodes('ekis', codes);
    },
    ModalInvidial.prototype.setChoazas = function (codes) {
        var name = 'choazas'
        this[name] = $.extend({}, codes);
        // this._setCodes('choazas', codes);
        // var name = 'choazas';
        // this[name] = codes;
    },
    ModalInvidial.prototype._setCodes = function (name, codes) {
        this[name] = [];
        for (var i in codes) {
            for (var j in codes[i]) {
                this[name].push(codes[i][j]);
            }
        }
    },
    ModalInvidial.prototype.getPrefName = function (prefCodes) {
        var prefName = [];
		for (var code in prefCodes) {
            prefName.push(this.invidial.Master.prefMaster[prefCodes[code]]);
        }
        return prefName;
    };
    ModalInvidial.prototype.modalShikugun = function(prefCode, searchChosonEnabled) {
        var pref = {
            code: prefCode,
            name: this.getPrefName(prefCode)
        };
        var data = {
            shikuguns: this.shikuguns || [],
            chosons: this.setDataModal(prefCode, this.chosons) || {},
            choazas: this.setDataModal(prefCode, this.choazas) || {},
            searchChosonEnabled: searchChosonEnabled
        };
        var modal = new app.estate.ShikugunChosonModal(pref, data, this.invidial.allowed, this.invidial);
    };
    ModalInvidial.prototype.modalEnsen = function(prefCode, backStation) {
        var pref = {
            code: prefCode,
            name: this.getPrefName(prefCode)
        };
        var data = {
            ensens: this.ensens || [],
            ekis: this.ekis || []
        };
        this.invidial.backStation = false;
        if (typeof backStation != 'undefined' && backStation) {
            this.invidial.backStation = backStation;
        }
        var modal = new app.estate.EnsenModal(pref, data, this.invidial.allowed, this.invidial);
    };

    ModalInvidial.prototype.setDataModal = function(prefCodes, data) {
        var result = {};
        for (var code in prefCodes) {
            if (typeof data[prefCodes[code]] == 'undefined' && !data[prefCodes[code]]) {
                return data;
            }
            Object.keys(data[prefCodes[code]]).forEach(function(i){
                result[i] = data[prefCodes[code]][i];
            }) ;
        }
        return result;
    }
    
    var HouseListModal = estate.HouseListModal = app.inherits(ModalInvidial, function () {
        ModalInvidial.apply(this, arguments);
    }, {
        template: ''+
			'<div class="modal-contents size-list modal-scroll">'+
				'<div class="modal-header">'+
					'<h2 class="js-modal-title">公開する物件を選択してください。</h2>'+
					'<div class="modal-close"><a href="javascript:;" class="btn-modal js-modal-close"><i class="i-e-delete"></i></a></div>'+
				'</div>'+
				'<div class="modal-body js-modal-contents">'+
					'<div class="modal-body-inner align-top js-modal-contents-main" id="js-modal-invidial">'+
					'</div>'+
					'<div class="modal-btns js-modal-contents-btn-area">'+
						'<a class="btn-t-gray js-modal-prev" href="javascript:;">戻る</a>'+
						'<a class="btn-t-blue size-l js-modal-ok" href="javascript:;">決定する</a>'+
					'</div>'+
				'</div>'+
            '</div>',
            
        init: function(data)
        {
            var self = this;
            this.$contents = this.$element.find('.js-modal-contents');
            this.$main     = this.$contents.find('.js-modal-contents-main');
            this.$body = $('body');
            this.$invidial = this.$body.find('.individual-setting');
            this.page = 1;
            this.sort = null;
            this.estateType = this.setting.enabled_estate_type;

            this.checks = {};
			data = data || [];
			for (var i=0,l=data.length;i<l;i++) {
				this.checks[ data[i] ] = true;
            }
            
            this.renderHouseAll();

            this.$element.on('click', '.paging li:not(.is-active) a', function() {
                self.setPage(parseInt($(this).data('page')));
                self.renderHouseAll();
            });
            this.$element.on('click', '.sort-table a', function() {
                self.setSort($(this).parent().data('value'));
                self.renderHouseAll();
            });
            this.$element.on('click', '.confirm-condition-search a', function() {
                var modal = new confirmModal(self.invidial, null);
                self.modal.$el.hide();
            });
            // ボタン
			this.$element.on('click', '.js-modal-ok:not(.is-disable)', function () {
                self.setHouses(self.houseSelect);
                self.setPage(1);
                self.setSort(null);
				self.onOk();
				return false;
            });
            this.$element.on('click', '.js-modal-prev:not(.is-disable)', function () {
                if (self.type == 3) {
                    var modal = new searchFilterModal(self.invidial, null);
                }
                if (self.type == 1) {
                    if (self.isLinkHouse && self.invidial.OldModal) {
                        self.invidial.showModal();
                    }
                }
                self.close();
				return false;
			});
        },
        setPage: function(page) {
            this.page = page;
        },
        setSort: function(sort) {
            this.sort = sort;
        },
        setEstateType: function(type) {
            this.estateType = type;
        },
        setHouses: function(houses) {
            this.houseSelect = houses;
        },
        renderHouseAll: function(page) {
            var self = this;
            // loading on
            this.invidial.clearErrorHouseNo();
			var $loading = this.createLoading();
			this.$main.html($loading);
            this.$contents.addClass('is-loading');
            var $ok = this.$element.find('.js-modal-ok').addClass('is-disable');
            var params = {
                estateClass: this.estateType,
                page: this.page,
                sort: this.sort,
                isModal: true,
                setting: this.invidial.setting
                
            };
            if (this.type == 1) {
                params.houses_no = this.housesNo;
            }
            if (this.type == 3) {
                params.isCondition = true;
            }
            if (this.isLinkHouse) {
                params.link_page = true;
            }
            EstateMaster.getHouseAll(params).done(function (data) {
                // loading off
                if (data.info.total_count == 0) {
                    self.close();
                    if (self.type == 1) {
                        if (!self.isLinkHouse) {
                            self.invidial.showErrorHouseNo('物件が見つかりません');
                        } else {
                            self.invidial.showError();
                        }
                    } else {
                        if (!self.isLinkHouse) {
                            self.showError('物件が見つかりません');
                            self.invidial.$btnResultCondition.removeClass('show');
                        } else {
                            self.invidial.showError();
                        }

                    }
                    return;
                } else {
                    if (self.type == 3 && !self.isLinkHouse) {
                        self.invidial.setting.area_search_filter.search_condition['count'] = data.info.total_count;
                        self.invidial.$sectionList.removeClass('hide');
                        self.invidial.$btnResultCondition.addClass('show');
                    }
                }
                self.$contents.removeClass('is-loading');
                $ok.removeClass('is-disable');
                $loading.remove();
                self.$main.html(data.content);
                self.cunrentCheckHouse();
            });
        },
        validateListHouse: function () {
            if (this.houseSelect.length == 0) {
                return false;
            }
            // var $checked = this.$element.find('.js-estate-select-group-container input:not(:disabled):checked');
            // if ($checked.length == 0) {
            //     return false;
            // }
            return true;
        },
        onOk: function () {
            var self = this;
			if (!this.validateListHouse()) {
                this.showError('物件が選択されていません。');
                return;
            }
            this.close();
            self.invidial.housesRemove = self.invidial.housesRemove.filter(function(value) {
                if (!($.inArray(value, self.houseSelect) > -1)) {
                    return value;
                }
            });
            self.invidial.setHousesNo(self.houseSelect);
            self.invidial.actionListHouse();
            if (self.isLinkHouse && self.invidial.OldModal) {
                self.invidial.showModal();
            }
        },
        cunrentCheckHouse: function () {
            var self = this;
            if(self.isLinkHouse) {
                this.$main.find('.js-estate-select-group-container input').eq(0).prop('checked', true).change();
            } else {
                var check = true;
                this.$main.find('input[name="houses_id[]"]').each(function() {
                    var val = $(this).val();
                    if (self.checks[val]) {
                        $(this).prop('checked', true);
                    } else {
                        check = false;
                    }
                });
                if (check) {
                    this.$main.find('.js-estate-select-group-check input').prop('checked', true);
                }
            }
        }
    });

    var conditionHouseListModal = estate.conditionHouseListModal = app.inherits(ModalInvidial, function () {
        ModalInvidial.apply(this, arguments);
    }, {
        template: ''+
        '<div class="modal-contents size-l modal-scroll">'+
            '<div class="modal-header-no-title">' +
                '<div class="modal-close"><a href="javascript:;" class="btn-modal js-modal-close"><i class="i-e-delete"></i></a></div>' +
            '</div>' +
            '<div class="modal-body js-modal-contents">'+
                '<div class="modal-body-inner align-top js-modal-contents-main modal-condition">'+
                '</div>' +
                '<div class="modal-btns js-modal-contents-btn-area">'+
                    '<a class="btn-t-gray js-modal-close" href="javascript:;">戻る</a>'+
                    '<a class="btn-t-blue size-l js-modal-ok" href="javascript:;">次の設定に進む</a>'+
                '</div>'+
            '</div>'+
        '</div>',
        init: function()
        {
            var self = this;
            this.$contents = this.$element.find('.js-modal-contents');
            this.$main     = this.$contents.find('.js-modal-contents-main');
            this.$body = $('body');

            this.renderContent();
            this.initCheck();
            this.setData(
                this.setting.area_search_filter.area_2 || {},
                this.setting.area_search_filter.area_3 || [],
				this.setting.area_search_filter.area_4 || {},
				this.setting.area_search_filter.area_5 || {},
				this.setting.area_search_filter.area_6 || {},
				this.setting.area_search_filter.choson_search_enabled == 1);
            // ボタン
			this.$element.on('click', '.js-modal-ok:not(.is-disable)', function () {
				self.onOk();
				return false;
            });
            this.$element.on('click', '.js-modal-close:not(.is-disable)', function () {
                // self.close();
                if (self.isLinkHouse && self.invidial.OldModal) {
                    self.invidial.showModal();
                }
				return false;
			});
        },

		getData: function () {
			return {
				shikuguns: this.shikuguns,
				chosons: this.searchChosonEnabled ? this.chosons : {},
				choazas: this.searchChosonEnabled ? this.choazas : {}
			};
        },
        initCheck: function () {
            var self = this;
            this.$searchType = this.$main.find('input[name="search_type[]"]:not(:disabled)');
            this.$prefContainer = this.$main.find('#pref');
            this.$publishEstate = this.$main.find('#publish_estate input');
            this.$tesuryoKokokuhi = this.$main.find('.tesuryo_kokokuhi input'); 
            this.$onlyErEnabled = this.$publishEstate.filter('[value="only_er_enabled"]');
			this.$secondEstateEnabled = this.$publishEstate.filter('[value="second_estate_enabled"]');
			this.$endMukeEnabled = this.$publishEstate.filter('[value="end_muke_enabled"]');
			this.$excludeSecond = this.$publishEstate.filter('[value="exclude_second"]');


            $(document).on('change', '#tesuryo_check', function() {
                self.tesuryoCheckCtl();
            });
            this.$searchType.prop('checked', false);
            var $checked = this.$searchType.filter('[value="' + this.setting.area_search_filter.search_condition['type'] + '"]').prop('checked', true);
            if (!$checked.length) {
                $checked = this.$searchType.eq(0).prop('checked', true);
            }
            if (this.isLinkHouse) {
                this.invidial.modalConditionLinkHouseInit(this.$main);
            }
            this.$main.on('change', 'input[name="search_type[]"]', function () {
                self.toggleDisablePref();
            });
            this.$prefContainer.find('input').prop('checked', false)
            this.$prefContainer.find('input').each(function() {
                var $this = $(this);
                if($.inArray($this.val(), self.setting.area_search_filter.area_1) > -1) {
                    $this.prop('checked', true);
                }
            })
            this.$main.on('click', '#publish_estate input[value="second_estate_enabled"]', function () {
				self.toggleDisableSecondEstate();
			});
            this.$publishEstate.each(function () {
                var $this = $(this);
                $this.prop('checked', !!self.setting[$this.val()]);
            });
            $(document).on('change', '#publish_estate input', function() {
                self.publishEstateCheckCtl();
            });
            this.$tesuryoKokokuhi.each(function () {
                var $this = $(this);
                $this.prop('checked', !!self.setting[$this.val()]);
            });
            this.$main.find('.tesuryo_kokokuhi input[type=radio]').each(function() {
                if($(this).prop('checked')) {
                    self.$main.find('#tesuryo_check').prop('checked', true);
                }
            });

            this.tesuryoCheckCtl();
            if(this.$main.find('#tesuryo_check').prop('checked') == false) {
                this.$main.find(".tesuryo_kokokuhi input:radio[name='tesuryo_kokokuhi[]']").attr('disabled', 'disabled');
            }
            this.$main.find('#pref .errors .is-no-selecting').remove();

            this.toggleDisablePref();
            this.toggleDisablePublishEstate();
            this.toggleDisableSecondEstate();
            this.toggleDisableEndMuke();
            this.toggleDisableExcludeSecond();
            this.toggleDisableOwnerChange();
            this.publishEstateCheckCtl();
        },
        toggleDisablePref: function () {
            if (this.$searchType.filter('[value="4"]').prop('checked')) {
                this.$prefContainer.find('.is-required').addClass('is-disable')
                .find('input').prop('disabled', true).prop('checked', false);
            } else {
                this.$prefContainer.find('.is-required').removeClass('is-disable')
                .find('input').prop('disabled', false);
            }
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
            this.$secondEstateEnabled.prop('disabled', isChecked);
            this.$secondEstateEnabled.parent().toggleClass('is-disable', isChecked);
        },
        toggleDisableEndMuke: function () {
            var isChecked = this.$endMukeEnabled.prop('checked');
            if (! isChecked &&
                    (this.$onlyErEnabled.prop('checked') || this.$excludeSecond.prop('checked'))) {
                isChecked = true;
            }
            this.$secondEstateEnabled.prop('disabled', isChecked);
            this.$secondEstateEnabled.parent().toggleClass('is-disable', isChecked);
        },
        toggleDisableExcludeSecond: function () {
            var isChecked = this.$excludeSecond.prop('checked');
            if (! isChecked &&
                    (this.$onlyErEnabled.prop('checked') || this.$endMukeEnabled.prop('checked'))) {
                isChecked = true;
            }
            this.$secondEstateEnabled.prop('disabled', isChecked);
            this.$secondEstateEnabled.parent().toggleClass('is-disable', isChecked);
        },
        toggleDisableOwnerChange: function () {
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
        },
        publishEstateCheckCtl: function() {
            var ckcnt = 0;
            this.$publishEstate.each(function() {
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
                this.$onlyErEnabled.eq(0).prop('checked', false);
                this.$onlyErEnabled.attr('disabled', 'disabled');
                if(this.$onlyErEnabled.eq(0).parent().hasClass('is-disable') == false) {
                    this.$onlyErEnabled.eq(0).parent().addClass('is-disable');
                }
                this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').attr('disabled', false);
                this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').eq(0).parent().removeClass('is-disable');
            } else if((ckcnt == 1 && this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').prop('checked')) || ckcnt == 3 || (ckcnt != 1 && this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').prop('checked'))) {
                this.$onlyErEnabled.eq(0).prop('checked', false);
                this.$onlyErEnabled.attr('disabled', 'disabled');
                if(this.$onlyErEnabled.eq(0).parent().hasClass('is-disable') == false) {
                    this.$onlyErEnabled.eq(0).parent().addClass('is-disable');
                }
            } else {
                this.$onlyErEnabled.attr('disabled', false);
                this.$onlyErEnabled.eq(0).parent().removeClass('is-disable');
                if (this.$onlyErEnabled.prop('checked')) {
                    this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').eq(0).prop('checked', false);
                    this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').attr('disabled', 'disabled');
                    if(this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').eq(0).parent().hasClass('is-disable') == false) {
                        this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').eq(0).parent().addClass('is-disable');
                    }
                } else {
                    this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').attr('disabled', false);
                    this.$publishEstate.filter('[value="niji_kokoku_jido_kokai"]').eq(0).parent().removeClass('is-disable');
                }
            }
        },
        tesuryoCheckCtl: function() {
            if(!this.$main.find('#tesuryo_check').prop('checked')) {
                this.$main.find('.tesuryo_kokokuhi input[type=radio]').prop('checked', false).attr('disabled', 'disabled');
            } else {
                var ckcnt = 0;
                this.$main.find('.tesuryo_kokokuhi input[type=radio]').each(function() {
                    $(this).attr('disabled', false);
                    if($(this).prop('checked')) {
                        ckcnt++;
                    }
                });
                if(ckcnt == 0) {
                    this.$main.find('.tesuryo_kokokuhi input[type=radio]').eq(0).prop('checked', true);
                }
            }
        },
        getPref: function () {
            return this.$prefContainer.find(':checked').map(function () {
                return this.value;
            }).get();
        },
        renderContent: function() {
            var content = this.$body.find('#template_modal').html();
            this.$main.append(content);
        },
        showErrorEmpty: function(element) {
            element.find('.errors').html('値は必須です。');
        },
        clearErrorEmpty: function() {
            this.$main.find('.errors').html('');
        },
        validateRequired: function() {
            var self = this;
            this.clearErrorEmpty();
            var check = true;
            var required = this.$main.find('.is-required:not(.is-disable)');
            required.each(function(elem, i) {
                if ($(this).closest('#enabled_estate_type').length) {
                    if ($(this).closest('#enabled_estate_type').find('input[type="checkbox"]:checked').length == 0) {
                        self.showErrorEmpty($(this).parent());
                        check = false
                        return ;
                    }
                } else {
                    if ($(this).find(':checked').length == 0 ) {
                        self.showErrorEmpty($(this).parent());
                        check = false
                        return ;
                    }
                }
            });
            return check;
        },
        updateSetting: function() {
            var self = this;
            if (this.isLinkHouse) {
                this.invidial.updateSettingLinkHouse(this);
            }
            this.setting.area_search_filter.area_1 = this.getPref();
            this.$publishEstate.each(function () {
                var $this = $(this);
                self.setting[$this.val()] = ~~$this.prop('checked');
            });

            this.$tesuryoKokokuhi.each(function () {
                var $this = $(this);
                self.setting[$this.val()] = ~~$this.prop('checked');
            });
            this.setting.area_search_filter.search_condition['type'] = parseInt(this.$searchType.filter(':checked').val());
            if (this.$searchType.filter('[value="1"]').prop('checked')) {
                self.setting.area_search_filter.area_3 = {};
                self.setting.area_search_filter.area_4 = {};
                self.setting.area_search_filter.area_5 = {};
                self.setting.area_search_filter.area_6 = {};
            }
            if (this.$searchType.filter('[value="2"]').prop('checked')) {
                self.setting.area_search_filter.area_3 = {};
                self.setting.area_search_filter.area_4 = {};
            }
            if (this.$searchType.filter('[value="3"]').prop('checked')) {
                self.setting.area_search_filter.area_2 = {};
                self.setting.area_search_filter.area_5 = {};
                self.setting.area_search_filter.area_6 = {};
            }
            if (this.$searchType.filter('[value="4"]').prop('checked')) {
                self.setting.area_search_filter.area_1 = [];
                self.setting.area_search_filter.area_2 = {};
                self.setting.area_search_filter.area_3 = {};
                self.setting.area_search_filter.area_4 = {};
                self.setting.area_search_filter.area_5 = {};
                self.setting.area_search_filter.area_6 = {};
            }
        },
        hasShikugunModal: function() {
            return this.$searchType.filter('[value="1"]').prop('checked') || this.$searchType.filter('[value="2"]').prop('checked');
        },
        hasEnsenModal: function() {
            return this.$searchType.filter('[value="3"]').prop('checked');
        },
        onOk: function() {
            if (this.validateRequired()) {
                this.updateSetting();
                if (this.hasShikugunModal()) {
                    this.modalShikugun(this.getPref(), this.$searchType.filter('[value="2"]').prop('checked'));
                } else {
                    if (this.hasEnsenModal()) {
                        this.modalEnsen(this.getPref());
                    } else {
                        var modal = new searchFilterModal(this.invidial, null);
                    }
                }
                this.close();
            }
        }

    });

    var searchFilterModal = estate.searchFilterModal = app.inherits(ModalInvidial, function () {
        ModalInvidial.apply(this, arguments);
    },{
        template: ''+
			'<div class="modal-contents size-list modal-scroll">'+
				'<div class="modal-header">'+
					'<h2 class="js-modal-title">公開する物件を選択してください。</h2>'+
					'<div class="modal-close"><a href="javascript:;" class="btn-modal js-modal-close"><i class="i-e-delete"></i></a></div>'+
				'</div>'+
				'<div class="modal-body js-modal-contents">'+
					'<div class="modal-body-inner align-top js-modal-contents-main article-search" id="js-modal-invidial">'+
					'</div>'+
					'<div class="modal-btns js-modal-contents-btn-area">'+
						'<a class="btn-t-gray js-modal-prev" href="javascript:;">戻る</a>'+
						'<a class="btn-t-blue size-l js-modal-ok" href="javascript:;">検索結果を表示する</a>'+
					'</div>'+
				'</div>'+
            '</div>',
        init: function() {
            var self = this;
            this.$table = $('<table class="tb-basic ad-terms"></table>');
            this.$contents = this.$element.find('.js-modal-contents');
			this.$main     = this.$contents.find('.js-modal-contents-main');
            this.setTitle('絞り込み条件');
            var setting = this.invidial.setting;
            this.setData(
                setting.area_search_filter.area_2 || {},
                setting.area_search_filter.area_3 || {},
				setting.area_search_filter.area_4 || {},
				setting.area_search_filter.area_5 || {},
				setting.area_search_filter.area_6 || {},
				setting.area_search_filter.choson_search_enabled == 1);
			// loading on
			var $loading = this.createLoading();
			this.$main.html($loading);
			this.$contents.addClass('is-loading');
			var $ok = this.$element.find('.js-modal-ok').addClass('is-disable');
			var def = EstateMaster.getSpecialSearchFilter(setting.enabled_estate_type);;
			def.done(function (data) {
                self.$contents.removeClass('is-loading');
				$ok.removeClass('is-disable');
				$loading.remove();
				if (!data) {
					return;
				}
                self.render(data);
                self.$main.append(self.$table);
            });

            this.$element.on('click', '.js-modal-ok:not(.is-disable)', function () {
				self.onOk();
				return false;
			});
            this.$element.on('click', '.js-modal-prev:not(.is-disable)', function () {
                self.onPrev();
                return false;
            });
        },
        beforeNext: function () {
			var categories = [];

			// 種目は基本設定で指定済み
			var bsetting = this.invidial.setting.search_filter.categories;
			for(var cno = 0; cno < bsetting.length; cno++) {
				if(bsetting[cno]['category_id'] == 'shumoku') {
					categories.push(bsetting[cno]);
				}
			}

			this.$table.find('tr').each(function () {
				var $row = $(this);

				if($row.attr('data-category-id') == 'shumoku') {
					return true;
				}
				var category = {
					category_id: $row.attr('data-category-id'),
					items: []
				};

				var multiItemMap = {};
				$row.find('.js-search-filter-item').each(function () {
					var $this = $(this);
					var type = $this.attr('data-type');
					var itemId = $this.attr('data-item-id');
					if (type === 'list') {
						if (!parseInt($this.val())) {
							return;
						}
						category.items.push({
							item_id: itemId,
							item_value: $this.val()
						});
					}
					else if (type === 'multi') {
						if (!$this.prop('checked')) {
							return;
						}
						if (!multiItemMap[itemId]) {
							multiItemMap[itemId] = {
								item_id: itemId,
								item_value: []
							};
							category.items.push(multiItemMap[itemId]);
						}
						multiItemMap[itemId].item_value.push($this.val());
					}
					else if (type === 'radio') {
						if (!$this.prop('checked') || !parseInt($this.val())) {
							return;
						}
						category.items.push({
							item_id: itemId,
							item_value: $this.val()
						});
					}
					else {
						if (!$this.prop('checked')) {
							return;
						}
						category.items.push({
							item_id: itemId,
							item_value: 1
						});
					}
				});

				if (category.items.length) {
					categories.push(category);
				}
            });
			this.invidial.setting.search_filter.categories = categories;
		},
        onOk: function() {
            this.beforeNext();
            var modal = new estate.HouseListModal(this.invidial, null, 3);
            this.close();
            
        },
        onPrev: function() {
            this.close();
            var setting = this.invidial.setting;
            if (setting.area_search_filter.search_condition['type'] == 1) {
                this.modalShikugun(setting.area_search_filter.area_1, false)
            }
            if (setting.area_search_filter.search_condition['type'] == 2) {
                this.modalShikugun(setting.area_search_filter.area_1, true)
            }
            if (setting.area_search_filter.search_condition['type'] == 3) {
                this.modalEnsen(setting.area_search_filter.area_1, true)
            }
            if (setting.area_search_filter.search_condition['type'] == 4) {
                var modal = new conditionHouseListModal(this.invidial);
            }
        },
        render: function (data) {
			var tableHtml = '';
			var tdClassHtml = '';
			var i,il, category, $tr;
			for (i=0,il=data.categories.length;i< il;i++) {
				category = data.categories[i];
				tdClassHtml = '';
				if (category.category_id === 'madori') {
					tdClassHtml = ' class="floor-plans"';
				} else if (category.category_id === 'kitchen') {
					tdClassHtml = ' class="list-more"';
				}
                if (category.category_id === 'reform_renovation') {
                    category.label = 'リフォーム・リノベーション済/予定含む';
                }
				if (category.category_id === 'shumoku') {
					continue;
				}

				switch (category.category_id){
					case 'madori':
						tdClassHtml = ' class="floor-plans"';
						break;
					case 'kitchen':
					case 'bath_toilet':
					case 'reidanbo':
					case 'shuno':
					case 'tv_tsusin':
					case 'security':
					case 'ichi':
					case 'joken':
					case 'kyouyu_shisetsu':
					case 'setsubi_kinou':
					case 'tokucho':
					case 'koho_kozo':
					case 'other':
						tdClassHtml = ' class="list-more"';
						break;
					case 'shumoku':
						tdClassHtml = ' class="list-more" style="display:block"';
						break;
				}

				tableHtml += '<tr data-category-id="'+category.category_id+'">';
				tableHtml += '<th>'+category.label+'</th>';
				tableHtml += '<td'+tdClassHtml+'>';

				if (category.description) {
					tableHtml += '<p class="tx-annotation">'+category.description+'</p>';
				}

				if (category.category_id === 'kakaku') {
					tableHtml += this.renderKakaku(category);
				}
				else if (category.category_id === 'rimawari') {
					tableHtml += this.renderRimawari(category);
				}
				else if (category.category_id === 'menseki') {
					tableHtml += this.renderMenseki(category);
				}
				else if (category.category_id === 'reformable_parts') {
					tableHtml += this.renderReformableParts(category);
				}
				else if (category.category_id === 'chikunensu') {
					tableHtml += this.renderChikunensu(category);
				}
				else {
					tableHtml += this.renderCategory(category);
				}

				tableHtml += '</td>';
				tableHtml += '</tr>';
			}
			this.$table.html(tableHtml);

			// 価格上限の初期値を設定
			var $kakaku2 = this.$table.find('.js-search-filter-item-kakaku-2');
			$kakaku2[0].selectedIndex = $kakaku2.find('option').length - 1;

			// 面積・土地面積上限の初期値を設定
			var $menseki3 = this.$table.find('.js-search-filter-item-menseki-3');
			if($menseki3.length > 0) {
				$menseki3[0].selectedIndex = $menseki3.find('option').length - 1;
			}
			var $menseki4 = this.$table.find('.js-search-filter-item-menseki-4');
			if($menseki4.length > 0) {
				$menseki4[0].selectedIndex = $menseki4.find('option').length - 1;
			}

			// 利回り上限の初期値を設定
			var $rimawari2 = this.$table.find('.js-search-filter-item-rimawari-2');
			if($rimawari2.length > 0) {
                $rimawari2.val('0');
			}

			// 築年数上限の初期値を設定
			var $chikunensuTo = this.$table.find('#chikunensuTo');
			if($chikunensuTo.length > 0) {
				$chikunensuTo.val('0');
			}

			//契約条件初期値設定
			var $keiyaku_joken = this.$table.find('.js-search-filter-item-keiyaku_joken-1');
			if ($keiyaku_joken[0]) {
				$keiyaku_joken.val(0);
			}

			// ラジオの初期値を設定
			this.$table.find('input:radio[value="0"]').prop('checked', true);
			this.restore(data);
        },
        restore: function (data) {
			var setting = this.invidial.setting;
			var i,il, category, categoryMaster;
			var j,jl, item, itemMaster, $item;
			var k,kl, val;
			for (i=0,il=setting.search_filter.categories.length;i< il;i++) {
				category = setting.search_filter.categories[i];
				categoryMaster = data.categoryMap[category.category_id];
				if (!categoryMaster) {
					continue;
				}

				for (j=0,jl=category.items.length;j< jl;j++) {
					item = category.items[j];
					itemMaster = categoryMaster.itemMap[item.item_id];
					if (!itemMaster) {
						continue;
					}

					$item = this.$table.find('.js-search-filter-item-'+category.category_id+'-'+item.item_id);
					if (itemMaster.type === 'list') {
						if (itemMaster.elementType === 'radio') {
							$item.filter('[value="'+(item.item_value || 0)+'"]').prop('checked', true);
						}
						else {
							$item.val(item.item_value);
						}
					}
					else if (itemMaster.type === 'multi') {
						for (k=0,kl=item.item_value.length;k< kl;k++) {
							val = item.item_value[k];
							try {
								$item.filter('[value="'+val+'"]').prop('checked', true).trigger("change", [true]);;
							}
							catch (err) {}
						}
					}
					else if (itemMaster.type === 'radio') {
						try {
							$item.filter('[value="'+(item.item_value || 0)+'"]').prop('checked', true);
						}
						catch (err) {}
					}
					else {
						$item.prop('checked', true);
					}
				}
			}
			// 築年数上限の初期値を設定
			var $chikunensuTo = this.$table.find('#chikunensuTo');
			if($chikunensuTo.length > 0) {
				$chikunensuTo.closest('.chikunensu_block').find("input[name='chikunensu_1[]']").each(function() {
					if($(this).parent().text().match(/\d{1,2}/)) {
						if($(this).prop('checked')) {
							$chikunensuTo.val($(this).val());
						}
						
					}
				});
			}
            var rpCatBlock = this.$table.find('.rp_cat_block');
            if (rpCatBlock.length > 0) {
                rpCatBlock.each(function(element, index) {
                    var check = $(this).find('label input:checked');
                    if (check.length > 0 || ($.inArray(index, rpOtherCheck) > -1)) {
                        $(this).find('.rp_cb_summary').prop('checked', true);
                    }
                });
            }

		},

		renderKakaku: function (category) {
			var kakakuHtml = '';
			kakakuHtml += this.renderListItem(category, category.items[0]);
			kakakuHtml += ' ～ ';
			kakakuHtml += this.renderListItem(category, category.items[1]);

			kakakuHtml += '<br>';

			for (var i=2,l=category.items.length;i< l;i++) {
				kakakuHtml += this.renderFlagItem(category, category.items[i]);
				kakakuHtml += '<br>';
			}

			return kakakuHtml;
		},

        renderRimawari: function (category) {
			return this.renderKakaku(category);
		},

		renderMenseki: function (category) {
			var mensekiHtml = '<dl class="dl-inlineb">';

			var mensekiHtml1 = '';      //専有面積
			var mensekiHtml2 = '';      //土地

			if (category.items[1]) {
				for (var i=0,l=category.items.length;i< l;i++) {
					var itemId = category.items[i].item_id;

					if(itemId == "1") {
						mensekiHtml1 += '<dt>面積<span class="tx-annotation">（建物面積・専有面積・使用部分面積）</span></dt>';
						mensekiHtml1 += '<dd>' + this.renderItem(category, category.items[i]) + '</dd>';
					} else if(itemId == "3") {
						mensekiHtml1 += '<dd>&nbsp;～&nbsp;' + this.renderItem(category, category.items[i]) + '</dd>';
					} else if(itemId == "2") {
						mensekiHtml2 += '<dt>' + category.items[i].label + '　</dt>';
						mensekiHtml2 += '<dd>' + this.renderItem(category, category.items[i]) + '</dd>';
					} else {
						mensekiHtml2 += '<dd>&nbsp;～&nbsp;' + this.renderItem(category, category.items[i]) + '</dd>';
					}
				}

				mensekiHtml += mensekiHtml1;
				if(mensekiHtml1 != "" && mensekiHtml2 != "") {
					mensekiHtml += "　/　";
				}
				mensekiHtml += mensekiHtml2;

				mensekiHtml += '</dl>';
			} else {
				mensekiHtml += this.renderItem(category, category.items[0]);
			}
			return mensekiHtml;
		},

		renderTesuryo: function (category) {
			var category2 = JSON.parse(JSON.stringify(category));

			if((category2.items.length) == 1) {
				var tesuryoHtml = this.renderCategory(category);
				tesuryoHtml = tesuryoHtml.replace(/（\S+）/, '');
				return tesuryoHtml;
			}

			var regexp = /（(\S+)）$/;
            for(var i=0; i<category2.items.length;i++) {
                var tmpLabel = category2.items[i].label;
                var match = regexp.test(tmpLabel);
                category2.items[i].label = RegExp.$1;
            }

            var tesuryoHtml = this.renderCategory(category2);
            tesuryoHtml = tesuryoHtml.replace(/checkbox/g, 'radio');
            tesuryoHtml = '<div class="tesuryo_box"><label><input type="checkbox" name="tesuryo_use"/>手数料ありのみ</label>' + ' ( ' + tesuryoHtml + ' )</div>';

            var classNo = $(".main-contents h1").attr('estateclass');
            var elem = $(tesuryoHtml);
            $(elem).find("input:radio").attr('disabled', 'disabled');
            if(classNo == 1 || classNo == 2) {  // 賃貸の時は分かれを非活性するだけ
                $(elem).find("label").eq(1).addClass('is-disable');
            }
            tesuryoHtml = jQuery('<div>').append(elem.clone(true)).html();

            return tesuryoHtml;
		},

		renderCategory: function (category) {
			var categoryHtml = '';
			for (var i=0,l=category.items.length;i< l;i++) {
				categoryHtml += this.renderItem(category, category.items[i]);
			}
			return categoryHtml;
		},

		renderReformableParts: function (category) {
			// リフォーム箇所選択有無チェック
			var setting = this.setting;
			var selectFlg = false;
			for(var i=0; i<setting.search_filter.categories.length;i++) {
 				if(setting.search_filter.categories[i].category_id == 'reformable_parts') {
					selectFlg = true;
				}
			}

			// フォーム書き出し
			var reformablePartsHtml = '';

			reformablePartsHtml += '<div class="reformable_parts_block">';

			// 指定有無のradio
			if(selectFlg) {
				reformablePartsHtml += '<input type="radio" name="rp_use" value="1">指定なし' + "　　";
				reformablePartsHtml += '<input type="radio" name="rp_use" value="2" checked>リフォーム可能箇所' + "　　";
				reformablePartsHtml += '<a class="rp_detail_display" style="cursor: pointer;">詳細な設定を選ぶ</a>';
			} else {
				reformablePartsHtml += '<input type="radio" name="rp_use" value="1" checked>指定なし' + "　　";
				reformablePartsHtml += '<input type="radio" name="rp_use" value="2">リフォーム可能箇所' + "　　";
				reformablePartsHtml += '<a class="rp_detail_display is-disable">詳細な設定を選ぶ</a>';
			}

			// 指定箇所詳細:Start
			reformablePartsHtml += '<div class="rp_detail_block" style="display:none;">';

			for (var i=0,l=category.items.length;i< l;i++) {
				reformablePartsHtml += '<div class="rp_cat_block">';
				// サマリのcheckbox作成
				var rpCb = $('<input>').attr({ 'type':'checkbox', 'class':'rp_cb_summary' });
				reformablePartsHtml += '<b>' +  $(rpCb).prop('outerHTML') + category.items[i]['label'] + '</b>';
				reformablePartsHtml += '<br/>';
				if(category.items[i].options.length == 1) {
					reformablePartsHtml += '<div style="display:none;">';
					reformablePartsHtml += this.renderItem(category, category.items[i]);
					reformablePartsHtml += '</div>';
				} else {
					reformablePartsHtml += this.renderItem(category, category.items[i]);
				}
				reformablePartsHtml += '</div>';
			}

			reformablePartsHtml += '</div>';
			// 指定箇所詳細:End

			reformablePartsHtml += '</div>';
			return reformablePartsHtml;
		},

		renderChikunensu: function (category) {

			var chikunensuHtml = '';
			var chikunensuHtmlLast = '';
			var chikunensuLabels = '';

			chikunensuHtml += '<div class="chikunensu_block">';
			for (var i=0,l=category.items.length;i< l;i++) {
				var item_id = category.items[i].item_id;
				switch(item_id) {
					case 1:
						chikunensuLabels = this.renderItem(category, category.items[i]);
						$('<div>').append($(chikunensuLabels)).find('label').each(function() {
							if($(this).find('input').eq(0).val() == "0") {
								$(this).css('display', 'none');
							} else if($(this).text().match(/\d{1,2}/)) {
								$(this).css('display', 'none');
							}
							if($(this).text() == "新築を除く") {
								chikunensuHtmlLast += jQuery('<div>').append($(this)).html();
							} else {
								chikunensuHtml += jQuery('<div>').append($(this)).html();
							}
						});
						break;
					case 2:
						chikunensuHtml += '<div class="chikunensu_sel">';
						chikunensuHtml += this.renderItem(category, category.items[i]);
						break;

				}
			}
			chikunensuHtml += ' ～ ';

			var chikunensuTo = $('<select>').attr({
				id: 'chikunensuTo',
				class: "select-inlineb"
			});
			$('<div>').append($(chikunensuLabels)).find('label').each(function() {
				var str = $(this).text();
				var val = $(this).find('input').eq(0).val();
				if(str.match(/\d{1,2}/)) {
					$(chikunensuTo).append($('<option>').attr({value: val}).text(str));
				}
			});
			$(chikunensuTo).append($('<option>').attr({value:"0"}).text('上限なし'));

			chikunensuHtml += jQuery('<div>').append(chikunensuTo).html();

			chikunensuHtml += '</div>';

			chikunensuHtml += chikunensuHtmlLast;

			chikunensuHtml += '</div>';
			return chikunensuHtml;
		},

		renderItem: function (category, item) {
			switch (item.type) {
				case 'list':
					return this.renderListItem(category, item);
				case 'radio':
					return this.renderRadioItem(category, item);
				case 'multi':
					return this.renderMultiItem(category, item);
				default:
					return this.renderFlagItem(category, item);
			}
		},

		renderListItem: function (category, item) {
			var itemHtml = '';
			var opt;

			itemHtml += '<select class="select-inlineb js-search-filter-item js-search-filter-item-'+category.category_id+'-'+item.item_id+'" data-type="'+item.type+'" data-item-id="'+item.item_id+'">';
			for (var i=0,l=item.options.length;i< l;i++) {
				opt = item.options[i];

				if(category.category_id == 'menseki') {
					var dispay_label = '';
					dispay_label = app.h(opt.label);
					if(item.item_id == 1 || item.item_id == 2) {
						dispay_label = dispay_label.replace('以上', '');
						dispay_label = dispay_label.replace('指定なし', '下限なし');
					} else if(item.item_id == 3 || item.item_id == 4) {
						dispay_label = dispay_label.replace('指定なし', '上限なし');
					}
					itemHtml += '<option value="'+app.h(opt.value)+'">'+dispay_label+'</option>';
				} else {
					itemHtml += '<option value="'+app.h(opt.value)+'">'+app.h(opt.label)+'</option>';
				}
			}
			itemHtml += '</select>';
			return itemHtml;
		},
		renderRadioItem: function (category, item) {
			var itemHtml = '';
			var opt;

			for (var i=0,l=item.options.length;i< l;i++) {
				opt = item.options[i];
				itemHtml += this._renderCheckItem(category, item, 'radio', opt);
			}
			return itemHtml;
		},
		renderMultiItem: function (category, item) {
			var itemHtml = '';
			var opt;
			for (var i=0,l=item.options.length;i< l;i++) {
				opt = item.options[i];
				itemHtml += this._renderCheckItem(category, item, 'checkbox', opt, true);
			}
			return itemHtml;
		},
		renderFlagItem: function (category, item) {
			return this._renderCheckItem(category, item, 'checkbox', {value:1,label:item.label});
		},
		_renderCheckItem: function (category, item, type, opt, isMulti) {
			var nameSuffix = isMulti ? '[]' : '';
			return ''+
				'<label>'+
					'<input data-type="'+item.type+'" type="'+type+'" value="'+opt.value+'" '+
						'class="js-search-filter-item js-search-filter-item-'+category.category_id+'-'+item.item_id+'" '+
						'data-item-id="'+item.item_id+'" '+
						'name="'+category.category_id+'_'+item.item_id+nameSuffix+'" '+
						'>'+
						app.h(opt.label)+
				'</label>';
		},
    });

    var confirmModal = estate.confirmModal = app.inherits(ModalInvidial, function () {
        ModalInvidial.apply(this, arguments);
    },{
        template: ''+
        '<div class="modal-contents size-list modal-scroll">'+
            '<div class="modal-header">'+
                '<h2 class="js-modal-title">公開する物件を選択してください。</h2>'+
                '<div class="modal-close"><a href="javascript:;" class="btn-modal js-modal-confirm-close"><i class="i-e-delete"></i></a></div>'+
            '</div>'+
            '<div class="modal-body js-modal-contents main-contents ">'+
                '<div class="modal-body-inner align-top js-modal-contents-main article-search" id="js-modal-invidial">'+
                    '<div class="section js-confirm-special-basic-setting">' +
				        '<h2>基本設定</h2>' +
			        '</div>' +

                    '<div class="section confirm-area js-confirm-shikuguns">' +
                        '<h2>市区郡</h2>'+
                        '<span>※ 町名の一部が選択されている場合、町名の右端に<font color="#0747a6">*</font>が表示されます。マウスオーバーすることで選択されている町名が表示されます。</span>' +
                    '</div>' +

                   ' <div class="section confirm-station js-confirm-ensens">' +
                        '<h2>沿線・駅</h2>' +
                    '</div>' +

                    '<div class="section js-confirm-special-search-filter">' +
                        '<h2>絞り込み条件</h2>' +
                   ' </div>' +
                '</div>'+
                '<div class="modal-btns js-modal-contents-btn-area">'+
                    '<a class="btn-t-gray js-modal-prev" href="javascript:;">戻る</a>'+
                '</div>'+
            '</div>'+
        '</div>',
        init: function() {
            var self = this;
			this.specialBasicSetting = new app.estate.ConfirmHouseConditionModalBasicView(this.invidial.Master);
			this.$element.find('.js-confirm-special-basic-setting').append(this.specialBasicSetting.$element);

			this.confirmShikugun = new app.estate.ConfirmShikugunView(this.invidial.Master);
			this.$confirmShikugun = this.$element.find('.js-confirm-shikuguns').append(this.confirmShikugun.$element);

			this.confirmEnsen = new app.estate.ConfirmEnsenView(this.invidial.Master);
			this.$confirmEnsen = this.$element.find('.js-confirm-ensens').append(this.confirmEnsen.$element);

			this.specialSearchFilter = new app.estate.ConfirmSpecialSearchFilterView();
            this.$element.find('.js-confirm-special-search-filter').append(this.specialSearchFilter.$element);
            this.render();
            this.setTitle('条件の確認画面');
            this.$element.on('click', '.js-modal-prev', function() {
                self.onPrev();
                return;
            });
            this.$element.on('click', '.js-modal-confirm-close', function() {
                $('#js-modal-invidial').closest('.modal-set').remove()
                self.close();
                if (self.isLinkHouse && self.invidial.OldModal) {
                    self.invidial.showModal();
                }
                return;
            });
        },
        render: function() {
            
            var setting = this.invidial.setting;
            this.specialBasicSetting.render(setting, true);
            if (this.hasShikugun(setting)) {
                this.$confirmEnsen.hide();
                this.confirmShikugun.render(setting, true);
                this.$confirmShikugun.show();
            } else {
                this.$confirmShikugun.hide();
            }
            if (this.hasEnsen(setting)) {
                this.$confirmShikugun.hide();
                this.confirmEnsen.render(setting);
                this.$confirmEnsen.show();
            } else {
                this.$confirmEnsen.hide();
            }
            this.specialSearchFilter.render(setting);
            this.$element.find('.js-confirm-special-search-filter').show();
        },
        hasShikugun: function(setting) {
            return setting.area_search_filter.search_condition['type'] == 1
                    || setting.area_search_filter.search_condition['type'] == 2;
        },
        hasEnsen: function(setting) {
            return setting.area_search_filter.search_condition['type'] == 3;
        },
        onPrev: function() {
            this.close();
            $('.modal-set').show();
        }
    });
	var Modal = estate.Modal = function (pref, data, allowed, stepContent) {
		this._deferred = $.Deferred();
		this.promise = this._deferred.promise();

		this.pref = pref;
		this.data = data;
        this.allowed = allowed;
        this.stepContent = stepContent;

		this.$element = $(this.template);
		this.modal = app.modal(this.$element);
		this.modal.show();

		var self = this;
		this.updateSize = function () {
			var hsize = $(window).height() - 200;
			self.$element.find('.modal-body-inner').css('max-height', hsize + 'px');
		};
		$(window).on('resize', this.updateSize).resize();

		this.initClose();
		this.initCheckAll();
		this.init(data);
	};
	Modal.prototype.template = '<div/>';
	Modal.prototype.selectGroupTemplate = ''+
		'<div class="area-set js-estate-select-group">'+
			'<h3 class="heading-area"><label class="js-estate-select-group-check"></label></h3>'+
			'<ul class="js-estate-select-group-container">'+
			'</ul>'+
        '</div>';
    Modal.prototype.selectGroupPrefsTemplate = ''+
    '<div class="pref-set js-estate-select-group-pref">'+
        '<h3 class="heading-pref"><label class="js-estate-select-group-check-pref"></label></h3>'+
    '</div>';
	Modal.prototype.init = function () {};
	Modal.prototype.setTitle = function (title, shikugunCd) {
        if (!$.isArray(this.pref.name)) {
            this.$element.find('.js-modal-title').text(this.pref.name + '：' + title);
        } else {
            if (shikugunCd) {
                var prefCode = shikugunCd.slice(0, 2);
                var prefName = this.stepContent.Master.prefMaster[prefCode];
                this.$element.find('.js-modal-title').text(prefName + '：' + title);
            } else {
                this.$element.find('.js-modal-title').text(title);
            }
        }
	};
	Modal.prototype.initClose = function () {
		var self = this;
		this.$element.on('click', '.js-modal-close', function () {
			self._deferred.reject();
            self.close();
            if (typeof self.stepContent.OldModal != 'undefined' && self.stepContent.OldModal) {
                self.stepContent.showModal();
            }
			return false;
		});
	};
	Modal.prototype.initCheckAll = function () {
		this.$element.on('change', '.js-estate-select-group-check input', function () {
			var $this = $(this);
			$this.closest('.js-estate-select-group').find('.js-estate-select-group-container input:not(:disabled)').prop('checked', $this.prop('checked'));
		});
		this.$element.on('click', '.show-choaza-all', function () {
			var linkStr = $(this).text();
			switch(linkStr) {
				case '詳細を表示する':
					$(this).text('詳細を隠す');
					$(this).closest('.js-estate-select-group').find('.choaza-list').show()
					break;
				case '詳細を隠す':
					$(this).text('詳細を表示する');
					$(this).closest('.js-estate-select-group').find('.choaza-list').hide()
					break;
			}
			return;
		});
		this.$element.on('change', '.js-estate-select-group-container input', function () {

			// 子要素があれば制御
			if($(this).attr('name') == '_estate-select[]' && $(this).closest('li').find("input[name='_estate-select-aza[]']").length) {
				var pChecked = $(this).prop('checked');
				$(this).closest('li').find("input[name='_estate-select-aza[]']").each(function() {
					if($(this).is(':disabled')) {
						$(this).prop('checked', false);
						return true;
					}
					$(this).prop('checked', pChecked);
				});


			} else if($(this).attr('name') == '_estate-select-aza[]') {
				var all_aza_sel = true;
				var no_aza_sel  = true;
				$(this).closest('div').find("input[name='_estate-select-aza[]']").each(function() {
					if($(this).is(':disabled')) {
						return true;
					}
					if($(this).prop('checked') == true) {
						no_aza_sel = false;
					} else {
						all_aza_sel = false;
					}
				});

				if(all_aza_sel == true) {
					$(this).closest('li').find("input[name='_estate-select[]']").prop('checked', true);
				} else {
					$(this).closest('li').find("input[name='_estate-select[]']").prop('checked', false);
				}
            }
			var $group = $(this).closest('.js-estate-select-group');
			var $all = $group.find('.js-estate-select-group-check input');
			$all.prop('checked', !$group.find('.js-estate-select-group-container input:not(:disabled):not(:checked)').length);
		});
	};
	Modal.prototype.isAllowed = function (name, code) {
		return !!(!this.allowed[name] || this.allowed[name][code]);
	};
    Modal.prototype.isAllowedEkis = function (pref, code) {
        return !!(!this.allowed['pref_ekis'] || !this.allowed['pref_ekis'][pref] || this.allowed['pref_ekis'][pref][code]);
};
	Modal.prototype.isAllowedEnsens = function (pref, code) {
			return !!(!this.allowed['pref_ensens'] || !this.allowed['pref_ensens'][pref] || this.allowed['pref_ensens'][pref][code]);
	};
    Modal.prototype.isAllowedChosons = function (shikugunCd, chosonCd) {
        return !!(!this.allowed['shikugun_chosons'] || !this.allowed['shikugun_chosons'][shikugunCd] || this.allowed['shikugun_chosons'][shikugunCd][chosonCd]);
    };
    Modal.prototype.isAllowedChoazas = function (shikugunCd, chosonCd, choazaCd) {
        if(!this.allowed['choazas']) {
            return 0;
        }
        if(this.allowed['choazas'][shikugunCd] !== undefined && this.allowed['choazas'][shikugunCd][chosonCd] !== undefined) {
            if(this.allowed['choazas'][shikugunCd][chosonCd].indexOf(choazaCd) >= 0) {
                return 1;
            } else {
                return -1;
            }
        }
        return 0;
    };
	Modal.prototype.validateRequired = function ($container) {
		$container = $container || this.$element;
		return !!$container.find('.js-estate-select-group-container input:checked').length;
	};
	Modal.prototype.validateGroupChecked = function ($container) {
		$container = $container || this.$element;
		var $groupContainers = $container.find('.js-estate-select-group-container');
		for (var i=0,l=$groupContainers.length;i<l;i++) {
			if (!$groupContainers.eq(i).find('input:checked').length) {

				return false;
			}
		}
		return true;
    };
    Modal.prototype.validateGroupCheckedPrefs = function ($container) {
		$container = $container || this.$element;
		var $groupContainers = $container.find('.js-estate-select-group-pref');
		for (var i=0,l=$groupContainers.length;i<l;i++) {
			if (!$groupContainers.eq(i).find('input:checked').length) {

				return false;
			}
		}
		return true;
	};
	Modal.prototype.getCheckedCode = function ($container) {
        $container = $container || this.$element;
		return $container.find('.js-estate-select-group-container input:checked').map(function () {
			return this.value;
		}).get();
	};
	Modal.prototype.showError = function (message) {
		app.modal.alert('', message);
	};
	Modal.prototype.clearError = function () {
	};
	Modal.prototype.close = function () {
		$(window).off('resize', this.updateSize);
		this.$element.off();
		this.modal.close();
		this.modal = null;
	};
	Modal.prototype.createLoading = function () {
		return $('<div class="loading"><p><img alt="" src="/images/common/loading.gif"></p></div>');
	};

	var ShikugunModal = estate.ShikugunModal = app.inherits(Modal, function () {
		Modal.apply(this, arguments);

	}, {
		template: ''+
			'<div class="modal-contents size-l modal-scroll">'+
				'<div class="modal-header">'+
					'<h2 class="js-modal-title"></h2>'+
					'<div class="modal-close"><a href="javascript:;" class="btn-modal js-modal-close"><i class="i-e-delete"></i></a></div>'+
				'</div>'+
				'<div class="modal-body js-modal-contents">'+
					'<div class="modal-body-inner align-top js-modal-contents-main">'+
					'</div>'+
					'<div class="modal-btns js-modal-contents-btn-area">'+
						'<a class="btn-t-gray js-modal-close" href="javascript:;">戻る</a>'+
						'<a class="btn-t-blue size-l js-modal-ok" href="javascript:;">決定する</a>'+
					'</div>'+
				'</div>'+
			'</div>',

		init: function (data) {
			var self = this;

			this.checks = {};
			data = data || [];
			for (var i=0,l=data.length;i<l;i++) {
				this.checks[ data[i] ] = true;
			}

			// タイトル
			this.setTitle('市区郡を選択してください。');

			this.$contents = this.$element.find('.js-modal-contents');
			this.$main     = this.$contents.find('.js-modal-contents-main');


			// 選択項目を表示
			this.renderMain();

			// ボタン
			this.$element.on('click', '.js-modal-ok:not(.is-disable)', function () {
				self.onOk();
				return false;
			});
		},

		renderMain: function () {
			var self = this;
			// loading on
			var $loading = this.createLoading();
			this.$main.html($loading);
			this.$contents.addClass('is-loading');
			var $ok = this.$element.find('.js-modal-ok').addClass('is-disable');
			EstateMaster.getShikugun(this.pref.code).done(function (data) {
				// loading off
				self.$contents.removeClass('is-loading');
				$ok.removeClass('is-disable');
				$loading.remove();

				var $tmp = $('<ul/>');
				var i,il, group, $group;
				var j,jl, item, groupContents;
				var checkStr;
				for (i=0,il=data.locate_groups.length;i<il;i++) {
					group = data.locate_groups[i];
					$group = $(self.selectGroupTemplate);
					$group.find('.js-estate-select-group-check').html('<input type="checkbox" />'+app.h(group.locate_nm));
					groupContents = '';
					for (j=0,jl=group.shikuguns.length;j<jl;j++) {
						item = group.shikuguns[j];

						if (!self.isAllowed('shikuguns', item.code)) {
							continue;
						}

						checkStr = self.checks[ item.code ] ? ' checked' : '';
						groupContents += '<li><label><input type="checkbox"'+checkStr+' value="'+app.h(item.code)+'" name="_estate-select[]">'+app.h(item.shikugun_nm)+'</label></li>';
					}
					if (groupContents) {
						$group.find('.js-estate-select-group-container').html(groupContents);
						$tmp.append($group);
					}
				}

				self.$main.html($tmp.html());
				self.$main.find('.js-estate-select-group-container').each(function () {
					$(this).find('input:first').change();
				});
			});
		},

		onOk: function () {
			if (!this.validateRequired()) {
				this.showError('市区郡が選ばれていません。');
				return false;
			}

			this.clearError();

			this._deferred.resolve(this.getCheckedCode());
			this.close();
		}

    });

    var ShikugunChosonModal = estate.ShikugunChosonModal = app.inherits(Modal, function () {
        Modal.apply(this, arguments);

    }, {
        template: ''+
        '<div class="modal-contents size-l modal-scroll modal-invidial">'+
        '<div class="modal-header">'+
        '<h2 class="js-modal-title"></h2>'+
        '<div class="modal-close"><a href="javascript:;" class="btn-modal js-modal-close"><i class="i-e-delete"></i></a></div>'+
        '</div>'+
        '<div class="modal-body js-modal-contents">'+
        '<div class="modal-body-inner align-top js-modal-contents-main">'+
        '</div>'+
        '<div class="modal-btns js-modal-contents-btn-area">'+
        '<a class="btn-t-gray js-modal-close" href="javascript:;">戻る</a>'+
        '<a class="btn-t-blue size-l js-modal-ok" href="javascript:;">決定する</a>'+
        '</div>'+
        '</div>'+
        '<div class="modal-body js-modal-contents" style="display:none;">'+
        '<div class="modal-body-inner align-top js-modal-contents-main">'+
        '</div>'+
        '<div class="modal-btns js-modal-contents-btn-area">'+
        '<a class="btn-t-gray js-modal-prev" href="javascript:;">戻る</a>'+
        '<a class="btn-t-blue size-l js-modal-select-choson" href="javascript:;">決定する</a>'+
        '</div>'+
        '</div>'+
        '</div>',

        init: function (data) {
            var self = this;

            this.CHOSON_ALL = '全町名選択中';
            this.CHOSON_SELECTED = '町名選定済み';

            // 町名データ取得用Deferred
            this._chosonDeferred = null;

            // 選択する市区群
			this.currentShikugun = null;

            // データセット
            this.shikuguns = [];
            this.chosons = {};
            this.choazas = {};
            this.checks = {
                shikuguns: {},
                chosons: {},
                choazas: {}
            };
            this.searchChosonEnabled = data.searchChosonEnabled;
            this.setShikuguns( data.shikuguns || [] );
            this.setChosons( data.chosons || {} );
            this.setChoazas( data.choazas || {} );

            // タイトル
            this.setShikugunTitle();

            this.contents = [];
            this.$contents = this.$element.find('.js-modal-contents');
            this.$shikugunContents = this.$contents.eq(0);
            this.$chosonContents   = this.$contents.eq(1);
            this.$main     = this.$contents.find('.js-modal-contents-main')
            this.$shikugunMain = this.$main.eq(0);
            this.$chosonMain   = this.$main.eq(1);

            // 選択項目を表示
            this.renderShikugun();

            // 町名選択表示
			this.$element.on('change', '.js-modal-contents-main:eq(0) input:checkbox', function () {
                self.setShikuguns(self.getCheckedCode(self.$shikugunMain));
				var newChosons = {};
				var newChoazas = {};
                $.each(self.shikuguns, function (i, shikugunCd) {
                    if (self.chosons[ shikugunCd ] && self.chosons[ shikugunCd ].length) {
                        // 町名一部選択
                        newChosons[ shikugunCd ] = self.chosons[ shikugunCd ]
                    } else if(self.allowed !== undefined && self.allowed.shikugun_chosons !== undefined && self.allowed.shikugun_chosons[ shikugunCd ]) {
                        // 特集時は、町名未選択の場合、基本設定でフィルターされている可能性があるためそれを継承
                        newChosons[ shikugunCd ] = [];
                        $.each(self.allowed.shikugun_chosons[ shikugunCd ], function (chosonCode, flg) {
                            if(flg) { // 全部trueのはずだけど･･･
                                newChosons[ shikugunCd ].push(chosonCode);
                            }
                        });
                    }

					if (self.choazas[ shikugunCd ] && Object.keys(self.choazas[ shikugunCd ]).length > 0) {
						// 町字一部選択
						newChoazas[ shikugunCd ] = self.choazas[ shikugunCd ];
                    } else if(self.allowed !== undefined && self.allowed.choazas !== undefined && self.allowed.choazas[ shikugunCd ]) {
                        // 特集時は、町名詳細未選択の場合、基本設定でフィルターされている可能性があるためそれを継承
                        newChoazas[ shikugunCd ] = self.allowed.choazas[ shikugunCd ];
                    }
				});
                self.setChosons(newChosons);
				self.setChoazas(newChoazas);

				self.rerenderShikugun();
			});

            // ボタン
            this.$element.on('click', '.select-choson:not(.is-disable) a', function () {
                self.next($(this).attr('data-shikugun_cd'));
                return false;
            });
            this.$element.on('click', '.js-modal-prev', function () {
                self.prev();
                return false;
            });
            this.$element.on('click', '.js-modal-select-choson:not(.is-disable)', function () {
                self.selectChoson();
                return false;
            });
            this.$element.on('click', '.js-modal-next:not(.is-disable)', function () {
                self.onNext();
                return false;
            });
            this.$element.on('click', '.js-modals-shikugun-prev:not(.is-disable)', function () {
                self.onPrev();
                return false;
            });

            if ($.isArray(this.pref.code)) {
                this.$element.find('.modal-btns .js-modal-ok')
                .removeClass('js-modal-ok')
                .addClass('js-modal-next')
                .html('次の設定に進む');

                this.$element.find('.modal-btns .js-modal-close')
                .removeClass('js-modal-close')
                .addClass('js-modals-shikugun-prev')
            }
            this.$element.on('click', '.js-modal-ok:not(.is-disable)', function () {
                self.onOk();
                return false;
            });
        },

        setShikuguns: function (codes) {
            this._setCodes('shikuguns', codes);
        },
        setChosons: function (codes) {
        	var name = 'chosons';
            this[name] = $.extend({}, codes);
            this.checks[name] = {};
			for (var shikugun_cd in codes) {
                this.checks[name][shikugun_cd] = {};
				for (var i=0,l=codes[shikugun_cd].length;i<l;i++) {
					this.checks[name][shikugun_cd][ codes[shikugun_cd][i] ] = true;
				}
			}
        },
        setChoazas: function (codes) {
            this._setCodes('choazas', codes);
        	// var name = 'choazas';
            // this[name] = codes;
        },
        _setCodes: function (name, codes) {
            this[name] = codes;
            this.checks[name] = {};
			for (var i=0,l=codes.length;i<l;i++) {
				this.checks[name][ codes[i] ] = true;
			}
        },

        setShikugunTitle: function () {
            this.setTitle('市区郡を選択してください。');
        },
        setChosonTitle: function (shikugunCd) {
            this.setTitle('町名を選択してください。', shikugunCd);
        },

		hasSelectedChoson: function (shikugunCd) {
            // ベース設定で町名設定されている、または
            // 町名設定されている
            return (this.allowed.shikugun_chosons && this.allowed.shikugun_chosons[shikugunCd]) ||
		            (this.chosons[ shikugunCd ] && this.chosons[ shikugunCd ].length)

        },

        renderShikugun: function () {
            var self = this;
            var $main = this.$shikugunMain;
            // loading on
            var $loading = this.createLoading();
            $main.html($loading);
            $main.toggleClass('hide-select-choson', !this.searchChosonEnabled);
            this.$shikugunContents.addClass('is-loading');
            var $next = this.$element.find('.js-modal-next').addClass('is-disable');
            EstateMaster.getShikugun(this.pref.code).done(function (data) {
                // loading off
                self.$shikugunContents.removeClass('is-loading');
                $next.removeClass('is-disable');
                $loading.remove();

                var $tmp = $('<ul/>');
                var $tmpGroup = $('<div/>');
                var i,il, group, $group;
                var j,jl, item, groupContents, itemContent;
                var isChecked, checkStr;
                for (var key in data) {
                    for (i=0,il=data[key].locate_groups.length;i<il;i++) {
                        group = data[key].locate_groups[i];
                        $group = $(self.selectGroupTemplate);
                        $group.find('.js-estate-select-group-check').html('<input type="checkbox" />'+app.h(group.locate_nm));
                        groupContents = '';
                        for (j=0,jl=group.shikuguns.length;j<jl;j++) {
                            item = group.shikuguns[j];
                            itemContent = '';
    
                            if (!self.isAllowed('shikuguns', item.code)) {
                                continue;
                            }
    
                            isChecked = !!self.checks.shikuguns[ item.code ];
                            checkStr = isChecked ? ' checked' : '';
                            itemContent += '<li>';
                            itemContent += '<label><input type="checkbox"'+checkStr+' value="'+app.h(item.code)+'" name="_estate-select[]">'+app.h(item.shikugun_nm)+'</label>';
                            if (isChecked) {
                                // 一部選択
                                if (self.hasSelectedChoson(item.code)) {
                                    itemContent += '<div class="select-choson select-choson--selected"><a class="" data-shikugun_cd="'+item.code+'">' + self.CHOSON_SELECTED + '</a></div>';
                                } else {
                                    itemContent += '<div class="select-choson"><a class="" data-shikugun_cd="'+item.code+'">' + self.CHOSON_ALL + '</a></div>';
                                }
                            } else {
                                itemContent += '<div class="select-choson is-disable"><a class="" data-shikugun_cd="'+item.code+'">' + self.CHOSON_ALL + '</a></div>';
                            }
                            itemContent += '</li>';
                            groupContents += itemContent;
                        }
                        if (groupContents) {
                            $group.find('.js-estate-select-group-container').html(groupContents);
                            $tmp.append($group);
                        }
                    }
                    if ($.isArray(self.pref.code)) {
                        var $groupPref = $(self.selectGroupPrefsTemplate);
                        $groupPref.find('.heading-pref').html(self.pref.name[key]);
                        $groupPref.append($tmp)
                        $tmpGroup.append($groupPref);
                        $tmp = $('<ul/>');
                    }
                }
                if ($.isArray(self.pref.code)) {
                    $main.html($tmpGroup.html());
                } else {
                    $main.html($tmp.html());
                }
                $main.find('.js-estate-select-group-container').each(function () {
                    $(this).find('input:first').change();
                });
            });
        },

        renderChoson: function () {
            var self = this;
            var $main = this.$chosonMain;
            $main.empty();

            // loading on
            var $loading = this.createLoading();
            $main.html($loading);
            var $ok = this.$element.find('.js-modal-select-choson').addClass('is-disable');
            this.$chosonContents.addClass('is-loading');

            // 駅一括取得
			var currentShikugun = this.currentShikugun;
            var dfd = this._chosonDeferred = EstateMaster.getChoson([this.currentShikugun]);
            dfd.done(function (data) {
                if (
                	dfd !== self._chosonDeferred ||
					currentShikugun !== self.currentShikugun
				) {
                    return;
                }

                // loading off
                $ok.removeClass('is-disable');
                self.$chosonContents.removeClass('is-loading');
                $loading.remove();

				var $group = $(self.selectGroupTemplate);
				var $groupContainer = $group.find('.js-estate-select-group-container');
				$main.append($group);

				$groupContainer.empty();
				var i,il, item;
				var checkStr;
				var isChecked;

				var kanaLabelSt = '<h3 class="heading-area" style="margin:10px 0;"><label class="js-estate-select-group-check" style="padding:7px 12px;">';
				var kanaLabelEn = '</label></h3>'

				var contents_ini = [];
				contents_ini[0]  = {
					'label': kanaLabelSt + 'あ行' + kanaLabelEn,
					'contents': ''
				};	// あ行
				contents_ini[1]  = {
					'label': kanaLabelSt + 'か行' + kanaLabelEn,
					'contents': ''
				};	// か行
				contents_ini[2]  = {
					'label': kanaLabelSt + 'さ行' + kanaLabelEn,
					'contents': ''
				};	// さ行
				contents_ini[3]  = {
					'label': kanaLabelSt + 'た行' + kanaLabelEn,
					'contents': ''
				};	// た行
				contents_ini[4]  = {
					'label': kanaLabelSt + 'な行' + kanaLabelEn,
					'contents': ''
				};	// な行
				contents_ini[5]  = {
					'label': kanaLabelSt + 'は行' + kanaLabelEn,
					'contents': ''
				};	// は行
				contents_ini[6]  = {
					'label': kanaLabelSt + 'ま行' + kanaLabelEn,
					'contents': ''
				};	// ま行
				contents_ini[7]  = {
					'label': kanaLabelSt + 'や行' + kanaLabelEn,
					'contents': ''
				};	// や行
				contents_ini[8]  = {
					'label': kanaLabelSt + 'ら行' + kanaLabelEn,
					'contents': ''
				};	// ら行
				contents_ini[9]  = {
					'label': kanaLabelSt + 'わ行' + kanaLabelEn,
					'contents': ''
				};	// わ行
				contents_ini[99]  = {
					'label': kanaLabelSt + 'その他' + kanaLabelEn,
					'contents': ''
				};	// その他

				for (i=0,il=data.chosons.length;i<il;i++) {
					item = data.chosons[i];
					var disabledStr = '';
					var labelClass = '';
					var isAllowChecked = true;

					var contents = '';

					//物件検索で町名が選択されていないときはdisabledにする（チェックも入れない）
					if (!self.isAllowedChosons(data.shikugun_cd, item.code)) {
						// continue;
						disabledStr = ' disabled="disabled"';
						labelClass = 'is-disable';
						isAllowChecked = false;
					}
					
					isChecked = (self.checks.chosons[data.shikugun_cd] && self.checks.chosons[data.shikugun_cd][item.code]) || (!self.chosons[data.shikugun_cd] || !self.chosons[data.shikugun_cd].length);
					checkStr = isChecked && isAllowChecked ? ' checked' : '';

					// NHP-4472
					var contents_aza = "";
                    if(item.choazas.length > 0) {
						contents_aza += '<div style="color:#505050;border-left:solid 1px #c0c0c0;padding-left:5px;margin-top:2px;display:none;" class="choaza-list">';
						if( self.choazas[ data.shikugun_cd ] !== undefined && self.choazas[ data.shikugun_cd ][item.code] !== undefined) {
							for(var cno = 0; cno < item.choazas.length; cno++) {
								var labelClassAza = labelClass;
								var disabledStrAza = disabledStr;
								var checkStrAza = checkStr;
								switch(self.isAllowedChoazas(data.shikugun_cd, item.code, item.choazas[cno]['code'])) {
									case -1:
										labelClassAza = 'is-disable';
										disabledStrAza = 'disabled="disabled"';
										checkStrAza = '';
										break;
									case 1:
									case 0:
									default:
										break;
								}

								if(self.choazas[ data.shikugun_cd ][item.code].indexOf(item.choazas[cno]['code']) >= 0) {
									contents_aza += '<label class="'+labelClassAza+'">';
									contents_aza += '<input type="checkbox" checked value="'+app.h(item.choazas[cno]['code'])+'" name="_estate-select-aza[]" '+disabledStrAza+'>';
									contents_aza += item.choazas[cno]['choson_nm'];
									contents_aza += '</label>';
								} else {
									checkStr = '';
									contents_aza += '<label class="'+labelClassAza+'">';
									contents_aza += '<input type="checkbox" value="'+app.h(item.choazas[cno]['code'])+'" name="_estate-select-aza[]" '+disabledStrAza+'>';
									contents_aza += item.choazas[cno]['choson_nm'];
									contents_aza += '</label>';
								}
							}
						} else {
							for(var cno = 0; cno < item.choazas.length; cno++) {
								var labelClassAza = labelClass;
								var disabledStrAza = disabledStr;
								var checkStrAza = checkStr;
								switch(self.isAllowedChoazas(data.shikugun_cd, item.code, item.choazas[cno]['code'])) {
									case -1:
										labelClassAza = 'is-disable';
										disabledStrAza = 'disabled="disabled"';
										checkStrAza = '';
										break;
									case 1:
									case 0:
									default:
										break;
								}
								contents_aza += '<label class="'+labelClassAza+'">';
								contents_aza += '<input type="checkbox" ' + checkStrAza + ' value="'+app.h(item.choazas[cno]['code'])+'" name="_estate-select-aza[]" '+disabledStrAza+'>';
								contents_aza += item.choazas[cno]['choson_nm'];
								contents_aza += '</label>';
                            }
						}
						contents_aza += '</div>';
					}

					contents += '<li>';
					contents += '<label class="'+labelClass+'">';
					contents += '<input type="checkbox"'+checkStr+disabledStr+' value="'+app.h(item.code)+'" name="_estate-select[]">'+app.h(item.choson_nm);
					contents += '</label>';

					contents += contents_aza;

					contents += '</li>';
					//             a:0  k:1  s:2  t:3  n:4  h:5  m:6  y:7  r:8  w:9
					//             0    0    1    1    2    2    3    3    4    4
					//             0    5    0    5    0    5    0    5    0    5 
					var kanaPos = 'ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔ ﾕ ﾖﾗﾘﾙﾚﾛﾜ ｦ ﾝ'.indexOf(item.choson_kana_nm.slice(0,1));

					if(kanaPos >= 0) {
						contents_ini[ Math.floor(kanaPos / 5) ]['contents']+= contents;
					} else {
						contents_ini[ 99 ]['contents']+= contents;
					}
				}

				contents = '';

				contents_ini.forEach(function( value ) {

					if(value['contents'] !== '') {
						contents+= value['label'];
						contents+= value['contents'];
					}
				});

				if (contents) {
					$group.find('.js-estate-select-group-check').html('<input type="checkbox">'+app.h(data.shikugun_nm));

					var showAzaButton = '<div style="width:100%;text-align:right;padding-right:10px;"><a class="show-choaza-all" style="cursor: pointer;">詳細を表示する</a></div>';
					contents = showAzaButton + contents;

					$groupContainer.html(contents);
					// $groupContainer.find('input:first').change();
					// すべてがチェックしてたら、Groupもチェックする処理を愚直に書く
					if($groupContainer.find("input[name='_estate-select-aza[]']").length) {
						$groupContainer.find('.choaza-list').each(function() {
							if($(this).find("input[name='_estate-select-aza[]']:enabled").length > 0) {
								$(this).find("input[name='_estate-select-aza[]']:enabled").eq(0).change();
							}
						});
						//$groupContainer.find("input[name='_estate-select-aza[]']").eq(0).change();
					} else {
						$groupContainer.find('input:first').change();
					}
				}
				else {
					$group.remvoe();
				}
			});
        },

        rerenderShikugun: function () {
            var self = this;
            this.$shikugunMain.find('.select-choson').each(function (i, elem) {
                var $selectChoson = $(elem);
                var $selectChosonLink = $selectChoson.find('a');
                var shikugunCd = $selectChosonLink.attr('data-shikugun_cd');

                if (self.checks.shikuguns[ shikugunCd ]) {
                    // 市区群が選択されている場合
                    $selectChoson.removeClass('is-disable');
                    if (self.hasSelectedChoson(shikugunCd)) {
                        // 町名一部選択
                        $selectChoson.addClass('select-choson--selected');
                        $selectChosonLink.text(self.CHOSON_SELECTED);

                    } else {
						// 町名全選択
                        $selectChoson.removeClass('select-choson--selected');
                        $selectChosonLink.text(self.CHOSON_ALL);
                    }
                } else {
                    // 市区群が選択されていない場合
                    $selectChoson.addClass('is-disable');
                    $selectChoson.removeClass('select-choson--selected');
                    $selectChosonLink.text(self.CHOSON_ALL);
                }
            });
        },

        next: function (shikugun_cd) {
            this.clearError();

            this.currentShikugun = shikugun_cd;
            this.setShikuguns( this.getCheckedCode(this.$shikugunMain) );
            this.setChosonTitle(shikugun_cd);
            this.renderChoson();
            this.$contents.eq(0).hide();
            this.$contents.eq(1).show();
        },

        prev: function () {
            this.currentShikugun = null;
            this.clearError();
            this.setShikugunTitle();
            this.$contents.eq(0).show();
            this.$contents.eq(1).hide();
        },

        selectChoson: function () {
            if (this._chosonDeferred.state() === 'resolved') {
                // 町番地も含めてのチェック
            	var checkedChosons = this.getCheckedCode(this.$chosonMain);
                var checkedChoazas = {};

            	if (!checkedChosons.length) {
                    this.showError('町名が選ばれていません。');
                    return false;
				}

                checkedChosons = [];
                this.$chosonContents.find('input[name="_estate-select[]"]').each(function() {
                    if($(this).prop('checked') == true) {
                        checkedChosons.push($(this).val());

                        // 特集の場合は町名チェックでも一部選択の場合あり
                        if($(this).closest('li').find('input[name="_estate-select-aza[]"]').length > $(this).closest('li').find('input[name="_estate-select-aza[]"]:checked').length) {
                            var chosonCode = $(this).val();
                            checkedChoazas[ chosonCode ] = [];

                            $(this).closest('li').find('input[name="_estate-select-aza[]"]').each(function() {
                                if($(this).prop('checked') == true) {
                                    checkedChoazas[ chosonCode ].push($(this).val());
                                }
                            });
                        }
                    } else {
                        if($(this).closest('li').find('input[name="_estate-select-aza[]"]:checked').length) {
                            checkedChosons.push($(this).val());

                            var chosonCode = $(this).val();
                            checkedChoazas[ chosonCode ] = [];

                            $(this).closest('li').find('input[name="_estate-select-aza[]"]').each(function() {
                                if($(this).prop('checked') == true) {
                                    checkedChoazas[ chosonCode ].push($(this).val());
                                }
                            });
                        }
                    }
                });

				// 全選択の場合は設定しない
				if (
					(this.$chosonContents.find('input[name="_estate-select[]"]').length === checkedChosons.length) && (0 === Object.keys(checkedChoazas).length)
				) {
            		checkedChosons = [];
            		checkedChoazas = {};
				}

            	this.chosons[ this.currentShikugun ] = checkedChosons;
            	this.choazas[ this.currentShikugun ] = checkedChoazas;
                this.setChosons( this.chosons );
                this.setChoazas( this.choazas );
            }
            this.currentShikugun = null;
            this.clearError();
            this.setShikugunTitle();
            this.rerenderShikugun();
            this.$contents.eq(0).show();
            this.$contents.eq(1).hide();
        },

        onOk: function () {
            if (!this.validateRequired(this.$shikugunMain)) {
                this.showError('市区郡が選ばれていません。');
                return false;
            }

            this.clearError();

			var self = this;
            var chosons = {};
            var choazas = {};
            $.each(this.shikuguns, function (i, shikugunCd) {
            	if (self.chosons[ shikugunCd ] && self.chosons[ shikugunCd ].length) {
            		chosons[ shikugunCd ] = self.chosons[ shikugunCd ];
				}
            		choazas[ shikugunCd ] = self.choazas[ shikugunCd ];
			});

            this._deferred.resolve({shikuguns: this.shikuguns, chosons: chosons, choazas: choazas});
            this.close();
        },
        onNext: function() {
            if (!this.validateGroupCheckedPrefs(this.$shikugunMain)) {
                this.showError('市区郡が選ばれていません。');
                return false;
                
            }
            var self = this;
            var shikuguns = {};
            var chosons = {};
            var choazas = {};
            $.each(this.shikuguns, function (i, shikugunCd) {
                var prefCode = shikugunCd.slice(0, 2);
                if (typeof shikuguns[prefCode] == 'undefined') {
                    shikuguns[prefCode] = [];
                }
                shikuguns[prefCode].push(shikugunCd);
            	if (self.chosons[ shikugunCd ] && self.chosons[ shikugunCd ].length) {
                    if (typeof chosons[prefCode] == 'undefined') {
                        chosons[prefCode] = {};
                    }
            		chosons[prefCode][ shikugunCd ] = self.chosons[ shikugunCd ];
                }
                if (typeof choazas[prefCode] == 'undefined') {
                    choazas[prefCode] = {};
                }
            	choazas[prefCode][ shikugunCd ] = self.choazas[ shikugunCd ];
            });
            this.stepContent.setting.area_search_filter.area_2 = shikuguns;
            this.stepContent.setting.area_search_filter.area_5 = chosons;
            this.stepContent.setting.area_search_filter.area_6 = choazas;
            var modal = new searchFilterModal(this.stepContent, null);
            this.close();
        },
        onPrev: function () {
            var modal = new estate.conditionHouseListModal(this.stepContent, false);
            this.close();
        }


    });

	var EnsenModal = estate.EnsenModal = app.inherits(Modal, function () {
		Modal.apply(this, arguments);

	}, {
		template: ''+
			'<div class="modal-contents size-l modal-scroll modal-invidial">'+
				'<div class="modal-header">'+
					'<h2 class="js-modal-title"></h2>'+
					'<div class="modal-close"><a href="javascript:;" class="btn-modal js-modal-close"><i class="i-e-delete"></i></a></div>'+
				'</div>'+
				'<div class="modal-body js-modal-contents">'+
					'<div class="modal-body-inner align-top js-modal-contents-main">'+
					'</div>'+
					'<div class="modal-btns js-modal-contents-btn-area">'+
						'<a class="btn-t-gray js-modal-close" href="javascript:;">戻る</a>'+
						'<a class="btn-t-blue size-l js-modal-next" href="javascript:;">駅選択に進む</a>'+
					'</div>'+
				'</div>'+
				'<div class="modal-body js-modal-contents" style="display:none;">'+
					'<div class="modal-body-inner align-top js-modal-contents-main">'+
					'</div>'+
					'<div class="modal-btns js-modal-contents-btn-area">'+
						'<a class="btn-t-gray js-modal-prev" href="javascript:;">戻る</a>'+
						'<a class="btn-t-blue size-l js-modal-ok" href="javascript:;">決定する</a>'+
					'</div>'+
				'</div>'+
			'</div>',

		init: function (data) {
			var self = this;

			// 駅データ取得用Deferred
			this._ekiDeferred = null;

			// データセット
			this.ensens = [];
			this.ekis = [];
			this.checks = {
				ensens: {},
				ekis: {}
			};
			this.setEnsens( data.ensens || [] );
			this.setEkis( data.ekis || [] );

			// タイトル
			this.setEnsenTitle();

			this.contents = [];
			this.$contents = this.$element.find('.js-modal-contents');
			this.$ensenContents = this.$contents.eq(0);
			this.$ekiContents   = this.$contents.eq(1);
			this.$main     = this.$contents.find('.js-modal-contents-main')
			this.$ensenMain = this.$main.eq(0);
			this.$ekiMain   = this.$main.eq(1);


			// 選択項目を表示
			this.renderEnsen();

			// ボタン
			this.$element.on('click', '.js-modal-next:not(.is-disable)', function () {
				self.next();
				return false;
			});
			this.$element.on('click', '.js-modal-prev', function () {
				self.prev();
				return false;
			});
			this.$element.on('click', '.js-modal-ok:not(.is-disable)', function () {
				self.onOk();
				return false;
            });
            this.$element.on('click', '.js-modal-ok-next:not(.is-disable)', function () {
                self.onNext();
                return false;
            });
            this.$element.on('click', '.js-modal-close-prev:not(.is-disable)', function () {
                self.onPrev();
                return false;
            });

            if ($.isArray(this.pref.code)) {
                this.$element.find('.modal-btns .js-modal-close')
                .removeClass('js-modal-close')
                .addClass('js-modal-close-prev')
            }

            if (typeof this.stepContent != 'undefined' && typeof this.stepContent.backStation != 'undefined' && this.stepContent.backStation) {
                this.next();
            }
		},

		setEnsens: function (codes) {
			this._setCodes('ensens', codes);
		},
		setEkis: function (codes) {
			this._setCodes('ekis', codes);
		},
		_setCodes: function (name, codes) {
			this[name] = codes;
			this.checks[name] = {};
            for(var code in codes ) {
                if ($.isArray(codes[code])) {
                    this.checks[name][code] =  typeof this.checks[name][code] != 'undefined' ? this.checks[name][code] : {};
                    for (var i=0,l=codes[code].length;i<l;i++) {
                        this.checks[name][code][ codes[code][i] ] = true;
                    }
                } else {
                    var values = codes[code].split(':');
                    switch (name) {
                        case 'ensens':
                            if (values.length == 2) {
                                this.checks[name][values[0]] =  typeof this.checks[name][values[0]] != 'undefined' ? this.checks[name][values[0]] : {};
                                this.checks[name][values[0]][values[1]] = true;
                            } else {
                                this.checks[name][codes[code] ] = true;
                            }
                            break;
                        case 'ekis':
                            if (values.length == 3) {
                                this.checks[name][values[0]] =  typeof this.checks[name][values[0]] != 'undefined' ? this.checks[name][values[0]] : {};
                                this.checks[name][values[0]][values[1] + ':' + values[2]] = true;
                            } else {
                                this.checks[name][codes[code] ] = true;
                            }
                            break;
                    }
                }
			}
		},

		setEnsenTitle: function () {
			this.setTitle('沿線を選択してください。');
		},
		setEkiTitle: function () {
			this.setTitle('駅を選択してください。');
		},

		renderEnsen: function () {
			var self = this;
			var $main = this.$ensenMain;
			// loading on
			var $loading = this.createLoading();
			$main.html($loading);
			this.$ensenContents.addClass('is-loading');
			var $next = this.$element.find('.js-modal-next').addClass('is-disable');
			EstateMaster.getEnsen(this.pref.code).done(function (data) {
				// loading off
				self.$ensenContents.removeClass('is-loading');
				$next.removeClass('is-disable');
				$loading.remove();

                var $tmp = $('<ul/>');
                var $tmpGroup = $('<div/>');
				var i,il, group, $group;
				var j,jl, item, groupContents;
                var checkStr;
                for (var key in data) {
                    for (i=0,il=data[key].ensen_groups.length;i<il;i++) {
                        group = data[key].ensen_groups[i];
                        $group = $(self.selectGroupTemplate);
                        $group.find('.js-estate-select-group-check').html(app.h(group.ensen_group_nm));
                        groupContents = '';
                        for (j=0,jl=group.ensens.length;j<jl;j++) {
                            item = group.ensens[j];
                            if (!self.isAllowedEnsens(data[key].ken_cd, item.code)) {
                                continue;
                            }
                            checkStr = self.checks.ensens[ item.code ] || typeof self.checks.ensens[data[key].ken_cd] != 'undefined' && self.checks.ensens[data[key].ken_cd][ item.code ] ? ' checked' : '';
                            var value = item.code;
                            if ($.isArray(self.pref.code)) {
                                value = data[key].ken_cd + ':' +item.code;
                            }
                            groupContents += '<li><label><input type="checkbox"'+checkStr+' value="'+app.h(value)+'" name="_estate-select[]">'+app.h(item.ensen_nm)+'</label></li>';
                        }
                        if (groupContents) {
                            $tmp.append($group);
                            $group.find('.js-estate-select-group-container').html(groupContents);
                        }
                    }
                    if ($.isArray(self.pref.code)) {
                        var $groupPref = $(self.selectGroupPrefsTemplate);
                        $groupPref.find('.heading-pref').html(self.pref.name[key]);
                        $groupPref.append($tmp)
                        $tmpGroup.append($groupPref);
                        $tmp = $('<ul/>');
                    }
                }
                if ($.isArray(self.pref.code)) {
                    $main.html($tmpGroup.html());
                } else {
                    $main.html($tmp.html());
                }
				$main.find('.js-estate-select-group-container').each(function () {
					$(this).find('input:first').change();
				});
			});
		},

		renderEki: function () {
			var self = this;
			var $main = this.$ekiMain;
			$main.empty();

			// loading on
			var $loading = this.createLoading();
			$main.html($loading);
			var $ok = this.$element.find('.js-modal-ok').addClass('is-disable');
			this.$ekiContents.addClass('is-loading');

			// 確認画面で『沿線=>駅リスト』がここに入っているが、再利用させないためリセットする
			EstateMaster._master['eki'] = {};
			
            // 駅一括取得
            var ensens = this.ensens.map(function(value) {
                var values = value.split(":");
                if (values.length > 1) {
                    return values[1];
                }
                return values[0];
            })
			var dfd = this._ekiDeferred = EstateMaster.getEki(ensens);
			dfd.done(function () {
				if (dfd !== self._ekiDeferred) {
					return;
				}

				// loading off
				$ok.removeClass('is-disable');
				self.$ekiContents.removeClass('is-loading');
				$loading.remove();

				// 沿線毎に処理
				$.each(self.ensens, function (i, ensenCode) {
					var $group = $(self.selectGroupTemplate);
					var $groupContainer = $group.find('.js-estate-select-group-container');
					$main.append($group);
                    var values = ensenCode.split(":");
                    if (values.length > 1) {
                        ensenCode =  values[1];
                        var prefCode = values[0];
                    } else {
                        ensenCode =  values[0];
                        var prefCode = self.pref.code;
                    }
					EstateMaster.getEki(ensenCode).done(function (data) {
						// 取得データが変更された場合は処理しない
						if (dfd !== self._ekiDeferred) {
							return;
						}

						$groupContainer.empty();
						var contents = '';
						var i,il, item;
						var checkStr;

						for (i=0,il=data.ekis.length;i<il;i++) {
							item = data.ekis[i];
							if (!self.isAllowedEkis(prefCode, item.code)) {
								continue;
							}
                            checkStr = self.checks.ekis[ item.code ] || typeof self.checks.ekis[prefCode] != 'undefined' && self.checks.ekis[prefCode][ item.code ] ? ' checked' : '';
                            var value = item.code;
                            if ($.isArray(self.pref.code)) {
                                value = prefCode + ':' +item.code;
                            }
							contents += '<li><label><input type="checkbox"'+checkStr+' value="'+app.h(value)+'" name="_estate-select[]">'+app.h(item.eki_nm)+'</label></li>';
						}
						if (contents) {
							$group.find('.js-estate-select-group-check').html('<input type="checkbox">'+app.h(data.ensen_nm));
							$groupContainer.html(contents);
							$groupContainer.find('input:first').change();
						}
						else {
							$group.remove();
						}
					});
				});
				//県またぎ沿線対策で毎回リクエストを投げさせるためにmaster削除
				EstateMaster._master.eki = {}
			});
		},

		next: function () {
			if (!this.validateRequired(this.$ensenMain)) {
				this.showError('沿線が選ばれていません。');
				return false;
			}
			this.clearError();

			this.setEnsens( this.getCheckedCode(this.$ensenMain) );
			this.setEkiTitle();
            this.renderEki();
			this.$contents.eq(0).hide();
            this.$contents.eq(1).show();
            if ($.isArray(this.pref.code)) {
                this.$element.find('.js-modal-ok')
                    .removeClass('js-modal-ok')
                    .addClass('js-modal-ok-next')
                    .html('次の設定に進む');
            }
		},

		prev: function () {
			if (this._ekiDeferred.state() === 'resolved') {
				this.setEkis( this.getCheckedCode(this.$ekiMain) );
			}
			this.clearError();
			this.setEnsenTitle();
			this.$contents.eq(0).show();
			this.$contents.eq(1).hide();
		},

		onOk: function () {
			if (!this.validateGroupChecked(this.$ekiMain)) {
				this.showError('駅が選択されていない沿線があります。');
				return false;
			}

			this.clearError();

			this._deferred.resolve({ensens: this.ensens, ekis: this.getCheckedCode(this.$ekiMain)});
			this.close();
        },
        onNext: function() {
            if (!this.validateGroupChecked(this.$ekiMain)) {
                this.showError('駅が選択されていない沿線があります。');
                return false;
                
            }
            var ensens = {};
            var ekis = {};
            $.each(this.ensens, function (i, ensenCd) {
                var values = ensenCd.split(':');
                if (typeof ensens[values[0]] == 'undefined') {
                    ensens[values[0]] = [];
                }
                ensens[values[0]].push(values[1]);
            });
            $.each(this.ekis = this.getCheckedCode(this.$ekiMain), function (i, ekiCd) {
                var values = ekiCd.split(':');
                if (typeof ekis[values[0]] == 'undefined') {
                    ekis[values[0]] = [];
                }
                ekis[values[0]].push([values[1], values[2]].join(':'));
            });
            this.stepContent.setting.area_search_filter.area_3 = ensens;
            this.stepContent.setting.area_search_filter.area_4 = ekis;
            var modal = new searchFilterModal(this.stepContent, null);
            this.close();
        },
        onPrev: function () {
            var modal = new estate.conditionHouseListModal(this.stepContent, false);
            this.close();
        }

	});

	var EditView = function (Master, allowed, onUpdate) {
		if (arguments.length < 3) {
			onUpdate = allowed;
			allowed = null;
		}

		this.$element = $('<table class="tb-basic"></table>');
		this.Master = Master;
		this.allowed = {};
		this.denied  = {};
		this.initAllowed(allowed);

		this.prefCodes = [];
		this.onUpdate = onUpdate;

		var self = this;
		this.$element.on('click', '.js-select-item', function () {
			self.selectItem( $(this).closest('tr').attr('data-pref-code') );
			EstateMaster.setPref($(this).closest('tr').attr('data-pref-code'));
		});
		$(window).on('unload', function onUnload () {
			$(window).off('unload', onUnload);
			self.onUpdate = null;
			self.$element.off();
		});
	};
	EditView.prototype.initAllowed = function (allowed) {
		this.allwoed = {};
		this.denied  = {};

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
	};
	EditView.prototype.setPrefCodes = function (codes) {
		this.prefCodes = [];
		var code;
		for (var i=0,l=codes.length;i<l;i++) {
			code = codes[i];
			if (this.isAllowed('pref', code)) {
				this.prefCodes.push(code);
			}
		}
	};
	EditView.prototype.setCodes = function (name, codes, update) {
		if (!update) {
			this[name] = {};
		}
		var prefCode, i, l;
		var codesInPref;
		var code;
		var isDenied = false;
		for (prefCode in codes) {
			if (!this.isAllowed('pref', prefCode)) {
				isDenied = true;
				this.denied[prefCode] = true;
				continue;
			}
			codesInPref = codes[prefCode];
			this[name][prefCode] = [];

			for (i=0,l=codesInPref.length;i<l;i++) {
				code = codesInPref[i];
				if (this.isAllowed(name, code)) {
					this[name][prefCode].push(code);
				}
				else {
					isDenied = true;
					this.denied[prefCode] = true;
				}
			}
		}
		return !isDenied;
	};
	EditView.prototype.isAllowed = function (name, code) {
		return !!(!this.allowed[name] || this.allowed[name][code]);
	};
    EditView.prototype.isAllowedChosons = function (shikugunCd, chosonCd) {
        return !!(!this.allowed['shikugun_chosons'] || !this.allowed['shikugun_chosons'][shikugunCd] || this.allowed['shikugun_chosons'][shikugunCd][chosonCd]);
    };
    EditView.prototype.isAllowedChoazas = function (shikugunCd, chosonCd, choazaCd) {
        if(!this.allowed['choazas']) {
            return 0;
        }
        if(this.allowed['choazas'][shikugunCd] !== undefined && this.allowed['choazas'][shikugunCd][chosonCd] !== undefined) {
            if(this.allowed['choazas'][shikugunCd][chosonCd].indexOf(choazaCd) >= 0) {
                return 1;
            } else {
                return -1;
            }
        }
        return 0;
    };
	EditView.prototype.getDeniedError = function (prefCode) {
		return this.denied[prefCode] ?
			'<span>物件検索設定が変更された為、設定中の項目が選択できなくなりました。</span>':
			'';
	};
	EditView.prototype.selectItem = function (prefCode) {

	};
	EditView.prototype.setError = function (prefCodes) {
		this.clearError();

		var prefCode;
		for (var i=0,l=prefCodes.length;i<l;i++) {
			prefCode = prefCodes[i];
			this.$element.find('[data-pref-code="'+prefCode+'"]').addClass('is-no-setting');
		}
	};

	EditView.prototype.clearError = function () {
		this.$element.find('.is-no-setting').removeClass('is-no-setting');
	};

	var EditShikugunView = estate.EditShikugunView = app.inherits(EditView, function (Master, allowed, onUpdate) {
		EditView.apply(this, arguments);

		this.MORE_CLOSED = '…もっと見る';
		this.MORE_OPENED = '…閉じる';

		this.selectItemBtnLabel = '市区郡・町名選択';

		var self = this;
		// 町名もっと見る
		this.$element.on('click', '.choson-search-list__more', function () {
			var $more = $(this);
			if ($more.hasClass('is-opened')) {
				$more.parent().find('span:gt(4)').hide();
				$more.removeClass('is-opened');
				$more.text(self.MORE_CLOSED);
			} else {
                $more.parent().find('span').show();
                $more.addClass('is-opened');
                $more.text(self.MORE_OPENED);
			}
		});

		this.shikuguns = {};
		this.chosons = {};
		this.searchChosonEnabled = false;
	},
	{
		setSelectItemBtnLabel: function (label) {
			this.selectItemBtnLabel = label;
			this.$element.find('.js-select-item').text(label);
		},

		setData: function (prefCodes, shikuguns, chosons, choazas, searchChosonEnabled) {
			this.searchChosonEnabled = searchChosonEnabled;
			this.setPrefCodes(prefCodes);
            var ret1 = this.setShikuguns(shikuguns);
            var ret2 = this.setChosons(chosons);
            var ret3 = this.setChoazas(choazas);
            return ret1 && ret2 && ret3;
		},

		getData: function () {
			return {
				shikuguns: this.shikuguns,
				chosons: this.searchChosonEnabled ? this.chosons : {},
				choazas: this.searchChosonEnabled ? this.choazas : {}
			};
		},

		setShikuguns: function (shikuguns, update) {
			this.setCodes('shikuguns', shikuguns, update);
		},

        setChosons: function (codes, update) {
            var name = 'chosons';
            if (!update) {
                this[name] = {};
            }
            var prefCode, i, l;
            var codesInPref;
            var code;
            var isDenied = false;
            for (prefCode in codes) {
                if (!this.isAllowed('pref', prefCode)) {
                    isDenied = true;
                    this.denied[prefCode] = true;
                    continue;
                }
                codesInPref = codes[prefCode];
				/**
				 * 町
				 */
				this[name][prefCode] = {};
				for (var shikugun_cd in codesInPref) {
					this[name][prefCode][shikugun_cd] = [];
					for (i=0,l=codesInPref[shikugun_cd].length;i<l;i++) {
						code = codesInPref[shikugun_cd][i];
						if (this.isAllowedChosons(shikugun_cd, code)) {
							this[name][prefCode][shikugun_cd].push(code);
						}
						else {
							isDenied = true;
							this.denied[prefCode] = true;
						}
					}
				}
            }
            return !isDenied;
        },
        // NHP-4472 
        setChoazas: function (codes, update) {
            var name = 'choazas';
            if (!update) {
                this[name] = {};
            }
            var prefCode, i, l;
            var codesInPref;
            var code;
            var isDenied = false;
            for (prefCode in codes) {
                if (!this.isAllowed('pref', prefCode)) {
                    isDenied = true;
                    this.denied[prefCode] = true;
                    continue;
                }
                codesInPref = codes[prefCode];
                /**
                 * 町
                 */
                this[name][prefCode] = {};
                for (var shikugun_cd in codesInPref) {
                    this[name][prefCode][shikugun_cd] = {};

                    for (var choson_cd in codesInPref[shikugun_cd]) {
                        //alert(codesInPref[shikugun_cd][choson_cd].length);

                        this[name][prefCode][shikugun_cd][choson_cd] = [];

                        for (i=0,l=codesInPref[shikugun_cd][choson_cd].length;i<l;i++) {
                            code = codesInPref[shikugun_cd][choson_cd][i];
                            this[name][prefCode][shikugun_cd][choson_cd].push(code);
                        }

                        code = codesInPref[shikugun_cd][choson_cd];
                    }
                }
            }
            return !isDenied;
        },

		render: function () {
			var i, l;

			this.$element.empty();

			// 表示に必要なマスタ取得一括取得
			var prefs = [];
            var shikugunCds = [];
			var prefCode;
			for (i=0,l=this.prefCodes.length;i< l;i++) {
				prefCode = this.prefCodes[i];
				if (this.shikuguns[ prefCode ]) {
					prefs.push(prefCode);
				}
				if (this.chosons[ prefCode ]) {
                    for (var shikugun_cd in this.chosons[ prefCode ]) {
                        if (this.chosons[ prefCode ][shikugun_cd].length) {
                            shikugunCds.push(shikugun_cd);
                        }
                    }
				}
			}
            if (shikugunCds.length) {
                EstateMaster.getChoson(shikugunCds);
            }
			if (prefs.length) {
				EstateMaster.getShikugun(prefs);
			}

			var $tr;
			for (i=0,l=this.prefCodes.length;i< l;i++) {
				prefCode = this.prefCodes[i];
				$tr = this.createRow(prefCode);
				this.$element.append($tr);
			}
		},

		createRow: function (prefCode) {
			var $tr = $(
				'<tr data-pref-code="'+prefCode+'">'+
					'<th class="alC nowrap">'+app.h(this.Master.prefMaster[prefCode])+'</th>'+
					'<td></td>'+
					'<td class="alC"><a href="javascript:;" class="btn-t-gray size-s update-setting-btn js-select-item">' + this.selectItemBtnLabel + '</a></td>'+
				'</tr>'
			);
			if (this.denied[prefCode]) {
				$tr.addClass('is-no-selecting');
			}
			return this.updateRow($tr);
		},

		updateRow: function ($tr) {
			var self = this;
			var prefCode = $tr.attr('data-pref-code');
			var $selected = $tr.find('td').eq(0);
			var shikugunsInPref = this.shikuguns[prefCode];
			var chosonsInPref = this.chosons[prefCode] || {};
			var choazasInPref = this.choazas[prefCode] || {};

			// 選択済み
			if (shikugunsInPref && shikugunsInPref.length) {
				// loading on
				var closer = app.loading();
				var dfds = [EstateMaster.getShikugun(prefCode)];
				var shikugunCds = [];
				for (var shikugunCd in chosonsInPref) {
					if (chosonsInPref[shikugunCd].length) {
						shikugunCds.push(shikugunCd);
					}
				}
				if (shikugunCds.length) {
					dfds.push(EstateMaster.getChoson(shikugunCds));
				}
				$.when.apply($, dfds).done(function () {
                    // loading off
                    closer();
                    EstateMaster.getShikugun(prefCode).done(function (prefData) {

                        var selectedHtml = '<ul class="list-item">';
                        var i, l, shikugunCode;
                        var renderChosons = [];
                        for (i=0,l=shikugunsInPref.length;i<l;i++) {
                            shikugunCode = shikugunsInPref[i];
                            if (prefData[0].shikugunMap[shikugunCode]) {
                                var shikugunData = prefData[0].shikugunMap[shikugunCode];
                                selectedHtml += '<li data-shikugun-code="'+shikugunData.code+'">'+app.h(shikugunData.shikugun_nm)+'</li>';

                                if (chosonsInPref[shikugunCode] && chosonsInPref[shikugunCode].length) {
                                    renderChosons.push({
                                        shikugun: shikugunData,
                                        chosons: chosonsInPref[shikugunCode],
                                        choazas: choazasInPref[shikugunCode]
                                    });
                                }
                            }
                        };
                        selectedHtml += '</ul>';
                        $selected.html(selectedHtml + '<div class="choson-search-list"></div>' + self.getDeniedError(prefCode));

                        var $chosons = $selected.find('.choson-search-list');
                        if (self.searchChosonEnabled && renderChosons.length) {
                        	for (i = 0; renderChosons[i]; i++) {
								(function ($dl, renderChoson) {
									EstateMaster.getChoson([renderChoson.shikugun.code]).done(function (data) {
                                        var dlHtml = '<dt><span>'+app.h(data.shikugun_nm)+'</span></dt>';
                                        var choson;
										var j = 0, jl = renderChoson.chosons.length;
										var renderCount = 0;
										dlHtml += '<dd>';
										for (;j < jl; j++) {
											if (!data.chosonMap[renderChoson.chosons[j]]) {
												continue;
											}
											renderCount++;
											choson = data.chosonMap[renderChoson.chosons[j]];
                                            // 町字が存在する?
                                            if(choazasInPref[ data.shikugun_cd ] !== undefined && choazasInPref[ data.shikugun_cd ][ choson.code ] !== undefined) {
                                                dlHtml += '<span>';
                                                dlHtml += '<p style="display:inline-block"';
                                                dlHtml += ' onmouseover="' + "$(this).closest('span').find('.tc-choson-detail').eq(0).show();" +'"';
                                                dlHtml += '  onmouseout="' + "$(this).closest('span').find('.tc-choson-detail').eq(0).hide();" +'"';
                                                dlHtml += '">'+app.h(choson.choson_nm)+'</p>';
                                                dlHtml += '<p style="color:#0747a6;display:inline-block">*</p>';

                                                dlHtml += '<div class="tc-choson-detail">';
                                                $.each(choson.choazas, function(index, choazaObj) {
                                                    if(choazasInPref[ data.shikugun_cd ][ choson.code ].indexOf(choazaObj.code) >= 0) {
                                                        dlHtml += choazaObj.choson_nm + '&nbsp;'　
                                                    }
                                                });
                                                dlHtml += '</div>';
                                                dlHtml += '</span>';
                                            } else {
											    dlHtml += '<span>'+app.h(choson.choson_nm)+'</span>'
                                            }
										}
                                        dlHtml += '</dd>';

										$dl.html(dlHtml);

										// 一定数以上の場合、もっと見るリンク
										if (renderCount > 5) {
											$dl.find('dd span:gt(4)').hide();
											$dl.find('dd').append($('<a class="choson-search-list__more">'+self.MORE_CLOSED+'</a>'));
										}
									})
								})($('<dl/>').appendTo($chosons), renderChosons[i]);
							}
						} else {
                        	$chosons.remove();
						}

                    });
				});
			}
			// 未選択
			else {
				$selected.html('（未選択）' + this.getDeniedError(prefCode));
			}

			return $tr;
		},

		selectItem: function (prefCode) {
			var self = this;
			var $row = this.$element.find('tr[data-pref-code="'+prefCode+'"]');
			var pref = {
				code: prefCode,
				name: this.Master.prefMaster[prefCode]
			};
			var data = {
                shikuguns: this.shikuguns[prefCode] || [],
				chosons: this.chosons[prefCode] || {},
                choazas: this.choazas[prefCode] || {},		// NHP-4472
				searchChosonEnabled: this.searchChosonEnabled
			};
			var modal = new app.estate.ShikugunChosonModal(pref, data, this.allowed);
			modal.promise.done(function (shikugunData) {
				var data = {};
				data[prefCode] = shikugunData.shikuguns || [];
				self.setShikuguns(data, true);
				data[prefCode] = shikugunData.chosons || {};
				self.setChosons(data, true);

                // NHP-4472 :area_6:choazas
				data[prefCode] = shikugunData.choazas || {};
				self.setChoazas(data, true);

				$row.removeClass('is-no-setting');
				self.updateRow($row);
				self.onUpdate && self.onUpdate(self.shikuguns, self.chosons);
			});
		}
	});

	var EditEnsenView = estate.EditEnsenView = app.inherits(EditView, function () {
		EditView.apply(this, arguments);
		this.ensens = {};
		this.ekis   = {};
	},
	{
		setData: function (prefCodes, ensens, ekis) {
			this.setPrefCodes(prefCodes);
			var ret1 = this.setEnsens(ensens);
			var ret2 = this.setEkis(ekis);
			return ret1 && ret2;
		},

		getData: function () {
			return {
				ensens: this.ensens,
				ekis: this.ekis
			};
		},

		setEnsens: function (ensens, update) {
			this.setCodes('ensens', ensens, update);
		},

		setEkis: function (ekis, update) {
			this.setCodes('ekis', ekis, update);
		},

		render: function () {
			var i, l;

			this.$element.empty();

			// 表示に必要なマスタ取得一括取得
			var prefs = [];
			var prefCode;
			for (i=0,l=this.prefCodes.length;i< l;i++) {
				prefCode = this.prefCodes[i];
				if (this.ensens[ prefCode ]) {
					prefs.push(prefCode);
				}
			}
			if (prefs.length) {
				EstateMaster.getEnsen(prefs);
			}

			var $tr;
			for (i=0,l=this.prefCodes.length;i< l;i++) {
				prefCode = this.prefCodes[i];
				$tr = this.createRow(prefCode);
				this.$element.append($tr);
			}
		},

		createRow: function (prefCode) {
			var $tr = $(
				'<tr data-pref-code="'+prefCode+'">'+
					'<th class="alC nowrap">'+app.h(this.Master.prefMaster[prefCode])+'</th>'+
					'<td></td>'+
					'<td class="alC"><a href="javascript:;" class="btn-t-gray size-s update-setting-btn js-select-item">沿線・駅選択</a></td>'+
				'</tr>'
			);
			if (this.denied[prefCode]) {
				$tr.addClass('is-no-selecting');
			}
			return this.updateRow($tr);
		},

		updateRow: function ($tr) {
			var self = this;
			var prefCode = $tr.attr('data-pref-code');
			var $selected = $tr.find('td').eq(0);
			var ensenssInPref = this.ensens[prefCode];
			// 選択済み
			if (ensenssInPref && ensenssInPref.length) {
				// loading on
				var closer = app.loading();
				EstateMaster.getEnsen(prefCode).done(function (prefData) {
					// loading off
					closer();

					var selectedHtml = '<ul class="list-item">';
					var i, l, ensenCode, ensenData;
					for (i=0,l=ensenssInPref.length;i<l;i++) {
						ensenCode = ensenssInPref[i];
						if (prefData[0].ensenMap[ensenCode]) {
							ensenData = prefData[0].ensenMap[ensenCode];
							selectedHtml += '<li data-ensen-code="'+ensenData.code+'">'+app.h(ensenData.ensen_nm)+'</li>';
						}
					};
					selectedHtml += '</ul>';
					$selected.html(selectedHtml+self.getDeniedError(prefCode));
				});
			}
			// 未選択
			else {
				$selected.html('（未選択）'+this.getDeniedError(prefCode));
			}

			return $tr;
		},

		selectItem: function (prefCode) {
			var self = this;
			var $row = this.$element.find('tr[data-pref-code="'+prefCode+'"]');
			var pref = {
				code: prefCode,
				name: this.Master.prefMaster[prefCode]
			};
			var data = {
				ensens: this.ensens[prefCode] || [],
				ekis: this.ekis[prefCode] || []
			};
			var modal = new app.estate.EnsenModal(pref, data, this.allowed);
			modal.promise.done(function (ensenData) {
				var ensens = {};
				var ekis = {};

				ensens[prefCode] = ensenData.ensens;
				ekis[prefCode]   = ensenData.ekis;

				self.setEnsens(ensens, true);
				self.setEkis(ekis, true);
				$row.removeClass('is-no-setting');
				self.updateRow($row);
				self.onUpdate && self.onUpdate(self.ensens, self.ekis);
			});
		}
	});

	var ConfirmBasicSettingView = estate.ConfirmBasicSettingView = function ( Master , dispEstateRequest ) {
		var estateRequest	= '' ;
		if ( dispEstateRequest == 1 )
		{
			estateRequest = '<dt>物件リクエスト</dt><dd class="js-confirm-estate_request_flg"></dd>' ;
		}

		this.$element = $(
			'<dl class="confirm-basic">'+
				'<dt>探し方</dt>'+
				'<dd class="js-confirm-search-type"></dd>'+

				'<dt>物件種目</dt>'+
				'<dd class="js-confirm-enabled-estate-type"></dd>'+

				'<dt>都道府県</dt>'+
				'<dd class="js-confirm-pref"></dd>'+
				
				estateRequest +
                
                '<dt>フリーワード検索</dt>'+
                '<dd class="js-confirm-search-free-word"></dd>'+
                '<dt class="js-confirm-fdp-title">周辺エリア情報設定</dt>'+
                '<dd class="js-confirm-fdp"></dd>'+
			'</dl>'
		);
		this.$searchType = this.$element.find('.js-confirm-search-type');
		this.$estateType = this.$element.find('.js-confirm-enabled-estate-type');
		this.$pref       = this.$element.find('.js-confirm-pref');
		this.$estateRequestFlg = this.$element.find('.js-confirm-estate_request_flg');
        this.$freeWord = this.$element.find('.js-confirm-search-free-word');
        this.$fdp = this.$element.find('.js-confirm-fdp');
        this.$fdpTitle = this.$element.find('.js-confirm-fdp-title');
		this.Master = Master;
	};
	ConfirmBasicSettingView.prototype.render = function (setting) {
		var self = this;

		var searchType = setting.area_search_filter.search_type;
		var estateType = setting.enabled_estate_type;
		var pref       = setting.area_search_filter.area_1;
		//物件リクエスト
		var estateRequestFlg = setting.estate_request_flg;
        //フリーワード
        var freeWordFlg = setting.display_freeword;
        var fdpFlg = setting.display_fdp.fdp_type;
        var townFlg = setting.display_fdp.town_type;
        var isDFP = setting.is_fdp;
        var townLabel = setting.town_label;
        var fdpcheckLabel = setting.fdp_check_label;
        var towncheckLabel = setting.town_check_label;

		var searchTypeHtml = '<ul class="list-item">';
		$.each(searchType, function (i, type) {
			var label = self.Master.searchTypeMaster[ type ];
			if (!label) {
				return;
			}
			if (type == 3) {
                // 「地図から探す」は、オプション
                if ( setting.mapOption == false )  {
                    return;
                }
				if (setting.map_search_here_enabled == 1 ) {
					// 初期ph3では現在地から探すをはずす
					// label += '【現在地から探す（スマホのみ）:利用あり】';
				} else {
					// label += '【現在地から探す（スマホのみ）:利用なし】';
				}
			}
			searchTypeHtml += '<li>'+app.h( label )+'</li>';
		});
        searchTypeHtml += '</ul>';
		this.$searchType.html( searchTypeHtml );

		// 物件種目
		var estateTypeHtml = '<ul class="list-item">';
		$.each(estateType, function (i, type) {
			var label = self.Master.estateTypeMaster[ type ];
			if (!label) {
				return;
			}
			estateTypeHtml += '<li>'+app.h( label )+'</li>';
		});
		estateTypeHtml += '</ul>';
		this.$estateType.html( estateTypeHtml );

		// 都道府県
		var prefHtml = '<ul class="list-item">';
		$.each(pref, function (i, code) {
			var label = self.Master.prefMaster[ code ];
			if (!label) {
				return;
			}
			prefHtml += '<li>'+app.h( label )+'</li>';
		});
		prefHtml += '</ul>';
		this.$pref.html( prefHtml );

		//物件リクエスト
		var estateRequestFlgHtml = '<ul class="list-item">';

		if(estateRequestFlg == 1) {
			estateRequestFlgHtml += '<li>利用する</li>';			
		}else{
			estateRequestFlgHtml += '<li>利用しない</li>';			
		}
		estateRequestFlgHtml += '</ul>';
		this.$estateRequestFlg.html( estateRequestFlgHtml );

        //フリーワード
		var freeWordHtml = '<ul class="list-item">';

		if(freeWordFlg == 1) {
			freeWordHtml += '<li>利用する</li>';			
		}else{
			freeWordHtml += '<li>利用しない</li>';			
		}
		freeWordHtml += '</ul>';
		this.$freeWord.html( freeWordHtml );

		if (isDFP) {
			var fdpHtml = '<ul class="list-item">';
			if (typeof fdpFlg !== 'undefined' && fdpFlg.length > 0) {
				fdpHtml += '<li>'+fdpcheckLabel+'</li>';
				if (typeof townFlg !== 'undefined' && townFlg.length > 0) {
					fdpHtml += '<ul class="list-item">';
                    // 4489: Change UI setting FDP
					fdpHtml += '<li class="fdp-label"><strong>'+townLabel+'</strong></li>';
					fdpHtml += '</ul>';
					fdpHtml += '<ul class="list-item">';
					fdpHtml += '<li>'+towncheckLabel+'</li>';
					fdpHtml += '</ul>';
				}
			}else{
				fdpHtml += '<li>利用しない</li>';
			}
			fdpHtml += '</ul>';
			this.$fdp.html( fdpHtml );
		} else {
			this.$fdp.remove();
			this.$fdpTitle.remove();
		}


	};

	var ConfirmSpecialPageBasicView = estate.ConfirmSpecialPageBasicView = function (isPublished) {
		this.$element = $(
			'<dl class="confirm-basic">'+
				'<dt>ページの状態</dt>'+

				'<dd><p class="page-edit-status">'+
					(isPublished?'<span class="is-public">公開中</span>':'<span class="is-draft">下書き</span>')+
				'</p></dd>'+

				'<dt>ページのタイトル</dt>'+
				'<dd class="js-confirm-title"></dd>'+

				/*
				'<dt>ページの説明</dt>'+
				'<dd class="js-confirm-description"></dd>'+

				'<dt>ページのキーワード</dt>'+
				'<dd class="js-confirm-keywords"></dd>'+
				*/

				'<dt>ページ名</dt>'+
				'<dd class="js-confirm-filename"></dd>'+

				'<dt>特集ページの紹介コメント</dt>'+
				'<dd class="js-confirm-comment"></dd>'+
			'</dl>'
		);
	};
	ConfirmSpecialPageBasicView.prototype.render = function (setting) {
		var elems = ['title', 'description', 'filename', 'comment'];
		var i, l, name;
		for (i=0,l=elems.length;i<l;i++) {
			name = elems[i];
			this.$element.find('.js-confirm-'+name).text( setting[name] || '' );
		}

		var $keywords = $('<ul class="list-item"></ul>').appendTo(this.$element.find('.js-confirm-keywords'));
		for (i=0,l=setting.keywords.length;i<l;i++) {
			$keywords.append($('<li/>').text(setting.keywords[i]));
		}
	};

	var ConfirmSpecialBasicView = estate.ConfirmSpecialBasicView = function (Master) {
		this.$element = $(
			'<dl class="confirm-basic">'+
				'<dt>特集の物件種目</dt>'+
				'<dd class="js-confirm-estate-type"></dd>'+

                '<dt>特集の設定方法</dt>'+
                '<dd class="js-confirm-method-setting"></dd>'+

				// '<dt>取り扱い都道府県</dt>'+
				// '<dd class="js-confirm-pref"></dd>'+

				'<dt>ホームページ上の検索方法</dt>'+
				'<dd class="js-confirm-search-type"></dd>'+

				// '<dt>公開する物件</dt>'+
				// '<dd class="js-confirm-publish-estate"></dd>'+

    //             '<dt>手数料/広告費</dt>'+
    //             '<dd class="js-confirm-tesuyo-kokokuhi"></dd>'+
                
                '<dt>フリーワード検索</dt>'+
                '<dd class="js-confirm-search-free-word"></dd>'+
			'</dl>'
		);

		this.Master = Master;
	};
	ConfirmSpecialBasicView.prototype.render = function (setting, isModal) {
		var i,l,item;

		var estateHtml = '<ul class="list-item">';
		var estateTypeMaster = this.Master.estateTypeMaster;
		var shumokuTypeMaster = this.Master.shumokuTypeMaster;

		var shumokuShosai = {};
		if(typeof setting.search_filter.categories != 'undefied') {
			$.each(setting.search_filter.categories, function(i, type) {
				if(type.category_id == 'shumoku') {
					for(var sno=0; sno < type.items.length; sno++) {
						shumokuShosai[ type.items[sno].item_id ] = 1;
					}
				}
			});
		}

        //Method Setting
        var methodSettingHtml = '<ul class="list-item">';

        if (setting.method_setting == 1) {
            methodSettingHtml += '<li>条件を指定して特集をつくる</li>';
        } else if (setting.method_setting == 2) {
            methodSettingHtml += '<li>個別に物件を選択して特集をつくる</li>';
        } else {
            methodSettingHtml += '<li>おすすめ公開中の特集をつくる</li>';
        }
        methodSettingHtml += '</ul>';
        this.$element.find('.js-confirm-method-setting').html(methodSettingHtml);

		var estateClassNo = 0;
		$.each(setting.enabled_estate_type, function (i, type) {
			estateHtml += '<li>' + estateTypeMaster[ type ] + '</li>';
			estateClassNo = type;

			var shosai = '';

			if(typeof shumokuTypeMaster[ type ] != 'undefined') {
				console.log(shumokuTypeMaster[ type ]);
				for(var sno = 0; sno < shumokuTypeMaster[ type ].length; sno++) {
					var item_id = shumokuTypeMaster[ type ][sno].item_id;
					if(shumokuShosai[ item_id ] == 1) {
						shosai += '<li>' + shumokuTypeMaster[ type ][sno].label + '</li>';
					}
				}
				if(shosai != '') {
					estateHtml += '<br><span class="custom-list-item">' + shosai + '</span>';
				}
			}
			if(type == 12 && setting.enabled_estate_type.length == 1) {
				if(setting.owner_change == 1) {
					estateHtml += '<br/><br/>';
					estateHtml += '<li>オーナーチェンジ:オーナーチェンジのみ</li>';
				} else if(setting.owner_change == 2) {
					estateHtml += '<br/><br/>';
					estateHtml += '<li>オーナーチェンジ:オーナーチェンジを除く</li>';
				}
			}

			estateHtml += '<br/>';
		});
		estateHtml += '</ul>';
		this.$element.find('.js-confirm-estate-type').html(estateHtml);

		// var prefs = setting.area_search_filter.area_1 || [];
		// var prefHtml = '<ul class="list-item">';
		// for (i=0,l=prefs.length;i<l;i++) {
		// 	item = prefs[i];
		// 	if (this.Master.prefMaster[item]) {
		// 		prefHtml += '<li>'+this.Master.prefMaster[item]+'</li>';
		// 	}
		// }
		// prefHtml += '</ul>';
		// this.$element.find('.js-confirm-pref').html(prefHtml);

		var searchTypeMaster = this.Master.searchTypeMaster;
        var $searchType = this.$element.find('.js-confirm-search-type');
        // 検索ページ有無
        var searchTypeHtml = '';
        // 検索方法
        searchTypeHtml = this.Master.specialSearchPageTypeMaster[setting.area_search_filter.has_search_page?1:0];
        var searchTypes = [];
        $.each(setting.area_search_filter.search_type || [], function (i, type) {
            var value = searchTypeMaster[type];
            if (type == 3) {
                // 「地図から探す」は、オプション
                if ( setting.mapOption == false )  {
                    return;
                }

                if (setting.map_search_here_enabled == 1 ) {
                    // 初期ph3では現在地から探すをはずす
                    // value += '【現在地から探す（スマホのみ）:利用あり】';
                } else {
                    // value += '【現在地から探す（スマホのみ）:利用なし】';
                }
            }
            searchTypes.push(value);
        });
        if (setting.area_search_filter.has_search_page) {
            searchTypeHtml += '（'+searchTypes.join(',')+'）';
        }
		$searchType.html(searchTypeHtml);

		// var publishEstateHtml = '<ul class="list-item">';
		// if (setting.jisha_bukken) {
		// 	publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.jisha_bukken+'</li>';
		// }
		// if (setting.niji_kokoku) {
		// 	publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku+'</li>';
		// }
		// if (setting.niji_kokoku_jido_kokai) {
		// 	publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku_jido_kokai+'</li>';
		// }
		// if (setting.only_er_enabled) {
		// 	publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.only_er_enabled+'</li>';
		// }
		// publishEstateHtml += '</ul>';
		// this.$element.find('.js-confirm-publish-estate').html(publishEstateHtml);
		// var tesuryoKokokuhiHtml = '<ul class="list-item">';
		// if (setting.end_muke_enabled) {
		// 	tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.end_muke_enabled+'</li>';
		// }
		// if (setting.tesuryo_ari_nomi) {
		// 	if(estateClassNo < 7) {
		// 		tesuryoKokokuhiHtml += '<li>手数料ありの物件だけ表示する</li>';
		// 	} else {
		// 	tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.tesuryo_ari_nomi+'</li>';
		// 	}
		// }
		// if (setting.tesuryo_wakare_komi) {
		// 	tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.tesuryo_wakare_komi+'</li>';
		// }
		// if (setting.kokokuhi_joken_ari) {
		// 	tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.kokokuhi_joken_ari+'</li>';
		// }
		// tesuryoKokokuhiHtml += '</ul>';
		// this.$element.find('.js-confirm-tesuyo-kokokuhi').html(tesuryoKokokuhiHtml);
        
        //フリーワード
		var freeWordHtml = '<ul class="list-item">';

        if(setting.display_freeword == 1) {
            freeWordHtml += '<li>利用する</li>';			
        }else{
            freeWordHtml += '<li>利用しない</li>';			
        }
        freeWordHtml += '</ul>';
        this.$element.find('.js-confirm-search-free-word').html( freeWordHtml );
	};

    var ConfirmHouseConditionModalBasicView = estate.ConfirmHouseConditionModalBasicView = function(Master) {
        this.$element = $(
            '<dl class="confirm-basic">'+
                '<dt>特集の物件種目</dt>'+
				'<dd class="js-confirm-estate-type"></dd>'+

				'<dt>設定方法を選択</dt>'+
                '<dd class="js-confirm-search-type"></dd>'+

                '<dt>取り扱い都道府県</dt>'+
                '<dd class="js-confirm-pref"></dd>'+

                '<dt>公開する物件</dt>'+
                '<dd class="js-confirm-publish-estate"></dd>'+

                '<dt class="js-confirm-house">手数料/広告費</dt>'+
                '<dd class="js-confirm-tesuyo-kokokuhi"></dd>'+
            '</dl>'
        );

        this.Master = Master;
    }
    ConfirmHouseConditionModalBasicView.prototype.render = function (setting) {
        var i,l,item;
        var estateHtml = '<ul class="list-item">';
		var estateTypeMaster = this.Master.estateTypeMaster;
		var shumokuTypeMaster = this.Master.shumokuTypeMaster;

		var shumokuShosai = {};
		if(typeof setting.search_filter.categories != 'undefied') {
			$.each(setting.search_filter.categories, function(i, type) {
				if(type.category_id == 'shumoku') {
					for(var sno=0; sno < type.items.length; sno++) {
						shumokuShosai[ type.items[sno].item_id ] = 1;
					}
				}
			});
		}
        var estateClassNo = 0;
		$.each(setting.enabled_estate_type, function (i, type) {
			estateHtml += '<li>' + estateTypeMaster[ type ] + '</li>';
			estateClassNo = type;

			var shosai = '';

			if(typeof shumokuTypeMaster[ type ] != 'undefined') {
				for(var sno = 0; sno < shumokuTypeMaster[ type ].length; sno++) {
					var item_id = shumokuTypeMaster[ type ][sno].item_id;
					if(shumokuShosai[ item_id ] == 1) {
						shosai += '<li>' + shumokuTypeMaster[ type ][sno].label + '</li>';
					}
				}
				if(shosai != '') {
					estateHtml += '<br><span class="custom-list-item">' + shosai + '</span>';
				}
			}
			if(type == 12 && setting.enabled_estate_type.length == 1) {
				if(setting.owner_change == 1) {
					estateHtml += '<br/><br/>';
					estateHtml += '<li>オーナーチェンジ:オーナーチェンジのみ</li>';
				} else if(setting.owner_change == 2) {
					estateHtml += '<br/><br/>';
					estateHtml += '<li>オーナーチェンジ:オーナーチェンジを除く</li>';
				}
			}

			estateHtml += '<br/>';
		});
		estateHtml += '</ul>';
		this.$element.find('.js-confirm-estate-type').html(estateHtml);

        var $searchType = this.$element.find('.js-confirm-search-type');
        var searchTypeMaster = this.Master.SearchTypeCondition;
        var searchTypeHtml = searchTypeMaster[setting.area_search_filter.search_condition['type']];
        $searchType.html(searchTypeHtml);

        var prefs = setting.area_search_filter.area_1 || [];
        var prefHtml = '<ul class="list-item">';
        for (i=0,l=prefs.length;i<l;i++) {
         item = prefs[i];
         if (this.Master.prefMaster[item]) {
             prefHtml += '<li>'+this.Master.prefMaster[item]+'</li>';
         }
        }
        prefHtml += '</ul>';
        this.$element.find('.js-confirm-pref').html(prefHtml);

        var estateClassNo = 0;
        $.each(setting.enabled_estate_type, function (i, type) {
            estateClassNo = type;
        });

        var publishEstateHtml = '<ul class="list-item">';
        if (setting.jisha_bukken) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.jisha_bukken+'</li>';
        }
        if (setting.niji_kokoku) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku+'</li>';
        }
        if (setting.niji_kokoku_jido_kokai) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku_jido_kokai+'</li>';
        }
        if (setting.only_er_enabled) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.only_er_enabled+'</li>';
        }
        publishEstateHtml += '</ul>';
        this.$element.find('.js-confirm-publish-estate').html(publishEstateHtml);
        var tesuryoKokokuhiHtml = '<ul class="list-item">';
        if (setting.end_muke_enabled) {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.end_muke_enabled+'</li>';
        }
        if (setting.tesuryo_ari_nomi) {
         if(estateClassNo < 7) {
             tesuryoKokokuhiHtml += '<li>手数料ありの物件だけ表示する</li>';
         } else {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.tesuryo_ari_nomi+'</li>';
         }
        }
        if (setting.tesuryo_wakare_komi) {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.tesuryo_wakare_komi+'</li>';
        }
        if (setting.kokokuhi_joken_ari) {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.kokokuhi_joken_ari+'</li>';
        }
        tesuryoKokokuhiHtml += '</ul>';
        this.$element.find('.js-confirm-tesuyo-kokokuhi').html(tesuryoKokokuhiHtml);
    };

    var ConfirmHouseSpecialBasicView = estate.ConfirmHouseSpecialBasicView = function (Master) {
        this.$element = $(
            '<dl class="confirm-basic">'+

                '<dt class="search-type">物件の指定方法</dt>'+
                '<dd class="js-confirm-search-type search-type"></dd>'+

                '<dt class="js-confirm-house">取り扱い都道府県</dt>'+
                '<dd class="js-confirm-pref"></dd>'+

                '<dt>公開する物件</dt>'+
                '<dd class="js-confirm-publish-estate"></dd>'+

                '<dt class="js-confirm-house">手数料/広告費</dt>'+
                '<dd class="js-confirm-tesuyo-kokokuhi"></dd>'+
            '</dl>'
        );

        this.Master = Master;
    };
    ConfirmHouseSpecialBasicView.prototype.render = function (setting) {
        var i,l,item;

        if (setting.area_search_filter.has_search_page == 0) {
            var searchTypeMaster = this.Master.searchTypeDirectMaster;
            var searchType;
            if (setting.area_search_filter.search_type[0] == 1) {
                searchType = setting.area_search_filter.search_type[0] + '-' + setting.area_search_filter.choson_search_enabled;
            } else {
                searchType = setting.area_search_filter.search_type[0]
            }
            var searchTypeHtml = searchTypeMaster[searchType];
            this.$element.find('.js-confirm-search-type').html(searchTypeHtml);
            this.$element.find('.search-type').show();
        } else {
            this.$element.find('.search-type').hide();
        }
        var prefs = setting.area_search_filter.area_1 || [];
        var prefHtml = '<ul class="list-item">';
        for (i=0,l=prefs.length;i<l;i++) {
         item = prefs[i];
         if (this.Master.prefMaster[item]) {
             prefHtml += '<li>'+this.Master.prefMaster[item]+'</li>';
         }
        }
        prefHtml += '</ul>';
        this.$element.find('.js-confirm-pref').html(prefHtml);

        var estateClassNo = 0;
        $.each(setting.enabled_estate_type, function (i, type) {
            estateClassNo = type;
        });

        var publishEstateHtml = '<ul class="list-item">';
        if (setting.jisha_bukken) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.jisha_bukken+'</li>';
        }
        if (setting.niji_kokoku) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku+'</li>';
        }
        if (setting.niji_kokoku_jido_kokai) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku_jido_kokai+'</li>';
        }
        if (setting.only_er_enabled) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.only_er_enabled+'</li>';
        }
        publishEstateHtml += '</ul>';
        this.$element.find('.js-confirm-publish-estate').html(publishEstateHtml);
        var tesuryoKokokuhiHtml = '<ul class="list-item">';
        if (setting.end_muke_enabled) {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.end_muke_enabled+'</li>';
        }
        if (setting.tesuryo_ari_nomi) {
         if(estateClassNo < 7) {
             tesuryoKokokuhiHtml += '<li>手数料ありの物件だけ表示する</li>';
         } else {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.tesuryo_ari_nomi+'</li>';
         }
        }
        if (setting.tesuryo_wakare_komi) {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.tesuryo_wakare_komi+'</li>';
        }
        if (setting.kokokuhi_joken_ari) {
         tesuryoKokokuhiHtml += '<li>'+this.Master.specialTesuryoKokokuhiMaster.kokokuhi_joken_ari+'</li>';
        }
        tesuryoKokokuhiHtml += '</ul>';
        this.$element.find('.js-confirm-tesuyo-kokokuhi').html(tesuryoKokokuhiHtml);
    };

    var ConfirmSecondHouseSpecialView = estate.ConfirmSecondHouseSpecialView = function (Master) {
        this.$element = $(
            '<dl class="confirm-basic">'+
                '<dt>公開する物件</dt>'+
                '<dd class="js-confirm-publish-estate"></dd>'+
            '</dl>'
        );

        this.Master = Master;
    };
    ConfirmSecondHouseSpecialView.prototype.render = function (setting) {
        var publishEstateHtml = '<ul class="list-item">';
        if (setting.jisha_bukken) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.jisha_bukken+'</li>';
        }
        if (setting.niji_kokoku) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku+'</li>';
        }
        if (setting.niji_kokoku_jido_kokai) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.niji_kokoku_jido_kokai+'</li>';
        }
        if (setting.only_er_enabled) {
         publishEstateHtml += '<li>'+this.Master.specialPublishEstateMaster.only_er_enabled+'</li>';
        }
        publishEstateHtml += '</ul>';
        this.$element.find('.js-confirm-publish-estate').html(publishEstateHtml);
    };

	var ConfirmSecondBasicView = estate.ConfirmSecondBasicView = function (Master) {
		this.$element = $(
				'<dl class="confirm-basic">'+
					'<dt>2次広告自動公開</dt>'+
					'<dd class="js-confirm-enabled"></dd>'+

					'<dt>都道府県</dt>'+
					'<dd class="js-confirm-pref"></dd>'+

					'<dt>物件種目</dt>'+
					'<dd class="js-confirm-enabled-estate-type"></dd>'+

					'<dt>市区郡/沿線・駅選択</dt>'+
					'<dd class="js-confirm-search-type"></dd>'+
				'</dl>'
			);
			this.$enabled    = this.$element.find('.js-confirm-enabled');
			this.$searchType = this.$element.find('.js-confirm-search-type');
			this.$estateType = this.$element.find('.js-confirm-enabled-estate-type');
			this.$pref       = this.$element.find('.js-confirm-pref');

			this.Master = Master;
	};
	ConfirmSecondBasicView.prototype.render = function (setting) {
		var self = this;
		var enabled    = setting.enabled;
		var searchType = setting.area_search_filter.search_type || '';
		var estateType = setting.enabled_estate_type;
		var pref       = setting.area_search_filter.area_1;

		this.$enabled.text( this.Master.secondEnabledMaster[ enabled ] || '' );

		var searchTypeStr = '';
		if (searchType == 1) {
			if (setting.area_search_filter.choson_search_enabled == 1) {
				searchTypeStr = '町名を対象にする';
			} else {
                searchTypeStr = '市区郡を対象にする';
			}
		} else if (this.Master.searchTypeMaster[ searchType ]) {
			searchTypeStr = this.Master.searchTypeMaster[ searchType ];
		}
		this.$searchType.html(searchTypeStr);

		// 物件種目
		var estateTypeHtml = '<ul class="list-item">';
		$.each(estateType, function (i, type) {
			var label = self.Master.estateTypeMaster[ type ];
			if (!label) {
				return;
			}
			estateTypeHtml += '<li>'+app.h( label )+'</li>';
		});
		estateTypeHtml += '</ul>';
		this.$estateType.html( estateTypeHtml );

		// 都道府県
		var prefHtml = '<ul class="list-item">';
		$.each(pref, function (i, code) {
			var label = self.Master.prefMaster[ code ];
			if (!label) {
				return;
			}
			prefHtml += '<li>'+app.h( label )+'</li>';
		});
		prefHtml += '</ul>';
		this.$pref.html( prefHtml );
	};


	var ConfirmShikugunView = estate.ConfirmShikugunView = function (Master) {
		this.$element = $('<table class="tb-basic"></table>');
		this.Master = Master;
	};
	ConfirmShikugunView.prototype.render = function(setting, isModal) {
		this.$element.empty();

        this.MORE_CLOSED = '…もっと見る';
        this.MORE_OPENED = '…閉じる';

        var self = this;
        // 町名もっと見る
        this.$element.on('click', '.choson-search-list__more', function () {
            var $more = $(this);
            if ($more.hasClass('is-opened')) {
                $more.parent().find('span:gt(4)').hide();
                $more.removeClass('is-opened');
                $more.text(self.MORE_CLOSED);
            } else {
                $more.parent().find('span').show();
                $more.addClass('is-opened');
                $more.text(self.MORE_OPENED);
            }
        });

        var prefCodes = setting.area_search_filter.area_1;
		var shikuguns = setting.area_search_filter.area_2;
		var chosons   = setting.area_search_filter.area_5;
		var choazas   = setting.area_search_filter.area_6;

		EstateMaster.getShikugun(prefCodes);

		var i,l;
		var prefCode;
		var $tr;
		for (i=0,l=prefCodes.length;i< l;i++) {
			prefCode = prefCodes[i];
			$tr = $('<tr/>');
			$tr.appendTo(this.$element);

            if (typeof isModal != 'undefined' && isModal) {
                this.renderRow(prefCode, $tr, shikuguns[ prefCode ] || [], chosons[ prefCode ] || {}, choazas[ prefCode ] || {}, setting.area_search_filter.search_condition['type'] == 2);
            } else {
                this.renderRow(prefCode, $tr, shikuguns[ prefCode ] || [], chosons[ prefCode ] || {}, choazas[ prefCode ] || {}, setting.area_search_filter.choson_search_enabled == 1);
            }
		}
	};
	ConfirmShikugunView.prototype.renderRow = function (prefCode, $row, shikugunsInPref, chosonsInPref, choazasInPref, chosonSearchEnabled) {
		$row.append('<th class="cell1 alC">'+this.Master.prefMaster[prefCode]+'</th>');
		var $selected = $('<td class="cell2"></td>').appendTo($row);

		if (!shikugunsInPref.length) {
			return;
		}

		var self = this;
		var closer = app.loading();
		EstateMaster.getShikugun(prefCode).done(function (data) {
			closer();
			var shikugunCode;
			var shikugunName;
			var $ul = $('<ul class="list-item"></ul>');
			var renderChosons = [];
			for (var i=0,l=shikugunsInPref.length;i< l;i++) {
				shikugunCode = shikugunsInPref[i];
				if (data[0].shikugunMap[ shikugunCode ]) {
					shikugunName = data[0].shikugunMap[ shikugunCode ].shikugun_nm;
					$ul.append('<li>'+app.h(shikugunName)+'</li>');

                    if (chosonsInPref[shikugunCode] && chosonsInPref[shikugunCode].length) {
                        renderChosons.push({
                            shikugun: data[0].shikugunMap[ shikugunCode ],
                            chosons: chosonsInPref[shikugunCode],
                            choazas: choazasInPref[shikugunCode]
                        });
                    }
				}
			}
			$selected.append($ul);

			if (chosonSearchEnabled && renderChosons.length) {
				var $chosons = $('<div class="choson-search-list"></div>').appendTo($selected);
                for (var i = 0; renderChosons[i]; i++) {
                    (function ($dl, renderChoson) {
                        var closer = app.loading();
                        EstateMaster.getChoson([renderChoson.shikugun.code]).done(function (data) {
                            closer();
                            var dlHtml = '<dt><span>'+app.h(data.shikugun_nm)+'</span></dt>';
                            var choson;
                            var j = 0, jl = renderChoson.chosons.length;
                            var renderCount = 0;
                            dlHtml += '<dd>';
                            for (;j < jl; j++) {
                                if (!data.chosonMap[renderChoson.chosons[j]]) {
                                    continue;
                                }
                                renderCount++;
                                choson = data.chosonMap[renderChoson.chosons[j]];

                                // 町字が存在する?
                                if(choazasInPref[ data.shikugun_cd ] !== undefined && choazasInPref[ data.shikugun_cd ][ choson.code ] !== undefined) {
                                    dlHtml += '<span>';
                                    dlHtml += '<p style="display:inline-block"';
                                    dlHtml += ' onmouseover="' + "$(this).closest('span').find('.tc-choson-detail').eq(0).show();" +'"';
                                    dlHtml += '  onmouseout="' + "$(this).closest('span').find('.tc-choson-detail').eq(0).hide();" +'"';
                                    dlHtml += '">'+app.h(choson.choson_nm)+'</p>';
                                    dlHtml += '<p style="color:#0747a6;display:inline-block">*</p>';

                                    dlHtml += '<div class="tc-choson-detail">';
                                    $.each(choson.choazas, function(index, choazaObj) {
                                        if(choazasInPref[ data.shikugun_cd ][ choson.code ].indexOf(choazaObj.code) >= 0) {
                                            dlHtml += choazaObj.choson_nm + '&nbsp;'　
                                        }
                                    });
                                    dlHtml += '</div>';

                                    dlHtml += '</span>';
                                } else {
                                    dlHtml += '<span>'+app.h(choson.choson_nm)+'</span>'
                                }
                            }
                            dlHtml += '</dd>';

                            $dl.html(dlHtml);

                            // 一定数以上の場合、もっと見るリンク
                            if (renderCount > 5) {
                                $dl.find('dd span:gt(4)').hide();
                                $dl.find('dd').append($('<a class="choson-search-list__more">'+self.MORE_CLOSED+'</a>'));
                            }
                        })
                    })($('<dl/>').appendTo($chosons), renderChosons[i]);
                }
			}
		});
	};

	var ConfirmEnsenView = estate.ConfirmEnsenView = function (Master) {
		this.$element = $('<table class="tb-basic"></table>');
		this.Master = Master;
	};
	ConfirmEnsenView.prototype.render = function (setting) {
		this.$element.empty();

		var prefCodes = setting.area_search_filter.area_1;
		var ensens    = setting.area_search_filter.area_3;
		var ekis      = setting.area_search_filter.area_4;

		EstateMaster.getEnsen(prefCodes);

		var i,l;
		var prefCode;
		var $tr;
		for (i=0,l=prefCodes.length;i< l;i++) {
			prefCode = prefCodes[i];
			$tr = $('<tr/>');
			$tr.appendTo(this.$element);

			this.renderRow(prefCode, $tr, ensens[ prefCode ] || [], ekis[ prefCode ] || []);
		}
	};
	ConfirmEnsenView.prototype.renderRow = function (prefCode, $row, ensensInPref, ekisInPref) {
		var self = this;

		var $th = $('<th class="cell1 alC">'+this.Master.prefMaster[prefCode]+'</th>').appendTo($row);
		var $selectedEnsen = $('<td class="cell2"></td>').appendTo($row);
		var $selectedEki   = $('<td class="cell3"></td>').appendTo($row);

		if (!ekisInPref.length) {
			return;
		}

		var closer = app.loading();
		//確認画面では沿線の駅を全部持ってくるためにken_cdを解除
		EstateMaster.setPref('');
		EstateMaster.getEki(ensensInPref);
		EstateMaster.getEnsen(prefCode).done(function (kenData) {
			var ensenCode;
			var ensenData;
			var ensenLength = 0;
			var $beforeRow;
			var $currentRow;
			for (var i=0,l=ensensInPref.length;i< l;i++) {
				ensenCode = ensensInPref[i];
				ensenData = kenData[0].ensenMap[ ensenCode ];
				if (!ensenData) {
					continue;
				}
				ensenLength++;

				if (!$beforeRow) {
					$currentRow = $beforeRow = $row;
				}
				else {
					$beforeRow = $currentRow;
					$currentRow = $('<tr/>');
					$beforeRow.after($currentRow);
					$selectedEnsen = $('<td class="cell2"></td>').appendTo($currentRow);
					$selectedEki   = $('<td class="cell3"></td>').appendTo($currentRow);
				}
				$th.attr('rowspan', ensenLength);

				$selectedEnsen.text(ensenData.ensen_nm);
				self.renderEki(ensenCode, ekisInPref, $selectedEki);
			}
			closer();
		});
	};
	ConfirmEnsenView.prototype.renderEki = function (ensenCode, ekisInPref, $selectedEki) {
		var closer = app.loading();

		EstateMaster.getEki(ensenCode).done(function (data) {
			closer();

			var ekiCode;
			var ekiName;
			var $ul = $('<ul class="list-item"></ul>');
			for (var i=0,l=ekisInPref.length;i< l;i++) {
				ekiCode = ekisInPref[i];
				if (data.ekiMap[ ekiCode ]) {
					ekiName = data.ekiMap[ ekiCode ].eki_nm;
					$ul.append('<li>'+app.h(ekiName)+'</li>');
				}
			}
			$selectedEki.append($ul);
		});
	};

	var ConfirmSearchFilterView = estate.ConfirmSearchFilterView = function () {
		this.$element = $('<table class="tb-basic ad-terms"></table>');
		this.deferred = null;
	};
	ConfirmSearchFilterView.prototype.render = function (setting) {
		var self = this;

		var closer = app.loading();
		this.$element.empty();
		var def = this.deferred = this.getSearchFilterMaster(setting);
		def.done(function (data) {
			closer();

			if (def !== self.deferred) {
				return;
			}

			var specialHtml = '';
			var i,il,category, categoryMaster, categoryHtml;
			var j,jl,item, itemMaster;
			var valueMap = {};
			for (i=0,il=setting.search_filter.categories.length;i<il;i++) {
				category = setting.search_filter.categories[i];
				valueMap[category.category_id] = {};
				for (j=0,jl=category.items.length;j<jl;j++) {
					item = category.items[j];
					valueMap[category.category_id][item.item_id] = item.item_value;
				}
			}

			var itemValueMap;
			for (i=0,il=data.categories.length;i<il;i++) {
				category = data.categories[i];
				categoryMaster = data.categoryMap[category.category_id];
				if (!categoryMaster) {
					continue;
				}
				if(category.category_id === 'shumoku') {
					continue;
				}

				categoryHtml = '';
				itemValueMap = valueMap[category.category_id] || {};
				if (category.category_id === 'kakaku') {
					categoryHtml = self.renderKakaku(category, categoryMaster, itemValueMap);
				} else if(category.category_id === 'rimawari') {
					categoryHtml = self.renderRimawari(category, categoryMaster, itemValueMap);
				} else if(category.category_id === 'menseki') {
					var categoryHtml1 = '';
					var categoryHtml2 = '';
					for (j=0,jl=category.items.length;j<jl;j++) {
						item = category.items[j];
						itemMaster = categoryMaster.itemMap[item.item_id];
						if (!itemMaster) {
							continue;
						}

						var itemId = item.item_id;
						if(itemId == "1") {
							categoryHtml1 += '<dt>面積<span class="tx-annotation">（建物面積・専有面積・使用部分面積）　</span></dt>';
							if(itemValueMap[item.item_id] == 0 || typeof itemValueMap[item.item_id] == "undefined") {
								categoryHtml1 += '<dd>下限なし</dd>';
							} else {
								var display_val = self.renderItem(item, itemMaster, itemValueMap[item.item_id]);
								display_val = display_val.replace('以上', '');
								categoryHtml1 += '<dd>' + display_val + '</dd>';
							}
						} else if(itemId == "3") {
							categoryHtml1 += '<dd>&nbsp;～&nbsp;</dd>';
							if(itemValueMap[item.item_id] == 0 || typeof itemValueMap[item.item_id] == "undefined") {
								categoryHtml1 += '<dd>上限なし</dd>';
							} else {
								categoryHtml1 += '<dd>' + self.renderItem(item, itemMaster, itemValueMap[item.item_id]) + '</dd>';
							}
						} else if(itemId == "2") {
							categoryHtml2 += '<dt>土地面積　</dt>';
							if(itemValueMap[item.item_id] == 0 || typeof itemValueMap[item.item_id] == "undefined") {
								categoryHtml2 += '<dd>下限なし</dd>';
							} else {
								var display_val = self.renderItem(item, itemMaster, itemValueMap[item.item_id]);
								display_val = display_val.replace('以上', '');
								categoryHtml2 += '<dd>' + display_val + '</dd>';
							}
						} else {
							categoryHtml2 += '<dd>&nbsp;～&nbsp;</dd>';
							if(itemValueMap[item.item_id] == 0 || typeof itemValueMap[item.item_id] == "undefined") {
								categoryHtml2 += '<dd>上限なし</dd>';
							} else {
								categoryHtml2 += '<dd>' + self.renderItem(item, itemMaster, itemValueMap[item.item_id]) + '</dd>';
							}
						}
					}
					categoryHtml += '<dl class="dl-inlineb">';
					categoryHtml += categoryHtml1;
					if(categoryHtml1 != "" && categoryHtml2 != "") {
						categoryHtml += '<dd>　/　</dd>';
					}
					categoryHtml += categoryHtml2;
					categoryHtml += '</dl>';
				} else if(category.category_id === 'chikunensu') {
                    categoryHtml = self.renderChikunensu(category, categoryMaster, itemValueMap);
				} else {
					for (j=0,jl=category.items.length;j<jl;j++) {
						item = category.items[j];
						itemMaster = categoryMaster.itemMap[item.item_id];
						if (!itemMaster) {
							continue;
						}
						if ((category.category_id === 'menseki') && category.items[1]) {
							if (j == 0) {
								categoryHtml += '<dl class="dl-inlineb">';
								categoryHtml += '<dt>面積<span class="tx-annotation">（建物面積・専有面積・使用部分面積）</span></dt>';
								categoryHtml += '<dd class="parts-split">' + self.renderItem(item, itemMaster, itemValueMap[item.item_id]) + '</dd>';
							} else {
								categoryHtml += '<dt>土地面積</dt>';
								categoryHtml += ' <dd>' + self.renderItem(item, itemMaster, itemValueMap[item.item_id]) + '</dd>';
								categoryHtml += '</dl>';
							}
						} else if(category.category_id === 'reformable_parts' && item.label == '水回り') {
							var categoryHtmlTmp = self.renderItem(item, itemMaster, itemValueMap[item.item_id]);
							categoryHtmlTmp = categoryHtmlTmp.replace('その他', '水回り(その他)');
							categoryHtml += categoryHtmlTmp;
						} else if(category.category_id === 'reformable_parts' && item.label == '内装') {
							var categoryHtmlTmp = self.renderItem(item, itemMaster, itemValueMap[item.item_id]);
							categoryHtmlTmp = categoryHtmlTmp.replace('その他', '内装(その他)');
							categoryHtml += categoryHtmlTmp;
						} else {
							categoryHtml += self.renderItem(item, itemMaster, itemValueMap[item.item_id]);
						}
					}
					if (categoryHtml) {
						categoryHtml = '<ul class="list-item">' + categoryHtml + '</ul>';
					}
					else if (category.category_id === 'keiyaku_joken') {
						for (var tmp=0,tm=category.items[0].options.length; tmp < tm; tmp++) {
							if (category.items[0].options[tmp].value == 0) {
								categoryHtml = category.items[0].options[tmp].label;
							}
						}
					}
					else {
						categoryHtml = '（未設定）';
					}
				}
				if (categoryHtml) {
                    if (category.category_id === 'reform_renovation') {
                        categoryMaster.label = 'リフォーム・リノベーション済/予定含む';
                    }
					specialHtml += '<tr><th class="alC nowrap">'+app.h(categoryMaster.label)+'</th><td>'+categoryHtml+'</td>';
				}
			}
			if (specialHtml) {
				self.$element.html(specialHtml);
			}
		});
	};
	ConfirmSearchFilterView.prototype.getSearchFilterMaster = function (setting) {
		throw Error('override!');
	};
	ConfirmSearchFilterView.prototype.renderKakaku = function (category, categoryMaster, itemValueMap) {
		var itemMap = {};
		var i,l,item, itemMaster, val;
		for (i=0,l=category.items.length;i<l;i++) {
			item = category.items[i];
			itemMap[item.item_id] = item;
		}

		var result = '';
		var lower = '';
		var upper = '';
		if (itemMap[1]) {
			item = itemMap[1];
			itemMaster = categoryMaster.itemMap[1];
			val = itemMaster.optionMap[ itemValueMap[1] || 0 ];
			if (val) {
				lower = app.h(val);
			}
		}
		if (itemMap[2]) {
			item = itemMap[2];
			itemMaster = categoryMaster.itemMap[2];
			val = itemMaster.optionMap[ itemValueMap[2] || 0 ];
			if (val) {
				upper += app.h(val);
			}
		}
		if (lower || upper) {
			result += lower + ' ～ ' + upper + '<br>';
		}

		var itemHtml = '';
		for (i=2,l=categoryMaster.items.length;i<l;i++) {
			itemMaster = categoryMaster.items[i];
			item = itemMap[itemMaster.item_id];
			if (!item) {
				continue;
			}

			itemHtml += this.renderFlagItem(item, itemMaster, itemValueMap[item.item_id]);
		}
		if (itemHtml) {
			itemHtml = '<ul class="list-item">' + itemHtml + '</ul>';
		}
		return result + itemHtml;
	};
	ConfirmSearchFilterView.prototype.renderRimawari = function (category, categoryMaster, itemValueMap) {
		return this.renderKakaku(category, categoryMaster, itemValueMap);
	};
	ConfirmSearchFilterView.prototype.renderChikunensu = function  (category, categoryMaster, itemValueMap) {

		var itemMap = {};
		var i,l,item, itemMaster, val;
		for (i=0,l=category.items.length;i<l;i++) {
			item = category.items[i];
			itemMap[item.item_id] = item;
		}
		var itemHtml = '';
		var lower, upper;

		// itemValueMap[1]を3つに分割
		var itemValueMapTmp = [];
		itemValueMapTmp[1] = [];    // 新築&築後未入居
		itemValueMapTmp[2] = [];    // 築〇年以内
		itemValueMapTmp[3] = [];    // 新築を除く

		if(typeof itemMap[1] !== "undefined" && typeof itemValueMap[1] !== "undefined") {
			item = itemMap[1];
			itemMaster = categoryMaster.itemMap[1];
			for(var vno = 0; vno < itemValueMap[1].length; vno++) {

				switch(itemMaster.optionMap[ itemValueMap[1][vno] ]) {
					case '新築':
					case '築後未入居':
						itemValueMapTmp[1].push(itemValueMap[1][vno]);
						break;
					case '新築を除く':
						itemValueMapTmp[3].push(itemValueMap[1][vno]);
						break;
					default:
						itemValueMapTmp[2].push(itemValueMap[1][vno]);
						break;
				}
			}
			itemHtml += this.renderItem(item, itemMaster, itemValueMapTmp[1]);
		}

		if(itemValueMapTmp[2].length) {
			upper = itemMaster.optionMap[ itemValueMapTmp[2][0] ];
		} else {
			upper = '';
		}
		if(typeof itemMap[2] !== "undefined") {
			item = itemMap[2];
			itemMaster = categoryMaster.itemMap[2];
			val = itemMaster.optionMap[ itemValueMap[2] || 0 ];
			if(val != '下限なし') {
				lower = val;
			} else {
				lower = '';
			}
		}
		var priceRange = "";
		if (lower || upper) {
			if(!lower) {
				priceRange = '下限なし' + ' ～ ' + upper;
			} else if(!upper) {
				priceRange = lower + ' ～ ' + '上限なし';
			} else {
				priceRange = lower + ' ～ ' + upper;
			}
		} else {
			priceRange = '下限なし' + ' ～ 上限なし';
		}
		priceRange = '<li>' + priceRange + '</li>';

		var node = jQuery.parseHTML('<div>' + itemHtml + '</div>');
		var elem = jQuery(node[0]);

		// 新築が設定されている場合は価格不要かつ、新築を除くはなし
		if($(elem).find('li').length) {
			// 新築&築後未入居 のいずれかもしくは両方が選択済み
			if($(elem).find('li').eq(0).text() == '新築') {
				// 新築なら築年数は不要
				return itemHtml;
			}
			// 新築以外なら、要築年数
			itemHtml += priceRange;
			return itemHtml;
		}

		// 新築,築後未入居未設定
		if(itemValueMapTmp[3].length) {
			itemMaster = categoryMaster.itemMap[1];
			itemHtml += this.renderItem(item, itemMaster, itemValueMapTmp[3]);

			// 新築を除く選択時は価格を上部に追加
			if(itemHtml != '') {
				itemHtml = priceRange + itemHtml;
			}
		} else if (lower || upper) {
			itemHtml = priceRange;
		}
		if (itemHtml == '') {
			itemHtml = '（未設定）';
		}
		return itemHtml;
    };

	ConfirmSearchFilterView.prototype.renderItem = function (item, itemMaster, itemValue) {
		switch (itemMaster.type) {
		case 'list':
		case 'radio':
			return this.renderListItem(item, itemMaster, itemValue);
		case 'multi':
			return this.renderMultiItem(item, itemMaster, itemValue);
		default:
			return this.renderFlagItem(item, itemMaster, itemValue);
		}
	};
	ConfirmSearchFilterView.prototype.renderListItem = function (item, itemMaster, itemValue) {
		var label = itemMaster.optionMap[itemValue];
		if (!label) {
			return '';
		}
		return '<li>'+app.h(label)+'</li>';
	};
	ConfirmSearchFilterView.prototype.renderMultiItem = function (item, itemMaster, itemValue) {
		var result = '';
		var i,l,val, label;
		var values = itemValue || [];
		for (i=0,l=values.length;i<l;i++) {
			val = values[i];
			label = itemMaster.optionMap[val];
			if (label) {
				result += '<li>'+app.h(label)+'</li>';
			}
		}
		return result;
	};
	ConfirmSearchFilterView.prototype.renderFlagItem = function (item, itemMaster, itemValue) {
		if (!itemValue) {
			return '';
		}
		return '<li>'+app.h(itemMaster.label)+'</li>';
	};

	var ConfirmSpecialSearchFilterView = estate.ConfirmSpecialSearchFilterView = app.inherits(ConfirmSearchFilterView, function () {
		ConfirmSearchFilterView.apply(this, arguments);
	}, {
		getSearchFilterMaster: function (setting) {
			return EstateMaster.getSpecialSearchFilter(setting.enabled_estate_type);
		}
	});

	var ConfirmSecondSearchFilterView = estate.ConfirmSecondSearchFilterView = app.inherits(ConfirmSearchFilterView, function () {
		ConfirmSearchFilterView.apply(this, arguments);
	}, {
		getSearchFilterMaster: function (setting) {
			return EstateMaster.getSecondSearchFilter(setting.estate_class);
		}
	});
	ConfirmSecondSearchFilterView.prototype.render = function (setting) {

		this.$element = $('<table class="tb-basic ad-terms"></table>');
		this.deferred = null;

		var self = this;
		var closer = app.loading();
		this.$element.empty();
		var def = this.deferred = this.getSearchFilterMaster(setting);
		def.done(function (data) {
			closer();

			if (def !== self.deferred) {
				return;
			}

			var tableHtml = '';
			var typeIdx,typeIdxl,estateType,estateTypeCategories;
			var i,il,category, categoryMaster, categoryHtml;
			var j,jl,item, itemMaster;
			var valueMap = {};

			// 有効な物件種別を初期化
			for (typeIdx=0;typeIdx<setting.enabled_estate_type.length;typeIdx++) {
				valueMap[setting.enabled_estate_type[typeIdx]] = {};
			}

			// 設定内容を準備
			for (typeIdx=0,typeIdxl=setting.search_filter.estate_types.length;typeIdx<typeIdxl;typeIdx++) {
				estateType = setting.search_filter.estate_types[typeIdx].estate_type;
				estateTypeCategories = setting.search_filter.estate_types[typeIdx].categories;
				valueMap[estateType] = {};
				for (i = 0, il = estateTypeCategories.length; i < il; i++) {
					category = estateTypeCategories[i];
					valueMap[estateType] [category.category_id] = {};
					for (j = 0, jl = category.items.length; j < jl; j++) {
						item = category.items[j];
						valueMap[estateType] [category.category_id][item.item_id] = item.item_value;
					}
				}
			}

			// レンダリング
			$('.js-confirm-search-filter').empty();
			$('.js-confirm-search-filter').append('<h2>絞り込み条件</h2>');
			var itemValueMap;
			for (typeIdx=0;typeIdx<data.estate_types.length;typeIdx++) {
				tableHtml = '';
				estateType = data.estate_types[typeIdx].estate_type;
				estateTypeCategories = data.estate_types[typeIdx].categories;
				if (setting.enabled_estate_type.indexOf(String(estateType)) == -1){
					continue;
				}
				var $tabele = $('<table class="tb-basic ad-terms"></table>');
				for (i = 0, il = estateTypeCategories.length; i < il; i++) {
					category = estateTypeCategories[i];
					categoryMaster = data.categoryMap[typeIdx][category.category_id];
					if (!categoryMaster) {
						continue;
					}
					categoryHtml = '';
					itemValueMap = valueMap[estateType][category.category_id] || {};
					if (category.category_id === 'kakaku'
						|| category.category_id === 'tochi_ms' || category.category_id === 'tatemono_ms')  {
						categoryHtml = self.renderKakaku(category, categoryMaster, itemValueMap);
					}
					else {
						for (j = 0, jl = category.items.length; j < jl; j++) {
							item = category.items[j];
							itemMaster = categoryMaster.itemMap[item.item_id];
							if (!itemMaster) {
								continue;
							}
							if ((category.category_id === 'menseki') && category.items[1]) {
								if (j == 0) {
									categoryHtml += '<dl class="dl-inlineb">';
									categoryHtml += '<dt>面積<span class="tx-annotation">（建物面積・専有面積・使用部分面積）</span></dt>';
									categoryHtml += '<dd class="parts-split">' + self.renderItem(item, itemMaster, itemValueMap[item.item_id]) + '</dd>';
								} else {
									categoryHtml += '<dt>土地面積</dt>';
									categoryHtml += ' <dd>' + self.renderItem(item, itemMaster, itemValueMap[item.item_id]) + '</dd>';
									categoryHtml += '</dl>';
								}
							} else {
								categoryHtml += self.renderItem(item, itemMaster, itemValueMap[item.item_id]);
							}
						}
						if (categoryHtml) {
							if(category.category_id === 'tesuryo') {
								if(setting.estate_class == 1 || setting.estate_class == 2) {	// 賃貸
									categoryHtml = categoryHtml.replace(/（\S+）/, '');
								}
							}
							categoryHtml = '<ul class="list-item">' + categoryHtml + '</ul>';
						}
						else if (category.category_id === 'keiyaku_joken') {
							for (var tmp = 0, tm = category.items[0].options.length; tmp < tm; tmp++) {
								if (category.items[0].options[tmp].value == 0) {
									categoryHtml = category.items[0].options[tmp].label;
								}
							}
						}
						else {
							categoryHtml = '（未設定）';
						}
					}
					if (categoryHtml) {
						tableHtml += '<tr><th class="alC nowrap">' + app.h(categoryMaster.label) + '</th><td>' + categoryHtml + '</td>';
					}
				}
				if (tableHtml) {
					$tabele.html(tableHtml);
					var sectionId = 'section-'+estateType;
					$('.js-confirm-search-filter').append('<div id="'+sectionId+'" class="section"></div>');
					$('.js-confirm-search-filter').find('#'+sectionId).append("<h3>"+data.estateTypeMaster[estateType]+"</h3>");
					$('.js-confirm-search-filter').find('#'+sectionId).append($tabele);

				}
			}
		});
    };
    
    var ConfirmSpecialHousesListView = estate.ConfirmSpecialHousesListView = function () {
		this.$element = null;
    };
	ConfirmSpecialHousesListView.prototype.render = function (setting, page, sort) {
		var self = this;

        var closer = app.loading();
        var params = {
            houses_id: setting.houses_id,
            estateClass: setting.enabled_estate_type,
            isConfirm: true,
            setting: setting
        };
        if (page) {
            params.page = page;
        }
        if (sort) {
            params.sort = sort;
        }
        EstateMaster.getHouseAll(params).done(function (data) {
            closer();
            if (data) {
                $('.js-confirm-special-houses-list').empty();
                $('.js-confirm-special-houses-list').append('<h2>公開する物件の個別設定一覧</h2>');
                $('.js-confirm-special-houses-list').append('<div class="section-house-list">' + data.content + '</div>');
                $('.js-confirm-special-houses-list').append('<span>※上記で設定している物件は、物件の状態により自動的に削除されることがあります。<br>（例）非公開物件になった場合など。</span>');
                $('.js-confirm-special-houses-list').find('input[type="checkbox"]')
                .prop('checked', true)
                .prop('disabled', true);
            }
		});
	};

    // 築年数新築チェック
    $(document).on('change', ".chikunensu_block [name='chikunensu_1[]']", function() {
        var chuko = $(this).closest('div').find("[name='chikunensu_1[]']").last();
        if($(this).val() == "10") {
            // 新築チェック
            if($(this).prop('checked') == true) {
                $(this).closest('div').find(".chikunensu_sel select").val(0).attr('disabled', 'disabled').addClass('is-disable');
                $(chuko).attr('disabled', 'disabled');
                $(chuko).prop('checked', false);
                $(chuko).closest('label').addClass('is-disable');

                $(".chikunensu_block [name='chikunensu_1[]']").each(function() {
                    var str = $(this).parent().text();
                    if(str.match(/\d{1,2}/)) {
                        $(this).prop('checked', false);
                    }
                });
            } else {
                $(this).closest('div').find(".chikunensu_sel select").attr('disabled', false).removeClass('is-disable');
            }
        } else if($(this).val() == "20") {
            // 築後未入居チェック
            if($(this).prop('checked') == true) {
                $(chuko).attr('disabled', 'disabled');
                $(chuko).prop('checked', false);
                $(chuko).closest('label').addClass('is-disable');
            }
        }
        if($(this).prop('checked') == false) {
            // 新築,築後未入居の両方にチェックが無いときに、『新築を除く』を有効化
            if($(".chikunensu_block [name='chikunensu_1[]']").eq(1).prop('checked') == false
            && $(".chikunensu_block [name='chikunensu_1[]']").eq(2).prop('checked') == false ) {
                $(chuko).attr('disabled', false);
                $(chuko).closest('label').removeClass('is-disable');
            }
        }
    });
    $(document).on('change', "#chikunensuTo", function() {
        var selVal = $(this).val();
        $(".chikunensu_block [name='chikunensu_1[]']").each(function() {
            var str = $(this).parent().text();
            if(str.match(/\d{1,2}/)) {
                if($(this).val() == selVal) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            }
        });

    });

	// リフォーム可能箇所 Event
	// 1. 指定なし、可能箇所の変更
	$(document).on('change', ".reformable_parts_block [name=rp_use]", function() {
		if($(this).val() == 1) {
			// すべての箇所のチェックを解除
			$(this).closest('div').find(".rp_detail_block").eq(0).find('input').prop('checked', false);
			// 詳細の非表示
			$(this).closest('div').find(".rp_detail_block").eq(0).hide();
			// リンク文言変更
			$(this).closest('div').find(".rp_detail_display").eq(0).text('詳細な設定を選ぶ');
			if($(this).closest('div').find(".rp_detail_display").eq(0).hasClass('is-disable') == false) {
				$(this).closest('div').find(".rp_detail_display").eq(0).addClass('is-disable');
				$(this).closest('div').find(".rp_detail_display").eq(0).css('cursor', '');
			}
		} else {
			$(this).closest('div').find(".rp_detail_display").eq(0).removeClass('is-disable');
			$(this).closest('div').find(".rp_detail_display").eq(0).css('cursor', 'pointer');
		}
    });
	// 2.詳細な設定を選ぶクリック時
	$(document).on('click', ".reformable_parts_block .rp_detail_display", function() {
		if($(this).hasClass('is-disable')) {
			return;
		}
		if($(this).text() == '詳細な設定を選ぶ') {
			$(this).closest('div').find(".rp_detail_block").show();
			$(this).text('詳細な設定を隠す');
		} else {
			$(this).closest('div').find(".rp_detail_block").hide();
			$(this).text('詳細な設定を選ぶ');
		}
	});
	// 3.サマリチェック時
    var rpOtherCheck = [];
	$(document).on('change', ".reformable_parts_block .rp_cb_summary", function(e) {
		var ckFlg = ($(this).prop('checked'));
		$(this).closest('div').find('.js-search-filter-item').prop('checked', ckFlg);
        if ($(this).find('label input').length == 0) {
            var index = $(this).eq();
            if (ckFlg) {
                rpOtherCheck.push(index);
            } else {
                var position = rpOtherCheck.indexOf(index);
                if (position >= 0) {
                    rpOtherCheck.splice(position, 1)
                }
            }
        }
	});
	// 4.個別箇所チェック時
	$(document).on('change', ".reformable_parts_block .js-search-filter-item", function() {
		var catDiv = $(this).closest('.rp_cat_block');
		var ckFlg = false;
		$(catDiv).find('.js-search-filter-item').each(function() {
			if($(this).prop('checked')) {
				ckFlg = true;
			}
		});
		$(catDiv).find(".rp_cb_summary").eq(0).prop('checked', ckFlg);
	});

})();

$(function () {
	$('.js-not-delete').on('click', function () {
		app.modal.alertBanDeletePage('','このページは「公開中」または特集コマとして利用中のため、削除ができません。<br>「サイトの公開/更新」の「公開設定（詳細設定）」より<br>公開停止を行ってください。<br>また、特集コマとして利用している場合は、利用しているページから削除し、<br>公開処理を行ってください。<br>※詳しくはマニュアル内「特集設定」をご確認ください。');
	});
});
$(function () {
	$('.js-not-delete-sched').on('click', function () {
		app.modal.alertBanDeletePage('このページは公開予約設定中のため、削除ができません。','「サイトの公開/更新」の「公開設定（詳細設定）」より公開予約の解除を行ってください。<br>公開予約の解除後、「削除」することができます。');
	});
});