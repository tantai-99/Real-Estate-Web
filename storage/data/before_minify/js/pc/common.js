(function () {

  'use strict';

  // global application
  var app = window.app = {};

  /**
   * Twitter 初期化
   *
   * @param document
   * @param tag
   * @param id
   */
  app.twitter = function (document, tag, id) {

    var js, fjs = document.getElementsByTagName(tag)[0], p = /^http:/.test(document.location) ? 'http' : 'https';
    if (!document.getElementById(id)) {
      js = document.createElement(tag);
      js.id = id;
      js.async = true;
      js.src = p + '://platform.twitter.com/widgets.js';
      fjs.parentNode.insertBefore(js, fjs);
    }
  };

  /**
   * Facebook 初期化
   *
   * @param document
   * @param tag
   * @param id
   */
  app.facebook = function (document, tag, id) {

    var js, fjs = document.getElementsByTagName(tag)[0];
    if (document.getElementById(id)) return;
    js = document.createElement(tag);
    js.id = id;
    js.async = true;
    js.src = '//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v2.3';
    fjs.parentNode.insertBefore(js, fjs);
  };

  /**
   * 開発環境専用のコンソールログ
   * @param content
   */
  app.customConsoleLog = function (content) {

    if (typeof devMode === 'undefined' || !devMode) {
      return;
    }
    console.log(content);
  };

  /**
   * 物件コマ（top）初期化
   *
   * @param $el
   */
  app.komaInit = function ($el) {

    var $komaElems, _deleteKomaArea, _findTitleParent;

    if (app.isPreview) {
      return;
    }

    $komaElems = $el.find('.estate-koma');

    if ($komaElems.length < 1) {
      return;
    }

    /**
     * コマ+ヘッダーを削除
     *
     * @param $koma
     * @private
     */
    _deleteKomaArea = function ($koma) {

      $koma.prev(':header').remove();
      $koma.remove();
    };

    /**
     * ヘッダーのテキストの親要素を探す
     * - デザインによってHTML構造が異なるため
     * -- natural：<span><span>foo</span></span>
     * -- その他：<span>foo</span>
     *
     * @param $el
     * @returns {*}
     * @private
     */
    _findTitleParent = function ($el) {

      while ($el.find('span').length > 0) {
        $el = $el.find('span');
      }
      return $el;
    };

    // count koma
    var countKoma = 0;
    $komaElems.each(function (i, v) {

      var data, $koma;

      $koma = $komaElems.eq(i);
      data = $koma.data();

      $.ajax({
        type: 'POST',
        url: '/api/koma/',
        data: {
          'special-path': data.specialPath,
          'rows': data.rows,
          'sort-option': data.sortOption
        },
        timeout: 120 * 1000,
        dataType: 'json'

      }).done(function (res) {

        var $newKoma;
        countKoma ++;

        app.customConsoleLog('----- ajax response -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax response end -----');

        if (typeof res.success === 'undefined' || !res.success) {
          _deleteKomaArea($koma);
          return;
        }

        $newKoma = $(res.content);

        // header
        var header = _findTitleParent($koma.prev(':header')).text(res.title);
        if (typeof FONTPLUS !== 'undefined' && countKoma == $komaElems.length) {
          FONTPLUS.reload(false);
        }

        // koma
        if ($newKoma.text().trim() === '') {
          $koma.empty().append('<div class="element"> <p>現在おすすめの物件はありません。</p></div>');
          return;
        }

        $koma.after($newKoma).remove();
        $newKoma.find('img').lazyload({effect: 'fadeIn'});

        // add listener
        // post special path
        $newKoma.find('a').on('click', function (e) {
          var href = $(e.target).closest('a').attr('href');
          app.request.postForm(href, {'special-path': $newKoma.data('special-path')}, true);
          return false;
        });

      }).fail(function (res) {

        app.customConsoleLog('----- ajax failed -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax failed end -----');

        _deleteKomaArea($koma);
      });
    });
  };

  /**
   * ユーザーエージェント判定
   *
   * @type {{tablet, mobile, pc}}
   */
  app.ua = (function () {

    var tablet = false, mobile = false, pc = false, userAgent;

    userAgent = window.navigator.userAgent.toLowerCase();

    if ((userAgent.indexOf('windows') != -1 && userAgent.indexOf('touch') != -1) || userAgent.indexOf('ipad') != -1 || (userAgent.indexOf('android') != -1 && userAgent.indexOf('mobile') == -1) || (userAgent.indexOf('firefox') != -1 && userAgent.indexOf('tablet') != -1) || userAgent.indexOf('kindle') != -1 || userAgent.indexOf('silk') != -1 || userAgent.indexOf('playbook') != -1) {
      tablet = true;
    }

    if ((userAgent.indexOf('windows') != -1 && userAgent.indexOf('phone') != -1) || userAgent.indexOf('iphone') != -1 || userAgent.indexOf('ipod') != -1 || (userAgent.indexOf('android') != -1 && userAgent.indexOf('mobile') != -1) || (userAgent.indexOf('firefox') != -1 && userAgent.indexOf('mobile') != -1) || userAgent.indexOf('blackberry') != -1) {
      mobile = true;
    }

    if (!tablet && !mobile) {
      pc = true;
    }

    return {
      tablet: tablet,
      mobile: mobile,
      pc: pc
    }
  })();

  /**
   * ユーティリティ Class
   *
   * constructor
   * @returns {app.utility}
   */
  app.utility = function () {

    if (!(this instanceof app.utility)) {
      return new app.utility();
    }
  };

  /**
   * オブジェクトの判定
   *
   * @param o
   * @returns {boolean}
   */
  app.utility.prototype.isObject = function (o) {

    return (o instanceof Object && !(o instanceof Array));
  };

  /**
   * NaNの判定
   *
   * @param num
   * @returns {boolean}
   */
  app.utility.prototype.isReallyNaN = function (num) {
    return num !== num;
  };

  /**
   * selectorまでスクロール
   *
   * @param selector
   */
  app.utility.prototype.scrollTo = function (selector) {

    $('body, html').animate({scrollTop: $(selector).offset().top}, 400, 'swing');
  };

  /**
   * 数値をカンマ区切りに
   *
   * @param num
   * @returns {*}
   */
  app.utility.prototype.separate = function (num) {

    num = String(num);
    var length = num.length;
    return length > 3 ? this.separate(num.substring(0, length - 3)) + ',' + num.substring(length - 3) : num;
  };

  app.utility = new app.utility(); // get instance

  /**
   * リクエスト class
   *
   * @returns {app.request}
   */
  app.request = function () {

    if (!(this instanceof app.request)) {
      return new app.request();
    }
  };

  /**
   * post
   *
   * @param url string
   * @param data object
   * @param openWindow boolean
   */
  app.request.prototype.postForm = function (url, data, openWindow) {

    var time, $form, params;


     /*
      iOSのアプリ内ブラウザは、マルチタブ前提で一覧(元タブ)→詳細画面(新タブ)遷移すると、新タブにpostできない不具合がでるので、シングルタブとして特別な扱いにする。
      ＃特集は特集IDをpostで引きまわす必要がある
      対象アプリ： facebookアプリ・Twitterアプリ・Lineアプリ・Googleアプリ・楽天ウェブ検索アプリ・Yahoo!アプリ
     */
     var ua = window.navigator.userAgent.toLowerCase();
     if((ua.indexOf('iphone') != -1 || ua.indexOf('ipod') != -1 || ua.indexOf('ipad') != -1) &&
         (ua.indexOf('fban') != -1 || ua.indexOf('twitter') != -1 || ua.indexOf('line') != -1 ||
          ua.indexOf('gsa/') != -1 || ua.indexOf('rakutenwebsearch/') != -1 || ua.indexOf('yjapp-ios jp.co.yahoo.ipn.appli/') != -1)) {
         openWindow = false;
     }

    params = {action: url, method: 'post'};
    time = $.now();

    if (!!openWindow) {

      // open new "Tab"
      window.open('', 'formpost' + time);

      // open new "Window"
      // window.open('', 'formpost', 'width=1000,height=800,scrollbars=yes');
      params.target = 'formpost' + time;
      data.target = 'formpost' + time;
    }

    $form = $('<form/>', params);
    $.each(data, function (i, v) {
      $form.append($('<input/>', {'type': 'hidden', 'name': i, 'value': v}));
    });
    $form.appendTo($('body')).submit().remove();
  };
  /**
   * request api counter search
   * 
   * @param data object
   */
  app.request.prototype.counter = function (data,intputName,countName,fObj) {
    var criteria = $(intputName);
    if(fObj !== undefined) {
        criteria = fObj.find(intputName);
    }
    if (criteria.length > 0) {
        criteria.fulltextCount({
            wait: 1000,
            enableIME: true,
            bukkenParams: data,
            success: function(res, query) {
                for(var cno = 0; cno < criteria.length; cno++) {
                    var fObj = $(criteria.eq(cno)).closest('form');

                    var total = res.count.toString();
                    var counterIcon = fObj.find('.' + countName + ':first i');
                    if (criteria.attr('disabled') == 'disabled') {
                        total = '0';
                    }
                    if (parseInt(total) > 99999) {
                        if (counterIcon.length == 5) {
                            fObj.find('.' + countName).append( "<i>0</i>" );
                        }
                    }
                    counterIcon = fObj.find('.' + countName + ':first i');
                    var range = counterIcon.length - total.length;
                    fObj.find('.' + countName).each( function () {
                        $(this).find('i').each( function (i,e) {
                            if (i - range >= 0) {
                                $(this).html(total[i - range]);
                            } else {
                                $(this).html('0');
                            }
                        });
                    });
                }
            },
            reset: function(resetChar) {

                var fObj = $(criteria).closest('form');

                fObj.find('.' + countName).each( function () {
                    $(this).find('i').each( function (i,e) {
                        $(this).html(resetChar);
                    })
                });
			}

        });
    }
  }

  /**
   * request api suggest search
   * 
   * @param data object
   */
  app.request.prototype.suggest = function (params,intputName,fObj) {
    var criteria = $(intputName);
    if(fObj !== undefined) {
        criteria = fObj.find(intputName);
    }
    if (criteria.length > 0) {
        criteria.fulltextSuggest({
            wait: 300,
            enableIME: true,
            bukkenParams: params,
            success: function(data, query) {
              var suggesteds = $(this).parent().find('.suggesteds');
              var suggestItems = $.map(data.suggestions, function(suggestion) {
                return data.originQuery + suggestion;
              });
              suggesteds.html(
                $.map(suggestItems, function(item) {
                  return '<option>' + item + '</option>';
                })
              ).replaceSuggest();
            },
            complete: function(data, query) {
                if(criteria.val() !== '') {
                    $(this).focus();
                }
            }
        });
    }
  }

  app.request = new app.request(); // get instance

  /**
   * Path class
   *
   * constructor
   * @returns {app.path}
   */
  app.path = function () {

    if (!(this instanceof app.path)) {
      return new app.path();
    }

    // {$物件種目}
    this.CHINTAI = 'chintai';
    this.KASI_TENPO = 'kasi-tenpo';
    this.KASI_OFFICE = 'kasi-office';
    this.PARKING = 'parking';
    this.KASI_TOCHI = 'kasi-tochi';
    this.KASI_OTHER = 'kasi-other';
    this.MANSION = 'mansion';
    this.KODATE = 'kodate';
    this.URI_TOCHI = 'uri-tochi';
    this.URI_TENPO = 'uri-tenpo';
    this.URI_OFFICE = 'uri-office';
    this.URI_OTHER = 'uri-other';
    this.CHINTAI_JIGYO_1 =  'chintai-jigyo-1';
    this.CHINTAI_JIGYO_2 =  'chintai-jigyo-2';
    this.CHINTAI_JIGYO_3 =  'chintai-jigyo-3';
    this.BAIBAI_KYOJU_1 =  'baibai-kyoju-1';
    this.BAIBAI_KYOJU_2 =  'baibai-kyoju-2';
    this.BAIBAI_JIGYO_1 =  'baibai-jigyo-1';
    this.BAIBAI_JIGYO_2 =  'baibai-jigyo-2';

    // {$都道府県}
    this.HOKKAIDO = 'hokkaido';
    this.AOMORI = 'aomori';
    this.IWATE = 'iwate';
    this.MIYAGI = 'miyagi';
    this.AKITA = 'akita';
    this.YAMAGATA = 'yamagata';
    this.FUKUSHIMA = 'fukushima';
    this.IBARAKI = 'ibaraki';
    this.TOCHIGI = 'tochigi';
    this.GUNMA = 'gunma';
    this.SAITAMA = 'saitama';
    this.CHIBA = 'chiba';
    this.TOKYO = 'tokyo';
    this.KANAGAWA = 'kanagawa';
    this.NIIGATA = 'niigata';
    this.TOYAMA = 'toyama';
    this.ISHIKAWA = 'ishikawa';
    this.FUKUI = 'fukui';
    this.YAMANASHI = 'yamanashi';
    this.NAGANO = 'nagano';
    this.GIFU = 'gifu';
    this.SHIZUOKA = 'shizuoka';
    this.AICHI = 'aichi';
    this.MIE = 'mie';
    this.SHIGA = 'shiga';
    this.KYOTO = 'kyoto';
    this.OSAKA = 'osaka';
    this.HYOGO = 'hyogo';
    this.NARA = 'nara';
    this.WAKAYAMA = 'wakayama';
    this.TOTTORI = 'tottori';
    this.SHIMANE = 'shimane';
    this.OKAYAMA = 'okayama';
    this.HIROSHIMA = 'hiroshima';
    this.YAMAGUCHI = 'yamaguchi';
    this.TOKUSHIMA = 'tokushima';
    this.KAGAWA = 'kagawa';
    this.EHIME = 'ehime';
    this.KOCHI = 'kochi';
    this.FUKUOKA = 'fukuoka';
    this.SAGA = 'saga';
    this.NAGASAKI = 'nagasaki';
    this.KUMAMOTO = 'kumamoto';
    this.OITA = 'oita';
    this.MIYAZAKI = 'miyazaki';
    this.KAGOSHIMA = 'kagoshima';
    this.OKINAWA = 'okinawa';

  };

  /**
   * 物件種目のディレクトリ名一覧を取得
   *
   * @returns {*[]}
   */
  app.path.prototype.shumokuAll = function () {
    return [
      this.CHINTAI,
      this.KASI_TENPO,
      this.KASI_OFFICE,
      this.PARKING,
      this.KASI_TOCHI,
      this.KASI_OTHER,
      this.MANSION,
      this.KODATE,
      this.URI_TOCHI,
      this.URI_TENPO,
      this.URI_OFFICE,
      this.URI_OTHER,
      this.CHINTAI_JIGYO_1,
      this.CHINTAI_JIGYO_2,
      this.CHINTAI_JIGYO_3,
      this.BAIBAI_KYOJU_1,
      this.BAIBAI_KYOJU_2,
      this.BAIBAI_JIGYO_1,
      this.BAIBAI_JIGYO_2
    ];
  };

  /**
   * 都道府県のディレクトリ名一覧を取得
   *
   * @returns {*[]}
   */
  app.path.prototype.prefectureAll = function () {

    return [
      this.HOKKAIDO,
      this.AOMORI,
      this.IWATE,
      this.MIYAGI,
      this.AKITA,
      this.YAMAGATA,
      this.FUKUSHIMA,
      this.IBARAKI,
      this.TOCHIGI,
      this.GUNMA,
      this.SAITAMA,
      this.CHIBA,
      this.TOKYO,
      this.KANAGAWA,
      this.NIIGATA,
      this.TOYAMA,
      this.ISHIKAWA,
      this.FUKUI,
      this.YAMANASHI,
      this.NAGANO,
      this.GIFU,
      this.SHIZUOKA,
      this.AICHI,
      this.MIE,
      this.SHIGA,
      this.KYOTO,
      this.OSAKA,
      this.HYOGO,
      this.NARA,
      this.WAKAYAMA,
      this.TOTTORI,
      this.SHIMANE,
      this.OKAYAMA,
      this.HIROSHIMA,
      this.YAMAGUCHI,
      this.TOKUSHIMA,
      this.KAGAWA,
      this.EHIME,
      this.KOCHI,
      this.FUKUOKA,
      this.SAGA,
      this.NAGASAKI,
      this.KUMAMOTO,
      this.OITA,
      this.MIYAZAKI,
      this.KAGOSHIMA,
      this.OKINAWA
    ];
  };

  // get instance
  app.path = new app.path();

  /**
   * location class
   *
   * constructor
   * @returns {app.location}
   */
  app.location = function () {

    if (!(this instanceof app.location)) {
      return new app.location();
    }

    this.currentUrl = location.href;
  };

  /**
   * reload
   *
   */
  app.location.prototype.customReload = function () {

    location.href = this.currentUrl;
  };

  /**
   * URL parse
   *
   * @param url
   * @returns {{
   *   protocol     : (*|string),
   *   host         : (*|string),
   *   hostname     : (*|string),
   *   port         : (*|string|Function),
   *   pathname     : (*|string),
   *   search       : (*|app.search|string),
   *   searchObject : {},
   *   hash         : (*|string),
   *   dirs         : Array
   *   }}
   */
  app.location.prototype.parseUrl = function (url) {

    var parser = document.createElement('a'),
      searchObject = {},
      directories,
      queries, split, i;

    parser.href = url;

    queries = parser.search.replace(/^\?/, '').split('&');
    for (i = 0; i < queries.length; i++) {
      split = queries[i].split('=');
      searchObject[split[0]] = split[1];
    }

    parser.dirs = [];

    directories = parser.pathname.split('/');
    for (i = 0; i < directories.length; i++) {
      if (directories[i] !== '') {
        parser.dirs.push(directories[i]);
      }
    }

    return {
      protocol: parser.protocol,
      host: parser.host,
      hostname: parser.hostname || location.hostname,
      port: parser.port,
      pathname: parser.pathname.charAt(0) === '/' ? parser.pathname : '/' + parser.pathname,
      search: parser.search,
      searchObject: searchObject,
      hash: parser.hash,
      dirs: parser.dirs
    };
  };

  /**
   * 物件ID取得
   * !!! 物件詳細ページでのみ使用 !!!
   *
   * @returns {*}
   */
  app.location.prototype.bukkenId = function () {

    return this.parseUrl(this.currentUrl).dirs[1].replace(/detail-/g, '');
  };

  /**
   * 現在の物件種目をURLから取得
   *
   * @returns {*}
   */
  app.location.prototype.currentShumoku = function () {

    var parsedUrl, shumokuAll, i, index;

    parsedUrl = this.parseUrl(this.currentUrl);
    shumokuAll = app.path.shumokuAll();
    index = null;

    // ie8 cannnot use function 'indexOf'...
    for (i = 0; i < shumokuAll.length; i++) {
      if (shumokuAll[i] === parsedUrl.dirs[0]) {
        index = i;
        break;
      }
    }

    if (index === null) {
      return null;
    }

    return shumokuAll[index];
  };

  /**
   * 現在の政令指定都市をURLから取得
   *
   * @returns {*}
   */
  app.location.prototype.currentMcity = function () {

    var dirname = this.parseUrl(this.currentUrl).dirs[3];

    if (dirname + ' '.indexOf('-mcity.html' + ' ' !== -1)) {
      return dirname.replace('-mcity.html', '');
    }
    return null;
  };

  /**
   * 特集PathをURLから取得
   *
   * @returns {*}
   */
  app.location.prototype.currentSpecialPath = function () {

    var dirname = this.parseUrl(this.currentUrl).dirs[0];

    if (' ' + dirname.indexOf(' ' + 'sp_') !== -1) {
      return dirname;
    }

    return null;
  };

  /**
   * 現在の都道府県をURLから取得
   *
   * @returns {*}
   */
  app.location.prototype.currentPrefecture = function () {

    var parsedUrl, prefectureAll, i, index;

    parsedUrl = this.parseUrl(this.currentUrl);
    prefectureAll = app.path.prefectureAll();
    index = null;

    // ie8 cannnot use function 'indexOf'...
    for (i = 0; i < prefectureAll.length; i++) {
      if (prefectureAll[i] === parsedUrl.dirs[1]) {
        index = i;
      }
    }

    if (index === null) {
      return null;
    }

    return prefectureAll[index];
  };

  /**
   * 現在のpathnameを取得
   */
  app.location.prototype.currentPathname = function () {

    var url = this.parseUrl(this.currentUrl).pathname;
    return url.indexOf('/') !== 0 ? '/' + url : url; // IEは先頭の"/"が含まれないので
  };

  /**
   * プロトコルにhttpsをセット
   *
   * @param url
   * @returns {*}
   */
  app.location.prototype.setHttpsProtocol = function (url) {

    var parser;

    parser = app.location.parseUrl(url);

    if (parser.protocol === 'https:') {
      return url;
    }

    return 'https://' + parser.hostname + parser.pathname + parser.hash;
  };

  app.location.prototype.updatePageNumber = function (num) {

    num = typeof num === 'undefined' ? 1 : num;
    var url = app.location.parseUrl(app.location.currentUrl).pathname;
    history.replaceState('', '', num > 1 ? url + '?page=' + num : url);
  };

  // get instance
  app.location = new app.location();

  /**
   * Cookie class
   *
   * constructor
   * @returns {app.cookie}
   */
  app.cookie = function () {

    if (!(this instanceof app.cookie)) {
      return new app.cookie();
    }

    this.MAX_HISTORIES = 50;
    this.MAX_FAVORITE = 50;

    this.KEY_HISTORIES = 'histories';
    this.KEY_FAVORITE = 'favorite';
    this.KEY_FAVORITE_CONFIG = 'favorite_config';
    this.KEY_SEARCH_CONFIG = 'search_config';

    $.cookie.json = true;

    this.COOKIE_CONFIG = {expires: 30, path: '/'};

    this.init();
  };

  /**
   * 初期化
   *
   */
  app.cookie.prototype.init = function () {

    var i, keys;

    keys = this.listKey();

    for (i = 0; i < keys.length; i++) {
      if (typeof $.cookie(keys[i]) === 'undefined') {
        $.cookie(keys[i], {}, this.COOKIE_CONFIG);
      }
    }
  };

  /**
   * キー一覧
   *
   * @returns {*[]}
   */
  app.cookie.prototype.listKey = function () {

    return [
      this.KEY_HISTORIES,
      this.KEY_FAVORITE,
      this.KEY_FAVORITE_CONFIG,
      this.KEY_SEARCH_CONFIG
    ];
  };

  /**
   * 最近見た物件を更新
   *
   */
  app.cookie.prototype.updateHistory = function () {

    var histories, timestamp, bukkenId, min,
      timestamps = [];

    histories = $.cookie(this.KEY_HISTORIES);

    // params
    bukkenId = app.location.bukkenId();

    // delete duplication
    $.each(histories, function (i, val) {
      if (val === bukkenId) {
        delete histories[i];
      }
    });

    timestamp = $.now();
    while (typeof histories[timestamp] !== 'undefined') {
      timestamp = $.now();
    }

    // add
    histories[timestamp] = bukkenId;

    // max
    $.each(histories, function (i, val) {
      timestamps.push(i);
    });

    while (timestamps.length > this.MAX_HISTORIES) {
      min = Math.min.apply(null, timestamps);
      delete histories[min];
      timestamps = timestamps.filter(function (v) {
        return v + '' !== min + '';
      });
    }

    $.cookie(this.KEY_HISTORIES, histories, this.COOKIE_CONFIG);

    // 行動情報を保存
    this.saveOperation('updateHistory', [bukkenId]);
  };

  /**
   * お気に入りを更新
   *
   */
  app.cookie.prototype.updateFavorite = function (config) {

    var favoriteList, timestamp, bukkenId, min,
      timestamps = [];

    favoriteList = $.cookie(this.KEY_FAVORITE);

    // params
    bukkenId = config.bukkenId;

    // delete duplication
    $.each(favoriteList, function (i, val) {
      if (val === bukkenId) {
        delete favoriteList[i];
      }
    });

    timestamp = $.now();
    while (typeof favoriteList[timestamp] !== 'undefined') {
      timestamp = $.now();
    }

    // add
    favoriteList[timestamp] = bukkenId;

    // max
    $.each(favoriteList, function (i, val) {
      timestamps.push(i);
    });

    while (timestamps.length > this.MAX_FAVORITE) {
      min = Math.min.apply(null, timestamps);
      delete favoriteList[min];
      timestamps = timestamps.filter(function (v) {
        return v + '' !== min + '';
      });
    }

    $.cookie(this.KEY_FAVORITE, favoriteList, this.COOKIE_CONFIG);
  };

  /**
   * お気に入りを削除
   *
   * @param array
   */
  app.cookie.prototype.deleteFavorite = function (array) {

    var favoriteList, i;

    favoriteList = $.cookie(this.KEY_FAVORITE);

    for (i = 0; i < array.length; i++) {
      $.each(favoriteList, function (j, val) {
        if (val === array[i]) {
          delete favoriteList[j];
          return true;
        }
      });
    }

    $.cookie(this.KEY_FAVORITE, favoriteList, this.COOKIE_CONFIG);

    // 行動情報を保存
    this.saveOperation('deleteFavorite', array);
  };

  /**
   * お気に入りの設定を更新
   *
   * @param _config
   */
  app.cookie.prototype.updateFavoriteConfig = function (_config) {

    var config = $.cookie(this.KEY_FAVORITE_CONFIG);

    // alert msg
    if (app.utility.isObject(_config) && 'showMsg' in _config) {
      config.showMsg = !!_config.showMsg;
    }

    $.cookie(this.KEY_FAVORITE_CONFIG, config, this.COOKIE_CONFIG);
  };

  /**
   * 物件検索の設定を更新
   */
  app.cookie.prototype.updateSearch = function (_config) {

    var config = $.cookie(this.KEY_SEARCH_CONFIG);

    // sort
    if (app.utility.isObject(_config) && 'sort' in _config) {

      if (typeof config.sort === 'undefined') {
        config.sort = {};
      }
      config.sort[
        _search.pageType.isSpecialCategory() ?
          app.location.currentSpecialPath() :
          app.location.currentShumoku()
        ] = _config.sort + '';
    }

    // per page
    if (app.utility.isObject(_config) && 'total' in _config) {
      config.total = parseInt(_config.total);
    }

    $.cookie(this.KEY_SEARCH_CONFIG, config, this.COOKIE_CONFIG);
  };

  /**
   * 行動情報を保存
   */
  app.cookie.prototype.saveOperation = function(operation, bukkenId) {

    console.log("saveOperation");

    $.ajax({
      type: 'POST',
      url: '/api/save/',
      data: {
        'operation': operation,
        'bukken_id': bukkenId
      },
      timeout: 120 * 1000,
      dataType: 'json'
    }).done(function(res) {
      app.customConsoleLog('----- ajax response -----');
      app.customConsoleLog(res);
      app.customConsoleLog('----- ajax response end -----');
    }).fail(function (res) {
      app.customConsoleLog('----- ajax failed -----');
      app.customConsoleLog(res);
      app.customConsoleLog('----- ajax failed end -----');
    });
  };

  // get instance
  app.cookie = new app.cookie();

  /**
   * イベントリスナーの追加
   * - 物件検索以外も共通で使うもの
   *
   * @param $el
   */
  app.addCommonEventListener = function ($el) {

    /**
     * スムーズスクロール
     */
    $el.find('a[href^=#]').click(function () {

      var href, target;

      if (this.href.match(/#[\w+]/gi)) {
        href = $(this).attr('href');
        target = $(href == '#' || href == '' ? 'html' : href);
        $('body, html').animate({scrollTop: target.offset().top}, 400, 'swing');
        return false;
      }
    });

    /**
     * デバイス切り替え
     */
    $el.find('.device-change a').on('click', function (e) {

      $.cookie('device', {device: $el.find('.device-change a').data('device')}, app.cookie.COOKIE_CONFIG);
      app.location.customReload();
      return false;
    });
  };

  /**
   * 物件検索クラスの実行 class
   *
   * constructor
   * @param config
   * @returns {app.search}
   */
  app.search = function (config) {

    if (!(this instanceof app.search)) {
      return new app.search(config);
    }

    this.$el = config.$el;
    this.search_config = config.search_config || search_config;
  };

  /**
   * run
   */
  app.search.prototype.run = function () {

    var self = this;

    $('body *').unbind();

    // only first access
    if (typeof _search.pageType === 'function') {

      // get instance
      _search.pageType = _search.pageType(self.search_config);
    }

    // common
    _search.common.tooltip(self.$el).run();
    _search.common.clickableArea(self.$el).run();
    _search.common.recommend(self.$el).run();
    _search.common.checkboxEngine(self.$el).run();
    self.$el.find('.element-search-kind ul').tile();

    // select prefecture only
    if (_search.pageType.isSelectPrefecture()) {

      _search.selectPrefecture.tab(self.$el).run();
    }

    // select city only
    if (_search.pageType.isSelectCity()) {

      _search.selectCity.search(self.$el).run();
    }

    if (_search.pageType.isSelectChoson()) {
      _search.selectChoson.search(self.$el).run();
    }

    // select railway only
    if (_search.pageType.isSelectRailway()) {

      _search.selectRailway.search(self.$el).run();
    }

    // select station only
    if (_search.pageType.isSelectStation()) {

      _search.selectStation.search(self.$el).run();
    }

    // list only
    else if (_search.pageType.isList()) {

      _search.common.lazyload(self.$el).run(['.object-thumb img']);
      _search.common.history(self.$el).run();
      _search.common.favorite(self.$el).run();
      _search.common.contact(self.$el).run();
      _search.common.article(self.$el).run();
      _search.list.sortTable(self.$el).run();
      _search.list.aside(self.$el).run();
      _search.list.list(self.$el).run();
      _search.list.modal(self.$el).run();
      _search.list.search(self.$el).run();
      _search.list.highlight(self.$el).run();
    }

    // map
    else if (_search.pageType.isResultMap()) {

        _search.common.history(self.$el).run();
        _search.common.favorite(self.$el).run();
        _search.common.contact(self.$el).run();
        _search.common.article(self.$el).run();
        _search.list.sortTable(self.$el).run();
        //_search.list.aside(self.$el).run();
        //_search.list.list(self.$el).run();
        //_search.list.modal(self.$el).run();
        //_search.list.search(self.$el).run();

        _search.map.searchmap = searchmap;
        _search.map.searchmap.run(app, _search, self.$el);


        //_search.map.map(self.$el).run();








    }

    // detail only
    else if (_search.pageType.isDetail()) {

      // common
      app.cookie.updateHistory();
      _search.common.favorite(self.$el).run();
      _search.common.contact(self.$el).run();
      _search.detail.tag(self.$el).run();

      // top
      if (_search.pageType.page_name === 'detail') {

        _search.detail.photoGallery(self.$el).run();
      }

      // around
      if (_search.pageType.page_name === 'detail_map') {

        _search.detail.aroundGallery(self.$el).run();

        // map fdp
        //if (self.$el.find('.section-map .map-facility').length > 0 || self.$el.find('.chart-area').length > 0) {
        //    _search.map.fdpmap = fdpmap;
        //    _search.map.fdpmap.run(app, _search, self.$el);
        //}

        // tile
        $(window).on('load', function () {
          self.$el.find('.section-around .around-list li').tile(4);
        });
      }

      // town
      if(_search.pageType.page_name === 'detail_town') {
        _search.map.fdptown = fdptown;
        _search.map.fdptown.run(app, _search, self.$el);
      }
    }

    // personal only
    else if (_search.pageType.isPersonal()) {

      _search.common.history(self.$el).run();
      _search.common.favorite(self.$el).run();
      _search.common.contact(self.$el).run();
      _search.common.article(self.$el).run();
      _search.personal.tab(self.$el).run();
      _search.personal.checkboxEngine(self.$el).run();
      _search.personal.sort(self.$el).run();
      _search.common.lazyload(self.$el).run(['.object-thumb img']);
      // _search.personal.sort内でlazyload実行に変更（2015/12/28 19:19）
    }

    app.addCommonEventListener(self.$el);

    $('.breadcrumb').find('li').each(function() {
        $(this).replaceWith(function() {
            return $('<li>').append($(this).contents());
        })
    });
  };

  // search classes
  var _search = {
    pageType: null,
    common: {},
    selectPrefecture: {},
    selectCity: {},
    selectChoson: {},
    selectRailway: {},
    selectStation: {},
    list: {},
    map: {},
    detail: {},
    personal: {}
  };

  /**
   * ページ判定 Class
   *
   * @param config
   * @private
   */
  _search.pageType = function (config) {

    if (!(this instanceof _search.pageType)) {
      return new _search.pageType(config);
    }

    this.page_code = config.page_code;
    this.page_name = config.page_name;

    if (typeof config.s_type !== 'undefined') {
      this.s_type = parseInt(config.s_type);
    }
  };

  /**
   * 都道府県選択画面の判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isSelectPrefecture = function () {

    switch (this.page_name) {
      case 'select_prefecture':
      case 'sp_select_prefecture':
        return true;
    }
    return false;
  };

  /**
   * 市区郡選択画面の判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isSelectCity = function () {

    switch (this.page_name) {
      case 'select_city':
      case 'sp_select_city':
        return true;
    }
    return false;
  };

  /**
   * 市区郡選択画面の判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isSelectChoson = function () {

      switch (this.page_name) {
          case 'select_choson':
          case 'select_choson_from_multi_city':
          case 'sp_select_choson':
          case 'sp_select_choson_from_multi_city':
              return true;
      }
      return false;
  };

  /**
   * 沿線選択画面の判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isSelectRailway = function () {

    switch (this.page_name) {
      case 'select_railway':
      case 'sp_select_railway':
        return true;
    }
    return false;
  };

  /**
   * 駅選択画面の判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isSelectStation = function () {

    switch (this.page_name) {
      case 'select_station':
      case 'sp_select_station':
      case 'select_station_from_multi_railway':
      case 'sp_select_station_from_multi_railway':
        return true;
    }
    return false;
  };

  /**
   * 物件一覧ページの判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isList = function () {

    switch (this.page_name) {
      case 'result_railway':
      case 'result_area':
      case 'result_mcity':
      case 'result_station':
      case 'result_prefecture':
      case 'result_area_form':
      case 'result_train_form':
      case 'sp_result_railway':
      case 'sp_result_area':
      case 'sp_result_mcity':
      case 'sp_result_station':
      case 'sp_result_prefecture':
      case 'sp_result_area_form':
      case 'sp_result_train_form':
      case 'sp_result_direct_result':
      case 'result_choson':
      case 'result_choson_form':
      case 'sp_result_choson':
      case 'sp_result_choson_form':
      case 'result_freeword':
        return true;
    }
    return false;
  };

  /**
   * 地図検索ページの判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isResultMap = function () {

    switch (this.page_name) {
      case 'result_map':
      case 'sp_result_map':
        return true;
    }
    return false;
  };


  /**
   * 物件詳細ページの判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isDetail = function () {

    switch (this.page_name) {
      case 'detail':
      case 'detail_map':
      case 'sp_detail':
      case 'sp_detail_map':
      case 'detail_town' :
        return true;
    }
    return false;
  };

  /**
   * お気に入り、最近見た物件一覧画面の判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isPersonal = function () {

    switch (this.page_name) {
      case 'history':
      case 'favorite':
        return true;
    }
    return false;
  };

  /**
   * 特集ページの判定
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isSpecialCategory = function () {

    return this.page_name.indexOf('sp_') !== -1;
  };

  /**
   * tooltip class
   *
   * constructor
   * @param $el
   * @returns {_search.common.tooltip}
   */
  _search.common.tooltip = function ($el) {

    if (!(this instanceof _search.common.tooltip)) {
      return new _search.common.tooltip($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.common.tooltip.prototype.run = function () {

    var $tooltip = this.$el.find('.tooltip');

    if ($tooltip.length < 1) {
      return;
    }

    // PC
    if (app.ua.pc) {

      $tooltip.hover(
        function () {
          $(this).addClass('on')
        },
        function () {
          $(this).removeClass('on')
        }
      );
      return;
    }

    // SP or Tablet
    $(window).on('touchend', function (e) {

      var $target = $(e.target).closest('.tooltip');

      $tooltip.not($target).removeClass('on');

      if ($target.length > 0) {
        $target.addClass('on')
      }
    });
  };

  /**
   * 最近見た物件 class
   *
   * @param $el
   * @returns {_search.common.history}
   */
  _search.common.history = function ($el) {

    if (!(this instanceof _search.common.history)) {
      return new _search.common.history($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.common.history.prototype.run = function () {

    this._showLabel();
  };

  /**
   * ラベルの表示
   *
   * @private
   */
  _search.common.history.prototype._showLabel = function () {

    var self, $list, bukkenId, histories;

    self = this;

    $list = self.$el.find('.article-object .object-name');

    if ($list.length < 1) {
      return;
    }

    histories = $.cookie(app.cookie.KEY_HISTORIES);

    $.each($list, function (i, v) {

      $list.eq(i).removeClass('opened');
      bukkenId = $list.eq(i).closest('.article-object').data('bukken-no');

      $.each(histories, function (j, historyId) {

        if (bukkenId + '' === historyId + '') {
          $list.eq(i).addClass('opened');
          return false;
        }
      });
    });
  };

  /**
   * お気に入り class
   *
   * @param $el
   * @returns {_search.common.favorite}
   */
  _search.common.favorite = function ($el) {

    if (!(this instanceof _search.common.favorite)) {
      return new _search.common.favorite($el);
    }

    this.$el = $el;

    this.NO_ITEM_HISTORY = '<p class="no-item">最近見た物件はありません。</p>';
    this.NO_ITEM_FAVORITE = '<p class="no-item">お気に入りの物件はありません。</p>';
  };

  /**
   * run
   */
  _search.common.favorite.prototype.run = function () {

    // this._init();
    this._add();
    this._delete();
  };

  /**
   * お気に入りに登録ボタンの初期化
   *
   * @param callbackAdd
   * @param callbackDelete
   * @private
   */
  _search.common.favorite.prototype._init = function (callbackAdd, callbackDelete) {

    var self, $btn, favoriteList, id;

    self = this;

    favoriteList = $.cookie(app.cookie.KEY_FAVORITE);

    // list or personal
    if (_search.pageType.isList() || _search.pageType.isPersonal()) {

      $btn = self.$el.find('.article-object .btn-fav');
      $.each($btn, function (i, v) {

        // id get from attr data-bukken-no
        id = $btn.eq(i).closest('.article-object').data('bukken-no');

        $.each(favoriteList, function (j, savedId) {
          if (id === savedId && !$btn.eq(i).hasClass('done')) {
            self._toggle($btn.eq(i));
          }
        });
      });
      return;
    }

    // Map
    if(_search.pageType.isResultMap()){
      $btn = self.$el.find('.map-bl-list .bl-item__btn_fav');
      $.each($btn, function (i, v) {

        // id get from attr data-bukken-no
        id = $btn.eq(i).closest('.bl-item').data('bukken-no');

        $.each(favoriteList, function (j, savedId) {
          if (id === savedId && !$btn.eq(i).hasClass('done')) {
            self._toggle($btn.eq(i));
          }
        });
      });

      $btn = self.$el.find('.floatbox__map .btn-fav');
      $.each($btn, function (i, v) {

        // id get from attr data-bukken-no
        id = $btn.eq(i).closest('.article-object').data('bukken-no');

        $.each(favoriteList, function (j, savedId) {
          if (id === savedId && !$btn.eq(i).hasClass('done')) {
            self._toggle($btn.eq(i));
          }
        });
      });


      return;
    }

    // detail
    if (_search.pageType.isDetail()) {

      $btn = self.$el.find('.article-main-info .btn-fav');
      $.each($btn, function (i, v) {

        // id get from url
        id = app.location.bukkenId();

        $.each(favoriteList, function (j, savedId) {
          if (id === savedId) {
            self._toggle($btn.eq(i));
          }
        });
      });
    }
  };

  /**
   * お気に入りに登録ボタンのトグル処理
   *
   * @param $btn
   * @private
   */
  _search.common.favorite.prototype._toggle = function ($btn) {

    $btn.toggleClass('done');

    if ($btn.hasClass('done')) {
      $btn.empty().append('<span href="#">お気に入り登録</span>');
      return;
    }
    $btn.empty().append('<a href="#">お気に入り登録</a>');
  };

  _search.common.favorite.prototype._getNoItemElem = function () {

    if (_search.pageType.page_name === 'history') {
      return this.NO_ITEM_HISTORY;
    }

    if (_search.pageType.page_name === 'favorite') {
      return this.NO_ITEM_FAVORITE;
    }
  };

  _search.common.favorite.prototype._isShowMsg = function () {

    var config = $.cookie(app.cookie.KEY_FAVORITE_CONFIG);

    if (app.utility.isObject(config) && 'showMsg' in config) {
      return config.showMsg;
    }
    return true;
  };

  /**
   * お気に入りに追加
   *
   * @param target
   * @private
   */
  _search.common.favorite.prototype._add = function (target) {

    var self, $msg, showMsg;

    self = this;

    target = target ? target : '.btn-fav';

    $msg = self.$el.find('.fav-done-message');

    /**
     * show message
     * @private
     */
    showMsg = function () {
      $msg.css({'position': 'fixed', 'top': '50%', 'margin-top': -110}).fadeIn(100);
    };

    // add listener
    // add favodite
    self.$el.find(target + ' a').on('click', function (e) {

      var $btn, bukkenId;

      $btn = $(this).closest(target);

      // single
      if ($btn.closest('.collect-processing').length < 1) {

        self._toggle($btn);
        if (self._isShowMsg()) {
          showMsg();
        }

        // list or personal
        if(_search.pageType.isList() || _search.pageType.isPersonal()){
          bukkenId = $btn.closest('.article-object').data('bukken-no');

        // map
        }else if(_search.pageType.isResultMap()){
          //map side list
          bukkenId = $btn.parents('.bl-item').data('bukken-no');
          if(bukkenId==null){
            // map list
            bukkenId = $btn.closest('.article-object').data('bukken-no');
          }

        // detail
        }else{
          bukkenId = app.location.bukkenId();
        }
        app.cookie.updateFavorite({bukkenId: bukkenId});

        // 行動情報を保存
        app.cookie.saveOperation('updateFavorite', [bukkenId]);

        return false;
      }

      // multi
      var has_checked = false;
      var list = [];

      self.$el.find('.article-object-wrapper .object-check input[type="checkbox"]').each(function () {
        //target = .floatbox__map .btn-favだと.btn-favが取得出来ないため上書き
        var $btn, $article,
            target = '.btn-fav';

        if (!this.checked) {
          return true;
        }

        has_checked = true;

        $article = $(this).closest('.article-object');
        $btn = $article.find(target);

        if ($btn.hasClass('done')) {
          return true;
        }

        self._toggle($btn);
        list.push($article.data('bukken-no'));
      });

      if (!has_checked) {
        alert('物件が1つもチェックされていません');
        return false;
      }

      if (self._isShowMsg()) {
        showMsg();
      }

      for (var i = 0; i < list.length; i++) {
        app.cookie.updateFavorite({bukkenId: list[i]});
      }

      // 行動情報を保存
      app.cookie.saveOperation('updateFavorite', list);

      return false;
    });

    // add listener
    // modal close
    self.$el.find('.fav-done-message .btn-close a').on('click', function (e) {

      (e.preventDefault) ? e.preventDefault() : e.returnValue = false;

      app.cookie.updateFavoriteConfig({
        showMsg: !$(e.target).closest('.fav-done-message').find('#fav-done-next').prop('checked')
      });

      $msg.fadeOut(100);
    });
  };

  /**
   * お気に入りから削除
   *
   * @param target
   * @private
   */
  _search.common.favorite.prototype._delete = function (target) {

    var self, $msg, _showMsg;

    self = this;

    target = target ? target : '.btn-delete';

    _showMsg = function ($msg) {
      $msg.css({'position': 'fixed', 'top': '50%', 'margin-top': -110}).fadeIn(200, function () {
        $msg.delay(800).fadeOut(500);
      });
    };

    $msg = $('.fav-done-message');

    // add listener
    self.$el.find(target + ' a').on('click', function (e) {

      var $btn, $targetArticle, $ramainArticles,
        countTab4 = 0,
        articleList = [],
        bukkenIdList = [],
        i = 0;

      $btn = $(this).closest(target);

      /*
       * single
       */
      if ($btn.closest('.collect-processing').length === 0) {

        $targetArticle = $btn.closest('.article-object');

        $targetArticle.fadeTo(200, .5, function () {

          // confirm
          if (!confirm('この物件をお気に入りリストから削除します。よろしいですか？')) {
            $targetArticle.fadeTo(400, 1);
            return false;
          }

          // delete from cookie
          app.cookie.deleteFavorite([$targetArticle.data('bukken-no')]);

          $ramainArticles = self.$el.find('.article-object:visible');

          // remain article
          if ($ramainArticles.length > 1) {

            // delete element
            $targetArticle.delay(100).slideUp(300, function () {

              // show message
              _showMsg($msg);

              // delete
              $targetArticle.remove();

              // tab count
              self._updateTabCount(self);
            });
          }
          // if delete all
          else {

            // delete element
            $ramainArticles.remove();
            _showMsg($msg);

            // append no item msg
            $(self._getNoItemElem()).appendTo('.article-object-wrapper:visible').fadeIn(500);

            // hide collect processing
            self.$el.find('.collect-processing, .sort-select').hide();

            // tab count
            self._updateTabCount(self);
          }
        });

        return false;
      }

      /*
       * multi
       */

      // validate
      self.$el.find('.element-tab-body .object-check input[type="checkbox"]').each(function () {

        if (!this.checked) {
          return true;
        }
        countTab4++;
        articleList.push($(this).closest('.article-object'));
      });

      // alert
      if (articleList.length === 0) {
        alert('物件が1つもチェックされていません');
        return false;
      }

      $.each(articleList, function () {
        this.css({opacity: .5});
      });

      // confirm
      if (!confirm('これら物件をお気に入りリストから削除します。よろしいですか？')) {
        $.each(articleList, function () {
          this.css({opacity: 1});
        });
        return false;
      }

      // delete from cookie
      for (i = 0; i < articleList.length; i++) {
        bukkenIdList.push(articleList[i].data('bukken-no'));
      }
      app.cookie.deleteFavorite(bukkenIdList);

      // delete element
      $.each(articleList, function () {
        this.remove();
      });

      // show msg
      _showMsg($msg);

      // tab count
      self._updateTabCount(self);

      // if delete all
      if (self.$el.find('.article-object:visible').length < 1) {

        // no item msg
        $(self._getNoItemElem()).appendTo('.article-object-wrapper:visible').fadeIn(500);

        // collect processing hide
        self.$el.find('.collect-processing, .sort-select').hide();
      }

      return false;
    });

    // add listener
    // modal close
    self.$el.find('.fav-done-message .btn-close a').on('click', function (e) {

      (e.preventDefault) ? e.preventDefault() : e.returnValue = false;

      $msg.fadeOut(100);
    });
  };

  /**
   * お気に入り画面：タブのカウントを更新
   *
   * @param self
   * @private
   */
  _search.common.favorite.prototype._updateTabCount = function (self) {

    var $visibleTab4Area, $activeTab4, $tab4List, classes, total, i, $activeBaseTab;

    // update tab4 count
    $visibleTab4Area = self.$el.find('.element-tab-search:visible');
    $activeTab4 = $visibleTab4Area.find('.active a');

    $activeTab4.text($activeTab4.first().text().replace(/\（.+?\）/, '（' + self.$el.find('.article-object:visible').length + '件）'));

    // update base tab count
    $tab4List = $visibleTab4Area.first().find('a');
    classes = [];
    $.each($tab4List, function (i, v) {
      classes.push(self._getClassName($tab4List.eq(i)));
    });

    total = 0;
    for (i = 0; i < classes.length; i++) {
      total += self.$el.find('.article-object-wrapper.' + classes[i] + ' .article-object').length;
    }

    $activeBaseTab = self.$el.find('.checklist-tab .active a');
    $activeBaseTab.text($activeBaseTab.first().text().replace(/\（.+?\）/, '（' + total + '件）'));
  };

  /**
   * ターゲットのクラス名を取得
   *
   * @param $targetLink
   * @returns {*}
   * @private
   */
  _search.common.favorite.prototype._getClassName = function ($targetLink) {

    var classes, i;

    classes = $targetLink.closest('li').attr('class').split(' ');

    for (i = 0; i < classes.length; i++) {
      if (classes[i] === 'active') {
        continue;
      }
      return classes[i];
    }
  };

  /**
   * lazyload class
   *
   * @param $el
   * @returns {_search.common.lazyload}
   */
  _search.common.lazyload = function ($el) {

    if (!(this instanceof _search.common.lazyload)) {
      return new _search.common.lazyload($el);
    }
    this.$el = $el;
  };

  /**
   * run
   *
   * @param array
   */
  _search.common.lazyload.prototype.run = function (array) {

    var $target;

    for (var i = 0; i < array.length; i++) {
      $target = this.$el.find(array[i]);
      if ($target.length > 0) {
        $target.lazyload({effect: 'fadeIn'});
      }
    }
  };

  /**
   * 物件一覧 class
   *
   * @param $el
   * @returns {_search.common.article}
   */
  _search.common.article = function ($el) {

    if (!(this instanceof _search.common.article)) {
      return new _search.common.article($el);
    }
    this.$el = $el;
  };

  /**
   * run
   *
   */
  _search.common.article.prototype.run = function () {

    var self = this;
    var $article = self.$el.find('.article-object');
    var $target;

    // add listener
    // open detail
    $article.on('click', function (e) {

      var href;

      // checkbox
      if (e.target.tagName.toLowerCase() === 'input' || e.target.className === 'object-check') {
        return;
      }

      $target = $(e.target).parent();
      if ($target.hasClass('btn-near-info') || $target.hasClass('btn-chart-town')) {
        return;
      }

      href = $(this).closest('.article-object').find('.object-name a').attr('href');

      // 特集
      if (_search.pageType.isSpecialCategory()) {
        app.request.postForm(href, {'special-path': app.location.currentSpecialPath()}, true);
        return false;
      }

      window.open(href);
      return false;
    });

    // add listener
    // highlight
    $article.hover(
      function () {
        $(this).addClass('hover')
      },
      function () {
        $(this).removeClass('hover')
      }
    );

    // add listener
    // zoom image
    $article.find('.object-thumb img').hover(
      function () {
        var $zoom = $(this).closest('figure').next('.object-thumb-zoom');
        var $img = $zoom.find('img');
        if ($img.attr('src') === '' || typeof $img.attr('src') === 'undefined' || $img.attr('src') === null) {
          $img.attr('src', $img.data('original'));
        }
        $zoom.show().animate({marginLeft: 10}, 50);
      }, function () {
        var $zoom = $(this).closest('figure').next('.object-thumb-zoom');
        $zoom.hide().animate({marginLeft: 0}, 50);
      }
    )
  };

  /**
   * clickable area class
   *
   * @param $el
   * @returns {_search.common.clickableArea}
   */
  _search.common.clickableArea = function ($el) {

    if (!(this instanceof _search.common.clickableArea)) {
      return new _search.common.clickableArea($el);
    }
    this.$el = $el;
  };

  /**
   * run
   */
  _search.common.clickableArea.prototype.run = function () {

    // add listener
    this.$el.find('.js-clickable-area').click(function (e) {

      var $checkbox;

      if (e.target.nodeName.toLowerCase() === 'input') {
        return true;
      }

      $checkbox = $(e.target).children('input[type="checkbox"]');
      $checkbox.prop('checked', !$checkbox.prop('checked'));
    });
  };

  /**
   * おすすめ物件 class
   *
   * @param $el
   * @returns {_search.common.recommend}
   */
  _search.common.recommend = function ($el) {

    if (!(this instanceof _search.common.recommend)) {
      return new _search.common.recommend($el);
    }
    this.$el = $el;
  };

  /**
   * おすすめ物件スライドの初期化
   */
  _search.common.recommend.prototype.run = function () {

    var self, $recommend;

    self = this;

    $recommend = self.$el.find('.recommend-item-show');

    if ($recommend.length < 1) {
      return;
    }

    $recommend.slick({
      slidesToShow: 5,
      slidesToScroll: 5,
      infinite: true,
      prevArrow: $recommend.prev('.btn-prev')[0],
      nextArrow: $recommend.next('.btn-next')[0]
    });


    $recommend.find('.recommend-item a').on('click', function (e) {

      var $target, href,
        params = {},
        cnt = 0;

      $target = e.target.nodeName.toLowerCase() === 'a' ? $(e.target) : $(e.target).closest('a');

      href = $target.attr('href');

      // おすすめ物件からの遷移判定
      params['from-recommend'] = 'true';

      // 特集判定
      if (_search.pageType.isSpecialCategory()) {
        params['special-path'] = app.location.currentSpecialPath();
      }

      app.request.postForm(href, params, true);
      return false;
    })
  };

  /**
   * checkbox engine class
   *
   * @param $el
   * @returns {_search.common.checkboxEngine}
   */
  _search.common.checkboxEngine = function ($el) {

    if (!(this instanceof _search.common.checkboxEngine)) {
      return new _search.common.checkboxEngine($el);
    }
    this.$el = $el;
  };

  /**
   * checkbox engine 初期化
   *
   */
  _search.common.checkboxEngine.prototype.run = function () {

    var $searchArea = this.$el.find('.element-search-area-item');
    if ($searchArea.length > 0) {
      $searchArea.athome_checkbox_engine({
        parent_selector: 'h4 input[type="checkbox"]',
        wrapper_selector: 'ul'
      }, false);
    }
  };

  /**
   * お問い合わせ class
   *
   * @param $el
   * @returns {_search.common.contact}
   */
  _search.common.contact = function ($el) {

    if (!(this instanceof _search.common.contact)) {
      return new _search.common.contact($el);
    }
    this.$el = $el;
  };

  /**
   * run
   */
  _search.common.contact.prototype.run = function () {

    var self, $btnSingle, $btnMulti, $btnDetail;

    self = this;

    // single
    $btnSingle = self.$el.find('.article-object .btn-contact a');

    // add listener
    $btnSingle.on('click', function (e) {

      var $articleObject, params, parsed, dirs, i;

      $articleObject = $(e.target).closest('.article-object');

      var $shumokuCt = self.getShumokuCt();

      // 複合組み合わせの物件種別の場合の例外処理
      switch($shumokuCt) {
            case "chintai-jigyo-1":
            case "chintai-jigyo-2":
            case "chintai-jigyo-3":
            case "baibai-kyoju-1":
            case "baibai-kyoju-2":
            case "baibai-kyoju-3":
            case "baibai-jigyo-1":
            case "baibai-jigyo-2":
              var shumoku_parsed = $(this).closest('ul').find('a').eq(0).attr('href').split('/');
              $shumokuCt = shumoku_parsed[1];
              break;
            default:
              break;
      }

      params = {
        id: [$articleObject.data('bukken-no')],
        type: $shumokuCt
      };

      // 地図検索からのアクセス
      if (_search.pageType.page_name=='result_map') {
        params['from_searchmap'] = true;
      }

      // 特集
      if (_search.pageType.isSpecialCategory()) {
        params['special-path'] = app.location.currentSpecialPath();
      }
      // お気に入り、履歴
      else if (_search.pageType.isPersonal()) {
        // self.getShumokuCt() は8種目を取得しているので12種目を取得
        parsed = app.location.parseUrl($articleObject.find('.object-name a').attr('href'));
        dirs = parsed.pathname.split('/');
        for (i = 0; i < dirs.length; i++) {
          if (typeof dirs[i] === 'string' && dirs[i] !== '') {
            params.type = dirs[i];
            break;
          }
        }
      }

      app.customConsoleLog('----- お問い合わせパラメータ start -----');
      app.customConsoleLog(params);
      app.customConsoleLog('----- お問い合わせパラメータ end -----');

      app.request.postForm(
        app.location.setHttpsProtocol(e.target.href), params, true);
      return false;
    });

    // multi
    $btnMulti = self.$el.find('.collect-processing .btn-contact a');

    // add listener
    $btnMulti.on('click', function (e) {

      var $checkbox, list, $article, params, parsed, dirs, i, href;

      $checkbox = self.$el.find('.article-object .object-header input:checked');

      if ($checkbox.length < 1) {
        alert('物件が1つもチェックされていません');
        return false;
      }

      list = [];
      $article = $checkbox.closest('.article-object');

      $.each($article, function (i, v) {
        list.push($article.eq(i).data('bukken-no'));
      });

      var $shumokuCt = self.getShumokuCt();

      // 複合組み合わせの物件種別の場合の例外処理
      switch($shumokuCt) {
        case "chintai-jigyo-1":
        case "chintai-jigyo-2":
        case "chintai-jigyo-3":
        case "baibai-kyoju-1":
        case "baibai-kyoju-2":
        case "baibai-kyoju-3":
        case "baibai-jigyo-1":
        case "baibai-jigyo-2":
          var shumoku_parsed = $checkbox.eq(0).closest('div').find('a').eq(0).attr('href').split('/');
          $shumokuCt = shumoku_parsed[1];
          break;
        default:
          break;
      }

      params = {
        id: list,
        type: $shumokuCt
      };

      // 地図検索からのアクセス
      if (_search.pageType.page_name=='result_map') {
        params['from_searchmap'] = true;
      }

      // 特集
      if (_search.pageType.isSpecialCategory()) {
        params['special-path'] = app.location.currentSpecialPath();
      }
      // お気に入り、履歴
      else if (_search.pageType.isPersonal()) {
        // self.getShumokuCt() は8種目を取得しているので12種目を取得
        parsed = app.location.parseUrl($article.eq(0).find('.object-name a').attr('href'));
        dirs = parsed.pathname.split('/');
        for (i = 0; i < dirs.length; i++) {
          if (typeof dirs[i] === 'string' && dirs[i] !== '') {
            params.type = dirs[i];
            break;
          }
        }
      }

      // href
      href = e.target.href;
      if (_search.pageType.isPersonal()) {
        href = $article.eq(0).find('.btn-contact a').attr('href');
      }

      app.customConsoleLog('----- お問い合わせパラメータ start -----');
      app.customConsoleLog(params);
      app.customConsoleLog('----- お問い合わせパラメータ end -----');

      app.request.postForm(app.location.setHttpsProtocol(href), params, true);

      return false;
    });

    // detail
    $btnDetail = self.$el.find('.btn-mail-contact a');

    // add listener
    $btnDetail.on('click', function (e) {

      var params = {
        id: [app.location.bukkenId()],
        type: self.getShumokuCt()
      };

      // おすすめ物件からのアクセス
      if (search_config.from_recommend) {
        params['from-recommend'] = true;
      }

      // 地図検索からのアクセス
      if (search_config.from_searchmap) {
        params['from_searchmap'] = true;
      }


      app.customConsoleLog('----- お問い合わせパラメータ start -----');
      app.customConsoleLog(params);
      app.customConsoleLog('----- お問い合わせパラメータ end -----');
      var href = (typeof e.target.href != 'undefined') ? e.target.href : this.href;
      app.request.postForm(app.location.setHttpsProtocol(href), params, true);

      return false;
    });

  };

  /**
   * 物件種目の文字列を取得
   *
   * @returns {*|_search.shumoku|{}}
   */
  _search.common.contact.prototype.getShumokuCt = function () {

    var self, getShumokuCtPersonal;

    self = this;

    /**
     * 物件種目の文字列を取得（お気に入り、最近見た物件）
     *
     * @returns {*}
     */
    getShumokuCtPersonal = function () {

      return self.$el.find('.element-tab-search:visible').first().find('li.active').clone(true).removeClass('active').attr('class');
    };

    // 通常の物件一覧はパスから
    // 特集はjsonパラメータから
    // お気に入り、最近見た物件はClass名から取得

    return app.location.currentShumoku() || search_config.shumoku || getShumokuCtPersonal();
  };

  /**
   * 都道府県選択 タブ class
   *
   * @param $el
   * @returns {_search.selectPrefecture.tab}
   */
  _search.selectPrefecture.tab = function ($el) {

    if (!(this instanceof _search.selectPrefecture.tab)) {
      return new _search.selectPrefecture.tab($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.selectPrefecture.tab.prototype.run = function () {

    var self, $tabArea, $tabList, $activeTab, $prefLinks,
      pathList = {},
      _rewriteHref;

    self = this;

    /**
     * リンク先のhrefを書き換え
     *
     * @param $activeTab
     * @param $prefLinks
     * @param pathList
     * @private
     */
    _rewriteHref = function ($activeTab, $prefLinks, pathList) {

      var filename = '';
      if ($activeTab.hasClass('ensen')){
        filename ='line.html';
      }else if($activeTab.hasClass('spatial')) {
        filename ='map.html';
      }

      $prefLinks.each(function (i, v) {
        var $link = $prefLinks.eq(i);
        $link.attr('href', pathList[$link.data('name')] + filename);
      });
    };

    // tab
    $tabArea = self.$el.find('.element-tab-search');
    $tabList = $tabArea.find('li');
    $activeTab = $tabList.filter(function (i, v) {
      return $tabList.eq(i).hasClass('active');
    });

    // links
    $prefLinks = $('.element-search-table a');
    $prefLinks.each(function (i, v) {
      var $link = $prefLinks.eq(i);
      pathList[$link.data('name')] = $link.attr('href');
    });

    // no selected
    if ($activeTab.length < 1) {
      $activeTab = $tabList.first().addClass('active');
    }

    // rewrite link
    _rewriteHref($activeTab, $prefLinks, pathList);

    // add listener
    $tabList.on('click', function (e) {
      $activeTab = $(e.target).closest('li').addClass('active');
      $tabList.not($activeTab).removeClass('active');
      _rewriteHref($activeTab, $prefLinks, pathList);
      return false;
    });

  };

  /**
   * 市区郡選択 class
   *
   * @param $el
   * @returns {_search.selectCity.search}
   */
  _search.selectCity.search = function ($el) {

    if (!(this instanceof _search.selectCity.search)) {
      return new _search.selectCity.search($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.selectCity.search.prototype.run = function () {

    var self, $cityArea, $detailArea, $headingCheckbox, $checkbox, $searchBtn, _filterChecked, _toggleBtn;
	var $searchChosonBtn, $searchText, _toggleInput;
    var _resetCount;
    
    self = this;

    /**
     * checkのついているcheckboxのみ抽出
     *
     * @returns {*|Array.<T>|{TAG, CLASS, ATTR, CHILD, PSEUDO}}
     * @private
     */
    _filterChecked = function () {

      return $checkbox.filter(function (i) {
        return $checkbox.eq(i).prop('checked');
      });
    };

    /**
     * 検索ボタンのトグル
     *
     * @private
     */
    _toggleBtn = function () {

      _filterChecked().length < 1 ?
        $searchBtn.addClass('no').attr('disabled', 'disabled') :
        $searchBtn.removeClass('no').removeAttr('disabled');
    };
    /**
     * Toggle search input
     *
     * @private
     */
    _toggleInput = function () {

        _filterChecked().length < 1 ?
          $searchText.addClass('no').attr('disabled', 'disabled') :
          $searchText.removeClass('no').removeAttr('disabled');
      };
    /**
     * counter search city
     *
     * @private
     */
    var setEvent = function (data) {
        $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
        var $checked, values, city = '';
        var shumoku = null, special_path = null , prefecture = null;
        $checked = _filterChecked();
        if ($checked.length > 0) {
            _search.pageType.isSpecialCategory() ?
            special_path = app.location.currentSpecialPath() :
            shumoku = app.location.currentShumoku();
            prefecture = app.location.parseUrl(app.location.currentUrl).dirs[1];
            values = $checked.serializeArray();
            $.each(values, function (i, v) {
                city += v.value;
                if (i !== values.length - 1) {
                    city += ',';
                }
            });
            app.request.suggest({
                prefecture: prefecture,
                city: city,
                shumoku : shumoku,
                special_path: special_path,
                condition_side: $detailArea.find('input, select').serialize(),
            },'input[name="search_filter[fulltext_fields]"]');
            app.request.counter({
                prefecture: prefecture,
                city: city,
                shumoku : shumoku,
                special_path: special_path,
                condition_side: $detailArea.find('input, select').serialize(),
            },'input[name="search_filter[fulltext_fields]"]','fulltext_count');
        }
    }

    var _toggleChomeiBtn = function () {
      var checkedCount = $cityArea.find('ul input:checkbox:checked').length;
      checkedCount < 1 || checkedCount > 5?
          $searchChosonBtn.addClass('no').prop('disabled', true):
          $searchChosonBtn.removeClass('no').prop('disabled', false);
    };

    /**
     * Reset fulltext count
     *
     * @private
     */
    _resetCount = function () {
        $('.fulltext_count i').each(function () {
            $(this).text(0);
        })
    };

    $cityArea = self.$el.find('.element-search-area');
    $detailArea = self.$el.find('.contents-main-1column section').not($cityArea.find('section'));

    $headingCheckbox = $cityArea.find('.heading-area :checkbox');
    $checkbox = $cityArea.find(':checkbox').not($headingCheckbox);

    $searchBtn = self.$el.find('.element-btn-search input');
    $searchChosonBtn = $cityArea.find('.element-search-area-item-btn-filter input');
    $searchText = self.$el.find('.element-input-search input');

    // toggle search btn
    // init
    _toggleBtn();
    // toggle search input
    _toggleInput();

    setEvent();
    _filterChecked().length > 0 && $searchText.length > 0  ? $searchText.data('plugin_fulltextCount').getCount() : _resetCount();

    $cityArea.find('section').each(function (i, elem) {
      _toggleChomeiBtn();
    });

    // add listener
    $cityArea.find(':checkbox').on('change', function () {
      _toggleBtn();
      _toggleChomeiBtn();
      _toggleInput();
      setEvent();
      _filterChecked().length > 0 && $searchText.length > 0  ? $searchText.data('plugin_fulltextCount').getCount() : _resetCount();
    });
    $(document).on('keyup', $searchText.selector, function(e){
        if(e.keyCode != 40 && e.keyCode != 38){
            $searchText.val($(this).val());
        }
    });
    $(document).on('change', $searchText.selector, function(e){
        $searchText.val($(this).val());
    });
    self.$el.find('.form-search-freeword').on('submit', function (e) {
        e.preventDefault();
        $searchBtn.trigger("click");
    });
    // add listener
    // search
    $searchBtn.on('click', function (e) {

      var $checked, values, city = '';

      $checked = _filterChecked();

      if ($checked.length < 1) {
        alert('市区郡を選択してください');
        app.utility.scrollTo('.element-search-area');
        return false;
      }

      values = $checked.serializeArray();
      $.each(values, function (i, v) {
        city += v.value;
        if (i !== values.length - 1) {
          city += ',';
        }
      });

      app.request.postForm(
        app.location.currentPathname() + 'result/', {
          from_city_select: 1,
          city: city,
          detail: $detailArea.find('input, select').serialize()
        });
    });

    $cityArea.on('click', '.element-search-area-item-btn-filter input', function (e) {

      var $checked, values, city = '';
      var $section = $(this).closest('.element-search-area');

      $checked = $section.find('input:checkbox[name="shikugun_ct"]:checked');

      if ($checked.length < 1) {
          alert('市区郡を選択してください');
          return false;
      }

      if ($checked.length === 1) {
        location.href = app.location.currentPathname() + $checked.val() + '-city/';
        return false;
      }

      values = $checked.serializeArray();
      $.each(values, function (i, v) {
          city += v.value;
          if (i !== values.length - 1) {
              city += ',';
          }
      });

      app.request.postForm(
          app.location.currentPathname() + 'city/search/', {
              city: city,
              detail: $detailArea.find('input, select').serialize()
          });
    });
    $detailArea.find('input[type="checkbox"], select').on('change', function () {
        if ($searchText.length > 0) {
            setEvent();
            $searchText.data('plugin_fulltextCount').getCount();
        }
    });

  };

  /**
   * 町村選択画面 class
   *
   * @param $el
   * @returns {_search.selectChoson.search}
   */
    _search.selectChoson.search = function ($el) {

      if (!(this instanceof _search.selectChoson.search)) {
          return new _search.selectChoson.search($el);
      }

      this.$el = $el;
    };

    /**
     * run
     */
    _search.selectChoson.search.prototype.run = function () {
      var self, $stationArea, $detailArea, _filterChecked, _toggleBtn, $headingCheckbox, $checkbox, $searchBtn;

      self = this;

      /**
       * checkのついているcheckboxのみ抽出
       *
       * @returns {*|Array.<T>|{TAG, CLASS, ATTR, CHILD, PSEUDO}}
       * @private
       */
      _filterChecked = function () {

          return $checkbox.filter(function (i) {
              return $checkbox.eq(i).prop('checked');
          });
      };

      /**
       * 検索ボタンのトグル
       *
       * @private
       */
      _toggleBtn = function () {

          _filterChecked().length < 1 ?
              $searchBtn.addClass('no').attr('disabled', 'disabled') :
              $searchBtn.removeClass('no').removeAttr('disabled');
      };

      _toggleInput = function () {
        _filterChecked().length < 1 ? $searchText.addClass("no").attr("disabled", "disabled") : $searchText.removeClass("no").removeAttr("disabled");
      };
      var setEvent = function (data) {
        $searchText.unbind().data("plugin_fulltextCount", null).data("plugin_fulltextSuggest", null);
        var $checked,
            values,
            choson = "";
        var shumoku = null,
            special_path = null,
            prefecture = null;
        $checked = _filterChecked();
        if ($checked.length > 0) {
            _search.pageType.isSpecialCategory() ? (special_path = app.location.currentSpecialPath()) : (shumoku = app.location.currentShumoku());
            prefecture = app.location.parseUrl(app.location.currentUrl).dirs[1];
            values = $checked.serializeArray();
            $.each(values, function (i, v) {
                choson += v.value;
                if (i !== values.length - 1) {
                    choson += ",";
                }
            });
            app.request.suggest({ prefecture: prefecture, from_choson_select: 1, choson: choson, shumoku: shumoku, special_path: special_path, condition_side: $detailArea.find("input, select").serialize() }, 'input[name="search_filter[fulltext_fields]"]');
            app.request.counter(
                { prefecture: prefecture, from_choson_select: 1, choson: choson, shumoku: shumoku, special_path: special_path, condition_side: $detailArea.find("input, select").serialize() },
                'input[name="search_filter[fulltext_fields]"]',
                "fulltext_count"
            );
        }
      };
      var _toggleChomeiBtn = function () {
        var checkedCount = $stationArea.find("ul input:checkbox:checked").length;
        checkedCount < 1 || checkedCount > 5 ? $searchChosonBtn.addClass("no").prop("disabled", true) : $searchChosonBtn.removeClass("no").prop("disabled", false);
      };
      _resetCount = function () {
        $(".fulltext_count i").each(function () {
            $(this).text(0);
        });
      };

      $stationArea = self.$el.find('.element-search-area');
      $detailArea = self.$el.find('.contents-main-1column section').not($stationArea.find('section'));

      $headingCheckbox = $stationArea.find('.heading-area :checkbox');
      $checkbox = $stationArea.find(':checkbox').not($headingCheckbox);

      $searchBtn = self.$el.find('.element-btn-search input');
      $resultLink = self.$el.find('.element-tab-search .link-all-result');
      $searchChosonBtn = $stationArea.find(".element-search-area-item-btn-filter input");
      $searchText = self.$el.find(".element-input-search input");
      // toggle search btn
      // init
      _toggleBtn();
      _toggleInput();
      setEvent();
      _filterChecked().length > 0 && $searchText.length > 0 ? $searchText.data("plugin_fulltextCount").getCount() : _resetCount();
      // add listener
      $stationArea.find(':checkbox').on('change', function () {
          _toggleBtn();
          _toggleChomeiBtn();
          _toggleInput();
          setEvent();
          _filterChecked().length > 0 && $searchText.length > 0 ? $searchText.data("plugin_fulltextCount").getCount() : _resetCount();
      });
      $(document).on('keyup', $searchText.selector, function(e){
        if(e.keyCode != 40 && e.keyCode != 38){
            $searchText.val($(this).val());
        }
      });
      $(document).on('change', $searchText.selector, function(e){
          $searchText.val($(this).val());
      });
      self.$el.find('.form-search-freeword').on('submit', function (e) {
          e.preventDefault();
          $searchBtn.trigger("click");
      });

      // add listener
      // search
      $searchBtn.on('click', function (e) {

          var $checked, values, station = '', parsed;

          $checked = _filterChecked();

          if ($checked.length < 1) {
              alert('町名を選択してください');
              app.utility.scrollTo('.element-search-area');
              return false;
          }

          values = $checked.serializeArray();
          $.each(values, function (i, v) {
              station += v.value;
              if (i !== values.length - 1) {
                  station += ',';
              }
          });

          parsed = app.location.parseUrl(app.location.currentUrl);
          app.request.postForm(
              '/' + parsed.dirs[0] + '/' + parsed.dirs[1] + '/result/', {
                  from_choson_select: 1,
                  choson: station,
                  detail: $detailArea.find('input, select').serialize()
              });
      });

  };

  /**
   * 沿線選択画面 class
   *
   * @param $el
   * @returns {_search.selectRailway.search}
   */
  _search.selectRailway.search = function ($el) {

    if (!(this instanceof _search.selectRailway.search)) {
      return new _search.selectRailway.search($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.selectRailway.search.prototype.run = function () {

    var self, $railwayArea, $checkbox, $btn, $input, _toggleBtn, _filterChecked;
    var $searchText, _toggleInput;
    self = this;

    /**
     * 検索ボタンのトグル
     *
     * @param $checked
     * @private
     */
    _toggleBtn = function ($checked) {

      $checked.length < 1 ?
        $input.addClass('no').attr('disabled', 'disabled') :
        $input.removeClass('no').removeAttr('disabled');

    };
    /**
     * Toggle search input
     *
     * @param $checked
     * @private
     */
    _toggleInput = function ($checked) {

        $checked.length < 1 ?
          $searchText.addClass('no').attr('disabled', 'disabled') :
          $searchText.removeClass('no').removeAttr('disabled');
  
      };

    /**
     * チェックのついた要素ののみ抽出
     *
     * @returns {*|Array.<T>|{TAG, CLASS, ATTR, CHILD, PSEUDO}}
     * @private
     */
    _filterChecked = function () {

      return $checkbox.filter(function (i) {
        return $checkbox.eq(i).prop('checked');
      });
    };

    $railwayArea = self.$el.find('.element-search-area');

    $checkbox = $railwayArea.find(':checkbox:not(:disabled)');

    $btn = self.$el.find('.element-btn-search');
    $input = $btn.find('input');
    $searchText = self.$el.find('.element-input-search input')

    // init
    _toggleBtn(_filterChecked());
    _toggleInput(_filterChecked());

    $searchText.on('keyup', function (e) {
        if(e.keyCode == 13){
            $input.trigger('click');
        }
    })

    // add listener
    // search
    $input.on('click', function (e) {

      var $checked, values, railway = '', current, to;

      $checked = _filterChecked();

      if ($checked.length < 1) {
        alert('路線を選択してください');
        return false;
      }

      values = $checked.serializeArray();
      $.each(values, function (i, v) {
        railway += v.value;
        if (i !== values.length - 1) {
          railway += ',';
        }
      });

      current = app.location.currentPathname();
      to = current.substr(0, (current.length - '.html'.length)) + '/search/';

      app.request.postForm(to, {
        from_railway_select: 1,
        railway: railway,
      });
    });

    // add listener
    // toggle btn & max 5
    $checkbox.on('change', function (e) {

      var $checked = _filterChecked();

      _toggleBtn($checked);
      _toggleInput($checked);

      if ($checked.length === 5) {
        $('body, html').animate({scrollTop: $btn.offset().top - 20}, 400, 'swing');
        return;
      }

      if ($checked.length > 5) {
        $(e.target).prop('checked', false);
        alert('路線は5つまで選択できます');
      }
    });

  };

  /**
   * 駅選択画面 class
   *
   * @param $el
   * @returns {_search.selectStation.search}
   */
  _search.selectStation.search = function ($el) {

    if (!(this instanceof _search.selectStation.search)) {
      return new _search.selectStation.search($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.selectStation.search.prototype.run = function () {

    var self, $stationArea, $detailArea, _filterChecked, _toggleBtn, $headingCheckbox, $checkbox, $searchBtn;
    var $searchText;
    var _resetCount;
    var _toggleInput;
    self = this;

    /**
     * checkのついているcheckboxのみ抽出
     *
     * @returns {*|Array.<T>|{TAG, CLASS, ATTR, CHILD, PSEUDO}}
     * @private
     */
    _filterChecked = function () {

      return $checkbox.filter(function (i) {
        return $checkbox.eq(i).prop('checked');
      });
    };

    /**
     * 検索ボタンのトグル
     *
     * @private
     */
    _toggleBtn = function () {

      _filterChecked().length < 1 ?
        $searchBtn.addClass('no').attr('disabled', 'disabled') :
        $searchBtn.removeClass('no').removeAttr('disabled');
    };
    /**
     * Toggle search input
     *
     * @private
     */
    _toggleInput = function () {
 
      _filterChecked().length < 1 ?
          $searchText.addClass('no').attr('disabled', 'disabled') :
          $searchText.removeClass('no').removeAttr('disabled');
    };
    /**
     * counter search station
     *
     * @private
     */
    var setEvent = function () {
        var $checked, values, station = '';
        var shumoku = null, special_path = null , prefecture = null
        $checked = _filterChecked();
        if ($checked.length > 0) {
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
            _search.pageType.isSpecialCategory() ?
            special_path = app.location.currentSpecialPath() :
            shumoku = app.location.currentShumoku();
            prefecture = app.location.parseUrl(app.location.currentUrl).dirs[1];
            values = $checked.serializeArray();
            $.each(values, function (i, v) {
                station += v.value;
                if (i !== values.length - 1) {
                    station += ',';
                }
            });
            app.request.counter({
                prefecture: prefecture,
                station: station,
                shumoku : shumoku,
                special_path: special_path,
                condition_side: $detailArea.find('input, select').serialize(),
            }, 'input[name="search_filter[fulltext_fields]"]', 'fulltext_count')
            app.request.suggest({
                prefecture: prefecture,
                station: station,
                shumoku : shumoku,
                special_path: special_path,
                condition_side: $detailArea.find('input, select').serialize(),
            }, 'input[name="search_filter[fulltext_fields]"]');
        }
    }

    /**
     * Reset fulltext count
     *
     * @private
     */
    _resetCount = function () {
        $('.fulltext_count i').each(function () {
            $(this).text(0);
        })
    };

    $stationArea = self.$el.find('.element-search-area');
    $detailArea = self.$el.find('.contents-main-1column section').not($stationArea.find('section'));

    $headingCheckbox = $stationArea.find('.heading-area :checkbox');
    $checkbox = $stationArea.find(':checkbox').not($headingCheckbox);

    $searchBtn = self.$el.find('.element-btn-search input');
    $searchText = self.$el.find('.element-input-search input');

    // toggle search btn
    // init
    _toggleBtn();
    // toggle search input
    _toggleInput();

    setEvent();
    _filterChecked().length > 0 && $searchText.length > 0 ? $searchText.data('plugin_fulltextCount').getCount() : _resetCount();

    // add listener
    $stationArea.find(':checkbox').on('change', function () {
      _toggleBtn();
      _toggleInput();
      setEvent();
      _filterChecked().length > 0 && $searchText.length > 0 ? $searchText.data('plugin_fulltextCount').getCount() : _resetCount();
    });
    $(document).on('keyup', $searchText.selector, function(e){
        if(e.keyCode != 40 && e.keyCode != 38){
            $searchText.val($(this).val());
        }
    });
    $(document).on('change', $searchText.selector, function(e){
        $searchText.val($(this).val());
    });
    self.$el.find('.form-search-freeword').on('submit', function (e) {
        e.preventDefault();
        $searchBtn.trigger("click");
    });

    // add listener
    // search
    $searchBtn.on('click', function (e) {

      var $checked, values, station = '', parsed;

      $checked = _filterChecked();

      if ($checked.length < 1) {
        alert('駅を選択してください');
        app.utility.scrollTo('.element-search-area');
        return false;
      }

      values = $checked.serializeArray();
      $.each(values, function (i, v) {
        station += v.value;
        if (i !== values.length - 1) {
          station += ',';
        }
      });

      parsed = app.location.parseUrl(app.location.currentUrl);
      app.request.postForm(
        '/' + parsed.dirs[0] + '/' + parsed.dirs[1] + '/result/', {
          from_station_select: 1,
          station: station,
          detail: $detailArea.find('input, select').serialize()
        });
    });
    $detailArea.find('input[type="checkbox"], select').on('change', function () {
        if ($searchText.length > 0) {
            setEvent();
            $searchText.data('plugin_fulltextSuggest').getSuggests();
            $searchText.data("plugin_fulltextCount").getCount();
        }
    });

  };

  /**
   * 物件一覧：sort-table class
   *
   * @param $el
   * @returns {_search.list.sortTable}
   */
  _search.list.sortTable = function ($el) {

    if (!(this instanceof _search.list.sortTable)) {
      return new _search.list.sortTable($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.list.sortTable.prototype.run = function () {

    var self = this;

    var $target, $first, $last, top, bottom, scn;

    // target
    $target = this.$el.find('.sort-table');

    if ($target.length < 1) {
      return;
    }

    // mobile
    if (app.ua.mobile) {
      return;
    }

    // pc or table
    $first = this.$el.find('.article-object:first');
    $last = this.$el.find('.article-object:last');

    // no articles
    if ($first.length < 1 || $last.length < 1) {
      return;
    }

    top = $target.offset().top;
    bottom = $last.offset().top + $last.height();

    // add listener
    $(window).scroll(function (v) {

      scn = $(this).scrollTop();

      if (scn > top && scn < bottom) {
        $target.css({'position': 'fixed', 'left': $first.offset().left - self._pageXOffset(), 'top': 0, 'z-index': 4});
        return;
      }

      $target.removeAttr('style');
      $first.css('margin-top', 0)
    });

    // add listener
    $(window).resize(function () {

      scn = $(this).scrollTop();

      if (scn > top && scn < bottom) {
        $target.css({
          'position': 'fixed',
          'left': $first.offset().left - self._pageXOffset(),
          'top': 0,
          'z-index': 4
        });
      }
    });
  };

  /**
   * pageXOffset 取得
   *
   * @returns {*}
   * @private
   */
  _search.list.sortTable.prototype._pageXOffset = function () {

    // ie8
    if (typeof window.pageXOffset === 'undefined') {
      return document.documentElement.scrollLeft;
    }

    // other browsers
    return window.pageXOffset;

  };

  /**
   * モーダル class
   *
   * @param $el
   * @returns {_search.list.modal}
   */
  _search.list.modal = function ($el) {

    if (!(this instanceof _search.list.modal)) {
      return new _search.list.modal($el);
    }
    this.$el = $el;

    // this.MAX_RAILWARY_SELECTABLE = 5;
    this.classes = ['search-modal-area', 'search-modal-railway', 'search-modal-station', 'search-modal-detail'];
  };

  /**
   * run
   */
  _search.list.modal.prototype.run = function () {

    var self = this;
    self._initCheckboxDetail();
    self._initCheckboxCommon(self.__initCityNames);
    self._initBtn();
    self._addListeners();
  };

  /**
   * close overlay
   *
   * @param e
   */
  _search.list.modal.prototype.hideOverlay = function (e) {

    var $floatbox = $('.floatbox');

    $floatbox.prev('.box-overlay').fadeOut(200).next().fadeOut(200, function () {
      $floatbox.find('.contents-iframe').hide()
    });
  };

  /**
   * modal checkbox status
   *
   * @private
   */
  _search.list.modal.prototype._initCheckboxDetail = function () {

    var self, $inputElems;

    self = this;

    $inputElems = self.$el.find('.contents-iframe .element-detail-table td').find(':checkbox');

    // init
    $.each($inputElems, function (i, v) {

      var $input, $label;

      $input = $inputElems.eq(i);
      $label = $input.next('label');

      // color
      if ($input.prop('checked')) {
        $label.addClass('checked');
      }
      else {
        $label.removeClass('checked');
      }

      // disabled

      // 特集の設定項目
      if ($input.hasClass('sp-disable')) {
        $input.prop('disabled', true);
        $label.removeClass('tx-disable');
        return true;
      }

      // not checked && count === 0
      if (!$input.prop('checked') && parseInt($label.find('.count').text().replace(/[^0-9^\.]/g, '')) === 0) {
        $input.prop('disabled', true);
        $label.addClass('tx-disable');
        return true;
      }

      // others
      $input.prop('disabled', false);
      $label.removeClass('tx-disable');
      return true;
    });

    // add listener
    $inputElems.on('change', function (e) {

      // color only
      this.checked ? $(this).next('label').addClass('checked') : $(this).next('label').removeClass('checked');
    })
  };

  /**
   * checkboxの初期化
   * - 「すべてにチェック」が "checked" なら子要素も "checked"
   * - 子要素がすべて "checked" なら「すべてにチェック」を "checked"
   *
   * @private
   */
  _search.list.modal.prototype._initCheckboxCommon = function (callback) {

    var self, i, $wrapper, $checkedList, $headingArea;

    self = this;

    for (i = 0; i < self.classes.length; i++) {

      // modal
      $wrapper = self.$el.find('.' + self.classes[i]);

      // continue if no modal
      if ($wrapper.length < 1) {
        continue;
      }

      $headingArea = $wrapper.find('.heading-area');

      if ($headingArea.length < 1) {
        continue;
      }

      // 「すべてにチェック」が "checked" なら子要素も "checked"
      $checkedList = $headingArea.find(':checkbox:checked');
      if ($checkedList.length > 0) {
        $.each($checkedList, function (i, v) {
          var $checkbox = $checkedList.eq(i).closest('section').find(':checkbox:not(:disabled)').not($checkedList.eq(i));

          if ($checkbox.length > 0) {
            $checkbox.prop('checked', true);
            return true;
          }

          // 子要素が0の場合は「すべてにチェック」から "checked" を外す
          $checkedList.eq(i).prop({
            'checked': false
            //,
            //'disabled': true
          });
        });
      }

      // 子要素がすべて "checked" なら「すべてにチェック」を "checked"
      $.each($headingArea, function (i, v) {

        var $section, $enabled, $checked, $checkAll, enableLength, checkedLength;

        $section = $headingArea.eq(i).closest('section');

        $enabled = $section.find(':checkbox:not(:disabled)');
        $checked = $section.find(':checkbox:checked');
        $checkAll = $headingArea.find(':checkbox');

        enableLength = $enabled.not($checkAll).length;
        checkedLength = $checked.not($checkAll).length;

        if (0 < enableLength && enableLength <= checkedLength) {
          $enabled.prop('checked', true);
        }
      });

    }

    callback(self);
  };

  /**
   * モーダル表示ボタンの初期化
   *
   * @private
   */
  _search.list.modal.prototype._initBtn = function () {

    var _togglePref, _toggleArea, _toggleRailway,
      self, $floatbox, $modalPref, $modalArea, $modalRailway, $modalStation;

    /**
     * 都道府県モーダルボタンの表示切替
     *
     * @param $btn
     * @param $modalPref
     * @param $modalArea
     * @param $modalRailway
     * @private
     */
    _togglePref = function ($btn, $modalPref, $modalArea, $modalRailway) {

      var show_or_hide, list, i;

      show_or_hide = $modalPref.find('.element-search-table a').length < 2 ?
        'hide' :
        'show';

      // サイドの都道府県ボタン
      $btn[show_or_hide]();

      // 市区郡選択、沿線選択モーダルのリンク
      list = [$modalArea, $modalRailway];
      for (i = 0; i < list.length; i++) {
        if (list[i].length > 0) {
          list[i].find('.btn-change')[show_or_hide]();
        }
      }
    };

    /**
     * 市区郡モーダルボタンの表示切替
     *
     * @param $btn
     * @param $modalArea
     */
    _toggleArea = function ($btn, $modalArea) {
      // 町名で絞り込むボタンがある場合は表示
      if ($modalArea.find('.element-search-area-item-btn-filter').length) {
        $btn.show();
        return;
      }

      // サイドの都道府県ボタン
      //$modalArea.find(':checkbox:enabled')
      $modalArea.find(':checkbox')
        .not($modalArea.find('.heading-area :checkbox')).length < 2 ?
        $btn.hide() :
        $btn.show();
    };

    /**
     * 沿線モーダルボタンの表示切替
     *
     * @param $btn
     * @param $modalRailway
     * @param $modalStation
     */
    _toggleRailway = function ($btn, $modalRailway, $modalStation) {

      var show_or_hide;

      show_or_hide = $modalRailway.find(':checkbox:enabled').length < 2 ?
        'hide' :
        'show';

      // サイドの都道府県ボタン
      $btn[show_or_hide]();

      // 駅選択モーダルのリンク
      if ($modalStation.length > 0) {
        $modalStation.find('.btn-change')[show_or_hide]();
      }
    };

    self = this;

    $floatbox = self.$el.find('.floatbox');
    $modalPref = $floatbox.find('.search-modal-prefecture');
    $modalArea = $floatbox.find('.search-modal-area');
    $modalRailway = $floatbox.find('.search-modal-railway');
    $modalStation = $floatbox.find('.search-modal-station');

    // 都道府県モーダル
    if ($modalPref.length > 0) {
      _togglePref(self.$el.find('.change-area1 .btn-change'), $modalPref, $modalArea, $modalRailway);
    }

    // 市区郡モーダル
    if ($modalArea.length > 0) {
      _toggleArea(self.$el.find('.change-area2 .btn-change'), $modalArea);
    }

    // 沿線モーダル
    if ($modalRailway.length > 0) {
      _toggleRailway(self.$el.find('.change-area2 .btn-change li:first'), $modalRailway, $modalStation);
    }
  };

  /**
   * add listener
   *
   * @private
   */
  _search.list.modal.prototype._addListeners = function () {

    var self, i, $wrapper, $floatbox;

    self = this;

    $floatbox = self.$el.find('.floatbox');

    // add listener
    // show
    self.$el.find('a.js-modal').on('click', function (e) {

      var $modal, _showModal, className, $bk, $clone, config;

      // ATHOME_HP_DEV-4900 物件一覧での不要なリクエストを精査する
      var targetModal = $(this).data('target');

      var getListFlg = false;
      var condition;
      condition = _search.list.condition;
      switch(targetModal) {
          case 'search-modal-area':
          case 'search-modal-railway':
          case 'search-modal-station':
              getListFlg = true;
              break;
          default:
              // console.log(targetModal);
              break;
      }

      if(getListFlg) {
          var modalArea = $(".floatbox").find("." + targetModal);
          $(modalArea).html("");

          var modalUrl = '/api/modal/';
          if(_search.pageType.isSpecialCategory()) {
              modalUrl = '/api/' + app.location.parseUrl(app.location.currentUrl).dirs[0] + '/modal/';
          }

          $.ajax({
              type: 'POST',
              url: modalUrl,
              data: condition,
              timeout: 120 * 1000,
              async: false,
              dataType: 'json'
          }).done(function (res) {
              $(modalArea).html( $(res.hidden).find('.' + targetModal).html() );
          }).fail(function (res) {
          });
      }
      /**
       * show modal
       *
       * @private
       */
      _showModal = function () {

        var callback1, callback2;

        callback1 = function () {
          $floatbox.css({'opacity': 0, 'position': 'absolute'});
          $modal.fadeIn(100, callback2);
        };

        callback2 = function () {

          var left = $(window).innerWidth() * .5 - $floatbox.outerWidth() * .5;
          if (window.innerWidth < $floatbox.outerWidth()) {
            left = 10
          }

          $floatbox.css({'display': 'block', 'opacity': 1, 'left': left, 'top': 40});
          $floatbox.height($modal.height());
        };

        self.$el.find('.box-overlay').fadeIn(200, callback1);
      };

      className = $(this).data('target');
      $modal = $floatbox.find('.' + className);

      // 初期状態のモーダル残しておく
      $bk = $floatbox.find('.' + className + '-bk');

      if ($bk.length < 1) {

        // backup
        $bk = $modal.clone(true).removeClass(className).addClass(className + '-bk');
        $modal.after($bk);
      }
      else {

        // clone
        $clone = $bk.clone(true).removeClass(className + '-bk').addClass(className);
        $modal.after($clone).remove();
        $modal = $clone;
      }

      //ローディング表示時はモーダルさせない
      if($(this).attr("loading") != "true") {
        // resetListener
        app.search({$el: self.$el}).run();
        // show
        $('html,body').animate({scrollTop: 0}, '10', _showModal);
      }
      return false;
    });

    // add listener
    // close
    self.$el.find('.floatbox .btn-close, .box-overlay').on('click', self.hideOverlay);

    // add listener
    // resize
    $(window).resize(function () {
      $floatbox.css({'left': $(window).innerWidth() * .5 - $floatbox.outerWidth() * .5, 'top': 40})
    });

    // add listener
    // checkbox engine
    self.__initCheckboxEnginge();

    // add listener
    // switch
    $floatbox.find('.contents-iframe .btn-change a').on('click', function (e) {

      var $clicked, className, $modal, $clone, $bk, config;

      $clicked = $(e.target);
      $clicked.closest('.contents-iframe').hide();

      className = $clicked.data('target');
      $modal = $floatbox.find('.' + className);

      // 初期状態のモーダル残しておく
      $bk = $floatbox.find('.' + className + '-bk');

      if ($bk.length < 1) {

        // backup
        $bk = $modal.clone(true).removeClass(className).addClass(className + '-bk');
        $modal.after($bk);
      }
      else {

        // clone
        $clone = $bk.clone(true).removeClass(className + '-bk').addClass(className);
        $modal.after($clone).remove();
        $modal = $clone;
      }

      // reset listener
      app.search({$el: self.$el}).run();

      // show
      $modal.show();
      $floatbox.height($modal.height());

      return false;
    });

    // add listener
    // area, railway, station, detail
    // facet, btn
    for (i = 0; i < self.classes.length; i++) {

      // modal
      $wrapper = self.$el.find('.' + self.classes[i]);

      // continue if no modal
      if ($wrapper.length < 1) {
        continue;
      }

      switch (self.classes[i]) {
        case 'search-modal-area':
        case 'search-modal-station':
          // init
          self.__updateFacet($wrapper);

          // add listener
          $wrapper.find(':checkbox').not($wrapper.find('.heading-area :checkbox'))
            .on('change', {key: i}, function (e) {
              self.__updateFacet.call(self, $(e.target).closest('.' + self.classes[e.data.key]));
            });


          if (self.classes[i] === 'search-modal-area' && $wrapper.find('.element-search-area-item-btn-filter').length) {
            (function ($wrapper) {
              var $btns = $wrapper.find('.element-search-area-item-btn-filter input');
              function toggleChosonBtnDisabled() {
          var $checked = $wrapper.find('.element-search-area ul input:checkbox:checked');
          var isEnabled = ($checked.length >= 1 && $checked.length <= 5);
          $btns
            .prop('disabled', !isEnabled)
            .toggleClass('no', !isEnabled);
              }

              // チェックボックスチェックで活性・比活性
              $wrapper.on('change', function (e) {
                toggleChosonBtnDisabled();
              }).change();
            })($wrapper);
          }

          break;

        case 'search-modal-railway':

          self.__limitRailwayCount();

          // init
          _search.list.modal.toggleSearchBtn($wrapper, $wrapper.find(':checkbox:checked').length < 1);

          // addListener
          $wrapper.find(':checkbox').on('change', function () {
            var $modal = self.$el.find('.search-modal-railway');
            _search.list.modal.toggleSearchBtn($modal, $modal.find(':checkbox:checked').length < 1);
          });
          break;

        case 'search-modal-detail':

          // toggle btn init
          _search.list.modal.toggleSearchBtn($wrapper, $wrapper.find('.total-count:first').text() === '0件');

          // add listener
          // facet
          $wrapper.find(':checkbox').on('change', function (e) {

            var searchInstance, url, $changedElem;

            searchInstance = _search.list.search(self.$el);

            // abort
            searchInstance.abort();

            // sync with side checkbox
            $changedElem = $(e.target);
            self.$el
              .find('section.detail-side input[name="' + $changedElem.attr('name') + '"]')
              .prop('checked', !!$changedElem.prop('checked'));

            url = _search.pageType.isSpecialCategory() ? '/api/' + app.location.parseUrl(app.location.currentUrl).dirs[0] + '/fetchKodawariFacet/' : '/api/fetchKodawariFacet/';

            // fetch
            _search.list.currentRequest = $.ajax({

              type: 'POST',
              url: url,
              data: searchInstance.collectCondition({
                page: 1,
                side_or_modal: 'modal',
                condition_modal: self.$el.find('.search-modal-detail:visible').find('input, select').serialize()
              }),
              timeout: 120 * 1000,
              dataType: 'json'

            }).done(function (res) {

              app.customConsoleLog('----- ajax response -----');
              app.customConsoleLog(res);
              app.customConsoleLog('----- ajax response end -----');

              var $modal;

              // error
              if (typeof res.success === 'undefined' || !res.success) {
                _search.list.currentRequest = null;
                return;
              }

              // success
              $modal = self.$el.find('.search-modal-detail:visible');

              if ($modal.length < 1) {
                _search.list.currentRequest = null;
                return;
              }

              // total
              $modal.find('.total-count').text(app.utility.separate(res.count.total) + '件');

              // btn (when facet fetch)
              _search.list.modal.toggleSearchBtn($modal, res.count.total < 1);

              // facets
              $.each(res.count.facets, function (data_id, cnt) {

                var $input, $label, className = 'tx-disable';

                $input = $modal.find('input[data-id=' + data_id + ']');

                if ($input.length < 1) {
                  return true;
                }

                $label = $input.next('label');

                // 特集の設定項目
                if ($input.hasClass('sp-disable')) {
                  $input.prop('disabled', true);
                  $label.removeClass('tx-disable');
                  return true;
                }

                // input
                $input.prop('disabled', cnt < 1 && !$input.prop('checked'));

                // label
                cnt < 1 ? $label.addClass(className) : $label.removeClass(className);
                $label.find('.count').text('(' + app.utility.separate(cnt) + ')');

              });

              _search.list.currentRequest = null;

            }).fail(function (res) {

              app.customConsoleLog('----- ajax failed -----');
              app.customConsoleLog(res);
              app.customConsoleLog('----- ajax failed end -----');

              // error
              if (res.statusText !== 'abort') {
                _search.list.currentRequest = null;
                return;
              }

              // abort
              _search.list.currentRequest = null;
            });

          });
          break;
      }
    }
  };

  /**
   * modal checkbox engine
   *
   * @private
   */
  _search.list.modal.prototype.__initCheckboxEnginge = function () {

    var $chosonArea = this.$el.find('.search-modal-choson');
    var $area = this.$el.find('.element-search-area section')
                  .not($chosonArea.find('.element-search-area section'));

    var self = this;

    if ($area.length > 0) {
      $area.athome_checkbox_engine({
        parent_selector: 'h4 input[type="checkbox"]',
        wrapper_selector: 'ul'
      }, false, {
        method: this.__updateFacet,
        arg: this.$el.find('.search-modal-area, .search-modal-station')
      });
    }

    // 町名選択モーダルは中身だけ入れ替えるので、親要素でイベント処理する
    $chosonArea.on('change', 'input', function () {
      var $input = $(this);
      var $section = $input.closest('section');
      if ($input.attr('name') === 'shikugun_ct') {
        // 町名全選択/解除
        if ($input.prop('checked')) {
          // 全選択
          $section.find('ul input:checkbox:not(:disabled)').prop('checked', true);
        } else {
          // 全解除
          $section.find('ul input:checkbox').prop('checked', false);
        }
      } else {
        // 市区群チェックボックスに状態反映
        $section.find('h4 input').prop('checked', !$section.find('ul input:not(:disabled):not(:checked)').length);
      }
      self.__updateFacet($section.closest('.search-modal-choson'));
    });
  };

  /**
   * 沿線選択 最大5件
   *
   * @private
   */
  _search.list.modal.prototype.__limitRailwayCount = function () {

    var self, $modal, $list;

    self = this;

    $modal = self.$el.find('.search-modal-railway');
    $list = $modal.find('ul input[type="checkbox"]').filter(function (i) {
      return !this.disabled; // デフォルトで disabled は除く
    });

    // add listener
    $list.on('change', function (e) {

      var count = $list.filter(function (i) {
        return this.checked;
      }).length;

      if (count === 5) {
        $('body, html').animate({scrollTop: $modal.find('.btn-search').last().offset().top - 20}, 400, 'swing');
      }

      if (5 < count) {

        $(e.target).prop('checked', false);
        alert('路線は5つまで選択できます');
      }
    });

  };

  /**
   * サイド 市区郡名 初期化
   *
   * @private
   */
  _search.list.modal.prototype.__initCityNames = function (self) {

    var $checboxList, $txtArea, txt;

    $txtArea = self.$el.find('.change-area2 .area-detail');
    $checboxList = self.$el.find('.search-modal-area :checked').not(self.$el.find('.heading-area :checkbox'));

    if ($checboxList.length < 1 || $txtArea.text().length > 0) {
      return;
    }

    txt = '';

    $.each($checboxList, function (i, v) {

      if (2 <= i) {
        txt += '（他' + ($checboxList.length - 2) + '地域）';
        return false;
      }

      if (i === 1) {
        txt += '・';
      }

      txt += $checboxList.eq(i).next('label').text().replace(/\(.+?\)/, '');
    });

    $txtArea.text(txt);
  };

  /**
   * ファセットの更新
   *
   * @param $modal
   * @private
   */
  _search.list.modal.prototype.__updateFacet = function ($modal) {

    var self, $total, $checkboxList, $checkedList, className,
      total = 0;

    self = this;

    // modal class name
    className = $modal.hasClass('search-modal-station') ?
      'search-modal-station' :
      'search-modal-area';

    // total element
    $total = $modal.find('.total-count');

    // checkbox list
    $checkboxList = $modal.find(':checkbox')
      .not($modal.find('.heading-area :checkbox'));

    // checked list
    $checkedList = $checkboxList.filter(function (i, v) {
      return $checkboxList.eq(i).prop('checked');
    });

    // no checked
    if ($checkedList.length < 0) {
      $total.text('0件');
      _search.list.modal.toggleSearchBtn($modal, true);
      return;
    }

    $.each($checkedList, function (i, v) {

      var $count, count;

      $count = className === 'search-modal-station' ?
        $checkedList.eq(i).siblings('span').first() :
        $checkedList.eq(i).siblings('label').first();

      if ($count.length < 1) {
        return true;
      }

      // count number
      count = parseInt($count.text().replace(/[^0-9]/g, ''));
      if (!app.utility.isReallyNaN(count)) {
        total += count;
      }
    });

    // rewrite
    $total.text(app.utility.separate(total) + '件');

    _search.list.modal.toggleSearchBtn($modal, total < 1);
  };

  /**
   * 検索ボタンのトグル
   *
   * @param $modal
   * @param disabled
   */
  _search.list.modal.toggleSearchBtn = function ($modal, disabled) {

    var $btnSearch = $modal.find('.btn-search');
    var text = '検索する';

    disabled ?
      $btnSearch.addClass('no').empty().append($('<span>').text(text)) :
      $btnSearch.removeClass('no').empty().append($('<a>').attr({href: '#'}).text(text));
  };

  /**
   * 物件一覧：一覧 class
   *
   * @param $el
   * @returns {_search.list.list}
   */
  _search.list.list = function ($el) {

    if (!(this instanceof _search.list.list)) {
      return new _search.list.list($el);
    }
    this.$el = $el;
  };

  /**
   * run
   */
  _search.list.list.prototype.run = function () {

    var $article, $pager;

    $article = this.$el.find('.articlelist-inner');

    if ($article.length > 0) {

      // checkbox engine
      $article.athome_checkbox_engine({
        parent_selector: '.check-all',
        wrapper_selector: '.article-object-wrapper',
        target_selector: '.object-header input'
      });
    }

    // pager
    $pager = this.$el.find('.article-pager');

    // only 1 page
    // (first, prev, this page, next, last) * (top + bottom) = 10
    if ($pager.find('li').length <= 10) {

      $pager.find('li').hide();
    }
    else {

      // first page
      if ($pager.find('.pager-prev').next('li').find('a').length < 1) {
        $pager.find('.pager-first, .pager-prev').hide();
      }

      // last page
      if ($pager.find('.pager-next').prev('li').find('a').length < 1) {
        $pager.find('.pager-last, .pager-next').hide();
      }

    }

    // howtoinfo
    this.$el.find('.link-howto-see').on('click', function (e) {

      /**
       * post data
       *
       * @param url
       * @param data
       * @param openWindow
       */
      var post = function (url, data, openWindow) {

        var now, name, $form, params;

        now = $.now();
        name = 'formpost' + now;

        params = {action: url, method: 'post'};

        if (!!openWindow) {

          window.open('', name, 'width=1050,height=800,scrollbars=yes');
          params.target = name;
        }

        $form = $('<form/>', params);
        $.each(data, function (i, v) {
          $form.append($('<input/>', {type: 'hidden', name: i, value: v}));
        });
        $form.appendTo($('body')).submit().remove();
      };

      post(e.target.href, {type: app.location.currentShumoku() || search_config.shumoku}, true);

      return false;
    });
  };

  /**
   * 物件一覧：aside class
   *
   * @param $el
   * @returns {_search.list.aside}
   */
  _search.list.aside = function ($el) {

    if (!(this instanceof _search.list.aside)) {
      return new _search.list.aside($el);
    }
    this.$el = $el;
  };

  /**
   * run
   */
  _search.list.aside.prototype.run = function () {

    var self, $inputElems, $target, $label, isChecked;

    self = this;
    $inputElems = self.$el.find('.articlelist-side-section input');

    // init label
    for (var i = 0; i < $inputElems.length; i++) {

      $target = $inputElems.eq(i);
      $label = $target.next('label');
      isChecked = $target.prop('checked');

      // color
      $label[isChecked ? 'addClass' : 'removeClass']('checked');

      // count === 0
      if (!isChecked && parseInt($label.find('.count').text().replace(/[^0-9^\.]/g, '')) === 0) {
        $target.prop('disabled', true);
        $label.addClass('tx-disable');
      }
      // else {
      //   $targetInput.prop('disabled', false);
      //   $label.removeClass('tx-disable');
      // }
    }

    // add listener
    // toggle class for color
    $inputElems.on('change', function (e) {

      var $target = $(this);

      switch (this.type.toLowerCase()) {
        case 'radio':
          $target.closest('ul').find(':radio').next('label').removeClass('checked');
          $target.next('label').addClass('checked');
          break;
        case 'checkbox':
          $target.next('label')[this.checked ? 'addClass' : 'removeClass']('checked');
          break;
        default:
          break;
      }
    })
  };

  // 検索条件
  _search.list.condition = null;

  /**
   * 物件一覧：物件検索 class
   *
   * @param $el
   * @returns {_search.list.search}
   */
  _search.list.search = function ($el) {

    if (!(this instanceof _search.list.search)) {
      return new _search.list.search($el);
    }

    this.OVERLAY_Z_INDEX = 9999;
    this.S_TYPE_RESULT_RAILWAY = 1;
    this.S_TYPE_RESULT_CITY = 2;
    this.S_TYPE_RESULT_MCITY = 3;
    this.S_TYPE_RESULT_STATION = 4;
    this.S_TYPE_RESULT_MAP = 5;
    this.S_TYPE_RESULT_PREF = 6;
    this.S_TYPE_RESULT_CITY_FORM = 7;
    this.S_TYPE_RESULT_STATION_FORM = 8;
    this.S_TYPE_RESULT_DIRECT_RESULT = 9;
    this.S_TYPE_RESULT_CHOSON = 10;
    this.S_TYPE_RESULT_CHOSON_FORM = 11;
    this.S_TYPE_RESULT_FREEWORD = 12;

    this.WAIT_MS = 750;

    this.PATH_API_MODAL = '/api/modal/';
    this.PATH_SP_API_MODAL = '/api/' + app.location.parseUrl(app.location.currentUrl).dirs[0] + '/modal/';

    //this.$window = $(window);
    this.$el = $el;
    this.$loading = this.$el.find('.loading');
    this.$overlay = this.$el.find('.box-overlay');
    this.$floatbox = this.$overlay.next('.floatbox');
    this.$modalWindows = this.$floatbox.find('.contents-iframe');
    this.$disabledElems = null;

    this.modalInstance = _search.list.modal(this.$el);
    this.apiInstance = _search.list.api(this.$el);
  };

  /**
   * run
   */
  _search.list.search.prototype.run = function () {

    // add search listener
    this._changePrefecture();
    this._changeRailways();
    this._searchByStations();
    this._searchByArea();
    this._searchByDetailModal();
    this._searchByDetailAside();
    this._changeSort();
    this._changeTotal();
    this._movePage();

    _search.list.condition = this.collectCondition();
    app.customConsoleLog(_search.list.condition);
  };

  /**
   * 都道府県変更
   *
   * @private
   */
  _search.list.search.prototype._changePrefecture = function () {

    var self, $target;

    self = this;

    self.$el.find('.search-modal-prefecture .element-search-table li').on('click', function (e) {

      var condition;

      self.abort();

      // validation
      // none

      self.closeModal();
      self.showLoading();

      $target = $(e.target);

      if (!self._getSType('_changePrefecture')) {
        alert('system error');
        return false;
      }

      // collect condition
      condition = self.collectCondition({
        prefecture: $target.data('name'),
        area: null,
        railway: null,
        station: null,
        s_type: self._getSType('_changePrefecture')
      });

      // update condition
      // not update

      _search.list.currentRequest = $.ajax({
        type: 'POST',
        url: self._postToWhenUpdateModalElement(),
        data: condition,
        timeout: 120 * 1000,
        dataType: 'json'

      }).done(function (res) {

        app.customConsoleLog('----- ajax response -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax response end -----');

        var $el, $btn, msg, modalName = '';

        // has error
        if (!res || !res.success) {

          msg = !res || !res.message ? 'system error' : res.message;
          self.closeLoading();
          alert(msg);
          return false;
        }

        $el = self.$el;

        $el.find('.floatbox').after(res.hidden).remove();
        app.search({$el: $el}).run();
        $('body, html').animate({scrollTop: 0}, 0);

        self.closeLoading();

        // show
        $btn = $el.find('.js-modal');

        $btn.each(function (i, v) {

          modalName = $btn.eq(i).data('target');

          // area or railway
          switch (modalName) {
            case 'search-modal-area':
            case 'search-modal-railway':

              var $modal, $floatbox;

              $modal = self.$el.find('.' + modalName);
              $floatbox = self.$el.find('.floatbox');

              // show modal
              self.$el.find('.box-overlay').fadeIn(0, function () {

                $floatbox.css({'opacity': 0, 'position': 'absolute'});

                $modal.fadeIn(0, function () {

                  var left = $(window).innerWidth() * .5 - $floatbox.outerWidth() * .5;
                  if (window.innerWidth < $floatbox.outerWidth()) {
                    left = 10
                  }

                  $floatbox.css({'display': 'block', 'opacity': 1, 'left': left, 'top': 40});
                  $floatbox.height($modal.height());
                });
              });
              return false;
            default:
              return true;
          }
        });

        _search.list.currentRequest = null;

      }).fail(function (res) {

        app.customConsoleLog('----- ajax failed -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax failed end -----');

        // error
        if (res.statusText !== 'abort') {
          _search.list.currentRequest = null;
          return;
        }

        // abort
        _search.list.currentRequest = null;
      });

      return false;

    });
  };

  /**
   * 沿線変更
   *
   * @private
   */
  _search.list.search.prototype._changeRailways = function () {

    var self, $modal, $checkbox, $lineList, _validate, _success;

    self = this;

    /**
     * validation
     * max 5
     *
     * @param $checkbox
     * @returns {boolean}
     * @private
     */
    _validate = function ($checkbox) {

      var $checked, res;

      res = {result: true, msg: ''};

      $checked = $checkbox.filter(function (i) {
        return this.checked;
      });

      // no checked
      if ($checked.length < 1) {
        res.result = false;
        res.msg = '路線を選択してください';
        return res;
      }

      // max 5
      if (5 < $checked.length) {
        res.result = false;
        res.msg = '路線は5つまで選択できます';
        return res;
      }

      return res;
    };

    /**
     * callback method when success
     *
     * @private
     */
    _success = function (res) {

      var $modal, $floatbox;

      self.$el.find('.floatbox').after(res.hidden).remove();
      app.search({$el: self.$el}).run();
      $('body, html').animate({scrollTop: 0}, 0);

      self.closeLoading();

      if (self.$el.find('.js-modal[data-target="search-modal-station"]').length > 0) {

        $modal = self.$el.find('.search-modal-station');
        $floatbox = self.$el.find('.floatbox');

        // show modal
        self.$el.find('.box-overlay').fadeIn(0, function () {

          $floatbox.css({'opacity': 0, 'position': 'absolute'});

          $modal.fadeIn(0, function () {

            var left = $(window).innerWidth() * .5 - $floatbox.outerWidth() * .5;
            if (window.innerWidth < $floatbox.outerWidth()) {
              left = 10
            }

            $floatbox.css({'display': 'block', 'opacity': 1, 'left': left, 'top': 40});
            $floatbox.height($modal.height());
          });
        });

      }
      _search.list.currentRequest = null;
    };

    $modal = self.$el.find('.search-modal-railway');
    $checkbox = $modal.find('ul :checkbox:not(:disabled)');
    $lineList = $checkbox.next('span').find('a');

    // add listener
    // multi
    $modal.find('.btn-search').on('click', function (e) {

      var railwayArray, railwayCsv = '', $prefModal, pref, condition, validate;

      self.abort();

      validate = _validate($checkbox);

      if (!validate.result) {
        alert(validate.msg);
        return false;
      }

      self.closeModal();
      self.showLoading();

      // collect condition
      self._propDisabledAll(false);

      $prefModal = self.$el.find('.search-modal-prefecture');
      pref = $prefModal.length > 0 ?
        $prefModal.find('.selected').data('name') :// multi
        app.location.currentPrefecture();// single

      railwayArray = self.$el.find('.search-modal-railway section ul input:checked').serializeArray();
      if (railwayArray.length > 0) {
        $.each(railwayArray, function (i, v) {
          railwayCsv += v.value;
          if (i !== railwayArray.length - 1) {
            railwayCsv += ',';
          }
        });
      }

      // update condition
      // not update

      self._propDisabledAll(true);

      if (!self._getSType('_changeRailways')) {
        alert('system error');
        return false;
      }

      condition = self.collectCondition({
        prefecture: pref,
        railway: railwayCsv,
        page: 1,
        area: null,
        station: null,
        s_type: self._getSType('_changeRailways')
      });

      _search.list.currentRequest = $.ajax({
        type: 'POST',
        url: self._postToWhenUpdateModalElement(),
        data: condition,
        timeout: 120 * 1000,
        dataType: 'json'

      }).done(function (res) {

        app.customConsoleLog('----- ajax response -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax response end -----');

        var msg;

        if (!res || !res.success) {

          msg = !res || !res.message ? 'system error' : res.message;
          self.closeLoading();
          alert(msg);
          return false;
        }

        _success(res);

      }).fail(function (res) {

        app.customConsoleLog('----- ajax failed -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax failed end -----');

        var msg;

        // error
        if (res.statusText !== 'abort') {
          msg = !res || !res.message ? 'system error' : res.message;
          self.closeLoading();
          alert(msg);
          return false;
        }

        // abort
        _search.list.currentRequest = null;
      });

      return false;
    });

    // add listener
    // single
    $lineList.on('click', function (e) {

      var railway, pref, $prefModal, condition;

      self.abort();
      self.closeModal();
      self.showLoading();

      // collect condition
      self._propDisabledAll(false);

      $prefModal = self.$el.find('.search-modal-prefecture');
      pref = $prefModal.length > 0 ?
        $prefModal.find('.selected').data('name') :// multi
        app.location.currentPrefecture();// single

      railway = $(e.target).parent('span').prev('input').val();

      self._propDisabledAll(true);

      if (!self._getSType('_changeRailways')) {
        alert('system error');
        return false;
      }

      condition = self.collectCondition({
        prefecture: pref,
        railway: railway,
        // reset station, condition modal
        page: 1,
        station: null,
        s_type: self._getSType('_changeRailways')
      });

      // update condition
      // not update

      _search.list.currentRequest = $.ajax({
        type: 'POST',
        url: self._postToWhenUpdateModalElement(),
        data: condition,
        timeout: 120 * 1000,
        dataType: 'json'

      }).done(function (res) {

        app.customConsoleLog('----- ajax response -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax response end -----');

        var msg;

        if (!res || !res.success) {

          msg = !res || !res.message ? 'system error' : res.message;
          self.closeLoading();
          alert(msg);
          return false;
        }

        _success(res);

      }).fail(function (res) {

        // error
        if (res.statusText !== 'abort') {
          _search.list.currentRequest = null;
          return;
        }

        // abort
        _search.list.currentRequest = null;
      });

      return false;
    })
  };

  /**
   * 駅から検索
   *
   * @private
   */
  _search.list.search.prototype._searchByStations = function () {

    var self = this;

    var $modal = self.$el.find('.search-modal-station');

    // add listener
    // multi
    $modal.find('.btn-search').on('click', function (e) {

      var $modal, stationArray, stationString, pref, $prefModal, condition;

      $modal = self.$el.find('.search-modal-station');

      self.abort();

      self._propDisabledAll(false);
      stationArray = $modal.find('section ul input:checked').serializeArray();
      self._propDisabledAll(true);

      if (stationArray.length < 1) {
        alert('駅を選択してください');
        return false;
      }

      if ($modal.find('.total-count').first().text() === '0件') {
        return false;
      }

      self.closeModal();
      self.showLoading();

      // collect condition

      // prefecture
      $prefModal = self.$el.find('.search-modal-prefecture');
      pref = $prefModal.length > 0 ?
        $prefModal.find('.selected').data('name') :// multi
        app.location.currentPrefecture();// single

      // station
      stationString = '';
      $.each(stationArray, function (i, v) {
        stationString += v.value;
        if (i !== stationArray.length - 1) {
          stationString += ',';
        }
      });

      if (!self._getSType('_searchByStations', stationArray.length)) {
        alert('system error');
        return false;
      }

      condition = self.collectCondition({
        prefecture: pref,
        station: stationString,
        railway: null,
        page: 1,
        s_type: self._getSType('_searchByStations', stationArray.length)
      });

      self.apiInstance.post(condition, self);

      // update condition
      _search.list.condition = condition;

      app.location.updatePageNumber();

      return false;
    });

    // add listener
    // single
    $modal.find('.element-search-area a').on('click', function (e) {

      var condition, parsed, $target, $prefModal, pref;

      // validation
      // none

      self.abort();
      self.closeModal();
      self.showLoading();

      $target = $(e.target);

      // collect condition
      self._propDisabledAll(false);

      // prefecture
      $prefModal = self.$el.find('.search-modal-prefecture');
      pref = $prefModal.length > 0 ?
        $prefModal.find('.selected').data('name') :// multi
        app.location.currentPrefecture();// single

      condition = self.collectCondition({
        prefecture: pref,
        railway: null,
        station: $target.closest('span').prev('input').val(),
        page: 1
      });

      self._propDisabledAll(true);

      self.apiInstance.post(condition, self);

      _search.list.condition = condition;

      app.location.updatePageNumber();

      return false;
    });
  };

  /**
   * 市区郡から検索
   *
   * @private
   */
  _search.list.search.prototype._searchByArea = function () {

    var self = this;

    self.$el.find('.search-modal-area .btn-search').on('click', function (e) {

      var $modal, cityArray, cityCsv, pref, $prefModal, condition;

      $modal = self.$el.find('.search-modal-area');

      self.abort();

      self._propDisabledAll(false);
      cityArray = $modal.find('section ul input:checked').serializeArray();
      self._propDisabledAll(true);

      // validation
      if (cityArray.length < 1) {
        alert('市区郡を選択してください');
        return false;
      }

      if ($modal.find('.total-count').first().text() === '0件') {
        alert('該当する物件が見つかりませんでした。条件を変更してください。');
        return false;
      }

      self.closeModal();
      self.showLoading();

      // collect condition

      // prefecture
      $prefModal = self.$el.find('.search-modal-prefecture');
      pref = $prefModal.length > 0 ?
        $prefModal.find('.selected').data('name') :// multi
        app.location.currentPrefecture();// single

      cityCsv = '';
      $.each(cityArray, function (i, v) {
        cityCsv += v.value;
        if (i !== cityArray.length - 1) {
          cityCsv += ',';
        }
      });

      if (!self._getSType('_searchByArea', cityArray.length)) {
        alert('system error');
        return false;
      }

      condition = self.collectCondition({
        prefecture: pref,
        area: cityCsv,
        page: 1,
        s_type: self._getSType('_searchByArea', cityArray.length)
      });

      // update condition
      _search.list.condition = condition;

      self.apiInstance.post(condition, self);

      app.location.updatePageNumber();

      return false;
    });

    /**
     * 町名検索モーダル表示
     */
    self.$el.find('.search-modal-area .element-search-area-item-btn-filter input').on('click', function (e) {
      if ($(this).hasClass('no')) {
        return false;
      }

      var $modal, cityArray, cityCsv, pref, $prefModal, condition;

      $modal = self.$el.find('.search-modal-area');

      cityArray = $modal.find('section ul input:checked').serializeArray();

      self.closeModal();
      self.showLoading();

      // collect condition

      // prefecture
      $prefModal = self.$el.find('.search-modal-prefecture');
      pref = $prefModal.length > 0 ?
        $prefModal.find('.selected').data('name') :// multi
        app.location.currentPrefecture();// single

      cityCsv = '';
      $.each(cityArray, function (i, v) {
        cityCsv += v.value;
        if (i !== cityArray.length - 1) {
          cityCsv += ',';
        }
      });

      if (!self._getSType('_searchByArea', cityArray.length)) {
        alert('system error');
        return false;
      }

      condition = self.collectCondition({
        prefecture: pref,
        area: cityCsv,
        page: 1,
        only_choson_modal: 1,
        s_type: self._getSType('_searchByArea', cityArray.length)
      });

      _search.list.currentRequest = $.ajax({
        type: 'POST',
        url: self._postToWhenUpdateModalElement(),
        data: condition,
        timeout: 120 * 1000,
        dataType: 'json'

      }).done(function (res) {

        app.customConsoleLog('----- ajax response -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax response end -----');

        var msg;

        if (!res || !res.success) {

          msg = !res || !res.message ? 'system error' : res.message;
          self.closeLoading();
          alert(msg);
          return false;
        }

        // 町名一覧のみ更新
        self.$el.find('.search-modal-choson .element-search-area').html($(res.hidden).find('.search-modal-choson .element-search-area').html());
        // 全選択・解除チェックの更新
        self.$el.find('.search-modal-choson .element-search-area section').each(function () {
          $(this).find('ul input:checkbox').change();
        });

        $('body, html').animate({scrollTop: 0}, 0);

        self.closeLoading();

        var $modal = self.$el.find('.search-modal-choson');
        var $floatbox = self.$el.find('.floatbox');

        // show modal
        self.$el.find('.box-overlay').fadeIn(0, function () {

          $floatbox.css({'opacity': 0, 'position': 'absolute'});

          $modal.fadeIn(0, function () {

            var left = $(window).innerWidth() * .5 - $floatbox.outerWidth() * .5;
            if (window.innerWidth < $floatbox.outerWidth()) {
              left = 10
            }

            $floatbox.css({'display': 'block', 'opacity': 1, 'left': left, 'top': 40});
            $floatbox.height($modal.height());
          });
        });

        _search.list.currentRequest = null;

      }).fail(function (res) {

        app.customConsoleLog('----- ajax failed -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax failed end -----');

        var msg;

        // error
        if (res.statusText !== 'abort') {
          msg = !res || !res.message ? 'system error' : res.message;
          self.closeLoading();
          alert(msg);
          return false;
        }

        // abort
        _search.list.currentRequest = null;
      });

      return false;
    });

    /**
     * 町名検索
     * 町名モーダルは中身だけ入れ替えるので、親要素でイベント処理
     */
    self.$el.find('.search-modal-choson').on('click', '.btn-search:not(.no)', function (e) {

      var $modal, cityArray, cityCsv, pref, $prefModal, condition;

      $modal = self.$el.find('.search-modal-choson');

      self.abort();

      cityArray = $modal.find('section ul input:checked').serializeArray();

      if ($modal.find('.total-count').first().text() === '0件') {
        alert('該当する物件が見つかりませんでした。条件を変更してください。');
        return false;
      }

      self.closeModal();
      self.showLoading();

      // collect condition

      // prefecture
      $prefModal = self.$el.find('.search-modal-prefecture');
      pref = $prefModal.length > 0 ?
        $prefModal.find('.selected').data('name') :// multi
        app.location.currentPrefecture();// single

      cityCsv = '';
      $.each(cityArray, function (i, v) {
        cityCsv += v.value;
        if (i !== cityArray.length - 1) {
          cityCsv += ',';
        }
      });

      if (!self._getSType('_searchByChoson', cityArray.length)) {
        alert('system error');
        return false;
      }

      condition = self.collectCondition({
        prefecture: pref,
        area: '',
        choson: cityCsv,
        page: 1,
        s_type: self._getSType('_searchByChoson', cityArray.length)
      });

      // update condition
      _search.list.condition = condition;

      self.apiInstance.post(condition, self);

      app.location.updatePageNumber();

      return false;
    });

  };

  /**
   * こだわり条件（モーダル）から検索
   *
   * @private
   */
  _search.list.search.prototype._searchByDetailModal = function () {

    var self;

    self = this;

    self.$el.find('.search-modal-detail .btn-search').on('click', function (e) {

      var $modal, $checkbox, $sideSection, $notChecked, $inputHidden, params, condition;

      $modal = self.$el.find('.search-modal-detail');

      // validation
      if ($modal.find('.total-count').first().text() === '0件') {
        alert('該当する物件が見つかりませんでした。条件を変更してください。');
        return false;
      }

      self.abort();
      self.closeModal();
      self.showLoading();

      // sync with side checkbox
      $sideSection = self.$el.find('section.detail-side');
      $checkbox = $modal.find(':checkbox');
      $.each($checkbox, function (i, v) {
        var $target = $checkbox.eq(i);
        $sideSection.find('input[name="' + $target.attr('name') + '"]')
          .prop('checked', !!$target.prop('checked'));
      });

      // delete not checked hidden element
      $notChecked = $sideSection.find(':checkbox').not($sideSection.find(':checkbox:checked'));
      $inputHidden = $sideSection.find('input[type="hidden"]');
      $.each($notChecked, function (i, v) {
        $inputHidden.filter(function (j, val) {
          return $inputHidden.eq(j).attr('name') === $notChecked.eq(i).attr('name');
        }).remove();
      });

      // collect condition
      self._propDisabledAll(false);
      params = $checkbox.serialize();
      self._propDisabledAll(true);

      condition = self.collectCondition({
        page: 1,
        side_or_modal: 'modal',
        condition_modal: params
      });

      self.apiInstance.post(condition, self);

      // update condition
      _search.list.condition = condition;

      app.location.updatePageNumber();

      return false;
    });
  };

  /**
   * こだわり条件（サイド）から検索
   *
   * @private
   */
  _search.list.search.prototype._searchByDetailAside = function () {

    var self = this, timeoutId = null;
    ;
    var $searchText;

    $searchText = self.$el.find('.element-input-search-result').find('input');

    self.$el.find('.articlelist-side-section').find('input[type="checkbox"],input[type="radio"],select').on('change', function (e) {

      var $changedElem, condition,
        now = (new Date()).getTime();

      // sync with modal checkbox
      $changedElem = $(e.target);
      if (
        $changedElem.closest('section').hasClass('detail-side') &&
        $changedElem.prop('tagName').toLocaleLowerCase() === 'input'
      ) {
        self.$el.find('input[name="' + $changedElem.attr('name') + '"]')
          .prop('checked', !!$changedElem.prop('checked'));
      }

      self.abort();
      self.showLoading(false);

      self._propDisabledAll(false);
      var free_word = self.$el.find('.element-input-search-result').eq(0).find('input').serialize();
      var config = {
        condition_side: [self.$el.find('.articlelist-side-section').find('input, select').serialize(), free_word].join('&'),
        page: 1,
        side_or_modal: 'side',
        condition_modal: self.$el.find('.search-modal-detail').find('input, select').serialize()
      };
      self._propDisabledAll(true);
      condition = self.collectCondition(config);

      if (timeoutId) {
        clearTimeout(timeoutId);
      }

      timeoutId = setTimeout(function () {
        self.apiInstance.post(condition, self, false);
        timeoutId = null;
      }, self.WAIT_MS);

      // update condition
      _search.list.condition = condition;

      app.location.updatePageNumber();

      setEvent();
    });
    self.$el.find('a.search-freeword').on('click', function () {
      var condition;
      self.abort();
      self.showLoading(false);
      var free_word = self.$el.find('.element-input-search-result').eq(0).find('input').serialize();
      var config = {
          condition_side: [self.$el.find('.articlelist-side-section').find('input, select').serialize(), free_word].join('&'),
          page: 1,
        };
        condition = self.collectCondition(config);
      self.apiInstance.post(condition, self);
      _search.list.condition = condition;

      app.location.updatePageNumber();
    });
    self.$el.find('.form-search-freeword').on('submit', function (e) {
        e.preventDefault();
        self.$el.find('a.search-freeword').trigger("click");
    });
    var setEvent = function () {
        $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
        var config = {
            condition_side: [self.$el.find('.articlelist-side-section').find('input, select').serialize(), $searchText.serialize()].join('&'),
          };
        var condition = self.collectCondition(config);
        app.request.suggest(condition, 'input[name="search_filter[fulltext_fields]"]');
    }
    setEvent();
  };

  /**
   * 並び替え
   *
   * @private
   */
  _search.list.search.prototype._changeSort = function () {

    var self = this;
    var $wrapper = self.$el.find('.sort-select');

    $wrapper.find('select:last').on('change', function (e) {

      var condition, sort;

      sort = $(e.target).find('option:selected').val();
      if (!sort) {
        return false;
      }
      self.abort();
      self.showLoading();
      app.cookie.updateSearch({sort: sort});

      condition = self.collectCondition({page: 1});
      self.apiInstance.post(condition, self);
      _search.list.condition = condition;
      app.location.updatePageNumber();
    });

    self.$el.find('.sort-table a').on('click', function (e) {

      var $target, condition;

      // (e.preventDefault) ? e.preventDefault() : e.returnValue = false;

      self.abort();
      self.showLoading();

      $target = $(e.target);

      // pic
      if ($target.closest('th').hasClass('cell1')) {

        condition = self.collectCondition({pic: $target.attr('class') === 'floor-plan' ? 2 : 1});

      }
      // sort
      else {

        app.cookie.updateSearch({sort: $target.closest('span').data('value')});
        condition = self.collectCondition({page: 1});
      }
      self.apiInstance.post(condition, self);
      _search.list.condition = condition;
      app.location.updatePageNumber();
      return false;
    });

  };

  /**
   * 表示件数変更
   *
   * @private
   */
  _search.list.search.prototype._changeTotal = function () {

    var self, $wrapper;

    self = this;
    $wrapper = self.$el.find('.sort-select');

    $wrapper.find('select:first').on('change', function (e) {
      var condition;

      self.abort();
      self.showLoading();

      app.cookie.updateSearch({total: parseInt($(e.target).find('option:selected').val())});

      condition = self.collectCondition({page: 1});
      self.apiInstance.post(condition, self);
      _search.list.condition = condition;
      app.location.updatePageNumber();
    });
  };

  /**
   * ページ番号変更
   *
   * @private
   */
  _search.list.search.prototype._movePage = function () {

    var self = this;

    self.$el.find('.article-pager li').on('click', function (e) {

      var $clicked, $btn, $pager, total, per_page, current, go_to, last, condition;

      if (e.target.nodeName.toLowerCase() !== 'a') {
        return false;
      }

      self.abort();
      self.showLoading();

      $clicked = $(e.target);
      $btn = $clicked.closest('li');
      $pager = $btn.closest('.article-pager');

      total = parseInt($pager.closest('.count-wrap').find('.total-count span').text().replace(/,/g, ''));
      per_page = self.$el.find('.sort-select select').first().find(':selected').val();

      current = parseInt($pager.find('span').text());
      last = Math.ceil(total / per_page);

      // first
      if ($btn.hasClass('pager-first')) {

        go_to = 1;
      }
      // last
      else if ($btn.hasClass('pager-last')) {

        go_to = last;
      }
      // prev
      else if ($btn.hasClass('pager-prev')) {

        go_to = current - 1 < 1 ? 1 : current - 1;

      }
      // next
      else if ($btn.hasClass('pager-next')) {

        go_to = last < current + 1 ? last : current + 1;
      }
      // click number
      else {
        go_to = parseInt($clicked.text());
      }

      condition = self.collectCondition({page: go_to});
      self.apiInstance.post(condition, self);
      _search.list.condition = condition;

      app.location.updatePageNumber(go_to);
      return false;
    });
  };

  /**
   * 検索条件まとめる
   *
   * @param config
   * @returns {{}|*}
   */
  _search.list.search.prototype.collectCondition = function (config) {

    var self = this,
      condition = {},
      $modalPrefecture, values, $modalArea, $modalRailway, $modalStation, $modalDetail;

    if (typeof config === 'undefined') {
      config = {};
    }

    self._propDisabledAll(false);

    // init
    if (_search.list.condition === null) {

      // shumoku or special
      _search.pageType.isSpecialCategory() ?
        condition.special_path = app.location.currentSpecialPath() :
        condition.shumoku = app.location.currentShumoku();

      _search.pageType.page_name == 'result_freeword' ?
        condition.type_freeword = app.location.parseUrl(app.location.currentUrl).dirs[0] : null;
    
      // page
      condition.page = parseInt(self.$el.find('.article-pager:first li span').text());
      condition.page = app.utility.isReallyNaN(condition.page) ? 1 : condition.page; // NaN

      // per page -> cookie

      // sort     -> cookie

      // pic
      condition.pic = self.$el.find('.sort-table .cell1 a').attr('class') === 'floor-plan' ? 1 : 2;
      condition.pic = app.utility.isReallyNaN(condition.pic) ? 1 : condition.pic; // NaN

      // prefecture
      $modalPrefecture = self.$el.find('.search-modal-prefecture');
      condition.prefecture = $modalPrefecture.length > 0 ?
        $modalPrefecture.find('.selected').data('name') : // multi
        app.location.currentPrefecture(); // single

      // area
      $modalArea = self.$el.find('.search-modal-area');
      values = $modalArea.find('section ul input:checked').serializeArray();
      if (values.length > 0) {
        condition.area = '';
        $.each(values, function (i, v) {
          condition.area += v.value;
          if (i !== values.length - 1) {
            condition.area += ',';
          }
        });
      }

      // choson
      if (search_config.chosons) {
        condition.choson = search_config.chosons.join(',');
      }


      // railway
      $modalRailway = self.$el.find('.search-modal-railway');
      values = $modalRailway.find('section ul input:checked').serializeArray();
      if (values.length > 0) {
        condition.railway = '';
        $.each(values, function (i, v) {
          condition.railway += v.value;
          if (i !== values.length - 1) {
            condition.railway += ',';
          }
        });
      }

      // station
      $modalStation = self.$el.find('.search-modal-station');
      values = $modalStation.find('section ul input:checked').serializeArray();
      if (values.length > 0) {
        condition.station = '';
        $.each(values, function (i, v) {
          condition.station += v.value;
          if (i !== values.length - 1) {
            condition.station += ',';
          }
        });
      }
      // if has station, delete railway
      if (condition.station && typeof condition['railway'] !== 'undefined') {
        delete condition['railway'];
      }

      // condition side
      condition.condition_side = self.$el.find('.articlelist-side-section').find('input, select').serialize()+'&'+ self.$el.find('.element-input-search-result').find('input').serialize();

      // condition modal
      $modalDetail = self.$el.find('.search-modal-detail');
      condition.condition_modal = $modalDetail.find('input, select').serialize();

      // side or modal
      condition.side_or_modal = null;

      // s_type
      condition.s_type = search_config.s_type;
      ;

      // mcity
      if (condition.s_type === self.S_TYPE_RESULT_MCITY) {
        condition.mcity = app.location.currentMcity();
      }

      // direct result
      if (_search.pageType.isSpecialCategory()) {
        condition.direct_result = !!search_config.direct_result;
      }

    }
    else {
      // clone
      condition = $.extend(true, {}, _search.list.condition);
      $.each(config, function (key, val) {
        condition[key] = val;
      });

      // mcity
      condition.s_type === self.S_TYPE_RESULT_MCITY ?
        condition.mcity = app.location.currentMcity() :
        delete condition['mcity'];
    }

    app.customConsoleLog('----- 検索条件 start -----');
    app.customConsoleLog(condition);
    app.customConsoleLog('----- 検索条件 end -----');

    // disabled 戻す
    self._propDisabledAll(true);

    return condition;
  };

  /**
   * s_typeの判定
   *
   * @param type string
   * @param length int
   * @returns {*}
   * @private
   */
  _search.list.search.prototype._getSType = function (type, length) {

    var self = this;

    // 直接一覧
    if (search_config.direct_result) {
      return self.S_TYPE_RESULT_DIRECT_RESULT;
    }

    switch (type) {
      // 都道府県変更
      case '_changePrefecture':
        switch (search_config.s_type) {
          case self.S_TYPE_RESULT_RAILWAY :
          case self.S_TYPE_RESULT_STATION :
          case self.S_TYPE_RESULT_STATION_FORM :
            app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_RAILWAY);
            return self.S_TYPE_RESULT_RAILWAY;
          case self.S_TYPE_RESULT_CITY :
          case self.S_TYPE_RESULT_MCITY :
          case self.S_TYPE_RESULT_PREF :
          case self.S_TYPE_RESULT_CITY_FORM :
          case self.S_TYPE_RESULT_CHOSON :
          case self.S_TYPE_RESULT_CHOSON_FORM :
            app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_PREF);
            return self.S_TYPE_RESULT_PREF;
          default:
            app.customConsoleLog('s_type:' + false);
            return false;
        }
      // 路線変更
      case '_changeRailways':
        switch (search_config.s_type) {
          case self.S_TYPE_RESULT_RAILWAY :
          case self.S_TYPE_RESULT_STATION :
          case self.S_TYPE_RESULT_STATION_FORM :
            app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_RAILWAY);
            return self.S_TYPE_RESULT_RAILWAY;
          case self.S_TYPE_RESULT_CITY :
          case self.S_TYPE_RESULT_MCITY :
          case self.S_TYPE_RESULT_PREF :
          case self.S_TYPE_RESULT_CITY_FORM :
          case self.S_TYPE_RESULT_CHOSON :
          case self.S_TYPE_RESULT_CHOSON_FORM :
            app.customConsoleLog('s_type:' + false);
            return false;
          default:
            app.customConsoleLog('s_type:' + false);
            return false;
        }
      // 駅検索
      case '_searchByStations':
        switch (search_config.s_type) {
          case self.S_TYPE_RESULT_RAILWAY :
          case self.S_TYPE_RESULT_STATION :
          case self.S_TYPE_RESULT_STATION_FORM :
            if (length > 1) {
              app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_STATION_FORM);
              return self.S_TYPE_RESULT_STATION_FORM;
            }
            app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_STATION);
            return self.S_TYPE_RESULT_STATION;
          case self.S_TYPE_RESULT_CITY :
          case self.S_TYPE_RESULT_MCITY :
          case self.S_TYPE_RESULT_PREF :
          case self.S_TYPE_RESULT_CITY_FORM :
          case self.S_TYPE_RESULT_CHOSON :
          case self.S_TYPE_RESULT_CHOSON_FORM :
            app.customConsoleLog('s_type:' + false);
            return false;
          default:
            app.customConsoleLog('s_type:' + false);
            return false;
        }
      // 市区郡検索
      case '_searchByArea':
        switch (search_config.s_type) {
          case self.S_TYPE_RESULT_RAILWAY :
          case self.S_TYPE_RESULT_STATION :
          case self.S_TYPE_RESULT_STATION_FORM :
            app.customConsoleLog('s_type:' + false);
            return false;
          case self.S_TYPE_RESULT_CITY :
          case self.S_TYPE_RESULT_MCITY :
          case self.S_TYPE_RESULT_PREF :
          case self.S_TYPE_RESULT_CITY_FORM :
          case self.S_TYPE_RESULT_CHOSON :
          case self.S_TYPE_RESULT_CHOSON_FORM :
            if (length > 1) {
              app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_CITY_FORM);
              return self.S_TYPE_RESULT_CITY_FORM;
            }
            app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_CITY);
            return self.S_TYPE_RESULT_CITY;
          default:
            app.customConsoleLog('s_type:' + false);
            return false;
        }
      case '_searchByChoson':
        if (length > 1) {
          app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_CHOSON_FORM);
          return self.S_TYPE_RESULT_CHOSON_FORM;
        } else {
          app.customConsoleLog('s_type:' + self.S_TYPE_RESULT_CHOSON);
          return self.S_TYPE_RESULT_CHOSON;
        }
        return;
    }
  };

  /**
   * 先のリクエストをabort
   */
  _search.list.search.prototype.abort = function () {

    if (_search.list.currentRequest) {
      _search.list.currentRequest.abort();
    }
  };

  /**
   * callback when success
   *
   * @param res
   * @param scrollTop
   * @returns {boolean}
   */
  _search.list.search.prototype.callbackSuccess = function (res, scrollTop) {

    var self, $el, msg;

    self = this;

    // has error
    if (res === null || !res.success) {

      app.customConsoleLog('----- ajax failed -----');
      app.customConsoleLog(res);
      app.customConsoleLog('----- ajax failed end -----');

      msg = res === null || typeof res.message === 'undefined' ? 'system error' : res.message;
      self.closeLoading();
      alert(msg);
      return false;
    }

    $el = self.$el;

    // show
    $el.find('.box-overlay').hide(); // 検索中にモーダルを開いた場合
    $el.find('div.contents').after(res.content).remove();
    $el.find('.floatbox').after(res.hidden).remove();
    this.breadCrumb(res.breadCrumb);

    // init
    app.search({$el: $el}).run();

    // close loading
    if (scrollTop) {
      $('body, html').animate({scrollTop: 0}, 0);
    }
    self.closeLoading();

  };

    // render structure ld-json breadcrumb
  _search.list.search.prototype.breadCrumb = function(breadcrumb) {
    var script = $('script[type="application/ld+json"]');
    var dataJson = JSON.parse(script.html());
    var newJson = [];
    for(var key in dataJson.itemListElement) {
        if (key == 0) {
            newJson.push(dataJson.itemListElement[key]);
            break;
        }
    }
    var i = 2
    for(var url in breadcrumb) {
        if (url == '_empty_') {
            newJson.push({ '@type': 'ListItem', position: i, 'name': breadcrumb[url]});
        } else {
            newJson.push({ '@type': 'ListItem', position: i, 'name': breadcrumb[url], 'item': dataJson.itemListElement[0].item + url});
        }
        i++;
    }
    dataJson.itemListElement = newJson;
    script.html(JSON.stringify(dataJson));
}

  /**
   * callback when failed
   *
   * @param res
   */
  _search.list.search.prototype.callbackFail = function (res) {

    app.customConsoleLog('----- ajax failed -----');
    app.customConsoleLog(res);
    app.customConsoleLog('----- ajax failed end -----');

    this.closeLoading();
    alert('system error');
  };

  _search.list.search.prototype.whenValidationError = function (msg) {
    this.closeLoading();
    alert(msg || 'System error');
    this.closeModal();
  };

  /**
   * show loading
   *
   */
  _search.list.search.prototype.showLoading = function (showOverlay) {

    var self = this;
   $('a.js-modal').attr('loading', true);

    showOverlay = typeof showOverlay !== 'undefined' ? !!showOverlay : true;

    if (showOverlay) {
      self.$overlay.off('click', self.modalInstance.hideOverlay).show();
    }

    self.$loading.css({
      position: 'fixed',
      //top: (self.$window.height() ) / 2,
      //left: (self.$window.width() ) / 2,
      zIndex: self.OVERLAY_Z_INDEX + 1
    }).show();
  };

  /**
   * close loading
   *
   */
  _search.list.search.prototype.closeLoading = function () {

    var self = this;
    $('a.js-modal').attr('loading', false);

    // add overlay click listener & hide
    self.$el.find('.box-overlay')
      .on('click', self.modalInstance.hideOverlay)
      .hide();

    self.$loading.hide();
  };

  /**
   * close modal
   *
   */
  _search.list.search.prototype.closeModal = function () {

    this.$floatbox.hide();
    this.$modalWindows.hide();
    this.$overlay.hide();
 
  };

  _search.list.search.prototype._postToWhenUpdateModalElement = function () {

    // special
    if (_search.pageType.isSpecialCategory()) {
      return this.PATH_SP_API_MODAL;
    }

    // search
    return this.PATH_API_MODAL;

  };

  /**
   * prop disabled all
   *
   * @param boolean
   * @private
   */
  _search.list.search.prototype._propDisabledAll = function (boolean) {

    if (boolean) {
      this.$disabledElems.prop('disabled', true);
    }
    else {
      this.$disabledElems = this.$el.find(':disabled');
      this.$disabledElems.prop('disabled', false);
    }
  };

  /**
   * current request
   * use for cancel request
   *
   * @type {null}
   */
  _search.list.currentRequest = null;

  /**
   * api class
   *
   * @param $el
   * @returns {_search.list.api}
   */
  _search.list.api = function ($el) {

    if (!(this instanceof _search.list.api)) {
      return new _search.list.api($el);
    }

    this.parsed = app.location.parseUrl(app.location.currentUrl);
    this.host = this.parsed.protocol + '//' + this.parsed.host;

    this.PATH_API_SEARCH = '/api/search/';
    this.PATH_SP_API_SEARCH = '/api/' + this.parsed.dirs[0] + '/search/';
  };

  /**
   * search
   *
   * @param data
   * @param searchObj
   * @param scrollTop
   * @returns {boolean}
   */
  _search.list.api.prototype.post = function (data, searchObj, scrollTop) {

    scrollTop = typeof scrollTop === 'undefined' ? true : !!scrollTop;

    var self, _commonValidate, msg = '';

    // 共通のバリデーション
    _commonValidate = function () {

      // 沿線は常に単数
      if (typeof data.railway !== 'undefined' && data.railway !== null && data.railway.split(',').length !== 1) {
        msg = '【駅を変更】から駅を選択してください';
        return false;
      }

      // 沿線検索の場合、沿線必須
      if (data.s_type === searchObj.S_TYPE_RESULT_RAILWAY && !data.railway) {
        msg = '【路線変更】から路線を選択してください';
        return false;
      }

      return true;
    };

    self = this;

    // バリデーションエラーはリターン
    if (!_commonValidate()) {
      searchObj.whenValidationError(msg);
      _search.list.currentRequest = null;
      return false;
    }

    _search.list.currentRequest = $.ajax({
      type: 'POST',
      url: self._postTo(),
      data: data,
      timeout: 120 * 1000,
      dataType: 'json'

    }).done(function (res) {

      app.customConsoleLog('----- ajax response -----');
      app.customConsoleLog(res);
      app.customConsoleLog('----- ajax response end -----');

      // success
      searchObj.callbackSuccess(res, scrollTop);
      _search.list.currentRequest = null;

    }).fail(function (res) {

      app.customConsoleLog('----- ajax failed -----');
      app.customConsoleLog(res);
      app.customConsoleLog('----- ajax failed end -----');

      // error
      if (res.statusText !== 'abort') {
        searchObj.callbackFail(res);
        _search.list.currentRequest = null;
        return;
      }

      // abort
      _search.list.currentRequest = null;
    });
  };

  /**
   * post先のURL取得
   *
   * @returns {string}
   * @private
   */
  _search.list.api.prototype._postTo = function () {

    // special
    if (_search.pageType.isSpecialCategory()) {
      return this.PATH_SP_API_SEARCH;
    }

    // search
    return this.PATH_API_SEARCH;
  };

  _search.list.highlight = function ($el) {
    if (!(this instanceof _search.list.highlight)) {
        return new _search.list.highlight($el);
    }
    this.$el = $el;
    this.$highlightArea = this.$el.find('.highlightsArea');
  }
  _search.list.highlight.prototype.run = function () {
    var self;
    self = this;
    if (self.$highlightArea.length > 0) {
        self.$highlightArea.find('.grad-item').each(function () {
            if ($(this).height() > 98) {
                $(this).closest('.article-object').find('.grad-btn').removeClass('hide').addClass('show');
                $(this).addClass('blind');
            };
        });
        self.$highlightArea.find('.grad-btn').click( function (event) {
            $(this).parent().find('.grad-item').removeClass('blind');
            $(this).removeClass('show').addClass('hide');
            event.preventDefault();
            event.stopPropagation();
        });
    }
  };

  /**
   * 物件詳細：周辺情報 class
   *
   * @param $el
   * @returns {_search.detail.aroundGallery}
   */
  _search.detail.aroundGallery = function ($el) {

    if (!(this instanceof _search.detail.aroundGallery)) {
      return new _search.detail.aroundGallery($el);
    }

    this.$el = $el;

    this.$list = this.$el.find('.around-list');
    this.$overlay = this.$el.find('.box-overlay');
    this.$floatbox = this.$overlay.siblings('.floatbox');

    this.current = 0;
    this.total = this.$list.find('li img').length;
  };

  /**
   * run
   */
  _search.detail.aroundGallery.prototype.run = function () {

    var self;

    self = this;

    // init
    // zoom image
    self.$floatbox.find('.gallery-view .photo-zoom img').each(function () {

      var $this = $(this);

      $this.prop('src', $this.data('src')).css({display: 'none', position: 'absolute', top: 0, left: 45});
    });

    // add listener
    // show
    self.$list.find('a').on('click', function (e) {

      var $this = $(this);

      (e.preventDefault) ? e.preventDefault() : e.returnValue = false;

      self.current = parseInt(self.$list.find('li').index($this.closest('li')));
      self._header($this);
      self._open();
    });

    // add listener
    // slide
    self.$el.find('.btn-move a').on('click', function (e) {

      var direction;

      (e.preventDefault) ? e.preventDefault() : e.returnValue = false;

      direction = 1;
      if ($(this).closest('li').prop('class').indexOf('prev') > -1) {
        direction = -1;
      }

      self.current += direction;

      if (self.total <= self.current) {
        self.current = 0
      }
      else if (self.current < 0) {
        self.current = self.total - 1
      }

      self._header(self.$list.find('li:nth-child(' + (self.current + 1) + ')'), self.total);
    })
  };

  /**
   * open zoom image
   *
   * @private
   */
  _search.detail.aroundGallery.prototype._open = function () {

    var self = this;

    /**
     * close
     *
     * @private
     */
    var _close = function () {

      self.$floatbox.hide();
      self.$overlay.fadeOut(300);

      // remove listener
      self.$overlay.off('click', _close);
      self.$floatbox.find('.btn-close').off('click', _close);
    };

    /**
     * show
     *
     * @private
     */
    var _showImage = function () {

      var ml, pl;

      self.$floatbox.show();

      ml = -self.$floatbox.outerWidth() * .5;
      pl = '50%';

      if (window.innerWidth < self.$floatbox.outerWidth()) {
        pl = 10;
        ml = 0;
      }
      self.$floatbox.css({
        position: 'absolute',
        left: pl,
        top: $(window).scrollTop(),
        marginLeft: ml
      });

      // add listener
      // close
      self.$overlay.on('click', _close);
      self.$floatbox.find('.btn-close').on('click', _close);
    };

    // show zoom image
    self.$overlay.fadeIn(300, _showImage);
  };

  /**
   * set header info
   *
   * @param $target
   * @private
   */
  _search.detail.aroundGallery.prototype._header = function ($target) {

    var $parent, title, description, index;

    $parent = $target.closest('li');

    title = $parent.find('img').prop('title');
    description = $parent.find('.tx-desc').text();
    index = this.$list.find('li').index($parent) + 1;

    this.$floatbox.find('.photo-zoom img').hide();
    this.$floatbox.find('#gallery_detail_' + index).fadeIn(200);
    this.$floatbox.find('.tx-heading').text(title)
      .siblings('.tx-caption').text(description)
      .siblings('.count').text(index + '/' + this.total);

  };

  /**
   * tag
   *
   * @param $el
   * @returns {_search.detail.tag}
   */
  _search.detail.tag = function ($el) {

    if (!(this instanceof _search.detail.tag)) {
      return new _search.detail.tag($el);
    }

    this.$el = $el;
  };

  /**
   * run
   */
  _search.detail.tag.prototype.run = function () {

    // タグの2行対応
    this.$el.find('.article-main-info .article-tag li')
      .wrapInner('<span>')
      .tile();
  };

  /**
   * 物件詳細：物件情報 class
   *
   * @param $el
   * @returns {_search.detail.photoGallery}
   */
  _search.detail.photoGallery = function ($el) {

    if (!(this instanceof _search.detail.photoGallery)) {
      return new _search.detail.photoGallery($el);
    }

    this.$el = $el;

    // zoom image
    this.$overlay = $el.find('.box-overlay');
    this.$floatbox = this.$overlay.siblings('.floatbox');
    this.$btn_close = this.$floatbox.find('.btn-close');

    // thumbnail
    this.$thumbnail_wrap = $el.find('.thumb-wrap');
    this.$thumbnail_list = this.$thumbnail_wrap.find('.thumb-list li');
    this.$thumbnail_pager = this.$thumbnail_wrap.find('.pager-thumb');

    // preview
    this.$preview_wrap = $el.find('.photo-wrap');
    this.$preview = this.$preview_wrap.find('.photo-view li');

    // slide number
    this.currentSlide = 1;
    this.slide_total = this.$thumbnail_wrap.find('.thumb-view ul').length;

    // thumbnail number
    this.currentThumb = 1;
    this.thumb_total = this.$thumbnail_list.length;
  };

  /**
   * run
   */
  _search.detail.photoGallery.prototype.run = function () {

    var self = this;

    if (self.slide_total <= 1) {
      self.$thumbnail_pager.hide();
    }
    else {
      self._initIndicator();
      self._initThumbnail();
    }

    self._initZoom();
    self._showCurrentImage();

    // add listener
    // zoom image
    self.$preview_wrap.find('.link-zoom a').on('click', function (e) {

      (e.preventDefault) ? e.preventDefault() : e.returnValue = false;

      var _toClose = function () {
        self.$overlay.off('click', _toClose);
        self.$btn_close.off('click', _toClose);
        self.$floatbox.hide();
        self.$overlay.fadeOut(300)
      };

      self.$overlay.fadeIn(300, function () {
        self.$floatbox.show();
        var ml = -self.$floatbox.outerWidth() * .5;
        var pl = '50%';
        if (window.innerWidth < self.$floatbox.outerWidth()) {
          pl = 10;
          ml = 0;
        }

        self.$floatbox.css({position: 'absolute', left: pl, top: $(window).scrollTop(), marginLeft: ml});
        self.$overlay.on('click', _toClose);
        self.$btn_close.on('click', _toClose)
      })

    });

    // add listener
    // click thumb
    self.$thumbnail_list.on('click', function () {

      var num = self._currentImageNum(this);

      self.currentSlide = num.slide;
      self.currentThumb = num.thumb;

      self._showCurrentImage();
    });

    // add listener
    // slide preview and zoom image
    self.$el.find('.btn-move li a').on('click', function () {

      var direction, next_slide;

      direction = ($(this).closest('li').prop('class') === 'prev') ? -1 : 1;

      self.currentThumb += direction;

      if (self.currentThumb < 1) {
        self.currentThumb = self.thumb_total;
      }

      if (self.currentThumb > self.thumb_total) {
        self.currentThumb = 1;
      }

      next_slide = parseInt(self._currentImageNum((self.$thumbnail_list.closest('.thumb-list').find('li.num' + self.currentThumb))[0]).slide);

      if (self.slide_total > 1) {
        self.$thumbnail_wrap.find('.thumb-view')[0].slick.slickGoTo(next_slide)
      }

      self._showCurrentImage()
    });

    self.$el.find('.photo-view img, .photo-zoom img').on('click', function() {
        self.$preview_wrap.find('.btn-move .next a').trigger('click');
    });
  };

  /**
   * init dots
   *
   * @private
   */
  _search.detail.photoGallery.prototype._initIndicator = function () {

    var $dots, i, self, liArray = [];

    self = this;

    $dots = self.$thumbnail_pager.find('ul.dots');

    for (i = 1; i <= self.slide_total; i++) {
      liArray.push('<li' + (i === 1 ? ' class="cu"' : '') + '><a href="#">' + i + '</a></li>');
    }

    $dots.append(liArray.join(''));

    $dots.on('click', function (e) {

      var goto;

      if (e.target.tagName.toLocaleLowerCase() !== 'a') {
        return false;
      }

      goto = parseInt(e.target.innerText) - 1;

      if (self.currentSlide === goto) {
        return false;
      }

      self.$thumbnail_wrap.find('.thumb-view')[0].slick.slickGoTo(goto);
      self.currentSlide = goto;

      return false;
    });
  };

  /**
   * init thumbnail
   *
   * @private
   */
  _search.detail.photoGallery.prototype._initThumbnail = function () {

    var self = this;

    var $thumb_view = self.$thumbnail_wrap.find('.thumb-view');

    $thumb_view.slick({
      slide: 'ul',
      slidesToShow: 1,
      slidesToScroll: 1,
      variableWidth: true,
      prevArrow: self.$thumbnail_pager.find('.prev a'),
      nextArrow: self.$thumbnail_pager.find('.next a'),
      initialSlide: 0
    }).on('afterChange', function (event, slick, currentSlide, nextSlide) {

      self.$thumbnail_pager.find('.dots li').removeClass('cu');
      self.$thumbnail_pager.find('.dots li:nth-child(' + (slick.currentSlide + 1) + ')').addClass('cu');
      self.$thumbnail_list.closest('.thumb-list').find('li:not(.num' + self.currentThumb + ')').removeClass('active');
    });
  };

  /**
   * init zoom
   *
   * @private
   */
  _search.detail.photoGallery.prototype._initZoom = function () {

    var self = this;

    self.$floatbox.find('.photo-zoom img').each(function () {

      var $this = $(this);
      $this.prop('src', $this.data('src')).css({display: 'none', position: 'absolute', top: 0, left: 45});
      if (self.$floatbox.find('.photo-zoom img').length > 1) {
        $this.css({cursor: 'pointer'});
      }

      if (parseInt(this.id.replace('gallery_detail_', '')) !== self.currentThumb) {
        return true;
      }
      this.style.display = 'block';
    });

    if (self.$floatbox.find('.photo-zoom img').length < 2) {
        self.$floatbox.find('.btn-move').hide();
    }

  };

  /**
   * set current image
   *
   * @private
   */
  _search.detail.photoGallery.prototype._showCurrentImage = function () {

    var $currentThumb, number, heading, self, caption, $heading, $caption, $thumbImg;

    self = this;

    $currentThumb = self.$thumbnail_list.closest('.thumb-list').find('li.num' + self.currentThumb);

    // update numbers
    number = self._currentImageNum($currentThumb);
    self.currentThumb = number.thumb;
    self.currentSlide = number.slide;

    // get text
    $thumbImg = $currentThumb.find('img');
    heading = $thumbImg.data('desc');
    caption = $thumbImg.prop('alt');

    // update thumb
    self.$thumbnail_list.not($currentThumb.addClass('active')).removeClass('active');

    // update preview
    $caption = self.$preview_wrap.find('.photo-view .tx-caption').text(heading);
    $caption.siblings('.count').text(self.currentThumb + '/' + self.thumb_total);
    self.$preview.removeClass().addClass('num' + self.currentThumb);
    self.$preview.find('img').fadeOut(100, function () {
      var $this = $(this);
      $this.prop('src', self.$thumbnail_list.closest('.thumb-list').find('li.active img').attr('src'));
      $this.fadeIn(100);
      if (self.thumb_total > 1) {
        $this.css('cursor', 'pointer');
      }
    });

    // update zoom
    self.$floatbox.find('.photo-zoom img').hide();
    self.$floatbox.find('#gallery_detail_' + self.currentThumb).fadeIn(200);
    $heading = self.$floatbox.find('.tx-heading').text(heading);
    $heading.siblings('.tx-caption').text(caption);
    $heading.siblings('.count').text(self.currentThumb + '/' + self.thumb_total);

    heading === '' ? $heading.hide() : $heading.show();
  };

  /**
   * current image numbers
   *
   * @param target
   * @returns {{slide: *, thumb: *}}
   * @private
   */
  _search.detail.photoGallery.prototype._currentImageNum = function (target) {

    var $target = $(target);
    var slide = $target.closest('ul').prop('class');
    var thumb = $target.prop('class');

    return {
      slide: typeof (slide) == 'string' || slide instanceof String ? parseInt(slide.replace(/thumb-list|slick-slide|slick-active|slick-current|slick-cloned|list| /gi, '')) : 1,
      thumb: typeof (thumb) == 'string' || slide instanceof String ? parseInt(thumb.replace(/active|num| /gi, '')) : 1
    }
  };

  /**
   * お気に入り、最近見た物件 タブ Class
   *
   * @param $el
   * @returns {_search.personal.tab}
   */
  _search.personal.tab = function ($el) {

    if (!(this instanceof _search.personal.tab)) {
      return new _search.personal.tab($el);
    }

    this.$el = $el;
    this.$baseTabArea = this.$el.find('.checklist-tab');
    this.$baseTabList = this.$baseTabArea.find('li');
    this.$tab4List = this.$el.find('.element-tab-search');
    this.$articleListAll = this.$el.find('.article-object-wrapper');
    this.$checkboxAll = this.$el.find(':checkbox');
    this.$collectProcessing = this.$el.find('.collect-processing');
    this.$sortSelect = this.$el.find('.sort-select');
  };

  /**
   * run
   */
  _search.personal.tab.prototype.run = function () {

    var self = this;

    self._addListener();
    self._init();

  };

  /**
   * リスナー追加
   *
   * @private
   */
  _search.personal.tab.prototype._addListener = function () {

    var self, favoriteInstance;

    self = this;
    favoriteInstance = _search.common.favorite(self.$el);

    // add listener
    // switch base tab
    self.$baseTabArea.find('a').on('click', function (e) {

      // Activeタブクリック時何もしない
      if($(e.target).closest('li').hasClass('active')) {
        return;
      }

      // set form
      $("[name=sort]").val('asc');
      name = self._getClassName($(e.target));
      $("#hide-checklist-tab").val(name);
      $("#hide-search-tab").val("");
      $("#personalsort").submit();

      return;
    });

    // add listener
    // switch tab4
    self.$tab4List.find('a').on('click', function (e) {

      var $target, name, $targetTab4List, $oppositeTab4List, $activeTab, $articleList;

      // Activeタブクリック時何もしない
      if($(e.target).closest('li').hasClass('active')) {
        return;
      }

      // set form
      $("[name=sort]").val('asc');
      name = self._getClassName($(e.target));
      $("#hide-checklist-tab").val(self._getClassName($(".checklist-tab li.active")));
      $("#hide-search-tab").val(name);
      $("#personalsort").submit();

      return;
    });

  };

  /**
   * タブと中身の初期化
   *
   * @private
   */
  _search.personal.tab.prototype._init = function () {

    var self = this;
    // タブの中身がない場合にno-itemを表示
    // self.$baseTabList.first().find('a').trigger('click');
  };

  /**
   * class名取得
   *
   * @param $targetLink
   * @returns {*}
   * @private
   */
  _search.personal.tab.prototype._getClassName = function ($targetLink) {

    var classes, i;

    classes = $targetLink.closest('li').attr('class').split(' ');

    for (i = 0; i < classes.length; i++) {
      if (classes[i] === 'active') {
        continue;
      }
      return classes[i];
    }
  };

  _search.personal.tab.prototype.initSort = function () {

    this.$el.find('.sort-select select').trigger('change');
  };

  /**
   * お気に入り、最近見た物件 checkboxEngine Class
   *
   * @param $el
   * @returns {_search.personal.checkboxEngine}
   */
  _search.personal.checkboxEngine = function ($el) {

    if (!(this instanceof _search.personal.checkboxEngine)) {
      return new _search.personal.checkboxEngine($el);
    }

    this.$el = $el;
  };

  _search.personal.checkboxEngine.prototype.run = function () {

    var self, $checkAll;

    self = this;

    $checkAll = $('.collect-processing :checkbox');

    // add listener
    // change check-all
    $checkAll.on('change', function (e) {

      var checked = $(e.target).prop('checked');
      self.$el.find('.article-object-wrapper:visible .object-check :checkbox').prop('checked', checked);
      $checkAll.prop('checked', checked);
    });

    // add listener
    // article checkbox
    self.$el.find('.object-check :checkbox').on('change', function (e) {

      var $target, $wrapper, $all, $checked;

      $target = $(e.target);
      $wrapper = $target.closest('.article-object-wrapper');
      $all = $wrapper.find('.object-check :checkbox');
      $checked = $all.filter(function (i) {
        return $all.eq(i).prop('checked');
      });
      $checkAll.prop('checked', $all.length === $checked.length);
    });

  };

  /**
   * お気に入り、最近見た物件 sort Class
   *
   * @param $el
   * @returns {_search.personal.sort}
   */
  _search.personal.sort = function ($el) {

    if (!(this instanceof _search.personal.sort)) {
      return new _search.personal.sort($el);
    }

    this.$el = $el;
  };

  /**
   * sort
   *
   */
  _search.personal.sort.prototype.run = function () {

    this._addListener();
    this._init();
  };

  _search.personal.sort.prototype._addListener = function () {

    var self = this;
    var $wrapper = self.$el.find('.sort-select');

    $wrapper.find('select').on('change', function (e) {

      var $articles, direct, cookie, bukkenNoList, $area;

      // 物件
      $articles = self.$el.find('.article-object:visible');

      if ($articles.length < 1) {
        return;
      }

      // 向き
      direct = $(e.target).find(':selected').attr('class').toLowerCase();

      var cursort = $("#personalsort").find('select').attr('cursort');
      var sort = $('[name=sort]  option:selected').val();
      if(cursort == sort) {
        return;
      }

      var searchtab = $('.element-tab-search:visible').find('.active').eq(0);
      $(searchtab).removeClass('active');
      $("#hide-search-tab").val($(searchtab).attr('class'));

      var chklisttab = $('.checklist-tab').find('.active').eq(0);
      $(chklisttab).removeClass('active');
      $("#hide-checklist-tab").val($(chklisttab).attr('class'));

      $("#personalsort").submit();

      return;
    });
  };

  _search.personal.sort.prototype._init = function () {

    // Load時に並び替え
    // this.$el.find('.sort-select select').trigger('change');
  };


  /**
   * 地図検索：地図検索 class
   *
   * @param $el
   * @returns {_search.list.list}
   */
  _search.map.map = function ($el) {

    if (!(this instanceof _search.map.map)) {
      return new _search.map.map($el);
    }
    this.$el = $el;
  };
  /**
   * run
   */
  _search.map.map.prototype.run = function () {

    var $article, $pager;

    $article = this.$el.find('.articlelist-inner');

    if ($article.length > 0) {

      // checkbox engine
      $article.athome_checkbox_engine({
        parent_selector: '.check-all',
        wrapper_selector: '.article-object-wrapper',
        target_selector: '.object-header input'
      });
    }
  };

})();

$(function () {

  'use strict';

  var $el, $mainImage;

  $el = $('body');
  $mainImage = $el.find('.single-item');

  if ($el.find('.twitter-share-button').length > 0 || $el.find('.twitter-timeline').length > 0) {
    app.twitter(document, 'script', 'twitter-wjs');
  }

  if ($el.find('.fb-like').length > 0 || $el.find('.fb-like-box').length > 0) {
    app.facebook(document, 'script', 'facebook-jssdk');
  }

  if ($el.find('.element-map-canvas, .parts_map_canvas, .map-article, .map-facility, .chart-area').length > 0) {
    
    var script_url = '//maps.googleapis.com/maps/api/js?sensor=false&v=quarterly&';
    var api_key;
    var api_channel="";
      $('.element-map-canvas, .parts_map_canvas, .map-article, .map-facility, .chart-area').each(function () {
          var data = $(this).data();
          if (data.apiKey != 'undefined') {
              api_key = data.apiKey;
          }
          if (data.apiChannel != 'undefined') {
              api_channel = "&"+data.apiChannel;
          }
      });

      $.getScript(script_url + api_key + api_channel, function () {

      var device = 'pc';
      if (app.isPreview) {
        (function () {
          var dirs, i, id = '', parent_id = '';

          dirs = location.href.split('/');

          for (i = 0; i < dirs.length; i++) {

            if (dirs[i] === 'id') {
              id = dirs[i + 1];
              continue;
            }

            if (dirs[i] === 'parent_id') {
              parent_id = dirs[i + 1];
            }
          }
          $.getScript('/source/src/id/' + id + '/parent_id/' + parent_id + '/device/' + device + '/path/js%252Fgmaps.js');
        })();
        return;
      }
      $.getScript('/' + device + '/js/gmaps.js');
      if ($('.map-facility, .chart-area').length > 0) {
        fdpmap.run(window.app, $el);
      }
    });
  }

  // top page main image
  if ($mainImage.length > 0) {
    $mainImage.slick(app.configSlick);
    $el.find('.slick-dots').removeClass('slick-dots').addClass(app.navSlickClass);
    if (app.classSpeedBar != '') {
        $el.find('.' + app.navSlickClass + ' li').addClass(app.classSpeedBar);
    }
    var $maninImageThumb;
    $maninImageThumb = $el.find('.slider-thumb-nav');
    $maninImageThumb.slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        asNavFor: '.slider',
        dots: true,
        focusOnSelect: true
    });
    // Remove active class from all thumbnail slides
    $maninImageThumb.find('.slick-slide').css('opacity','0.5');

    // Set active class to first thumbnail slides
    $maninImageThumb.find('.slick-slide').eq(0).css('opacity','1');

    // On before slide change match active thumbnail to current slide
    $mainImage.on('beforeChange', function (event, slick, currentSlide, nextSlide) {
        var mySlideNumber = nextSlide;
        $maninImageThumb.find('.slick-slide').css('opacity','0.5');
        $maninImageThumb.find('.slick-slide').eq(mySlideNumber).css('opacity','1');
    });
  }

  // logo
  $el.find('.company-img img').load(function () {

    var $logo = $el.find('.logo');
    if ($logo.find('a').width() >= 580) {
      $logo.addClass('fs-small');
    }
  });

  // footer nav tile
  $el.find('.guide-nav .inner div').tile(5);

  // faq
  $el.find('.element-qa dt').click(function () {

    var $this = $(this);

    $this.next().slideToggle('fast');
    $this.toggleClass('q-open');
    return false;
  });

  // remove device change btn
  if (app.ua.pc) {
    $el.find('.device-change').remove();
  }

  // koma(top)
  app.komaInit($el);

  // smooth scroll
  // pc⇔sp
  app.addCommonEventListener($el);

  // only search and special pages
  if (typeof search_config !== 'undefined') {
    app.search({$el: $el}).run();

  }

    // search freeword top page
  $(window).load(function() {
    if (!app.isPreview) {
        var $searchTextMainparts, $searchSelectMainparts, $searchBtnMainparts, shumokuMainparts, hrefsMainparts, inputNameMainparts,countNameMainparts;
        inputNameMainparts = '#freeword-mainparts-suggested';
        countNameMainparts = 'fulltext_count_mainparts';
        $searchTextMainparts = $el.find(inputNameMainparts);
        $searchSelectMainparts = $el.find('.mainparts-search-type');
        $searchBtnMainparts = $el.find('.mainparts-btn-search-top');
        shumokuMainparts = $searchSelectMainparts.val();
        hrefsMainparts = app.location.parseUrl(app.location.currentUrl);
        if($searchBtnMainparts.length > 0) {
            $searchBtnMainparts.on('click', function () {
				if(shumokuMainparts == 'all') {
					return false;
				}
                app.request.postForm(
                    [hrefsMainparts['protocol'], '', hrefsMainparts['hostname'], shumokuMainparts, 'result/'].join('/'), {
                        detail: $searchTextMainparts.serialize(),
                });
            });
            var setMainpartsEvent = function () {
                $searchTextMainparts.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
                var data = {
                    s_type: 12,
                    condition_side: $searchTextMainparts.serialize(),
                    type_freeword: shumokuMainparts,
                    shumoku: shumokuMainparts,
                }
                app.request.suggest(data,inputNameMainparts);

                var countData = data;
                if(shumokuMainparts == 'all') {
                    var allShumoku = [];
                    $searchSelectMainparts.find('option').each(function() {
                        if($(this).val() == 'all') return true;
                        allShumoku.push($(this).val());
                    });
                    countData.type_freeword = allShumoku;
                }
                app.request.counter(countData,inputNameMainparts,countNameMainparts);
            }
          var getPlaceholder = function (type) {
            switch (type) {
              case 'chintai':
                return '例：12.2万円以下 和室';

              case 'kasi-tenpo':
              case 'kasi-office':
              case 'parking':
              case 'kasi-tochi':
              case 'kasi-other':
              case 'chintai-jigyo-1':
              case 'chintai-jigyo-2':
              case 'chintai-jigyo-3':
                return '例：12.2万円以下 駐車場あり';

              case 'mansion':
              case 'kodate':
              case 'uri-tochi':
              case 'baibai-kyoju-1':
              case 'baibai-kyoju-2':
              case 'baibai-kyoju-3':
                return '例：2000万円以下 南向き';

              case 'uri-tenpo':
              case 'uri-office':
              case 'uri-other':
              case 'baibai-jigyo-1':
              case 'baibai-jigyo-2':
                return '例：2000万円以下 駐車場あり';

              default :
                return '種別を選択してください';
            }
          }
            $searchSelectMainparts.on('change', function () {
                $searchSelectMainparts.val($(this).val());
                shumokuMainparts = $(this).val();
                setMainpartsEvent();
                var searchTextMainpartsPlaceholder = getPlaceholder(shumokuMainparts);
                $searchTextMainparts.attr('placeholder',searchTextMainpartsPlaceholder);
                $searchTextMainparts.data('plugin_fulltextCount').getCount();
            });
            setMainpartsEvent();
            $searchTextMainparts.data('plugin_fulltextCount').getCount();
            $(document).on('keyup', $searchTextMainparts.selector, function(e){
                if(e.keyCode != 40 && e.keyCode != 38){
                    $searchTextMainparts.val($(this).val());
                }
            });
            $(document).on('change', $searchTextMainparts.selector, function(e){
                $searchTextMainparts.val($(this).val());
            });
            $el.find('.form-search-freeword').on('submit', function (e) {
                e.preventDefault();
                $(this).find('.mainparts-btn-search-top').trigger("click");
            });
        }
      
        var $searchText, $searchSelect, $searchBtn, shumoku, hrefs, inputNameSideparts,countNameSideparts;
        inputNameSideparts = '#freeword-sideparts-suggested';
        countNameSideparts = 'fulltext_count_sideparts';
        $searchText = $el.find(inputNameSideparts);
        $searchSelect = $el.find('.sideparts-search-type');
        $searchBtn = $el.find('.sideparts-btn-search-top');
        shumoku = $searchSelect.val();
        hrefs = app.location.parseUrl(app.location.currentUrl);

        if($searchBtn.length > 0) {

            if($searchBtn.length == 2) {
                $el.find('.sideparts-btn-search-top').eq(1).closest('form').find('datalist').attr('id', 'suggesteds_side_2');
                $el.find('.sideparts-btn-search-top').eq(1).closest('form').find(inputNameSideparts).attr('list', 'suggesteds_side_2');
            }

            $searchBtn.on('click', function () {
                var shumokuEach = $(this).closest('form').find('.sideparts-search-type').val(); 
                if(shumokuEach == 'all') {
					return false;
				}
                app.request.postForm(
                    [hrefs['protocol'], '', hrefs['hostname'], shumokuEach, 'result/'].join('/'), {
                        detail: $(this).closest('form').find(inputNameSideparts).serialize(),
                });
            });
            var setEvent = function (fObj) {

                var searchTextEach = fObj.find(inputNameSideparts).eq(0);
                var shumokuEach = fObj.find('.sideparts-search-type').eq(0).val();

                searchTextEach.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);

                var data = {
                    s_type: 12,
                    condition_side: searchTextEach.serialize(),
                    type_freeword: shumokuEach,
                    shumoku: shumokuEach,
                }
                app.request.suggest(data,inputNameSideparts,fObj);

                var countData = data;
                if(shumokuEach == 'all') {
                    var allShumoku = [];
                    fObj.find('.sideparts-search-type').eq(0).find('option').each(function() {
                        if($(this).val() == 'all') return true;
                        allShumoku.push($(this).val());
                    });
                    countData.type_freeword = allShumoku;
                }
                app.request.counter(countData,inputNameSideparts,countNameSideparts,fObj);
            }
            var getPlaceholder = function (type) {
              switch (type) {
                case 'chintai':
                  return '例：12.2万円以下 和室';

                case 'kasi-tenpo':
                case 'kasi-office':
                case 'parking':
                case 'kasi-tochi':
                case 'kasi-other':
                case 'chintai-jigyo-1':
                case 'chintai-jigyo-2':
                case 'chintai-jigyo-3':
                  return '例：12.2万円以下 駐車場あり';

                case 'mansion':
                case 'kodate':
                case 'uri-tochi':
                case 'baibai-kyoju-1':
                case 'baibai-kyoju-2':
                case 'baibai-kyoju-3':
                  return '例：2000万円以下 南向き';

                case 'uri-tenpo':
                case 'uri-office':
                case 'uri-other':
                case 'baibai-jigyo-1':
                case 'baibai-jigyo-2':
                  return '例：2000万円以下 駐車場あり';

                default :
                  return '種別を選択してください';
              }
            }
            $searchSelect.on('change', function () {
                $(this).closest('form').find('.sideparts-search-type').val($(this).val());
                shumoku = $(this).val();
                setEvent($(this).closest('form'));
                var searchTextPlaceholder = getPlaceholder(shumoku);
                $(this).closest('form').find(inputNameSideparts).attr('placeholder',searchTextPlaceholder);
                $(this).closest('form').find(inputNameSideparts).data('plugin_fulltextCount').getCount();
            });

            $el.find('.sideparts-search-type').each(function() {
                setEvent($(this).closest('form'));
            });

            $searchBtn.each(function() {
                $(this).closest('form').find(inputNameSideparts).data('plugin_fulltextCount').getCount();
            });

            $(document).on('keyup', $searchText.selector, function(e){
                if(e.keyCode != 40 && e.keyCode != 38){
                    $(this).closest('form').find(inputNameSideparts).val($(this).val());
                }
            });
            $(document).on('change', $searchText.selector, function(e){
                $(this).closest('form').find(inputNameSideparts).val($(this).val());
            });
            $el.find('.form-search-freeword').on('submit', function (e) {
                e.preventDefault();
                $(this).find('.sideparts-btn-search-top').trigger("click");
            });
        }
    } else {
        // Previewでは placeholderのみサポート
        var $searchSelectMainparts = $el.find('.mainparts-search-type');
        var $searchSelectSideparts = $el.find('.sideparts-search-type');
        var getPlaceholder = function (type) {
          switch (type) {
            case 'chintai':
              return '例：12.2万円以下 和室';

            case 'kasi-tenpo':
            case 'kasi-office':
            case 'parking':
            case 'kasi-tochi':
            case 'kasi-other':
            case 'chintai-jigyo-1':
            case 'chintai-jigyo-2':
            case 'chintai-jigyo-3':
              return '例：12.2万円以下 駐車場あり';

            case 'mansion':
            case 'kodate':
            case 'uri-tochi':
            case 'baibai-kyoju-1':
            case 'baibai-kyoju-2':
            case 'baibai-kyoju-3':
              return '例：2000万円以下 南向き';

            case 'uri-tenpo':
            case 'uri-office':
            case 'uri-other':
            case 'baibai-jigyo-1':
            case 'baibai-jigyo-2':
              return '例：2000万円以下 駐車場あり';

            default :
              return '種別を選択してください';
          }
        }
        $searchSelectMainparts.on('change', function() {
          var searchTextMainpartsPlaceholder = getPlaceholder($(this).val());
          $("#freeword-mainparts-suggested").attr('placeholder', searchTextMainpartsPlaceholder);
        });
        $searchSelectSideparts.on('change', function() {
          var searchTextSidepartsPlaceholder = getPlaceholder($(this).val());
          $("#freeword-sideparts-suggested").attr('placeholder', searchTextSidepartsPlaceholder);
        });
    }
  });
  
    //物件詳細の備考の「続きをみる」
    $(".readmore").each(function() {
      var $ele = $(this);
      $ele.prepend(
        '<div class="open"><a href="#" style="color:#0000EE;float:right;">...続きを読む</a></div>'
      );
      $ele.append(
        '<div class="close"><a href="#" style="color:#0000EE;float:right;">...閉じる</a></div>'
      );
      $ele
        .find(".open")
        .nextAll()
        .hide();
      $ele.find(".open").click(function() {
        $ele.find(".open").hide();
        $("<span>").attr({id: "biko-more"}).html($ele.find(".open").next().html()).appendTo($ele.find(".open").parent().prev());
        $ele
          .find(".open")
          .nextAll(".close")
          .show();
        return false;
      });
      $ele.find(".close").click(function() {
        $ele.find(".open").show();
        $("#biko-more").remove();
        $ele
          .find(".open")
          .nextAll(".close")
          .hide();
        return false;
      });
    });

    $('.btn-contact-fdp img').each(function() {
        var $img = $(this);
        var imageSrc = $img.attr('src');
        jQuery.get(imageSrc, function(data) {
            // Get the SVG tag, ignore the rest
            var $svg = jQuery(data).find('svg');
            // Add replaced image's classes to the new SVG
            $svg = $svg.attr('class', 'show');
            // any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');
            // Replace image with new SVG
            $img.replaceWith($svg);
        }, 'xml');
    });
    $(".small-category span a[href='#']").click(function() {
        if ($(this).parent().parent().parent().hasClass("hidden-small")) {
            $(this).text('非表示');
        } else {
            $(this).text('表示');
        }
      $(this).parent().parent().parent().toggleClass("hidden-small");
      $(".small-category ul").toggle("slow");
      return false;
    });

    var smallCategory = $('.side-article .li-article').children('a');
    $(smallCategory).click(function(e) {
        e.preventDefault();
        var arrow = $(this).find('.arrow');
        var hasTop = arrow.hasClass('top');
        if (hasTop) {
            arrow.removeClass('top');
            arrow.addClass('bottom');
            arrow.closest('a').next().slideToggle('fast');
        } else {
            arrow.removeClass('bottom');
            arrow.addClass('top');
            arrow.closest('a').next().slideToggle('fast');
        }
	});

    /**
      * ラベル削除
      */
    $('.h-mark').on('click', function () {
      $(this).hide();
    });
});
