$(function () {

  'use strict';

  var $el, $topImageArea, _setHeight, $gnav, $gnav2;

  $el = $('body');

  if ($el.find('.twitter-share-button').length > 0 || $el.find('.twitter-timeline').length > 0) {
    app.twitter(document, 'script', 'twitter-wjs');
  }

  if ($el.find('.fb-like').length > 0 || $el.find('.fb-like-box').length > 0) {
    app.facebook(document, 'script', 'facebook-jssdk');
  }

  if ($el.find('.element-map-canvas, .parts_map_canvas, .map-article, #map-canvas, .chart-area').length > 0) {

    var script_url = '//maps.googleapis.com/maps/api/js?sensor=false&v=quarterly&';
    var api_key;
      var api_channel="";
    $('.element-map-canvas, .parts_map_canvas, .map-article, #map-canvas, .chart-area').each(function () {
      var data = $(this).data();
      if (data.apiKey != 'undefined') {
        api_key = data.apiKey;
      }
      if (data.apiChannel != 'undefined') {
        api_channel = "&"+data.apiChannel;
      }
    });

      $.getScript(script_url + api_key + api_channel, function () {

      var device = 'sp';
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
      if ($('.chart-area').length > 0) {
        fdptown.run(window.app, $el);
      }
    });
  }

  // main image
  $topImageArea = $el.find('.single-item');

  if ($topImageArea.length > 0) {

    /**
     * set height property
     *
     * @private
     */
    _setHeight = function () {
      var rate = app.slider.height/app.slider.width;
      var height = $(window).width() * 0.45;
      if (app.natural2) {
        height = ($(window).width() - 30) * rate;
      }
      $topImageArea.css('height', height).find('.slick-slide').css('height', height);

      height = ($(window).width()/5) * rate;
      if (app.natural2) {
        height = (($(window).width() - 30)/5) * rate;
      }
      $('.slider-thumb-nav').css('height', height).find('.img-slide').css('height', height);
    };

    var $maninImageThumb;
    $maninImageThumb = $el.find('.slider-thumb-nav');
    $topImageArea.slick(app.configSlick);
    $el.find('.slick-dots').removeClass('slick-dots').addClass(app.navSlickClass);
    if (app.classSpeedBar != '') {
        $el.find('.' + app.navSlickClass + ' li').addClass(app.classSpeedBar);
    }
    $('.slider-thumb-nav').slick({
      slidesToShow: 5,
      slidesToScroll: 1,
      asNavFor: '.slider',
      dots: true,
      focusOnSelect: true
      });
    // Remove active class from all thumbnail slides
    $maninImageThumb.find('.slick-slide').css('opacity','0.5');

    // Set active class to first thumbnail slides
    $maninImageThumb.find('.slick-slide').css('opacity','1');

    // On before slide change match active thumbnail to current slide
    $topImageArea.on('beforeChange', function (event, slick, currentSlide, nextSlide) {
        var mySlideNumber = nextSlide;
        $maninImageThumb.find('.slick-slide').css('opacity','0.5');
        $maninImageThumb.find('.slick-slide').eq(mySlideNumber).css('opacity','1');
    });

    _setHeight();

    $(window).on('resize', _setHeight);
  }

  // gnav
  $gnav = $el.find('.gnav');

  // init
  $gnav.css({display: 'none', position: 'absolute'});

  // toggle
  $el.find('.header-menu, .gnav-close').on('click', function () {
    $gnav.slideToggle('fast');
  });

  // faq
  $el.find('.element-qa dt').on('click', function () {
    $(this).toggleClass('q-open').next().slideToggle('fast');
    return false;
  });

  // smooth scroll
  $el.find('a[href^=#]').click(function () {

    var href, target;
    if (this.href.match(/#[\w+]/gi)) {
      href = $(this).attr('href');
      target = $(href == '#' || href == '#top' || href == '' ? 'html' : href);
      $('body, html').animate({scrollTop: target.offset().top}, 400, 'swing');
      return false;
    }
  });

  // telTap clicked
  $el.find('.company-tel a').click(function () {
      // post conversion teltap
      app.request.postConversion('teltap');
  });


    // guide-nav
  $gnav2 = $el.find('.gnav2');
  if ($gnav2.find('li').length % 2 !== 0) {
    $gnav2.append($('<li>'));
  }

  // pc <=> sp
  $el.find('.device-change a').on('click', function (e) {

    $.cookie('device', {device: $el.find('.device-change a').data('device')}, app.cookie.COOKIE_CONFIG);
    app.location.customReload();
    return false;
  });

  // koma(top)
  app.komaInit($el);

  // only search and special pages
  if (typeof search_config !== 'undefined') {
    app.search({
      $el: $el,
      search_config: search_config
    }).run();

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
            console.log($searchTextMainparts.serialize())
        if(shumokuMainparts == 'all') {
          return false;
        }
              app.request.postForm(
                  [hrefsMainparts['protocol'], '', hrefsMainparts['hostname'], shumokuMainparts, 'result/'].join('/'), {
                      fulltext: $searchTextMainparts.serialize(),
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
          $(document).on('keyup change', $searchTextMainparts.selector, function(e){
              $searchTextMainparts.val($(this).val());
              if (e.keyCode == 13) {
                  $searchBtnMainparts.trigger("click");
              }
              $searchTextMainparts.focusout(function() {
                  $(this).parent().find('.suggesteds').css({'display' : 'none'});
              });
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
              $el.find('.sideparts-btn-search-top').eq(1).closest('section').find('datalist').attr('id', 'suggesteds_side_2');
              $el.find('.sideparts-btn-search-top').eq(1).closest('section').find(inputNameSideparts).attr('list', 'suggesteds_side_2');
          }
          $searchBtn.on('click', function () {
              var shumokuEach = $(this).closest('section').find('.sideparts-search-type').val();
        if(shumokuEach == 'all') {
          return false;
        }
              app.request.postForm(
                  [hrefs['protocol'], '', hrefs['hostname'], shumokuEach, 'result/'].join('/'), {
                      fulltext: $(this).closest('section').find(inputNameSideparts).serialize(),
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
              $(this).closest('section').find('.sideparts-search-type').val($(this).val());
              shumoku = $(this).val();
              setEvent($(this).closest('section'));
              var searchTextPlaceholder = getPlaceholder(shumoku);
              $(this).closest('section').find(inputNameSideparts).attr('placeholder',searchTextPlaceholder);
              $(this).closest('section').find(inputNameSideparts).data('plugin_fulltextCount').getCount();
          });

          $el.find('.sideparts-search-type').each(function() {
              setEvent($(this).closest('section'));
          });
          $searchBtn.each(function() {
              $(this).closest('section').find(inputNameSideparts).data('plugin_fulltextCount').getCount();
          });

          $searchText.data('plugin_fulltextCount').getCount();
          $(document).on('keyup change', $searchText.selector, function(e){
              (this).closest('section').find(inputNameSideparts).val($(this).val());
              if (e.keyCode == 13) {
                  $(this).closest('section').find('.sideparts-btn-search-top').trigger("click");
              }
              $searchText.focusout(function() {
                  $(this).parent().find('.suggesteds').css({'display' : 'none'});
              });
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

});

(function () {

  'use strict';

  // global application
  var app = window.app = {};

  /**
   * Twitter init
   *
   * @param d
   * @param s
   * @param id
   */
  app.twitter = function (d, s, id) {

    var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
    if (!d.getElementById(id)) {
      js = d.createElement(s);
      js.id = id;
      js.async = true;
      js.src = p + '://platform.twitter.com/widgets.js';
      fjs.parentNode.insertBefore(js, fjs);
    }
  };

  /**
   * Facebook init
   *
   * @param d
   * @param s
   * @param id
   */
  app.facebook = function (d, s, id) {

    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
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
    var $timeoutWindow = null;

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
        
        var $pKoma;
        $pKoma = $koma.parent();

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
        // $newKoma.find('img').lazyload({effect: 'fadeIn'});

        // add listener
        // post special path
        $newKoma.find('a').on('click', function (e) {
          var href = $(e.target).closest('a').attr('href');
          app.request.postForm(href, {'special-path': $newKoma.data('special-path')}, true);
          return false;
        });
        //タイミングによって設定されないため遅延させる
        //window.clearTimeout($timeoutWindow);
        window.setTimeout(function($pKoma) {
            $pKoma.find('.element-recommend-caro').each( function () {
                if($(this).find('.recommend-caro-item').length > 0){
                    $(this).slick({
                        dots:           true,
                        arrows:         true,
                        infinite:       false,
                        speed:          500,
                        slidesToShow:   2,
                        slidesToScroll: 2,
                        autoplay:       false,
                        autoplaySpeed:  2000
                        // lazyLoad:       true
                      });
                }
            });
        }, 800, $pKoma);

      }).fail(function (res) {
        app.customConsoleLog('----- ajax failed -----');
        app.customConsoleLog(res);
        app.customConsoleLog('----- ajax failed end -----');

        _deleteKomaArea($koma);
      });
    });
  };


  /**
   * user argent
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
   * utility method class
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
   * check object
   *
   * @param o
   * @returns {boolean}
   */
  app.utility.prototype.isObject = function (o) {

    return (o instanceof Object && !(o instanceof Array));
  };

  // get instance
  app.utility = new app.utility();

  /**
   * request clss
   *
   * @returns {app.request}
   */
  app.request = function () {

    if (!(this instanceof app.request)) {
      return new app.request();
    }
  };

  /**
   *
   * post
   *
   * @param url
   * @param data
   * @param openWindow
   */
  app.request.prototype.postForm = function (url, data, openWindow) {

    var time, $form, params;

    /* iOSのアプリ内ブラウザは、マルチタブ前提で一覧(元タブ)→詳細画面(新タブ)遷移すると、新タブにpostできない不具合がでるので、シングルタブとして特別な扱いにする。
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
            wait: 100,
            enableIME: true,
            bukkenParams: data,
            success: function(res, query) {

                var fObj = $(criteria).closest('section');

                // SP版の『さらに条件を指定する』画面では、テキストエリアとカウンターが同一のsection内に存在しないため例外処理
                if(fObj.length == 0) {
                    fObj = $('.fixed-pagefooter');
                }
                var total = res.count.toString();
                var counterIcon = fObj.find('.' + countName + ':first i');
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
                    })
                })
            },
            reset: function(resetChar) {

                var fObj = $(criteria).closest('section');

                fObj.find('.' + countName).each( function () {
                    $(this).find('i').each( function (i,e) {
                        $(this).html(resetChar);
                    })
                });
            }

        });
    }
  }
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
            complete:function(data,query){
                if (detecedDevice()) {
                    if (!$(this).is(":focus")) {
                        $(this).parent().find('.suggesteds').css({'display' : 'none'});
                    }
                }
              }
        });
    }
  }


    /**
     * postConversion
     *
     * @param type : conversion type
     */
    app.request.prototype.postConversion = function (type) {

        var apiUrlBase = '/api-conversion/';
        var apiUrl = apiUrlBase + type;
        var data = {
            'page_url':window.location.href
        }

        $.ajax({
            type: 'POST',
            url: apiUrl,
            data: data,
            timeout: 120 * 1000,
            dataType: 'json'

        }).done(function (res) {

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
  app.request = new app.request();

  /**
   * path class
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
   * get shumoku list
   *
   * @returns {*[]}
   */
  app.path.prototype.shumokuAll = function () {
    return [this.CHINTAI, this.KASI_TENPO, this.KASI_OFFICE, this.PARKING, this.KASI_TOCHI, this.KASI_OTHER, this.MANSION, this.KODATE, this.URI_TOCHI, this.URI_TENPO, this.URI_OFFICE, this.URI_OTHER, this.CHINTAI_JIGYO_1, this.CHINTAI_JIGYO_2, this.CHINTAI_JIGYO_3, this.BAIBAI_KYOJU_1, this.BAIBAI_KYOJU_2, this.BAIBAI_JIGYO_1, this.BAIBAI_JIGYO_2
    ];
  };

  /**
   * get prefecture list
   *
   * @returns {*[]}
   */
  app.path.prototype.prefectureAll = function () {

    return [this.HOKKAIDO, this.AOMORI, this.IWATE, this.MIYAGI, this.AKITA, this.YAMAGATA, this.FUKUSHIMA, this.IBARAKI, this.TOCHIGI, this.GUNMA, this.SAITAMA, this.CHIBA, this.TOKYO, this.KANAGAWA, this.NIIGATA, this.TOYAMA, this.ISHIKAWA, this.FUKUI, this.YAMANASHI, this.NAGANO, this.GIFU, this.SHIZUOKA, this.AICHI, this.MIE, this.SHIGA, this.KYOTO, this.OSAKA, this.HYOGO, this.NARA, this.WAKAYAMA, this.TOTTORI, this.SHIMANE, this.OKAYAMA, this.HIROSHIMA, this.YAMAGUCHI, this.TOKUSHIMA, this.KAGAWA, this.EHIME, this.KOCHI, this.FUKUOKA, this.SAGA, this.NAGASAKI, this.KUMAMOTO, this.OITA, this.MIYAZAKI, this.KAGOSHIMA, this.OKINAWA
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

  app.location.prototype.removeParams = function (url) {

    var pathInfo = app.location.parseUrl(url);

    return pathInfo['protocol'] + '//' + pathInfo['host'] + pathInfo['pathname']
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
      hostname: parser.hostname,
      port: parser.port,
      pathname: parser.pathname,
      search: parser.search,
      searchObject: searchObject,
      hash: parser.hash,
      dirs: parser.dirs
    };
  };

  /**
   * get bukken id (Detail page only)
   *
   * @returns {*}
   */
  app.location.prototype.bukkenId = function () {

    return this.parseUrl(this.currentUrl).dirs[1].replace(/detail-/g, '');
  };

  /**
   * get current shumoku
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
   * get current prefecture
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

  app.location.prototype.setHttpsProtocol = function (url) {

    var parsed = app.location.parseUrl(url);

    if (parsed.protocol === 'https:') {
      return url;
    }

    return 'https://' + parsed.host + parsed.pathname;
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
   * init
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
   * list keys
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
   * update history
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

    while (timestamps.length > this.MAX_FAVORITE) {
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
   * update favorite
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
   * update favorite setting
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
   * update search condition
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
   * search
   *
   * @param config
   * @returns {app.search}
   */
  app.search = function (config) {

    if (!(this instanceof app.search)) {
      return new app.search(config);
    }

    this.config = config;
    this.search_config = config.search_config;
  };

  /**
   * run
   */
  app.search.prototype.run = function () {

    var self = this;

    // only first access
    if (typeof _search.pageType === 'function') {

      // get instance
      _search.pageType = _search.pageType(self.search_config);
    }

    // common
    _search.common.fixedFooter(self.config).run();
    _search.common.tooltip(self.config).run();

    // shumoku
    if (_search.pageType.isShumoku()) {
      _search.shumoku.tab(self.config).run();
    }

    // select prefecture
    else if (_search.pageType.isSelectPrefecture()) {
      _search.prefecture.tab(self.config).run();
      _search.prefecture.initToggle(self.config).run();
    }

    // select city
    else if (_search.pageType.isSelectCity()) {
      _search.area.checkbox(self.config).run();
      _search.area.searchBtn(self.config).run();
    }

    // select railway
    else if (_search.pageType.isSelectRailway()) {
      _search.railway.checkbox(self.config).run();
      _search.railway.searchBtn(self.config).run();
    }

    // select station
    else if (_search.pageType.isSelectStation()) {
      _search.station.checkbox(self.config).run();
      _search.station.initToggle(self.config).run();
      _search.station.searchBtn(self.config).run();
    }

    // select choson
    else if (_search.pageType.isSelectChoson()) {
      _search.choson.init(self.config).run();
    }

    // condition
    else if (_search.pageType.isSelectCondition()) {
      _search.condition.initToggle(self.config).run();
      _search.condition.searchBtn(self.config).run();
    }

    // list
    else if (_search.pageType.isList()) {
      _search.list.article(self.config).run();
      _search.list.contact(self.config).run();
      _search.list.changeCondition(self.config).run();
      _search.list.highlight(self.config).run();
    }

    // select map city
    else if (_search.pageType.isSelectMapCity()) {

      _search.map.selectMapCity = selectMapCity;
      _search.map.selectMapCity.run(app, _search, self.config);

    }

    // map
    else if (_search.pageType.isResultMap()) {

      _search.list.contact(self.config).run();
      _search.list.changeCondition(self.config).run();

      //_search.common.history(self.$el).run();
      //_search.common.favorite(self.config).run();
      //_search.common.article(self.config).run();
      //_search.list.sortTable(self.config).run();
      //_search.list.aside(self.$el).run();
      //_search.list.list(self.$el).run();
      //_search.list.modal(self.config).run();
      //_search.list.search(self.$el).run();

      _search.map.searchmap = searchmap;
      _search.map.searchmap.run(app, _search, self.config);


      //_search.map.map(self.$el).run();
    }




      // detail top
    else if (_search.pageType.page_name === 'detail') {
      app.cookie.updateHistory();
      _search.common.favorite(self.config).run();
      _search.detail.modal(self.config).run();
      _search.detail.contact(self.config).run();
      _search.detail.howtoinfo(self.config).run();
      _search.detail.accordion(self.config).run();
      if (self.config.$el.find('.chart-area').length == 0 && self.config.$el.find('.article-town').length > 0) {
        fdptown.run(app, self.config.$el);
      }
    }

    // detail map
    else if (_search.pageType.page_name === 'detail_map') {
      app.cookie.updateHistory();
      if (self.config.$el.find('#map-canvas-fdp').length > 0){
        _search.map.fdpmap = fdpmap;
        _search.map.fdpmap.run(app, _search, self.config.$el);
        }
    }

    // favorite
    // history
    else if (_search.pageType.page_name === 'favorite' || _search.pageType.page_name === 'history') {
      _search.list.article(self.config).run();
      _search.personal.tab(self.config).run();
      _search.personal.bottomBtn(self.config).run();
      _search.personal.sort(self.config).run();
    }
    else if (_search.pageType.isEstateContact()) {
      self.config.$el.find('.article-object>.object-body').on('click', function () {
        var $checkbox = $(this).prevAll().find(':checkbox');
        $checkbox.prop('checked', !$checkbox.prop('checked'));
      });
    }

    $('.breadcrumb').find('li').each(function() {
        $(this).replaceWith(function() {
            return $('<li>').append($(this).contents());
        })
    });
  };

  // search classes
  var _search = {
    pageType: function () {
    },
    common: {},
    shumoku: {},
    prefecture: {},
    area: {},
    choson: {},
    railway: {},
    station: {},
    condition: {},
    list: {},
    map: {},
    detail: {},
    personal: {}
  };

  /**
   * search page info class
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
  };

  _search.pageType.prototype.isShumoku = function () {

    switch (this.page_name) {
      case 'shumoku':
        return true;
    }
    return false;
  };

  _search.pageType.prototype.isSelectPrefecture = function () {

    switch (this.page_name) {
      case 'select_prefecture':
      case 'sp_select_prefecture':
        return true;
    }
    return false;
  };

  _search.pageType.prototype.isSelectCity = function () {

    switch (this.page_name) {
      case 'select_city':
      case 'sp_select_city':
        return true;
    }
    return false;
  };

  _search.pageType.prototype.isSelectRailway = function () {

    switch (this.page_name) {
      case 'select_railway':
      case 'sp_select_railway':
        return true;
    }
    return false;
  };

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


  _search.pageType.prototype.isSelectChoson = function () {

    switch (this.page_name) {
      case 'select_choson':
      case 'sp_select_choson':
      case 'select_choson_from_multi_city':
      case 'sp_select_choson_from_multi_city':
        return true;
    }
    return false;
  };

  _search.pageType.prototype.isSelectCondition = function () {

    switch (this.page_name) {
      case 'select_condition':
      case 'sp_select_condition':
        return true;
    }
    return false;
  };

  /**
   * is list page
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
      case 'result_condition':
      case 'result_change_condition':
      case 'result_choson':
      case 'result_choson_form':
      case 'sp_result_railway':
      case 'sp_result_area':
      case 'sp_result_mcity':
      case 'sp_result_station':
      case 'sp_result_prefecture':
      case 'sp_result_area_form':
      case 'sp_result_train_form':
      case 'sp_result_condition':
      case 'sp_result_change_condition':
      case 'sp_result_direct_result':
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

  _search.pageType.prototype.isSelectMapCity = function () {

    switch (this.page_name) {
      case 'select_map_city':
      case 'sp_select_map_city':
        return true;
    }
    return false;
  };

  /**
   * is detail page
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isDetail = function () {

    switch (this.page_name) {
      case 'detail':
      case 'detail_map':
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
   * is special category
   *
   * @returns {boolean}
   */
  _search.pageType.prototype.isSpecialCategory = function () {

    return this.page_name.indexOf('sp_') !== -1;
  };

  _search.pageType.prototype.isEstateContact = function () {

    switch (this.page_name) {
      case 'kasi_jigyou_complete':
      case 'kasi_jigyou_confirm':
      case 'kasi_jigyou_edit':
      case 'kasi_kyojuu_complete':
      case 'kasi_kyojuu_confirm':
      case 'kasi_kyojuu_edit':
      case 'uri_jigyou_complete':
      case 'uri_jigyou_confirm':
      case 'uri_jigyou_edit':
      case 'uri_kyojuu_complete':
      case 'uri_kyojuu_confirm':
      case 'uri_kyojuu_edit':
        return true;
    }
    return false;
  };

  _search.common.fixedFooter = function (config) {

    if (!(this instanceof _search.common.fixedFooter)) {
      return new _search.common.fixedFooter(config);
    }
    this.$el = config.$el;
  };

  _search.common.fixedFooter.prototype.run = function () {

    var self = this;
    var $footer = self.$el.find('.fixed-pagefooter');

    if ($footer.length < 1) {
      return;
    }

    // bottom space
    //self.$el.find('.device-change').css({padding: '10px 0 ' + ($footer.outerHeight() + 10) + 'px 0'});

    // add listener
    // Android 4.2.2
    var handle;
    $(window).resize(function () {
      if (typeof handle === 'number') {
        clearTimeout(handle);
      }
      handle = setTimeout(function () {
        $footer.css('width', self.$el.find('.page-header').width());
      }, 200);
    });
  };

  /**
   * tooltip
   *
   * @param config
   * @returns {_search.common.tooltip}
   */
  _search.common.tooltip = function (config) {

    if (!(this instanceof _search.common.tooltip)) {
      return new _search.common.tooltip(config);
    }

    this.ARCHTECTURE = 'archtecture';
    this.AGREEMENT = 'agreement';

    this.$el = config.$el;

    this.$swiperOverlayWrapper = this.$el.find('.box-overlay');
    this.$floatbox = this.$swiperOverlayWrapper.siblings('.floatbox');
    this.$closeBtn = this.$floatbox.find('.btn-modal-close');
    this.$title = this.$floatbox.find('.floatbox-heading');
    this.$content = this.$floatbox.find('.floatbox-tx');
  };

  /**
   * run
   */
  _search.common.tooltip.prototype.run = function () {

    var self = this;

    // add listener
    self.$el.find('.icon_question').on('click', function (e) {

      e.preventDefault();

      self._open(this.className.match(/agreement/gi) ? self.AGREEMENT : self.ARCHTECTURE);
    })
  };

  /**
   * open
   *
   * @param key
   * @private
   */
  _search.common.tooltip.prototype._open = function (key) {

    var self = this;

    self.$title.text(self.getContent(key).title);
    self.$content.html(self.getContent(key).detail);

    self.$swiperOverlayWrapper.fadeIn(300, function () {

      self.$floatbox.css({
        position: 'fixed',
        left: '50%',
        top: '50%',
        marginLeft: -self.$floatbox.width() * .5,
        marginTop: -self.$floatbox.height() * .5
      }).show();

      // add listener
      self.$swiperOverlayWrapper.on('click', {self: self}, self._close);
      self.$closeBtn.on('click', {self: self}, self._close);
    })
  };

  /**
   * close
   *
   * @param e
   * @private
   */
  _search.common.tooltip.prototype._close = function (e) {

    var self = e.data.self;

    self.$floatbox.hide();
    self.$swiperOverlayWrapper.fadeOut(300);

    // remove listener
    self.$swiperOverlayWrapper.off('click', self._close);
    self.$closeBtn.off('click', self._close);
  };

  /**
   * get content
   *
   * @param key
   * @returns {*}
   */
  _search.common.tooltip.prototype.getContent = function (key) {

    if (key === this.AGREEMENT) {

      return {
        title: '定期建物賃貸借のこと',
        detail: '<p>' +
        '一般の賃貸契約とは異なり契約期間満了によって契約が終了し、契約更新は行われません。<br>' +
        '契約期間は物件によって異なります。貸主との合意があれば再契約は可能ですが、賃料等の賃貸条件の変更や、敷金・礼金・仲介手数料等があらためて発生する場合がございます。<br>' +
        'お問合せの際に十分ご確認ください。' +
        '</p>'
      }

    }
    else if (key === this.ARCHTECTURE) {

      return {
        title: '建物構造のこと',
        detail: '<p>' +
        '●鉄筋系<br>' +
        '「RC（鉄筋コンクリート）」「SRC（鉄骨鉄筋コンクリート）」「PC（プレキャストコンクリート）」の建物を検索します。<br>' +
        '●鉄骨系<br>' +
        '「軽量鉄骨」「鉄骨造」「重量鉄骨造」「HPC（鉄骨プレキャストコンクリート造）」「ALC（軽量気泡コンクリート）」の建物を検索します。<br>' +
        '●木造<br>' +
        '「木造」の建物を検索します。<br>●その他<br>「ブロック」「鉄筋ブロック造」「CFT（コンクリート充鎮鋼管造）」「その他」の建物を検索します。' +
        '</p>'
      }
    }
  };

  /**
   * favorite class
   *
   * @param config
   * @returns {_search.common.favorite}
   */
  _search.common.favorite = function (config) {

    if (!(this instanceof _search.common.favorite)) {
      return new _search.common.favorite(config);
    }

    this.$el = config.$el;
  };

  /**
   * run
   */
  _search.common.favorite.prototype.run = function () {

    var self = this;

    self._init();

    // add
    self.$el.find('.btn-fav a').on('click', function (e) {

      e.preventDefault();

      self._toggle($(e.target).closest('.btn-fav'));

      // detail
      // if (_search.pageType.isDetail()) {
      app.cookie.updateFavorite({bukkenId: app.location.bukkenId()});
      // }

      // 行動情報を保存
      app.cookie.saveOperation('updateFavorite', [app.location.bukkenId()]);

      setTimeout(function () {
        alert('お気に入りに追加しました');
      }, 200);
    });
  };

  _search.common.favorite.prototype._init = function () {

    var self, $btn, favoriteList;

    self = this;
    favoriteList = $.cookie(app.cookie.KEY_FAVORITE);

    $btn = self.$el.find('.btn-fav');
    $.each($btn, function (i, v) {

      // id get from url
      var id = app.location.bukkenId();
      $.each(favoriteList, function (j, savedId) {
        if (id === savedId) {
          self._toggle($btn.eq(i));
        }
      });
    });
  };

  _search.common.favorite.prototype._toggle = function ($btn) {

    $btn.toggleClass('done');

    if ($btn.hasClass('done')) {
      $btn.empty().append('<span href="#">お気に入り登録</span>');
      return;
    }
    $btn.empty().append('<a href="#">お気に入り登録</a>');
  };

  _search.area.searchBtn = function (config) {

    if (!(this instanceof _search.area.searchBtn)) {
      return new _search.area.searchBtn(config);
    }

    this.$el = config.$el;
  };

  _search.area.searchBtn.prototype.run = function () {

    var self = this;

    var $searchText;
    
    $searchText = self.$el.find('.element-input-search input');

    // add listener
    self.$el.find('.fixed-pagefooter').on('click', function (e) {

      var $checkedList, values,
        city = '';

      e.preventDefault();

      if (e.target.nodeName.toLocaleLowerCase() !== 'a') {
        return;
      }

      $checkedList = self.$el.find(':checkbox:checked');

      // no checked
      if ($checkedList.length < 1) {
        alert('市区郡を選択してください');
        return;
      }

      values = $checkedList.serializeArray();
      $.each(values, function (i, v) {
        city += v.value;
        if (i !== values.length - 1) {
          city += ',';
        }
      });

      // post
      app.request.postForm(e.target.href, {
        city: city,
        fulltext: $searchText.serialize(),
        from_city_select: true,
      });
    });
    $(document).on('keyup', $searchText.selector, function (e) {
        if(e.keyCode == 13){
            self.$el.find('.fixed-pagefooter > ul > li:nth-child(2) a').trigger('click');
        }
    });

    $(document).on('focusout', $searchText.selector, function(e){
        clearSuggest($searchText);
    });

    self.$el.find(':checkbox').on('change', function () {
        var $checkedList = self.$el.find(':checkbox:checked');
        if ($checkedList.length > 0) {
            setEvent();
        } else {
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
        }
    });
    var setEvent = function () {
        var shumoku, special_path, city = '';
        var $checkedList = self.$el.find(':checkbox:checked');
        if($checkedList.length > 0) {
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
            _search.pageType.isSpecialCategory() ?
            special_path = app.location.currentSpecialPath() :
            shumoku = app.location.currentShumoku();
            var prefecture = app.location.parseUrl(app.location.currentUrl).dirs[1];
            var values = $checkedList.serializeArray();
            $.each(values, function (i, v) {
                city += v.value;
                if (i !== values.length - 1) {
                    city += ',';
                }
            });
            app.request.suggest({
                shumoku: shumoku,
                special_path: special_path,
                prefecture: prefecture,
                city: city,
            }, 'input[name="search_filter[fulltext_fields]"]');
        }
    }
    setEvent();
  };

  _search.shumoku.tab = function (config) {

    if (!(this instanceof _search.shumoku.tab)) {
      return new _search.shumoku.tab(config);
    }

    this.$el = config.$el;
    this.$tabArea = this.$el.find('.element-search-tab');
    this.$tabList = this.$tabArea.find('li');
    this.$bodyList = this.$tabArea.siblings('.element-search-tab-body');
  };

  _search.shumoku.tab.prototype.run = function () {

    var self = this;

    // add listener
    self.$tabArea.on('click', function (e) {

      var $clicked, $targetBody, classes, i;

      e.preventDefault();

      if (e.target.nodeName.toLowerCase() !== 'a') {

        return false;
      }

      $clicked = $(e.target).closest('li');

      // tab
      self.$tabList.not($clicked).removeClass('active');
      $clicked.addClass('active');

      // body (shumoku)
      if (self.$bodyList.length < 1) {
        return;
      }

      classes = ($(e.target).closest('li').attr('class') + '').split(' ');
      for (i = 0; i < classes.length; i++) {

        if (classes[i] === 'active') {
          continue;
        }

        $targetBody = self.$bodyList.filter(function (j) {
          return self.$bodyList.eq(j).hasClass(classes[i]);
        }).show();
        self.$bodyList.not($targetBody).hide();
        break;
      }

    });
  };

  _search.prefecture.tab = function (config) {

    if (!(this instanceof _search.prefecture.tab)) {
      return new _search.prefecture.tab(config);
    }

    this.$el = config.$el;
    this.$tabArea = this.$el.find('.element-search-tab');
    this.$tabList = this.$tabArea.find('li');
    this.$listArea = this.$tabArea.siblings('.element-search-toggle');
  };

  _search.prefecture.tab.prototype.run = function () {

    var self, $activeTab, $prefLinks, _rewriteHref, $tabArea, $tabList,
      pathList = {};

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
      if ($activeTab.hasClass('shikugun')){
        filename ='';
        $('.element-search-list').hide();
      }
      else if ($activeTab.hasClass('ensen')){
        filename ='line.html';
        $('.element-search-list').hide();
      }else if($activeTab.hasClass('spatial')) {
        filename ='map.html';
        $('.element-search-list').show();
      }

      // 現在地から探すメニュー
      if(!$activeTab.hasClass('spatial')) {


      }
      app.customConsoleLog(filename);

      $prefLinks.each(function (i, v) {
        var $link = $prefLinks.eq(i);
        $link.attr('href', pathList[$link.data('name')] + filename);
      });
    };

    // tab
    $tabArea = self.$el.find('.element-search-tab');
    $tabList = $tabArea.find('li');
    $activeTab = $tabList.filter(function (i, v) {
      return $tabList.eq(i).hasClass('active');
    });

    // links
    $prefLinks = self.$listArea.find('a');
    $prefLinks.each(function (i, v) {
      var $link = $prefLinks.eq(i);
      pathList[$link.data('name')] = $link.attr('href');
    });

    // no selected
    app.customConsoleLog($activeTab.length);
    if ($activeTab.length < 1) {
      $activeTab = $tabList.first().addClass('active');
    }

    // rewrite link
    _rewriteHref($activeTab, $prefLinks, pathList);

    // add listener
    // tab
    self.$tabList.on('click', function (e) {

      var $activeTab;
      $activeTab = $(e.target).closest('li').addClass('active');
      self.$tabList.not($activeTab).removeClass('active');
      _rewriteHref($activeTab, $prefLinks, pathList);
      return false;
    });
  };

  _search.prefecture.initToggle = function (config) {

    if (!(this instanceof _search.prefecture.initToggle)) {
      return new _search.prefecture.initToggle(config);
    }

    this.$el = config.$el;
  };

  _search.prefecture.initToggle.prototype.run = function () {

    var self = this;
    var $targets = self.$el.find('.js-search-toggle dt');

    // open
    if ($targets.length === 1) {
      $targets.addClass('open').next('dd').css({display: 'block'});
    }

    // add listener
    $targets.on('click', function () {
      $(this).toggleClass('open').next().slideToggle('fast');
      return false;
    });
  };

  _search.area.checkbox = function (config) {

    if (!(this instanceof _search.area.checkbox)) {
      return new _search.area.checkbox(config);
    }

    this.MAX = 10;
    this.$el = config.$el;
  };

  _search.area.checkbox.prototype.run = function () {

    var self, $checkbox;

    self = this;
    $checkbox = self.$el.find('.list-select-set :checkbox:enabled'); // only enabled

    var _toggleDisabled = function ($checkbox) {

      var $checked;

      $checked = $checkbox.filter(function (i) {
        return $checkbox.eq(i).prop('checked');
      });

      // lock
      if ($checked.length >= self.MAX) {

        $checkbox.not($checked).prop('disabled', true)
          .closest('li').addClass('tx-disable');
        return;
      }

      // release
      $checkbox.prop('disabled', false)
        .closest('li').removeClass('tx-disable');
    };

    // inti
    _toggleDisabled($checkbox);

    // add listener
    $checkbox.on('change', function (e) {

      _toggleDisabled($checkbox);
    });
  };

  _search.railway.checkbox = function (config) {

    if (!(this instanceof _search.railway.checkbox)) {
      return new _search.railway.checkbox(config);
    }

    this.MAX = 5;
    this.$el = config.$el;
  };

  _search.railway.checkbox.prototype.run = function () {

    var self, $checkbox;

    self = this;
    $checkbox = self.$el.find('.list-select-set :checkbox:enabled'); // only enabled

    var _toggleDisabled = function () {

      var $checked;

      $checked = $checkbox.filter(function (i) {
        return $checkbox.eq(i).prop('checked');
      });

      // lock
      if ($checked.length >= self.MAX) {

        $checkbox.not($checked).prop('disabled', true)
          .closest('li').addClass('tx-disable');
        return;
      }

      // release
      $checkbox.prop('disabled', false)
        .closest('li').removeClass('tx-disable');
    };

    // init
    _toggleDisabled($checkbox);

    // add listener
    $checkbox.on('change', function (e) {

      _toggleDisabled($checkbox);
    });
  };

  _search.railway.searchBtn = function (config) {

    if (!(this instanceof _search.railway.searchBtn)) {
      return new _search.railway.searchBtn(config);
    }

    this.$el = config.$el;
  };

  _search.railway.searchBtn.prototype.run = function () {

    var self = this;

    // add listener
    self.$el.find('.fixed-pagefooter').on('click', function (e) {

      var $checkedList, values,
        railway = '';

      e.preventDefault();

      if (e.target.nodeName.toLocaleLowerCase() !== 'a') {
        return;
      }

      $checkedList = self.$el.find(':checkbox:checked');

      // no checked
      if ($checkedList.length < 1) {
        alert('路線を選択してください');
        return;
      }

      values = $checkedList.serializeArray();
      $.each(values, function (i, v) {
        railway += v.value;
        if (i !== values.length - 1) {
          railway += ',';
        }
      });

      // post
      app.request.postForm(e.target.href, {
        railway: railway
      });
    });
  };

  _search.station.initToggle = function (config) {

    if (!(this instanceof _search.station.initToggle)) {
      return new _search.station.initToggle(config);
    }

    this.$el = config.$el;
  };

  _search.station.initToggle.prototype.run = function () {

    var self = this;
    var $targets = self.$el.find('.js-search-toggle dt');
    var $first = $targets.first();

    // open first
    $first.addClass('open').next('dd').css({display: 'block'});

    // if ($targets.length === 1) {
    //   return;
    // }

    // add listener
    $targets.on('click', function () {
      $(this).toggleClass('open').next().slideToggle('fast');
      return false;
    });

  };

  _search.station.searchBtn = function (config) {

    if (!(this instanceof _search.station.searchBtn)) {
      return new _search.station.searchBtn(config);
    }

    this.$el = config.$el;
  };

  _search.station.searchBtn.prototype.run = function () {

    var self = this;
    var $searchText,station = '';

    $searchText = self.$el.find('.element-input-search input');

    // add listener
    self.$el.find('.fixed-pagefooter').on('click', function (e) {

      var $checkedList, values;

      e.preventDefault();

      if (e.target.nodeName.toLocaleLowerCase() !== 'a') {
        return;
      }

      $checkedList = self.$el
        .find('li').not('.check-all')// filter .check-all
        .find(':checkbox:checked');

      // no checked
      if ($checkedList.length < 1) {
        alert('駅を選択してください');
        return;
      }

      values = $checkedList.serializeArray();
      $.each(values, function (i, v) {
        station += v.value;
        if (i !== values.length - 1) {
          station += ',';
        }
      });

      // post
      app.request.postForm(e.target.href, {
        station: station,
        fulltext: $searchText.serialize(),
        from_station_select: true
      });
    });
    $(document).on('keyup', $searchText.selector, function (e) {
        if(e.keyCode == 13){
            self.$el.find('.fixed-pagefooter > ul > li:nth-child(2) a').trigger('click');
        }
    });

    $(document).on('focusout', $searchText.selector, function(e){
        clearSuggest($searchText);
    });

    self.$el.find(':checkbox').on('change', function () {
        var $checkedList = self.$el.find(':checkbox:checked');
        if ($checkedList.length > 0) {
            setEvent();
        } else {
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
        }
    });
    var setEvent = function () {
        var shumoku, special_path, station = '';
        var $checkedList = self.$el.find(':checkbox:checked');
        if ($checkedList.length > 0) {
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
            _search.pageType.isSpecialCategory() ?
            special_path = app.location.currentSpecialPath() :
            shumoku = app.location.currentShumoku();
            var prefecture = app.location.parseUrl(app.location.currentUrl).dirs[1];
            var values = $checkedList.serializeArray();
            $.each(values, function (i, v) {
                station += v.value;
                if (i !== values.length - 1) {
                station += ',';
                }
            });
            app.request.suggest({
                shumoku: shumoku,
                special_path: special_path,
                prefecture: prefecture,
                station: station,
            }, 'input[name="search_filter[fulltext_fields]"]');
        }
    }
    setEvent();
  };

  _search.choson.init = function (config) {

    if (!(this instanceof _search.choson.init)) {
      return new _search.choson.init(config);
    }

    this.$el = config.$el;
  };

  _search.choson.init.prototype.run = function () {
    this.checkbox();
    this.initToggle();
    this.searchBtn();
  };

  _search.choson.init.prototype.checkbox = function () {

    var self, $checkbox;

    self = this;
    $checkbox = self.$el.find('.list-select-set :checkbox:enabled'); // only enabled

    // add listener
    $checkbox.on('change', function (e) {

      var $target, $parent, $children, $checkall, $siblings;

      $target = $(e.target);
      $parent = $target.closest('li');
      $children = $parent.closest('ul').find('li :checkbox:enabled');

      // check all
      if ($parent.hasClass('check-all')) {

        $children.prop('checked', $target.prop('checked'));
        return;
      }

      // 個別
      $checkall = $children.filter(function (i) {
        return $children.closest('li').eq(i).hasClass('check-all');
      });

      if ($checkall.length < 1) {
        return;
      }

      $siblings = $children.not($checkall);
      $checkall.prop('checked', $siblings.length > 0 && $siblings.length === $siblings.filter(function (i) {
          return $siblings.eq(i).prop('checked');
        }).length);
    });
  };

  _search.choson.init.prototype.initToggle = function () {

    var self = this;

    var $targets = self.$el.find('.js-search-toggle dt');
    var $first = $targets.first();

    // open first
    $first.addClass('open').next('dd').css({display: 'block'});

    // if ($targets.length === 1) {
    //   return;
    // }

    // add listener
    $targets.on('click', function () {
      $(this).toggleClass('open').next().slideToggle('fast');
      return false;
    });
  };

  _search.choson.init.prototype.searchBtn = function () {

    var self = this;
    var $searchText;
    
    $searchText = self.$el.find('.element-input-search input');
    // add listener
    self.$el.find('.fixed-pagefooter').on('click', function (e) {

      var $checkedList, values,
        choson = '';

      e.preventDefault();

      if (e.target.nodeName.toLocaleLowerCase() !== 'a') {
        return;
      }

      $checkedList = self.$el
        .find('li').not('.check-all')// filter .check-all
        .find(':checkbox:checked');

      // no checked
      if (!$(e.target).parent().hasClass('btn-more') && $checkedList.length < 1) {
        alert('町名を選択してください');
        return;
      }

      values = $checkedList.serializeArray();
      $.each(values, function (i, v) {
        choson += v.value;
        if (i !== values.length - 1) {
          choson += ',';
        }
      });

      // post
      app.request.postForm(e.target.href, {
        choson: choson,
        from_choson_select: true,
        fulltext: $searchText.serialize()
      });
    });
    $(document).on('keyup', $searchText.selector, function (e) {
      if(e.keyCode == 13){
          self.$el.find('.fixed-pagefooter > ul > li:nth-child(2) a').trigger('click');
      }
  });

    $(document).on('focusout', $searchText.selector, function(e){
        clearSuggest($searchText);
    });

    self.$el.find(':checkbox').on('change', function () {
        var $checkedList = self.$el.find(':checkbox:checked');
        if ($checkedList.length > 0) {
            setEvent();
        } else {
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
        }
    });
    var setEvent = function () {
        var shumoku, special_path, choson = '';
        var $checkedList = self.$el.find(':checkbox:checked');
        if($checkedList.length > 0) {
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
            _search.pageType.isSpecialCategory() ?
            special_path = app.location.currentSpecialPath() :
            shumoku = app.location.currentShumoku();
            var prefecture = app.location.parseUrl(app.location.currentUrl).dirs[1];
            var values = $checkedList.serializeArray();
            $.each(values, function (i, v) {
                choson += v.value;
                if (i !== values.length - 1) {
                    choson += ',';
                }
            });
            app.request.suggest({
                shumoku: shumoku,
                special_path: special_path,
                prefecture: prefecture,
                choson: choson,

            }, 'input[name="search_filter[fulltext_fields]"]');
        }
    }
    setEvent();
  };

  _search.condition.initToggle = function (config) {

    if (!(this instanceof _search.condition.initToggle)) {
      return new _search.condition.initToggle(config);
    }

    this.$el = config.$el;
  };

  _search.condition.initToggle.prototype.run = function () {

    var self = this;
    var $targets = self.$el.find('.js-search-toggle dt');

    // add listener
    $targets.on('click', function () {
      $(this).toggleClass('open').next().slideToggle('fast');
      return false;
    });

    $targets.each(function (i, v) {
      var $checked = $targets.eq(i).next().find(':checked');
      if ($checked.length > 0) {
        $targets.eq(i)[0].click();
      }
    });
  };

  _search.condition.searchBtn = function (config) {

    if (!(this instanceof _search.condition.searchBtn)) {
      return new _search.condition.searchBtn(config);
    }

    this.$el = config.$el;
  };

  _search.condition.searchBtn.prototype.run = function () {

    var self = this;

    var _conditionCounter, shumoku = null, special_path = null, prefecture = null, city = null, station = null;
    var $searchText, $searchBtn, freewordTimeOut = null;

    $searchBtn = self.$el.find('.fixed-pagefooter li.btn-lv3 a');
    $searchText = self.$el.find('.element-input-search input');


    $(document).on('keyup', $searchText.selector, function (e) {
        if (e.keyCode == 13) {
            $searchBtn.trigger("click");
        }
    });

    $(document).on('focusout', $searchText.selector, function(e){
        clearSuggest($searchText);
    });

    self.$el.find('input, select').on('change', function() {
        if ($searchText.length > 0) {
            setEvent();
            $searchText.data('plugin_fulltextCount').getCount();
        }
    });
    // add listener
    $searchBtn.on('click', function (e) {

      var $list;

      e.preventDefault();

      if (e.target.nodeName.toLocaleLowerCase() !== 'a') {
        return;
      }

      // post
      app.request.postForm(e.target.href, {
        condition: self.$el.find('input, select').serialize(),
        fulltext: $searchText.serialize(),
        from_condition: true
      });

      return false;
    });

    self.$el.find('.js-select-choson').on('click', function (e) {

      var $list;
      var $a = $(this).closest('a');

      e.preventDefault();

      // post
      app.request.postForm($a[0].href, {
        condition: self.$el.find('input, select').serialize(),
        fulltext: $searchText.serialize(),
        from_condition: true
      });

      return false;
    });
    var setEvent = function () {
        var shumoku, special_path, prefecture, type_freeword, city, from_searchmap = 0;
        $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
        if (_search.pageType.isSpecialCategory()) {
            special_path = app.location.currentSpecialPath();
        } else {
            shumoku = app.location.currentShumoku();
        }
        if (app.location.currentPrefecture() != 'condition') {
            prefecture = app.location.currentPrefecture();
        }
        if (app.location.parseUrl(app.location.currentUrl).dirs[1] == 'condition' ) {
            type_freeword = app.location.parseUrl(app.location.currentUrl).dirs[0];
        }
        var btnSubmit = self.$el.find('.fixed-pagefooter.btn-search-submit a');
        if (btnSubmit.length > 0 && btnSubmit.attr('href').indexOf('-map.html') > -1) {
            var href = btnSubmit.attr('href');
            var values = href.split('/');
            city = values[values.length - 2].replace('-map.html', '');
            from_searchmap = 1;
        }
        app.request.suggest({
            shumoku: shumoku,
            special_path: special_path,
            prefecture: prefecture,
            type_freeword: type_freeword,
            condition: self.$el.find('input, select').serialize(),
            city: city,
            from_searchmap: from_searchmap,
        },'input[name="search_filter[fulltext_fields]"]');
        app.request.counter({
            prefecture: prefecture,
            shumoku: shumoku,
            special_path: special_path,
            type_freeword: type_freeword,
            condition: self.$el.find('input, select').serialize(),
            city: city,
            from_searchmap: from_searchmap,
        },'input[name="search_filter[fulltext_fields]"]','fulltext_count');
    }
    // run condition counter
    if ($searchText.length > 0) {
        setEvent();
        $searchText.data('plugin_fulltextCount').getCount();
    }
  };

  _search.station.checkbox = function (config) {

    if (!(this instanceof _search.station.checkbox)) {
      return new _search.station.checkbox(config);
    }

    this.$el = config.$el;
  };

  _search.station.checkbox.prototype.run = function () {

    var self, $checkbox;

    self = this;
    $checkbox = self.$el.find('.list-select-set :checkbox:enabled'); // only enabled

    // add listener
    $checkbox.on('change', function (e) {

      var $target, $parent, $children, $checkall, $siblings;

      $target = $(e.target);
      $parent = $target.closest('li');
      $children = $parent.closest('ul').find('li :checkbox');

      // check all
      if ($parent.hasClass('check-all')) {

        $checkbox.filter($children).prop('checked', $target.prop('checked'));
        return;
      }

      // 個別
      $checkall = $children.filter(function (i) {
        return $children.closest('li').eq(i).hasClass('check-all');
      });

      if ($checkall.length < 1) {
        return;
      }

      $siblings = $children.not($checkall);
      $checkall.prop('checked', $siblings.length === $siblings.filter(function (i) {
          return $siblings.eq(i).prop('checked');
        }).length);
    });
  };

  _search.list.article = function (config) {

    if (!(this instanceof _search.list.article)) {
      return new _search.list.article(config);
    }

    this.$el = config.$el;
    this.$wrapperList = this.$el.find('.article-object');
    this.$bodyList = this.$wrapperList.find('.object-body');
  };

  _search.list.article.prototype.run = function () {

    var self;

    self = this;

    // add listener
    // go to detail page
    self.$bodyList.on('click', function (e) {

      var href;

      href = $(this).closest('.article-object').find('.object-body a').attr('href');

      // 特集
      if (_search.pageType.isSpecialCategory()) {
        app.request.postForm(href, {'special-path': app.location.currentSpecialPath()}, true);
        return false;
      }

      window.open(href);
      return false;

    });
  };

  _search.list.contact = function (config) {

    if (!(this instanceof _search.list.contact)) {
      return new _search.list.contact(config);
    }

    this.$el = config.$el;
    this.$wrapperList = this.$el.find('.article-object');
  };

  /**
   * 物件種目の文字列を取得
   *
   * @returns {*|_search.shumoku|{}}
   */
  _search.list.contact.prototype.getShumokuCt = function () {

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

  _search.list.contact.prototype.run = function () {

    var self;

    self = this;

    // add listener
    self.$el.find('.btn-all-contact').on('click', function (e) {

      var $checked, params,
        list = [];

      e.preventDefault();

      $checked = self.$wrapperList.find('.object-check :checkbox:checked');

      if ($checked.length < 1) {
        alert('物件が1つもチェックされていません');
        return;
      }

      $.each($checked, function (i, v) {
        list.push($checked.eq(i).closest('.article-object').data('bukken-no'));
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
          var shumoku_parsed = $checked.eq(0).closest('div').find('a').eq(0).attr('href').split('/');
          $shumokuCt = shumoku_parsed[1];
          break;
        default:
          break;
      }

      params = {
        id: list,
        type: $shumokuCt
      };

      // 特集
      if (_search.pageType.isSpecialCategory()) {
        params['special-path'] = app.location.currentSpecialPath();
      }

      app.customConsoleLog('----- お問い合わせパラメータ start -----');
      app.customConsoleLog(params);
      app.customConsoleLog('----- お問い合わせパラメータ end -----');

      app.request.postForm(app.location.setHttpsProtocol(e.target.href), params, true);
    });
  };

  _search.list.changeCondition = function (config) {

    if (!(this instanceof _search.list.changeCondition)) {
      return new _search.list.changeCondition(config);
    }

    this.PER_PAGE = 10;
    this.$el = config.$el;
  };

  _search.list.changeCondition.prototype.run = function () {

    var self;

    var $searchText;

    self = this;

    $searchText = self.$el.find('.search-freeword').find('input');

    // add listener
    // change condition
    self.$el.find('.btn-narrow-down, .btn-term-change').on('click', function (e) {

      e.preventDefault();

      if (e.target.nodeName.toLowerCase() !== 'a') {
        return;
      }
      app.request.postForm(e.target.href, {
        from_result: true,
        fulltext: $searchText.serialize(),
      });
    });

    // sort
    self.$el.find('.sort-select select').on('change', function (e) {

      e.preventDefault();

      var value = $(e.target).find(':selected').val();

      app.cookie.updateSearch({
        sort: value
      });

      app.request.postForm(app.location.removeParams(app.location.currentUrl), {
        from_result: true,
        sort: value
      });
    });

    // pager
    self.$el.find('.pager-prev, .pager-next').on('click', function (e) {

      var $btn, params, current, goto, i;

      e.preventDefault();

      if (e.target.nodeName.toLowerCase() !== 'a') {
        return;
      }

      // current
      current = 1;
      params = app.location.parseUrl(app.location.currentUrl)['search'].substr(1).split('&');
      for (i = 0; i < params.length; i++) {
        var param = params[i].split('=');
        if (param[0] === 'page') {
          current = parseInt(param[1]);
          break;
        }
      }

      // goto
      goto = 1;
      $btn = $(e.target).closest('li');
      if ($btn.hasClass('pager-prev')) {
        goto = current < 2 ? 1 : current - 1;
      }
      else if ($btn.hasClass('pager-next')) {
        goto = current + 1;
      }

      app.request.postForm(app.location.removeParams(app.location.currentUrl) + '?page=' + goto, {
        from_result: true, fulltext: $searchText.serialize(),
      });

      return false;
    });

    // search freeword
    $(document).on('keyup', $searchText.selector, function(e){
      if (e.keyCode == 13) {
        self.$el.find('.search-freeword a').trigger('click');
      }
    });

    $(document).on('focusout', $searchText.selector, function(e){
        clearSuggest($searchText);
    });

    self.$el.find('.search-freeword a').on('click', function (e) {
      e.preventDefault();
      app.request.postForm(e.target.href, {
        from_result: true,
        fulltext: $searchText.serialize(),
        from_condition: true,
      });
    });
    var setEvent = function () {
        var shumoku, special_path, prefecture;
        $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);
        if (_search.pageType.isSpecialCategory()) {
            special_path = app.location.currentSpecialPath();
        } else {
            shumoku = app.location.currentShumoku();
        }
        if (app.location.currentPrefecture() != 'condition') {
            prefecture = app.location.currentPrefecture();
        }
        app.request.suggest({
            shumoku: shumoku,
            special_path: special_path,
            prefecture: prefecture,
            condition_side: self.$el.find('input, select').serialize(),
            from_condition: true,
        }, 'input[name="search_filter[fulltext_fields]"]');
    }
    setEvent();
  };

  _search.list.highlight = function (config) {
  
    if (!(this instanceof _search.list.highlight)) {
      return new _search.list.highlight(config);
    }

    this.$el = config.$el;
    this.$wrapperList = this.$el.find('.article-object');
    this.$highlightArea = this.$wrapperList.find('.highlightsArea');
  };

  _search.list.highlight.prototype.run = function () {
    var self;
    self = this;
    if (self.$highlightArea.length > 0) {
        self.$highlightArea.find('.grad-item').each(function () {
            if ($(this).height() > 60) {
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

  _search.detail.modal = function (config) {

    if (!(this instanceof _search.detail.modal)) {
      return new _search.detail.modal(config);
    }

    this.$el = config.$el;
    this.$mainArea = this.$el.find('.article-main-info');
    this.$swiper = this.$mainArea.find('.swiper-wrapper');
    this.$photoView = this.$mainArea.find('.photo-view .swiper-wrapper');
    this.$photoNav = this.$mainArea.find('.photo-nav .swiper-wrapper');
    this.$swiperNumber = this.$mainArea.find('.photo-slider-num');
    this.$swiperCaption = this.$mainArea.find('.photo-slider-caption');
    this.$swiperOverlayWrapper = this.$el.find('.photo-gallery-overlaywrap');
    this.$aroundOverlayWrapper = this.$el.find('.around-overlaywrap');
    this.$photoModalList = this.$el.find('.photo-modal-list');
  };

  _search.detail.modal.prototype.run = function () {

    this.initSwiper();
    this.initCommission();
    this.initAroundPhoto();
    this.initListModal();
  };

  /**
   * init swiper
   */
  _search.detail.modal.prototype.initSwiper = function () {

    var self = this;
    var activeTimeout = null;
    var slideCount;
  
    self.$el.find('.photo-nav').css("display", "block");

    // add listener
    // init & update text
    self.$swiper.on('init reInit', function (event, slick, currentSlide) {

      var $targetSlide;

      if (typeof currentSlide === 'undefined') {
        currentSlide = 0;
      }

      $targetSlide = self.$el.find('[data-slick-index="' + currentSlide + '"]');

      // set swiper number
      slideCount = slick.slideCount
      self.$swiperNumber.find('.photo-slider-num-now').text((parseInt(currentSlide) + 1));
      self.$swiperNumber.find('.photo-slider-num-total').text(slideCount);

      // rewrite swiper text
      self.$swiperCaption.find('.photo-slider-caption-heading').text($targetSlide.find('a').prop('title'));
      self.$swiperCaption.find('.photo-slider-caption-tx').text($targetSlide.find('a').data('caption'));
    });

    // slick start
    if (self.$swiper.length > 0) {
      //photo-view
      self.$photoView.slick({
        variableWidth: true,
        centerMode: true,
        asNavFor: '.photo-nav slick-track',
        prevArrow: self.$mainArea.find('.btn-move .prev'),
        nextArrow: self.$mainArea.find('.btn-move .next')
      }).on('afterChange', function (event, slick, currentSlide, nextSlide) {
        var $targetSlide = self.$el.find('[data-slick-index="' + currentSlide + '"]');
        slideCount = slick.slideCount
        self.$swiperNumber.find('.photo-slider-num-now').text((parseInt(currentSlide) + 1));
        self.$swiperNumber.find('.photo-slider-num-total').text(slideCount);
        // rewrite swiper text
        self.$swiperCaption.find('.photo-slider-caption-heading').text($targetSlide.find('a').prop('title'));
        self.$swiperCaption.find('.photo-slider-caption-tx').text($targetSlide.find('a').data('caption'));
          setTimeout(function() {
            var navCurrentIndex = self.$photoNav.find('.slick-current').data('slick-index');
            console.log(navCurrentIndex, currentSlide);
            var slickIndex = currentSlide;
            if (currentSlide == 0 && navCurrentIndex == (slideCount - 1)) {
                slickIndex = navCurrentIndex + 1;
            }
            if (currentSlide == (slideCount - 1) && navCurrentIndex == 0) {
                slickIndex = -1;
            }
            if (currentSlide == -1 && navCurrentIndex == (slideCount - 1)) {
                slickIndex = slideCount - 1;
            }
            if (currentSlide == slideCount && navCurrentIndex == 0) {
                slickIndex = 0;
            }
            if (currentSlide == 1 && navCurrentIndex == (slideCount - 1)) {
                slickIndex = slideCount + 1;
            }
            if (currentSlide ==  (slideCount - 1) && navCurrentIndex == 1) {
                slickIndex = -1;
            }
            $(self.$photoNav).slick("slickGoTo",slickIndex,false);
          }, 10);
      });
      //photo-nav
      self.$photoNav.slick({
        variableWidth: true,
        centerMode: true,
        asNavFor: '.photo-view slick-track',
        prevArrow: self.$mainArea.find('.btn-move .prev'),
        nextArrow: self.$mainArea.find('.btn-move .next'),
        focusOnSelect: true
      }).on('afterChange', function (event, slick, currentSlide, nextSlide) {
        activeTimeout = setTimeout(function() {
            self.$photoNav.find('[data-slick-index="' + currentSlide + '"] a').focus();
        }, 100);
      });
      var leftDrag, leftCurrent;
      //photo-view touchstart event
      self.$photoView.find('a').on('touchstart', function() {
        leftCurrent = self.$photoView.find('.slick-track').position().left;
      });
      //photo-view touchend event
      self.$photoView.find('a').on('touchend', function() {
        leftDrag = self.$photoView.find('.slick-track').position().left;
        var left = leftDrag - leftCurrent;
        var slickCurrent = self.$photoView.find('.slick-current');
        var widthCurrent = slickCurrent.width();
        var slickIndex = slickCurrent.data('slick-index');
        if (left < 0 && left < - (widthCurrent/2)) {
            slickIndex = slickIndex + 1;
        }
        if (left > 0 && left > widthCurrent/2) {
            slickIndex = slickIndex - 1;
        }
        $(self.$photoView).slick("slickGoTo",slickIndex,false);
      });
    }

    // add listener
    // zoom
    var pinchZoom = null;
    self.$photoView.find('a').on('click', function (e) {

      self._setText($(e.target).closest('.swiper-slide'));
      self._zoom(self.$swiperOverlayWrapper);
      if (!pinchZoom) {
        setTimeout(function() {
            var el = document.querySelector('div.pinch-zoom');
            pinchZoom = new PinchZoom.default(el, {
                minZoom: 1,
                imageReset: true
            });
          }, 200);
      }

      return false;
    });

    self.$photoNav.find('a').on('click', function (e) {
      clearTimeout(activeTimeout);
      var slickIndex = $(e.target).closest('.swiper-slide').data('slick-index');
      var currentIndex = $(self.$photoView).find('.slick-current').data('slick-index');
      var viewIndex = slickIndex;
      if (slickIndex >= slideCount && currentIndex > 0 && currentIndex < (slideCount - 1)) {
        viewIndex = 0;
      }
      if (slickIndex == -1 && currentIndex < (slideCount - 1) && currentIndex > 0 ) {
        viewIndex = slideCount - 1;
      }
      if (slickIndex == (slideCount - 1) && currentIndex == 0) {
        viewIndex = currentIndex - 1;
      }
      if (slickIndex == 0 && currentIndex == (slideCount - 1)) {
        viewIndex = currentIndex + 1;
      }
      if ((slickIndex == 0 && currentIndex == (slideCount - 2))
      || (slickIndex == 1 && currentIndex == (slideCount - 1))
      || (slickIndex == slideCount && currentIndex == (slideCount - 2))) {
        viewIndex = currentIndex + 2;
      }
      if ((slickIndex == -1 && currentIndex == (slideCount - 1)) 
        || (slickIndex == slideCount && currentIndex == 0)) {
        viewIndex = currentIndex;
      }
      if ((slickIndex == -1 || slickIndex == (slideCount - 1)) && currentIndex == 1) {
        viewIndex = currentIndex - 2;
      }
      $(self.$photoView).slick("slickGoTo",viewIndex,false);
      return false;
    });
  };

  /**
   * init commission
   *
   * commission = 仲介手数料
   *
   */
  _search.detail.modal.prototype.initCommission = function () {

    var self = this;

    self.$el.find('.commission-overlaywrap .floatbox')
      .css({position: 'fixed'});

      self.$el.find('.modal-commission-rent').on('click', function (e) {

        e.preventDefault();
  
        self._zoomCommission(self.$el.find('.commission-rent-overlaywrap'));
      });
  
      self.$el.find('.modal-commission-building-conditions').on('click', function (e) {
  
        e.preventDefault();
  
        self._zoomCommission(self.$el.find('.commission-building-overlaywrap'));
      });
  
      self.$el.find('.modal-commission-buy').on('click', function (e) {

      e.preventDefault();

      self._zoomCommission(self.$el.find('.commission-buy-overlaywrap'));
    });

  };

  /**
   * init around photo
   *
   */
  _search.detail.modal.prototype.initAroundPhoto = function () {

    var self, $clickTargets;

    self = this;
    $clickTargets = self.$el.find('div.around td a');

    // add listener
    // zoom
    $clickTargets.on('click', function (e) {

      e.preventDefault();

      self._setTextAroundPhoto($(e.target).parent());
      self._zoomAroundPhoto(self.$aroundOverlayWrapper);
    })

  };

  /**
   * set header text (swiper & modal)
   *
   * @param $targetSlide
   * @private
   */
  _search.detail.modal.prototype._setTextAroundPhoto = function ($targetSlide) {

    var self, $source, $floatbox, title, caption;

    self = this;

    // get content
    $source = $targetSlide.find('a');

    title = $source.prop('title');
    caption = $source.data('caption');

    // rewrite modal text
    $floatbox = self.$aroundOverlayWrapper.find('.floatbox');

    $floatbox.find('.photo-zoom img').prop('src', $source.prop('href'));
    $floatbox.find('.tx-heading').text(title);
    $floatbox.find('.tx-caption').text(caption);

  };

  /**
   * zoom
   *
   * @param $targetOverlayWrapper
   * @private
   */
  _search.detail.modal.prototype._zoomAroundPhoto = function ($targetOverlayWrapper) {

    var self, $boverlay, $floatbox;

    self = this;

    $boverlay = $targetOverlayWrapper.find('.box-overlay');
    $floatbox = $boverlay.siblings('.floatbox');

    $floatbox.css({
      top: $(window).scrollTop() + 20,
      position: 'absolute'
    });
    // add listener
    $floatbox.find('.btn-modal-close').on('click', {self: self}, self.closeNearbyInformation);
    $boverlay.on('click', {self: self}, self.close);

    $boverlay.fadeIn(100, function () {
      $floatbox.show();
    })
  };

  /**
   * zoom
   *
   * @param $targetOverlayWrapper
   * @private
   */
  _search.detail.modal.prototype._zoomCommission = function ($targetOverlayWrapper) {

    var self, $boverlay, $floatbox;

    self = this;

    $boverlay = $targetOverlayWrapper.find('.box-overlay');
    $floatbox = $boverlay.siblings('.floatbox');

    $floatbox.css({
      top: $(window).scrollTop() + 20,
      position: 'absolute'
    });
    // add listener
    $floatbox.find('.btn-modal-close').on('click', {self: self}, self.close);
    $boverlay.on('click', {self: self}, self.close);

    $boverlay.fadeIn(100, function () {
      $floatbox.show();
    })
  };

  /**
   * set header text (swiper & modal)
   *
   * @param $targetSlide
   * @private
   */
  _search.detail.modal.prototype._setText = function ($targetSlide) {

    var self, $source, $floatbox, title, caption;

    self = this;

    // get content
    $source = $targetSlide.find('a');

    title = $source.prop('title');
    caption = $source.data('caption');

    // rewrite modal text
    $floatbox = self.$swiperOverlayWrapper.find('.floatbox');

    $floatbox.find('.photo-zoom img').prop('src', $targetSlide.find('img').prop('src'));
    $floatbox.find('.tx-heading').text(title);
    $floatbox.find('.tx-caption').text(caption);

    // set swiper number
    var slickIndex = $targetSlide.data('slick-index');
    var now = parseInt(slickIndex) + 1;
    var total = self.$swiperNumber.find('.photo-slider-num-total').text();
    $floatbox.find('.photo-slider-num-now').text(now);
    $floatbox.find('.photo-slider-num-total').text(total);

    $('#tx-fadeout').fadeIn();
    setTimeout(function(){
      $('#tx-fadeout').fadeOut();
    }, 5000);

    // if (isAroundPhoto) {
    //   return;
    // }

    // rewrite swiper text
    // self.$swiperCaption.find('.photo-slider-caption-heading').text(title);
    // self.$swiperCaption.find('.photo-slider-caption-tx').text(caption);
  };

  _search.detail.modal.prototype.initListModal = function () {
    var self, targetSlide, imgUrl, total;
    self = this;

    //画像の総数をセット
    total = self.$swiperNumber.find('.photo-slider-num-total').text();
    self.$photoModalList.find('.photo-slider-num-total').text(total);

    for (var i = 0; i < total; ++i) {
      targetSlide = self.$el.find('[data-slick-index="' + i + '"]');
      imgUrl = targetSlide.find('img').prop('src');
      self.$photoModalList.find('.modal-list').append('<li><a><img src=' + imgUrl + ' alt=""  data-slick-index="' + i + '"></a></li>');
    }
    self.$photoModalList.find('img').addClass('photo-modal-list-img');

    self.$photoModalList.find('a').on('click', function (e) {
      var $slickIndex = $(e.target).data('slick-index');
      var $targetSlide = self.$el.find('[data-slick-index="' + $slickIndex + '"]');

      self.$photoModalList.find('.box-overlay').hide();
      self.$photoModalList.find('.floatbox').hide();
      
      self._setText($targetSlide);
      self._zoom(self.$swiperOverlayWrapper);

    // ATHOME_HP_DEV-5212 【フロント】【機能系】【SP】サムネイル画像拡大モーダルで「×」ボタン押下時に、
    // 表示中の画像ではなく拡大モーダルを開いた際のサムネイル画像が表示される
      $(self.$photoView).slick("slickGoTo",$slickIndex,false);
      $(self.$photoNav).slick("slickGoTo",$slickIndex,false);

      return false;
    });
  };

  /**
   * zoom
   *
   * @param $targetOverlayWrapper
   * @private
   */
  _search.detail.modal.prototype._zoom = function ($targetOverlayWrapper) {

    var self, $boverlay, $floatbox;

    self = this;

    $boverlay = $targetOverlayWrapper.find('.box-overlay');
    $floatbox = $boverlay.siblings('.floatbox');

    $floatbox.css({
      top: $(window).scrollTop(),
      position: 'absolute'
    });
    // add listener
    $floatbox.find('.btn-modal-close').on('click', {self: self}, self.close);
    $boverlay.on('click', {self: self}, self.close);

    $boverlay.fadeIn(100, function () {
      $floatbox.show();
    });

    if ($floatbox.find('.photo-slider-num-total').text() < 2) {
        $floatbox.find('.btn-move').hide();
    }

    //拡大表示モーダルの前へボタン押下時のイベントリスナー
    $floatbox.find('.prev').off('click');
    $floatbox.find('.prev').on('click', function () {
      var zoomnow, zoomprev, targetSlide, floatboxl, dataSlickIndex, headingcaption, title, caption;

      floatboxl = self.$el.find('.photo-gallery-overlaywrap .floatbox');
      zoomnow = floatboxl.find('.photo-slider-num-now').text();

      //スライド枚数の分子の更新
      if ( zoomnow < 2 ) {
        zoomprev = floatboxl.find('.photo-slider-num-total').text();
      } else {
        zoomprev = parseInt(zoomnow) - 1;
      };
      floatboxl.find('.photo-slider-num-now').text(zoomprev);

      //画像URLと種別とキャプションの更新
      dataSlickIndex = parseInt(zoomprev) - 1;
  
      targetSlide = self.$swiper.find('[data-slick-index="' + dataSlickIndex + '"]');
      floatboxl.find('.photo-zoom img').prop('src', targetSlide.find('img').prop('src'));

      headingcaption = targetSlide.find('a');
      title = headingcaption.prop('title');
      caption = headingcaption.data('caption');
      floatboxl.find('.tx-heading').text(title);
      floatboxl.find('.tx-caption').text(caption);

      // ATHOME_HP_DEV-5212 【フロント】【機能系】【SP】サムネイル画像拡大モーダルで「×」ボタン押下時に、
      // 表示中の画像ではなく拡大モーダルを開いた際のサムネイル画像が表示される
      self.$mainArea.find('.btn-move .prev').trigger('click');
    });

    //拡大表示モーダルの次へボタン押下時のイベントリスナー
    $floatbox.find('.next').off('click');
    $floatbox.find('.next').on('click', function () {
      var zoomnow, zoomnext, zoomtotal, floatboxl, dataSlickIndex, targetSlide, headingcaption, title, caption;

      floatboxl = self.$el.find('.photo-gallery-overlaywrap .floatbox');
      zoomnow = floatboxl.find('.photo-slider-num-now').text();
      zoomtotal = floatboxl.find('.photo-slider-num-total').text();

      //スライド枚数の分子の更新
      if ( zoomnow == zoomtotal ) {
        zoomnext = 1;
      } else {
        zoomnext = parseInt(zoomnow) + 1;
      };
      floatboxl.find('.photo-slider-num-now').text(zoomnext);

      //画像URLと種別とキャプションの更新
      dataSlickIndex = parseInt(zoomnext) - 1;
 
      targetSlide = self.$swiper.find('[data-slick-index="' + dataSlickIndex + '"]'); 
      floatboxl.find('.photo-zoom img').prop('src', targetSlide.find('img').prop('src'));

      headingcaption = targetSlide.find('a');
      title = headingcaption.prop('title');
      caption = headingcaption.data('caption');
      floatboxl.find('.tx-heading').text(title);
      floatboxl.find('.tx-caption').text(caption);

      // ATHOME_HP_DEV-5212 【フロント】【機能系】【SP】サムネイル画像拡大モーダルで「×」ボタン押下時に、
      // 表示中の画像ではなく拡大モーダルを開いた際のサムネイル画像が表示される
      self.$mainArea.find('.btn-move .next').trigger('click');
    });

    self.$el.find('.btn-modal-list').on('click', function (e) {
      e.preventDefault();

      self.$el.find('.photo-modal-list .floatbox')
        .css({position: 'absolute'});
      self._zoomlist(self.$el.find('.photo-modal-list'));

    })
  };

  /**
   * zoomlist
   *
   * @param $targetOverlayWrapper
   * @private
   */
  _search.detail.modal.prototype._zoomlist = function ($targetOverlayWrapper) {

    var self, $boverlay, $floatbox;

    self = this;

    $boverlay = $targetOverlayWrapper.find('.box-overlay');
    $floatbox = $boverlay.siblings('.floatbox');

    $floatbox.css({
      top: $(window).scrollTop(),
      position: 'absolute'
    });
    // add listener
    $floatbox.find('.btn-modal-close').on('click', {self: self}, self.close);
    $boverlay.on('click', {self: self}, self.close);

    $boverlay.fadeIn(100, function () {
      $floatbox.show();
    });
  };

  /**
   * close (all)
   *
   * @param e
   */
  _search.detail.modal.prototype.close = function (e) {

    var $overlay = e.data.self.$el.find('.box-overlay');

    $overlay.fadeOut(100, function () {
      $overlay.siblings('.floatbox').hide();
    });
    // ATHOME_HP_DEV-5212 【フロント】【機能系】【SP】サムネイル画像拡大モーダルで「×」ボタン押下時に、
    // 表示中の画像ではなく拡大モーダルを開いた際のサムネイル画像が表示される
    var floatboxl = e.data.self.$el.find('.photo-gallery-overlaywrap .floatbox');
    var $slickIndex = floatboxl.find('.photo-slider-num-now').text() - 1;
    $(e.data.self.$photoView).slick("slickGoTo",$slickIndex,false);
    $(e.data.self.$photoNav).slick("slickGoTo",$slickIndex,false);
  };

  /**
   * closeNearbyInformation (周辺情報)
   *
   * @param e
   */
  _search.detail.modal.prototype.closeNearbyInformation = function (e) {
    // ATHOME_HP_DEV-5230 【フロント】【画面系】【SP】周辺画像モーダルを閉じると、サムネイル画像まで画面が移動する
    // 周辺情報の「閉じる」ボタンをクリックした場合は移動しない
    var $overlay = e.data.self.$el.find('.box-overlay');

    $overlay.fadeOut(100, function () {
      $overlay.siblings('.floatbox').hide();
    });
  };


  _search.detail.contact = function (config) {

    if (!(this instanceof _search.detail.contact)) {
      return new _search.detail.contact(config);
    }

    this.$el = config.$el;
  };

  _search.detail.contact.prototype.run = function () {

    var self;
    self = this;

    self.$el.find('.btn-mail-contact').on('click', function (e) {

      var params;

      e.preventDefault();

      params = {
        id: [app.location.bukkenId()],
        type: app.location.currentShumoku()
      };

      // 地図検索からのアクセス
      if (search_config.from_searchmap) {
        params['from_searchmap'] = true;
      }

      /* スマホはおすすめ物件エレメントなし
       // おすすめ物件からのアクセス
       if (search_config.from_recommend) {
       params['from-recommend'] = true;
       }
       */
      var href = (typeof e.target.href != 'undefined') ? e.target.href : location.origin + self.$el.find('.btn-mail-contact a').attr('href');
      app.request.postForm(app.location.setHttpsProtocol(href), params, true);

    });
  };

  /**
   * 情報の見方
   *
   * @param config
   * @returns {_search.detail.howtoinfo}
   */
  _search.detail.howtoinfo = function (config) {

    if (!(this instanceof _search.detail.howtoinfo)) {
      return new _search.detail.howtoinfo(config);
    }

    this.$el = config.$el;
  };

  /**
   * 実行
   */
  _search.detail.howtoinfo.prototype.run = function () {

    /**
     * addEventListener
     */
    this.$el.find('.link-howto').on('click', function (e) {

      var href, params;

      href = e.target.href;
      params = {
        type: app.location.currentShumoku() || search_config.shumoku
      };
      app.request.postForm(href, params, true);
      return false;
    });
  };


    /**
     * アコーディオン
     *
     * @param config
     * @returns {_search.detail.howtoinfo}
     */
    _search.detail.accordion = function (config) {

        if (!(this instanceof _search.detail.accordion)) {
            return new _search.detail.accordion(config);
        }

        this.$el = config.$el;
    };

    /**
     * 実行
     */
    _search.detail.accordion.prototype.run = function () {

        var count = 200;
        var omit = "...";
        var moreText  = "続きはこちら";
        var closeText = "閉じる";

        var $tableArticleInfo = this.$el.find('.contents-article .table-article-info');

        $tableArticleInfo.find('.article-accordion').each(function() {
            var content = $(this).html();

            if(content.length > count) {
                var c = content.substr(0, count);
                var h = content.substr(count, content.length - count);
                var html = c + '<div class="hidden-content">' + h + '</div>' + '<span class="omit">' + omit + '</span> <a href="" class="morelink">' + moreText + '</a>';
                $(this).html(html);
            }
        });

        // 初期非表示
        $tableArticleInfo.find('.hidden-content').hide();

        // 開閉
        $tableArticleInfo.find(".morelink").click(function(){
            if($(this).hasClass("less")) {
                $(this).removeClass("less");
                $(this).html(moreText);

                if( $(this).closest('tr').height() >= $(window).height() ){
                    window.scrollTo( 0, $(this).closest('tr').offset().top ) ;
                }

            } else {
                $(this).addClass("less");
                $(this).html(closeText);
            }

            $(this).prevAll(".omit").slideToggle();
            $(this).prevAll(".hidden-content").slideToggle(
                "normal",
                function(){
                    if($(this).nextAll(".morelink").hasClass("less")) {
                        $(this).css('display','inline');
                    }
                }
            );

            return false;
        });
    };


    _search.personal.tab = function (config) {

    if (!(this instanceof _search.personal.tab)) {
      return new _search.personal.tab(config);
    }

    this.$el = config.$el;

    //this.HTML_NO_ARTICLE = '<div class="element"><p class="element-tx">該当の物件がありません。</p></div>';

    this.$baseTabArea = this.$el.find('.element-search-tab');
    this.$baseTabList = this.$baseTabArea.find('li');
    this.$childTabAreaList = this.$el.find('.element-search-tab4');
    this.$articleList = this.$el.find('.list-fav, .list-history');
  };

  _search.personal.tab.prototype.run = function () {

    var self;
    self = this;

    // add listener
    // switch chintai or baibai
    self.$baseTabArea.on('click', function (e) {

      var $clicked, className, $childTabList, $childTab, $targetList;

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
    self.$childTabAreaList.on('click', function (e) {
      var $clicked, $parent, className, $targetList;

      e.preventDefault();

      if (e.target.nodeName.toLowerCase() !== 'a') {
        return;
      }

      // Activeタブクリック時何もしない
      if($(e.target).closest('li').hasClass('active')) {
        return;
      }

      // set form
      $("[name=sort]").val('asc');
      name = self._getClassName($(e.target));
      $("#hide-checklist-tab").val(self._getClassName($(".element-search-tab li.active")));
      $("#hide-search-tab").val(name);

      $("#personalsort").submit();  

      return;
    });

    // init
    // self.$baseTabArea.find('.active a')[0].click();
  };

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

  _search.personal.bottomBtn = function (config) {

    if (!(this instanceof _search.personal.bottomBtn)) {
      return new _search.personal.bottomBtn(config);
    }

    this.HTML_NO_ARTICLE = '<div class="element"><p class="element-tx">該当の物件がありません。</p></div>';

    this.$el = config.$el;
    //this.$baseTabArea = this.$el.find('.element-search-tab');
    //this.$baseTabList = this.$baseTabArea.find('li');
    this.$childTabAreaList = this.$el.find('.element-search-tab4');
    this.$articleList = this.$el.find('.list-fav, .list-history');
  };

  _search.personal.bottomBtn.prototype.run = function () {

    var self = this;

    // add listener
    // add favorite
    self.$el.find('.btn-fav-add').on('click', function (e) {

      var $currentList, $checked, $articles;

      // get current list
      $currentList = self.$articleList.filter(function (i) {
        return !self.$articleList.eq(i).is(':hidden');
      });

      if ($currentList.length !== 1) {

        return;
      }

      // get bukkenId
      $checked = $currentList.find('.object-check :checkbox:checked');

      if ($checked.length < 1) {
        alert('物件が1つもチェックされていません');
        return;
      }

      $articles = $checked.closest('.article-object');

      // save in cookie
      var list = [];
      $.each($articles, function (i, v) {
        app.cookie.updateFavorite({bukkenId: $articles.eq(i).data('bukken-no')});
        list.push($articles.eq(i).data('bukken-no'));
      });

      // 行動情報を保存
      app.cookie.saveOperation('updateFavorite', list);

      alert('お気に入りに登録しました');
      return false;
    });

    // add listener
    // delete favorite
    self.$el.find('.btn-delete').on('click', function (e) {

      var $currentList, $checkedList, $articles, count, $count,
        list = [];

      // get current list
      $currentList = self.$articleList.filter(function (i) {
        return self.$articleList.eq(i).is(':visible');
      });

      if ($currentList.length !== 1) {
        return false;
      }

      // get bukkenId
      $checkedList = $currentList.find('.object-check :checked');

      if ($checkedList.length < 1) {
        alert('物件が1つもチェックされていません');
        return false;
      }

      if (!confirm('これら物件をお気に入りリストから削除します。よろしいですか？')) {
        return false;
      }

      $articles = $checkedList.closest('.article-object');

      $.each($articles, function (i, v) {
        list.push($articles.eq(i).data('bukken-no'));
      });

      // delete element
      $articles.remove();

      // delete from cookie
      app.cookie.deleteFavorite(list);

      // update count
      count = $currentList.find('.article-object').length;

      if (count < 1) {
        $currentList.html(self.HTML_NO_ARTICLE);
      }

      $count = self.$childTabAreaList.find('.active:visible a');
      $count.text($count.text().replace(/\（.+?\）/, '（' + count + '件）'));

      return false;
    });

    // add listener
    // contact
    self.$el.find('.btn-contact').on('click', function (e) {

      var $currentList, $checkedList, $articles, params, parsed, dirs, i,
        list = [];

      e.preventDefault();

      // get current list
      $currentList = self.$articleList.filter(function (i) {
        return !self.$articleList.eq(i).is(':hidden');
      });

      if ($currentList.length !== 1) {

        return;
      }

      // get bukkenId
      $checkedList = $currentList.find('.object-check :checkbox:checked');

      if ($checkedList.length < 1) {
        alert('物件が1つもチェックされていません');
        return;
      }

      $articles = $checkedList.closest('.article-object');

      $.each($articles, function (i, v) {
        list.push($articles.eq(i).data('bukken-no'));
      });

      params = {
        id: list
      };

      // get shumoku ct
      parsed = app.location.parseUrl($articles.eq(0).find('.object-body a').attr('href'));
      dirs = parsed.pathname.split('/');
      for (i = 0; i < dirs.length; i++) {
        if (typeof dirs[i] === 'string' && dirs[i] !== '') {
          params.type = dirs[i];
          break;
        }
      }

      app.customConsoleLog('----- お問い合わせパラメータ start -----');
      app.customConsoleLog(params);
      app.customConsoleLog('----- お問い合わせパラメータ end -----');

      // post
      app.request.postForm(app.location.setHttpsProtocol(self.$childTabAreaList.find('.active:visible a').attr('href')), params, true);

      return false;
    });

  };

  _search.personal.sort = function (config) {

    if (!(this instanceof _search.personal.sort)) {
      return new _search.personal.sort(config);
    }

    this.$el = config.$el;
  };

  _search.personal.sort.prototype.run = function () {

    this._addListener();
    this._init();
  };

  _search.personal.sort.prototype._addListener = function () {

    var self, $wrapper;

    self = this;

    $wrapper = self.$el.find('.sort-select');

    $wrapper.find('select').on('change', function (e) {

      var cursort = $("#personalsort").find('select').attr('cursort');
      var sort = $('[name=sort]  option:selected').val();
      if(cursort == sort) {
        return;
      }
      var searchtab = $('.element-search-tab4:visible').find('.active').eq(0);
      $(searchtab).removeClass('active');
      $("#hide-search-tab").val($(searchtab).attr('class'));

      var chklisttab = $('.element-search-tab').find('.active').eq(0);
      $(chklisttab).removeClass('active');
      $("#hide-checklist-tab").val($(chklisttab).attr('class'));

      $("#personalsort").submit();

      return;
    });
  };

  _search.personal.sort.prototype._init = function () {

    // Load時に並び替え
    this.$el.find('.sort-select select').trigger('change');
  };
  var clearSuggest = function ($searchText) {
    if (detecedDevice()) {
        $searchText.parent().find('.suggesteds').css({'display' : 'none'});
    }
  }
})
();

//物件リクエスト プレビュー用
$(function () {
  $('.element-recommend-caro').slick({
    dots:           true,
    arrows:         true,
    infinite:       false,
    speed:          500,
    slidesToShow:   2,
    slidesToScroll: 2,
    autoplay:       false,
    autoplaySpeed:  2000
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

  var height = $(window).height();
  var preHeight = 0;
  var curHeight = 0;
  $(window).on('resize', function() {
      curHeight = $(this).height();
      if (curHeight == height && preHeight < height) {
          $('input[name="search_filter[fulltext_fields]"]').blur();
          $('.suggesteds').empty();
      }
      preHeight = $(this).height();
      
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
    })

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
    $(".block-large-category .block-large-heading-dl, .link-other-category .link-other-category-dl").click(function() {
        $(this).find('dt').toggleClass("block-open");
        $(this).parent().find('h3').toggleClass("hidden-large");
        $(this).toggleClass("hidden-large");
        $(this).parent().find('div:nth-child(2)').toggle("fast");
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

var isIpSafari = /iPhone/.test(navigator.userAgent) && !window.MSStream && !!navigator.userAgent.match(/Version\/12+.*Safari/);
var supportsOrientationChange = "onorientationchange"in window
  , orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";
window.addEventListener(orientationEvent, function(event) {
  event.preventDefault();
  if (isIpSafari) {
    setTimeout(function() {
      var position_current = $(window).scrollTop() + $(window).height();
      if (position_current > $(document).height()) {
        $(window).scrollTop(999999);
      }
    }, 400)
  }
}, false);
document.addEventListener("touchstart", function(event) {
    if(event.touches.length > 1 ) {
        event.preventDefault();
        event.stopPropagation(); // maybe useless
    }
}, {passive: false});

// fix sticky button overlay
$(function(){
  var target = $('.fixed-pagefooter');
  if (target.length){
    var buttonHeight = target.outerHeight(true);
    document.body.style.paddingBottom = buttonHeight +'px';
  } 
})
