(function() {
    var extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
      hasProp = {}.hasOwnProperty;
  
    (function($, window, document) {
      var FulltextCount, FulltextRequest, FulltextSuggest, appVersion, defaults, isIE, isIE9, isSupported, ltIE8, minWait, userAgent;
      defaults = {
        timing: 'debounce',
        wait: 500,
        enableIME: false,
        fulltextFields: null,
        dataModelFulltextFields: null,
        fulltextType: 'morpho',
        before: function() {},
        success: function() {},
        error: function() {},
        complete: function() {},
        bukkenParams: function() {
          return {};
        },
        headersToApiRequest: function() {
          return {};
        }
      };
      minWait = 500;
      userAgent = window.navigator.userAgent.toLowerCase();
      appVersion = window.navigator.appVersion.toLowerCase();
      ltIE8 = isIE && (appVersion.indexOf("msie 6.") !== -1 || appVersion.indexOf("msie 7.") !== -1 || appVersion.indexOf("msie 8.") !== -1);
      isSupported = !ltIE8;
      FulltextRequest = (function() {
        function FulltextRequest(element, options) {
          this.element = element;
          this.settings = $.extend({}, defaults, options);
          if (this.settings['wait'] < minWait) {
            this.settings['wait'] = minWait;
          }
          this.$element = $(this.element);
          this._isIMEOn = false;
          this._lastQuery = this.$element.val();
          this._init();
        }
  
        FulltextRequest.prototype._init = function() {
          var func;
          switch (this.settings.timing) {
            case 'throttle':
              func = this._throttle(this._handleInput, this.settings.wait);
              break;
            default:
              func = this._debounce(this._handleInput, this.settings.wait);
          }
          
          this.$element.bind('input', func);
          this.$element.bind('compositionstart compositionend', (function(_this) {
            return function(ev) {
              return _this._handleIMEOnOff(ev);
            };
          })(this));
          this.$element.bind('click blur compositionend', (function(_this) {
            return function(ev) {
              return _this._hackIE(ev, func);
            };
          })(this));
        };
  
        FulltextRequest.prototype._handleIMEOnOff = function(ev) {
          switch (ev.type) {
            case 'compositionstart':
              this._isIMEOn = true;
              break;
            case 'compositionend':
              this._isIMEOn = false;
          }
        };
  
        FulltextRequest.prototype._handleInput = function(ev) {
          if (!this.settings['enableIME'] && this._isIMEOn) {
            return;
          }
          this.request(false);
        };
  
        FulltextRequest.prototype._queryPreProcess = function(str) {
          return str;
        };
  
        FulltextRequest.prototype.request = function(force) {
          var ajaxOptions, query, searchParams, url;
          query = this._queryPreProcess(this.$element.val());
          if (query === '' && this.actionName() === 'suggest') {
              var tempdata = {suggestions:[]};
            this.settings.success.apply(this.element, [tempdata, query]);
            this.settings.complete.apply(this.element, [query]);
            this._lastQuery = query;
            return;
          }
          if (!force && this._lastQuery === query && this.actionName() === 'count') {
            return;
          }
          this._lastQuery = query;
          searchParams = this._buildSearchOptions(query);
          url = "/api/" + this.actionName() + "/";
          if (searchParams.special_path != null) {
            url = "/api/" + searchParams.special_path + "/" + this.actionName() + "/";
          }

          if (this.actionName() === 'count') {
            // 指定文字(-)にリセット
            this.settings.reset.apply(this.element, ['-']);
          }

          if(Array.isArray(searchParams.type_freeword) && this.actionName() === 'count') {
            var ret = {
                        facets: [],
                        count: 0,
                        errors: ""
                      };
            var total_count = [];
            for(tc=0; tc < searchParams.type_freeword.length; tc++) {
              total_count.push(null);
            }
            var tcount = 1;
            var searchParamsSingle = $.extend(true, {}, searchParams);
            searchParams.type_freeword.forEach(function(shumoku) {
              searchParamsSingle.type_freeword = shumoku;
              searchParamsSingle.shumoku = shumoku;

              ajaxOptions = {
                url: url,
                dataType: "json",
                type: "POST",
                data: searchParamsSingle,
                async: true,
                context: tcount,
                success: function(data) {
                  total_count[ this - 1 ] = data.total_count;
                }
              };
              $.ajax(ajaxOptions);
              tcount++;
            });

            var intCount = 0;
            var intTimer = setInterval(function(_this) {
              if(total_count.indexOf(null) < 0) {
                for(tc=0; tc < total_count.length; tc++) {
                  ret.count+= total_count[tc];
                }
                intCount = 999;
              }
              intCount++;
              if(intCount > 75) {
                clearInterval(intTimer);
                return _this.settings.success.apply(_this.element, [ret, query, null]);
              }
            }, 200, this);
          } else {
            if(Array.isArray(searchParams.type_freeword)) {
              searchParams.type_freeword = 'all';
            }
            ajaxOptions = {
              url: url,
              dataType: 'json',
              type: 'POST',
              data: searchParams,
              beforeSend: (function(_this) {
                return function(xhr) {
                  _this._editHeadersToApiRequest(_this.settings.headersToApiRequest, xhr);
                  return _this.settings.before(xhr);
                };
              })(this),
              success: (function(_this) {
                return function(data, textStatus, jqXHR) {
                  _this.onSuccess(data, query, jqXHR);
                };
              })(this),
              error: (function(_this) {
                return function(jqXHR, textStatus, errorThrown) {
                  var errors;
                  errors = JSON.parse(jqXHR.responseText).errors;
                  _this.settings.error.apply(_this.element, [errors, query, jqXHR]);
                };
              })(this),
              complete: (function(_this) {
                return function(jqXHR, textStatus) {
                  _this.settings.complete.apply(_this.element, [query, jqXHR]);
                };
              })(this)
            };
            $.ajax(ajaxOptions);
          }
        };
  
        FulltextRequest.prototype._buildSearchOptions = function(query) {
          var addtionalParams, forceParams, searchParams;
          forceParams = {};
          searchParams = {
            fulltext: query,
            fulltext_type: this.settings.fulltextType
          };
          if (this.settings.fulltextFields) {
            searchParams['fulltext_fields'] = this.settings.fulltextFields;
          }
          if (this.settings.dataModelFulltextFields) {
            searchParams['data_model_fulltext_fields'] = this.settings.dataModelFulltextFields;
          }
          addtionalParams = this.settings.bukkenParams;
          return $.extend(searchParams, addtionalParams, forceParams);
        };
  
        FulltextRequest.prototype._debounce = function(func, wait, immediate) {
          var args, debounced, later, result, timeout, timestamp;
          if (immediate == null) {
            immediate = false;
          }
          timeout = args = timestamp = result = null;
          later = (function(_this) {
            return function() {
              var last;
              last = new Date() - timestamp;
              if (last < wait && last >= 0) {
                timeout = setTimeout(later, wait - last);
              } else {
                timeout = null;
                if (!immediate) {
                  result = func.apply(_this, args);
                  if (!timeout) {
                    args = null;
                  }
                }
              }
            };
          })(this);
          debounced = (function(_this) {
            return function() {
              var callNow;
              args = arguments;
              timestamp = new Date();
              callNow = immediate && !timeout;
              if (!timeout) {
                timeout = setTimeout(later, wait);
              }
              if (callNow) {
                result = func.apply(_this, args);
                args = null;
              }
              return result;
            };
          })(this);
          return debounced;
        };
  
        FulltextRequest.prototype._throttle = function(func, wait, leading, trailing) {
          var args, later, previous, result, throttled, timeout;
          if (leading == null) {
            leading = false;
          }
          if (trailing == null) {
            trailing = true;
          }
          args = timeout = result = null;
          previous = 0;
          later = (function(_this) {
            return function() {
              previous = leading === false ? 0 : new Date();
              timeout = null;
              result = func.apply(_this, args);
              if (!timeout) {
                args = null;
              }
            };
          })(this);
          throttled = (function(_this) {
            return function() {
              var now, remaining;
              now = new Date();
              if (!previous && leading === false) {
                previous = now;
              }
              remaining = wait - (now - previous);
              args = arguments;
              if (remaining <= 0 || remaining > wait) {
                if (timeout) {
                  clearTimeout(timeout);
                  timeout = null;
                }
                previous = now;
                result = func.apply(_this, args);
                if (!timeout) {
                  args = null;
                }
              } else if (!timeout && trailing !== false) {
                timeout = setTimeout(later, remaining);
              }
              return result;
            };
          })(this);
          return throttled;
        };
  
        FulltextRequest.prototype._hackIE = function(ev) {
          switch (ev.type) {
            case 'click':
                $(document).bind('selectionchange', ((function(_this) {
                  return function(ev) {
                     _this._triggerInputEvent(ev);
                  };
                })(this)));
              break;
            case 'blur':
                $(document).unbind('selectionchange');
              break;
            case 'compositionend':
                this._triggerInputEvent();
          }
        };
  
        FulltextRequest.prototype._triggerInputEvent = function(ev) {
          var event;
          event = document.createEvent('HTMLEvents');
          event.initEvent('input', true, false);
          this.element.dispatchEvent(event);
        };
  
        FulltextRequest.prototype._editHeadersToApiRequest = function(hash, xhr) {
          var key, value;
          for (key in hash) {
            value = hash[key];
            xhr.setRequestHeader(key, value);
          }
        };
  
        return FulltextRequest;
  
      })();
      FulltextCount = (function(superClass) {
        extend(FulltextCount, superClass);
  
        function FulltextCount() {
          return FulltextCount.__super__.constructor.apply(this, arguments);
        }
  
        FulltextCount.prototype._queryPreProcess = function(str) {
          return $.trim(str);
        };
  
        FulltextCount.prototype.actionName = function() {
          return 'count';
        };
  
        FulltextCount.prototype.getCount = function(force) {
          if (force == null) {
            force = true;
          }
          return this.request(force);
        };
  
        FulltextCount.prototype.onSuccess = function(data, query, jqXHR) {
          var ret;
          ret = {
            facets: data['facets'] || [],
            count: data['total_count'] || 0,
            errors: data['errors']
          };
          return this.settings.success.apply(this.element, [ret, query, jqXHR]);
        };
  
        return FulltextCount;
  
      })(FulltextRequest);
      FulltextSuggest = (function(superClass) {
        extend(FulltextSuggest, superClass);
  
        function FulltextSuggest() {
          return FulltextSuggest.__super__.constructor.apply(this, arguments);
        }
  
        FulltextSuggest.prototype.actionName = function() {
          return 'suggest';
        };
  
        FulltextSuggest.prototype.getSuggests = function(force) {
          if (force == null) {
            force = true;
          }
          return this.request(force);
        };
  
        FulltextSuggest.prototype.onSuccess = function(data, query, jqXHR) {
          var ret;
          if (query !== this.$element.val()) {
            return;
          }
          ret = {
            originQuery: data['origin_criteria'] || '',
            suggestions: data['suggestions'] || [],
            facets: data['facets'] || [],
            count: data['total_count'] || 0,
            errors: data['errors']
          };
          return this.settings.success.apply(this.element, [ret, query, jqXHR]);
        };
  
        return FulltextSuggest;
  
      })(FulltextRequest);
      $.fn['fulltextCount'] = function(options) {
        return this.each(function() {
          if (!isSupported) {
            return;
          }
          if (!$.data(this, "plugin_fulltextCount")) {
            $.data(this, "plugin_fulltextCount", new FulltextCount(this, options));
          }
        });
      };
      return $.fn['fulltextSuggest'] = function(options) {
        return this.each(function() {
          if (!isSupported) {
            return;
          }
          if (!$.data(this, "plugin_fulltextSuggest")) {
            $.data(this, "plugin_fulltextSuggest", new FulltextSuggest(this, options));
          }
        });
      };
    })(jQuery, window, document);
  
  }).call(this);
  