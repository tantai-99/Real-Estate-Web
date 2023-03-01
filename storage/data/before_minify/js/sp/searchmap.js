

searchmap = new function () {
    'use strict';

    var MAP_MIN_ZOOM         = 9;  // 地図倍率の最小
    var MAP_MAX_ZOOM         = 21; // 地図倍率の最大
    var MAP_MAPPING_MAX_ZOOM = 15; // 物件をマッピングする最大の地図倍率
    var MAP_DEFAULT_ZOOM     = 16; // 地図

    var DEBUG_DISP_MARKAREA  = false;
    var DEBUG_DISP_ALLBUKKEN = false;

    var EVENT_MAPUPDATE_FOR_DEFAULT           = 1; //
    var EVENT_MAPUPDATE_FOR_PREF              = 2; //
    var EVENT_MAPUPDATE_FOR_CITY              = 3; //
    var EVENT_MAPUPDATE_FOR_SIDE_COND         = 4; //
    var EVENT_MAPUPDATE_FOR_MODAL_COND_RESULT = 5; //
    var EVENT_MAPUPDATE_FOR_MODAL_COND        = 6; //

    var WAIT_MS = 750;

    this.run = function (_app,search,config) {
        var smap = searchmap;

        smap.app     = _app;
        smap.search  = search;

        smap.config         = config;
        smap.searchConfig   = config.search_config;
        smap.el             = config.$el;
        //smap.$loading       = smap.el.find('.loading');
        smap.$overlay       = smap.el.find('.box-overlay');
        smap.$floatbox      = smap.$overlay.next('.floatbox');
        smap.$modalWindows  = smap.$floatbox.find('.contents-iframe');
        smap.$disabledElems = null;

        smap.apiData           ={};
        smap.shumoku           = smap.app.location.currentShumoku();
        smap.isSpecial         = smap.search.pageType.isSpecialCategory();
        smap.specialPath       = smap.app.location.currentSpecialPath();

        smap.takeoverZoom      = null;

        smap.location          ={};

        // 地図物件リスト
        smap.mapBukkenlist.init();

        // 地図物件スライダー
        smap.mapslider.init();

        // 条件検索・市区郡変更
        smap.condition.init();

        smap.loading.init();

        smap.status =[];
        smap.status.initialized =false;
        smap.status.updating =false;

        smap.event={};
        smap.event.mapupdate = EVENT_MAPUPDATE_FOR_DEFAULT;

        smap.modal.init();
        smap.popup.init();

        smap.ua = navigator.userAgent.toLowerCase();
        smap.listinScrolledBottom();

        smap.pathname = '/'+smap.shumoku;
        if(smap.isSpecial){
            smap.pathname = '/'+smap.specialPath;
        }

        // 条件絞りこみ画面から検索
        if($(':hidden[name="map_condition"]').length>=1){
            var center = $(':hidden[name="center"]').val();
            var zoom   = parseInt($(':hidden[name="zoom"]').val());

            smap.location.ken      = smap.app.location.currentPrefecture();
            smap.location.shikugun = (smap.app.location.currentUrl.split('/')[6]).split('-')[0];
            smap.pathname += '/'+smap.location.ken+'/result/'+smap.location.shikugun+'-map.html'
            smap.takeoverZoom = zoom;

            center = center.split(':');
            var lat = center[0];
            var lng = center[1];

            smap.gmap =createGoogleMap(smap,lat,lng);

            initMappingData(smap);
            firstPositionEventListner(smap);
            smap.status.initialized = false;

        }

        //現在地から検索（スマホのみ）
        else if((smap.app.location.currentUrl.split('/')[4]=='here')){
            smap.pathname += '/here/result/here-map.html'

            // マップ中心位置
            mapCenterAtHereProc(smap);

        // 市区郡から検索
        }else{
            smap.location.ken      = smap.app.location.currentPrefecture();
            smap.location.shikugun = (smap.app.location.currentUrl.split('/')[6]).split('-')[0];
            smap.pathname += '/'+smap.location.ken+'/result/'+smap.location.shikugun+'-map.html'
            // マップ中心位置
            mapCenterProc(smap);
        }
    }


    /******************************
     * GoogleMap イベントハンドラ
     ******************************/
    // 中心値の変更
    var  centerChanged = function () {
        //console.log("center_changed");
    }

    // タイルロード
    var tilesloaded = function () {
        //console.log("tilesloaded");
        var smap = searchmap;
    }

    // バウンド変更
    var boundsChanged = function () {

        var smap = searchmap;
        if (smap.status.initialized == false) {
            //console.log("boundsChanged");
            updateMap(smap);
        }
    }

    // ズーム変更
    var zoomChanged = function () {

        var smap = searchmap;

        updateMap(smap);
        smap.mapslider.closeList();

        //console.log(smap.gmap.getZoom());
    }

    // ドラッグ中
    var drag = function () {
        //console.log("drag");
    }

    // ドラッグ終了
    var dragend = function () {
        var smap = searchmap;
        //console.log("dragend");
        updateMap(smap);
        smap.mapslider.closeList();

    }

    //クリック時
    var click = function () {
        removeClickedMarker();
        var smap = searchmap;
        smap.mapslider.closeList();
    }

    var clickedMarker = null

    // マーカクリック
    var markerClicked = function (marker, markerIdx) {
        var smap = searchmap;
        google.maps.event.addListener(marker, 'click', function () {
            removeClickedMarker();
            //クリックされたマーカー
            var marker = smap.mappingData.markers[markerIdx];

            var clickedMarkerOption = smap.mappingData.markersOptionClickedMarker
            clickedMarkerOption.position = marker.latlng
            clickedMarkerOption.zIndex = 2
            clickedMarker = new google.maps.Marker(clickedMarkerOption)
            //マーカーに紐けられた物件で、サイドの物件リストを更新する
            smap.mapslider.updateSideBukkenList(marker);

        });
    }

    var removeClickedMarker = function () {
        if (clickedMarker != null) {
            clickedMarker.setMap(null);
            clickedMarker = null;
        }
    }

    var infoWindowDomeready = function (infoWindow) {
        var smap = searchmap;

        google.maps.event.addListener( infoWindow , 'domready' , function(){

            // 物件件数の吹き出し
            ajastMarkerSummaryInfo();

        });
    }

    var ajastMarkerSummaryInfo = function () {
        $('.marker-summary-info').css({'color':'red'})
        $('.marker-summary-info').parents('.gm-style-iw').parent().css({'pointer-events':'none'});
        $('.marker-summary-info').parents('.gm-style-iw').prev().remove();
        $('.marker-summary-info').parents('.gm-style-iw').next().remove();

    }


    var initMappingData = function (smap) {
        smap.mappingData ={};
        smap.mappingData.tileAreas = [];
        smap.mappingData.markers = [];
        smap.mappingData.markersOption = [];
        smap.mappingData.markersOptionClickedMarker = [];

        // マーカーを生成
        var markerOption,markerIcon;
        var anchorPointY = 15;
        if(isSafari()){ anchorPointY -= 6;}
        var anchorPoint = new google.maps.Point(11, anchorPointY);
        var markerLabelOrigin = new google.maps.Point(5, 5);

        markerIcon = {url: "/sp/imgs/icon_map_bukken.png", labelOrigin: markerLabelOrigin};
        markerOption = {map: smap.gmap, anchorPoint: anchorPoint, icon: markerIcon};
        smap.mappingData.markersOption = markerOption;

        markerIcon = {url: "/sp/imgs/icon_map_click.png", labelOrigin: markerLabelOrigin};
        markerOption = {map: smap.gmap, anchorPoint: anchorPoint, icon: markerIcon};
        smap.mappingData.markersOptionClickedMarker = markerOption;

        // 最初の位置
        smap.mappingData.firstCenter=smap.gmap.center;

    }

    var mapCenterProc = function (smap) {

        var data = {
            'ken_ct': smap.location.ken,
            'shikugun_ct': smap.location.shikugun
        };

        if(smap.shumoku){
            data.type_ct = smap.shumoku;
        }
        if(smap.isSpecial){
            data.special_path=smap.specialPath;
        }


        // 地図の中心位置を取得する
        smap.api.addCallback('mapCenterByCity', mapCenterCallback);
        smap.api.mapCenterByCity(data);

    }

    // 地図中心位置取得ハンドラ
    var mapCenterCallback = function (smap) {

        // googl map object
        var mapCenterData = smap.apiData.mapCenterData;
        smap.gmap =createGoogleMap(smap,mapCenterData.lat,mapCenterData.lng);
        initMappingData(smap);
        firstPositionEventListner(smap);
        smap.status.initialized = false;

    }


    var mapCenterAtHereProc = function (smap) {

        //デバイスから取得する
        if (!navigator.geolocation) {
            alert('お使いの端末は現在位置の取得に対応していません。');
            return false;
        }

        // オプション・オブジェクト
        var optionObj = {
            "enableHighAccuracy": true ,
            "timeout": 10000 ,
            "maximumAge": 5000
        } ;

        // 現在位置の取得
        navigator.geolocation.getCurrentPosition(
            function(position) {

                // 緯度経度の取得
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                smap.gmap =createGoogleMap(smap,lat,lng);
                initMappingData(smap);
                firstPositionEventListner(smap);

                setAreaByLatlng(smap,smap.gmap.center);

                smap.status.initialized = false;

            },
            function(error) {
                var errorMessage = {
                    0: "エラーが発生しました。" ,
                    1: "位置情報サービスが無効です。" ,
                    2: "位置情報を取得できませんでした。" ,
                    3: "位置情報の取得がタイムアウトしました。"
                } ;

                var smap = searchmap;
                smap.modal.dispLocationHereErrorModal(errorMessage[error.code]);


            },
            optionObj
        );
    }


    var setAreaByLatlng = function (smap,latlng) {
        var geocoder = new google.maps.Geocoder();

        geocoder.geocode({
            latLng: latlng

        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {

                var prefName = getPrefName(results);
                var pref = getPref(prefName);

                smap.location.ken = pref;

                smap.pathname = '/'+smap.shumoku+'/'+smap.location.ken+'/result/here-map.html'

            } else {
                console.log(status);
            }
        });
    }

    var getPrefName = function (geoCodeResults) {

        var geoCnt = geoCodeResults[0].address_components.length;
        var prefName = "";
        for (var i = 0; i < geoCnt; i++) {
            if (geoCodeResults[0].address_components[i].types[0] === "administrative_area_level_1") {
                prefName = geoCodeResults[0].address_components[i].long_name;
                return prefName;
            }
        }
        return prefName;
    }
    var getPref = function (prefName) {

        return 'tokyo';

    }

    // 地図先頭位置に戻る
    var firstPositionEventListner = function () {

        //地図先頭位置に戻る
        $('.map-main__back').on('click', function(){
            var smap=searchmap;
            smap.gmap.panTo(smap.mappingData.firstCenter);
            updateMap(smap)

        });

    }



    /** google map object
     *
     * @param smap
     * @returns {google.maps.Map}
     */
    var createGoogleMap = function (smap,lat,lng) {

        // マップ情報
        var mapZoom = (smap.takeoverZoom) ? smap.takeoverZoom : MAP_DEFAULT_ZOOM;

        // 地図中心緯度経度
        var mapCenterlatlng = new google.maps.LatLng(lat, lng);

        //キャンバスを準備
        var mapCanvas = smap.el.find('#parts_map_canvas3')[0];
        var mapHeight = $(window).height()-($('.page-header').outerHeight(true)+
                                            $('.map-option__list').outerHeight(true)+
                                            $('.map-option__change').outerHeight(true));
        smap.el.find('#parts_map_canvas3').css({'height':mapHeight+'px'});
        smap.scrollToBottom(100);


        //オプション設定
        var mapOpts = {};
        mapOpts.zoom = mapZoom;
        mapOpts.center =mapCenterlatlng;
        mapOpts.minZoom = MAP_MIN_ZOOM;
        mapOpts.maxZoom = MAP_MAX_ZOOM;
        mapOpts.fullscreenControlOptions={};
        mapOpts.fullscreenControlOptions.position=google.maps.ControlPosition.RIGHT_BOTTOM;

        // mapオブジェクト
        var gmap = new google.maps.Map(mapCanvas, mapOpts);

        // map event listner
        google.maps.event.addListener(gmap, 'zoom_changed', zoomChanged);
        google.maps.event.addListener(gmap, 'dragend', dragend);
        google.maps.event.addListener(gmap, 'bounds_changed', boundsChanged);
        google.maps.event.addListener(gmap, 'click', click);

        return gmap;
    }


    // マップを更新する
    var updateMap = function (smap) {
        removeClickedMarker()

        // ズーム率が規定値以下の場合は更新しない
        if (smap.gmap.getZoom() < MAP_MAPPING_MAX_ZOOM){

            smap.popup.dispMapsizeoverPopup();
            clearMarkers(smap);
            smap.status.updating =false;
            return;
        }
        smap.popup.closeMapsizeoverPopup();

        smap.status.updating = true;

        var bounds   = smap.gmap.getBounds();
        var swLatlng = bounds.getSouthWest();
        var neLatlng = bounds.getNorthEast();

        var data = {
            'type_ct':smap.shumoku,
            'sw_lat_lan': swLatlng.lat()+':'+swLatlng.lng(),
            'ne_lat_lan': neLatlng.lat()+':'+neLatlng.lng()
        };


        var condition = smap.aside.collectCondition();
        data.condition_side = condition.condition_side;

        //smap.aside.saveScrollPos();

        // 初回は都道府県・市区郡情報を渡す
        if( !smap.status.initialized ){
            data.ken_ct =smap.location.ken;
            data.shikugun_ct =smap.location.shikugun;
        }

        // 物件情報を取得する
        smap.api.addCallback('mapUpdate', mapUpdateCallback);
        smap.api.mapUpdate(data);

    }

    // マップ更新コールバック
    var mapUpdateCallback = function (smap) {

        // ズーム率が規定値以下の場合は更新しない
        if (smap.gmap.getZoom() < MAP_MAPPING_MAX_ZOOM){
            smap.popup.dispMapsizeoverPopup();
            clearMarkers(smap);
            smap.status.updating =false;
            return;
        }

        var mapBounds = smap.gmap.getBounds();

        var bukkens=[];
        var bukken,coordinates,latlng;
        var i,j,bukkenIdx=0;
        for (i = 0; i < smap.apiData.coordinates.length; i++) {
            coordinates = smap.apiData.coordinates[i];
            latlng = new google.maps.LatLng(coordinates.lat,coordinates.lng);
            if (!mapBounds.contains(latlng)){
                continue;
            }
            for (j = 0; j<coordinates.count; j++)  {
                bukkens[bukkenIdx] = [];
                bukkens[bukkenIdx]['latlng']   =latlng;
                bukkens[bukkenIdx]['bukkenId'] =coordinates.keys[j];
                bukkenIdx++;
            }
        }

        // すでに表示されているマーカーがあったら消す
        clearMarkers(smap);

        smap.mappingData.bukkens = bukkens;

        // マッピングする
        mapping( smap );

        smap.status.updating = false;

        smap.el.find('.map-main .tx-nohit').remove();
        if(smap.apiData.detachedHouse) {
            smap.el.find('#parts_map_canvas3').hide();
            smap.el.find('.map-main__back').hide();
            smap.el.find('.map-main').append(smap.apiData.detachedHouse);
        } else {
            smap.el.find('#parts_map_canvas3').show();
            smap.el.find('.map-main__back').show();
        }

        // アサイドを更新する
        var $aside = $(smap.apiData.aside).find('.map-change__inner');
        var $mapwrap = smap.el.find('.map-change__inner');
        $mapwrap.html($aside.html());

        smap.aside.eventListner();
        smap.aside.restoreScrollPos();

        // 物件件数の吹き出し
        ajastMarkerSummaryInfo();

        smap.status.initialized = true;
        //smap.search.list.aside(smap.el).run();

    }


    /**マッピングする
     *
     * @param mapData
     * @param bukkens
     */
    var mapping = function(smap) {
        var ICON_HAND = "https://maps.gstatic.com/mapfiles/openhand_8_8.cur";
        var bukkens = smap.mappingData.bukkens;

        if(bukkens.length <= 0){
            return;
        }

        // マーカー情報
        var markers = getMarkers(smap);
        var markerOption = smap.mappingData.markersOption;

        var i;
        for (i = 0; i < markers.length; i++) {

            markerOption.position = markers[i]['latlng'];
            markerOption.cursor = ICON_HAND;
            markerOption.zIndex = 1;
            markers[i]['marker'] = new google.maps.Marker(markerOption);

            // マーカークリックを登録
            markerClicked(markers[i]['marker'], i);
        }

        smap.mappingData.markers=markers;

    }


    /** マッピング用のマーカーを取得する
     *
     */
    var getMarkers = function(smap) {
        var bukkens = smap.mappingData.bukkens;
        var circleSize = { 15: 38.57, 16: 19.29, 17: 9.64, 18: 4.91, 19: 2.45 }
        var markers = [];
        for (var i = 0; i < bukkens.length; i++) {
            var markeredBukken = bukkens[i];
            var marker = {};
            marker['latlng'] = markeredBukken['latlng'];
            marker['bukkens'] = [];

            for (var j = 0; j < bukkens.length; j++) {
                var bukken = bukkens[j];
                //マーカを作成する物件とすべての物件の距離を計算し適切な距離なら物件情報を追加する
                var distance = google.maps.geometry.spherical.computeDistanceBetween(markeredBukken['latlng'], bukken['latlng']);
                if (distance <= circleSize[smap.gmap.getZoom()]) {
                    marker['bukkens'].push(bukken);
                }
            }
            markers.push(marker)
        }
        return markers;
    }

    // マーカーをクリアする
    var clearMarkers = function (smap) {
        // すでに表示されているマーカーがあったら消す
        var markers = smap.mappingData.markers;
        for (var i = 0; i < markers.length; i++) {
            markers[i]['marker'].setMap(null);
            if (markers[i]['rectangle']){
                markers[i]['rectangle'].setMap(null);
            }
        }
        smap.mappingData.markers=[];
        // すでに表示されているタイルがあったら消す
        var tileAreas = smap.mappingData.tileAreas;
        for (var i = 0; i < tileAreas.length; i++) {
            if (tileAreas[i]['rectangle']) {
                tileAreas[i]['bounds']=null;
                tileAreas[i]['rectangle'].setMap(null);
            }
        }
        smap.mappingData.tileAreas=[];
    }

    var isSafari = function(){
        var smap = searchmap;
        var isSafari = (smap.ua.indexOf('safari') > -1) && (smap.ua.indexOf('chrome') == -1);
        return isSafari;
    }

    var openDetailPage = function(href){
        var smap = searchmap;
        var data={};
        data.from_searchmap =true;
        if(smap.isSpecial){
            data["special-path"] = smap.specialPath;
        }
        smap.app.request.postForm(href, data, true);
    }

    this.scrollToBottom = function (duration) {
        var duration = duration||1500;

        $('body').delay(100).animate({
            scrollTop: $(document).height()
        },duration);
    }
    this.scrollToTop = function (duration) {
        var duration = duration||1500;
        $('body').delay(100).animate({
            scrollTop: 0
        },duration);
    }

    this.listinScrolledBottom = function (duration) {
        var smap = searchmap;
        $(window).on("scroll", function() {
            var scrollHeight = $(document).height();
            var scrollPosition = $(window).height() + $(window).scrollTop();
            if($(window).scrollTop()<0){
                smap.mapslider.closeList();
                smap.scrollToBottom(100);
            }
        });
    }

    // モーダルクラス
    this.modal = new function () {
        this.msgModal = null;
        this.locationHereErrModal = null;
        this.init = function () {
        }

        this.closeModal = function () {
            var smap = searchmap;
        }

        this.dispMessageModal = function(title,msg){
            var smap = searchmap;
            var modalName = 'modal-streetview__error';

            smap.el.find('.box-overlay').after('<div class='+modalName+'></div>');
            this.msgModal = smap.el.find('.'+modalName);

            this.msgModal.append(
                '<div class="box-overlay"></div>' +
                '<div class="floatbox" style="top: 100px; position: absolute;">' +
                '<div class="inner">' +
                '<p class="floatbox-heading">'+title+'</p>' +
                '<div class="floatbox-tx">' +
                    msg +
                '</div>' +
                '<p class="btn-modal-close">閉じる</p>' +
                '</div>' +
                '</div>'
            );

            this.msgModal.find('.box-overlay').fadeIn();
            this.msgModal.find('.floatbox').fadeIn();
            var left = ($('body').width()-this.msgModal.find('.floatbox').outerWidth(true))/2;
            this.msgModal.find('.floatbox').css({'left':left});

            if($(window).scrollTop()<=0){
                var top = ($(window).height()-this.msgModal.find('.floatbox').outerHeight(true))/2;
                this.msgModal.find('.floatbox').css({'top':top});
            }else{
                var bottom = ($(window).height()-this.msgModal.find('.floatbox').outerHeight(true))/2;
                bottom -= ($('body').height()-$(window).height());
                this.msgModal.find('.floatbox').css({'top':''});
                this.msgModal.find('.floatbox').css({'bottom':bottom});
            }

            //smap.scrollToTop(10);

            this.msgModal.find('.btn-modal-close').off();
            this.msgModal.find('.btn-modal-close').on('click',function(){
                smap.modal.msgModal.find('.box-overlay').fadeOut();
                smap.modal.msgModal.find('.floatbox').fadeOut();
                smap.modal.msgModal.remove();
                smap.scrollToBottom(0);
            });

        }


        this.dispLocationHereErrorModal = function(errMsg){
            var smap = searchmap;
            var modalName = 'modal-location_here__error'
            var title = errMsg;

            smap.el.find('.box-overlay').after('<div class='+modalName+'></div>');
            var modal = smap.el.find('.'+modalName);
            this.locationHereErrModal = modal;

            modal.append(
                '<div class="box-overlay"></div>' +
                '<div class="floatbox" style="top: 100px; position: absolute;">' +
                '<div class="inner">' +
                '<p class="floatbox-heading">'+title+'</p>' +
                '<div class="floatbox-tx">' +
                '<p>' +
                'Googleマップでは現在地情報を利用します。<br>' +
                '端末の設定で位置情報サービスをオンにしてください。' +
                '</p>' +
                '</div>' +
                '<p class="btn-modal-close close">閉じる</p>' +
                '</div>' +
                '</div>'
            );

            modal.find('.box-overlay').fadeIn();
            modal.find('.floatbox').fadeIn();
            var left = ($('body').width()-modal.find('.floatbox').outerWidth(true))/2;
            modal.find('.floatbox').css({'left':left});
            if($(window).scrollTop()<=0){
                var top = ($(window).height()-modal.find('.floatbox').outerHeight(true))/2;
                modal.find('.floatbox').css({'top':top});
            }else{
                var bottom = ($(window).height()-modal.find('.floatbox').outerHeight(true))/2;
                bottom -= ($('body').height()-$(window).height());
                modal.find('.floatbox').css({'top':''});
                modal.find('.floatbox').css({'bottom':bottom});
            }

            modal.find('.btn-modal-close.close').off();
            modal.find('.btn-modal-close.close').on('click',function(){
                javascript:history.back();
                modal.find('.box-overlay').fadeOut();
                modal.find('.floatbox').fadeOut();
                smap.scrollToBottom(0);
            });
            modal.find('.btn-modal-close.setting').on('click',function(){
                modal.find('.box-overlay').fadeOut();
                modal.find('.floatbox').fadeOut();
                smap.scrollToBottom(0);
            });
        }
    }

    // ポップアップ
    this.popup = new function () {
        this.mapsizeoverPopup = null;
        this.init = function () {
        }

        this.dispMapsizeoverPopup = function () {

            var smap = searchmap;
            if(smap.popup.mapsizeoverPopup){
                return;
            }

            var modalName = 'modal-streetview__error';
            var msg = '地図を拡大すると物件情報が表示されます';
            var title = '';

            smap.el.find('.box-overlay').after('<div class=' + modalName + '></div>');
            this.mapsizeoverPopup = smap.el.find('.' + modalName);

            this.mapsizeoverPopup.append(
                '<div class="box-overlay"></div>' +
                '<div class="floatbox" style="top: 100px; position: absolute;">' +
                '<div class="inner">' +
                '<p class="floatbox-heading">' + title + '</p>' +
                '<div class="floatbox-tx" style="font-size:12px;">' + msg + '</div>'
            );

            this.mapsizeoverPopup.find('.floatbox').fadeIn();
            var left = ($('body').width() - this.mapsizeoverPopup.find('.floatbox').outerWidth(true)) / 2;
            this.mapsizeoverPopup.find('.floatbox').css({'left': left});

            var headerHeight = ($('.page-header').outerHeight(false)+
            $('.map-option__list').outerHeight(false)+
            $('.map-option__change').outerHeight(false));
            headerHeight/=2;
            if ($(window).scrollTop() <= 0) {
                var top = ($(window).height() - this.mapsizeoverPopup.find('.floatbox').outerHeight(true)) / 2;
                this.mapsizeoverPopup.find('.floatbox').css({'top': top});
            } else {
                var bottom = ($(window).height() - this.mapsizeoverPopup.find('.floatbox').outerHeight(true)) / 2;
                bottom -= ($('body').height() - $(window).height());
                bottom -= headerHeight;
                this.mapsizeoverPopup.find('.floatbox').css({'top': ''});
                this.mapsizeoverPopup.find('.floatbox').css({'bottom': bottom});
            }

        }

       this.closeMapsizeoverPopup = function () {

            var smap = searchmap;
            smap.el.find('.box-overlay').fadeOut();
            if( smap.popup.mapsizeoverPopup){
                smap.popup.mapsizeoverPopup.fadeOut();
                smap.popup.mapsizeoverPopup.remove();
            }
            smap.popup.mapsizeoverPopup=null;
       }
    }

    /** aside
     *
     */
    this.aside = new function () {

        var savedScrollPos;

        this.init = function () {
            eventListner();

        }

        this.eventListner = function () {
            var smap = searchmap;

            // 都道府県ダイアログ
            $('.map-change .change-area1 .btn-change a').on('click', function () {
                updateAside();
            });

            // 市区郡ダイアログ
            $('.map-change .change-area2 .btn-change a').on('click', function () {
                updateAside();
            });

            // すべてのこだわり条件ボタン
            $('.map-change .link-more-term a').on('click', function () {
                updateAside();
            });

            // 検索条件変更
            $('.map-change .articlelist-side-section').find('input').on('click', function () {
                updateAside();

            });
            $('.map-change .articlelist-side-section').find('select').on('change', function () {
                updateAside();
            });


        }


        var updateAside = function () {
            var smap = searchmap;

            // マップを更新する
            updateMap(smap);
            smap.mapslider.closeList();

        }


        this.collectCondition = function () {
            var smap = searchmap;

            var condition  = {};

            condition.condition_side = smap.el.find('.articlelist-side-section').find('input, select').serialize();


            return condition;
        }

        this.saveScrollPos = function () {
            var smap = searchmap;

            savedScrollPos = smap.el.find('.map-change__scroll').scrollTop();
        }

        this.restoreScrollPos = function () {
            var smap = searchmap;

            smap.el.find('.map-change__scroll').scrollTop(savedScrollPos);
        }



    }//aside




    /** 地図物件一覧
     *
     */
    this.mapBukkenlist = new function () {

        var listSetting={};

        this.init = function () {
            var smap = searchmap;

            //地図検索APIのコールバックを登録
            smap.api.addCallback('maplist', updateBukkenListCallback);

            // 地図物件一覧表示/非表示イベント
            dispListEventListner();

        }

        // 地図物件一覧表示/非表示イベント
        var dispListEventListner = function () {
            var smap = searchmap;

            //地図上の物件を一覧でみる
            $('.map-option__list').on('click', function(){
                createBukkenList();
            });

        }

        // マップ物件リストを更新する
        var createBukkenList = function () {
            var smap = searchmap;

            // ズーム率が規定値以下の場合は物件リストを表示しない。
            if (smap.gmap.getZoom() < MAP_MAPPING_MAX_ZOOM){
                return;
            }

            var bounds   = smap.gmap.getBounds();
            var swLatlng = bounds.getSouthWest();
            var neLatlng = bounds.getNorthEast();

            listSetting.perPage = 10 ;
            listSetting.page = 1;
            listSetting.swlatlan = swLatlng.lat()+':'+swLatlng.lng();
            listSetting.nelatlan = neLatlng.lat()+':'+neLatlng.lng();
            listSetting.type='init';

            var data = {
                'type_ct':smap.shumoku,
                'per_page': listSetting.perPage,
                'page': listSetting.page,
                'sw_lat_lan': listSetting.swlatlan,
                'ne_lat_lan': listSetting.nelatlan
            };

            var condition = smap.aside.collectCondition();
            data.condition_side = condition.condition_side;


            // 物件情報を取得する
            smap.api.maplist(data);

        }

        var updatelist = function () {
            var smap = searchmap;

            listSetting.type='update';

            var data = {
                'type_ct':smap.shumoku,
                'per_page': listSetting.perPage,
                'page': listSetting.page,
                'sw_lat_lan': listSetting.swlatlan,
                'ne_lat_lan': listSetting.nelatlan
            };
            var condition = smap.aside.collectCondition();
            data.condition_side = condition.condition_side;
            smap.api.maplist(data);
        }

        this.openList = function () {

            var bodyWidth = $('body').width();

            $('.slide-map-cover').addClass('opened');
            $('.cover-dark').addClass('opened');

            $('.slide-map-bl-list').css({
                'width': bodyWidth - 10 + 'px',
                'right': -bodyWidth
            });

            $('.slide-map-bl-list').animate({
                'right': 0
            },100);

        }

        this.closeList = function () {
            var smap = searchmap;
            var bodyWidth = $('body').width();
            $('.slide-map-cover').removeClass('opened');
            $('.cover-dark').removeClass('opened');

            $('.slide-map-bl-list').animate({
                'right': -bodyWidth
            },100);
        }


        // マップ物件リストを更新するコールバック
        var updateBukkenListCallback = function (smap) {

            var bodyWidth = $('body').width();

            // 物件一覧を開く
            smap.el.find('.slide-map-bl-list').remove();

            if($(smap.apiData.maplist).find('.tx-nohit').length>=1){
                var btn_request_anchor = $(smap.apiData.maplist).find('div.btn-request-txt a').clone();
                if (smap.el.find("p.btn-request-none").children('a').length === 0) {
                    smap.modal.dispMessageModal(
                        '地図上に物件がありません。',
                        '<p>地図上に物件がないため一覧での表示ができません。</p>'+
                        '<p>物件一覧にて確認する場合は物件が地図上で表示される地域にてご利用ください。</p>'+
                        '<p class="btn-request-none"></p>'
                    );
                    smap.el.find("p.btn-request-none").append(btn_request_anchor);
                    smap.el.find("p.btn-request-none a").css({
                        "background": "url(/sp/imgs/btn_req.png) no-repeat",
                        "display": "block",
                        "width": "180px",
                        "height": "36px",
                        "margin": "10px auto 0",
                        "text-indent": "-9999px",
                    });
                }
                return;
            }

            smap.el.find('.cover-dark').after(smap.apiData.maplist);

            if(listSetting.type=='init'){
                smap.mapBukkenlist.openList();

            }else{
                $('.slide-map-bl-list').css({
                    'width': bodyWidth - 10 + 'px',
                    'right': 0
                });
            }
            maplistEventLisner();
        }


        var maplistEventLisner = function () {

            var smap = searchmap;
            var bodyWidth = $('body').width();

            // 地図に戻るボタン
            $('.slide-map-bl-list__btn_back').on('click', function(){
                smap.mapBukkenlist.closeList();

            });

            // 並び替え
            var $selections = $('.slide-map-bl-list .sort-select select');
            $selections.on('change', function(){
                var $select = $(this);
                listSetting.sort = $select.val();
                listSetting.page = 1;
                smap.app.cookie.updateSearch({sort: listSetting.sort});
                updatelist();
            });

            //物件一覧ページャー
            $('.slide-map-bl-list .article-pager a').on('click', function(){
                var page = $(this).data('page');
                listSetting.page = page;
                updatelist();
                return false;
            });

            //詳細ページ
            $('.slide-map-bl-list .object-body a').on('click', function(){
                var href = $(this)[0].href;
                openDetailPage(href);
                return false;
            });

            //お問い合わせ
            contact();


        }

        var contact = function () {

            var smap = searchmap;

            // add listener
            smap.el.find('.btn-all-contact').off();
            smap.el.find('.btn-all-contact').on('click', function (e) {

                var $checked, params, list = [];

                e.preventDefault();

                $checked = smap.el.find('.slide-map-bl-list .object-check :checkbox:checked');

                if ($checked.length < 1) {
                    alert('物件が1つもチェックされていません');
                    return;
                }

                $.each($checked, function (i, v) {
                    list.push($checked.eq(i).closest('.article-object').data('bukken-no'));
                });

                params={};
                params['id'] = list;
                params['type'] = smap.shumoku;
                params['from_searchmap'] = true;
                // 特集
                if(smap.isSpecial){
                    params['type'] = smap.config.search_config.shumoku;
                    params['special-path'] = smap.specialPath;
                }

                app.customConsoleLog('----- お問い合わせパラメータ start -----');
                app.customConsoleLog(params);
                app.customConsoleLog('----- お問い合わせパラメータ end -----');

                app.request.postForm(app.location.setHttpsProtocol(e.target.href), params, true);
            });
        };


    }//地図物件一覧




    /** 地図物件スライダー
     *
     */
    this.mapslider = new function () {

        this.bukkenIds;
        this.selectedItemIdx;
        this.itemUpdating;


        var MAPSLIDER_PER_PAGE = 30;

        this.init = function () {
            var smap = searchmap;

            //地図検索APIのコールバックを登録
            smap.api.addCallback('mapsidelist', updateSideBukkenListCallback);

            toggleListEventHandler();
            this.selectedItemIdx =0;
            this.bukkenIds=null;
            this.itemUpdating=false;
        }


        // マップ物件スライダーを更新する
        this.updateSideBukkenList = function (marker) {
            var smap = searchmap;
            smap.mapslider.selectedItemIdx=0
            smap.mapslider.itemUpdating=false;

            // マーカーの物件情報を表示する
            var markedBukkens = marker['bukkens'];

            var bukkenIds='';
            for (var i = 0; i<markedBukkens.length; i++)  {
                bukkenIds += (i == 0) ? '': ',' ;
                bukkenIds += markedBukkens[i]['bukkenId'];
            }
            //var bukkenNum=i;
            var bukkenNum=MAPSLIDER_PER_PAGE;

            var data = {
                'type_ct':smap.shumoku,
                'per_page': bukkenNum,
                'page': 1,
                'bukken_id':bukkenIds
            };
            smap.mapslider.bukkenIds = bukkenIds;
            smap.api.mapslider(data);
        }

        // 物件スライダーを閉じる
        this.closeList = function () {
            var smap = searchmap;
            var $map_bl_list_toggle = smap.el.find('.map-bl-list__toggle');
            $map_bl_list_toggle.find('.no-select').parents('.map-bl-list__toggle__inner').hide();
            $map_bl_list_toggle.find('.map-bl-list').parents('.map-bl-list__toggle__inner').remove();
            $('.map-bl-list__btn').removeClass('opened');

            smap.mapslider.selectedItemIdx =0;
            smap.mapslider.itemUpdating=false;
            smap.mapslider.bukkenIds=null;
        }


        // 物件スライダー表示/非表示イベント
        var toggleListEventHandler = function () {

            var smap = searchmap;

            var $toggle = $('.map-bl-list__toggle');
            $('.map-bl-list__btn span').on('click', function(){
                var $togglesInner;
                if(smap.mapslider.bukkenIds==null){
                    $togglesInner = $toggle.find('.no-select').parents('.map-bl-list__toggle__inner');
                }else{
                    $togglesInner = $toggle.find('.map-bl-list').parents('.map-bl-list__toggle__inner');
                }

                $togglesInner.animate({
                    height: 'toggle',
                    overflow: 'hidden',
                });
                $('.map-bl-list__btn').toggleClass('opened');

                smap.scrollToBottom();

            });
        }

        var selecteItemInit = function () {
            var smap = searchmap;
            smap.mapslider.selectedItemIdx=0;
            smap.mapslider.itemUpdating=false;
            selecteItem(0);

        }

        var selecteItemPrev = function () {
            var smap = searchmap;
            var itemIdx = smap.mapslider.selectedItemIdx;
            var itemNo = itemIdx+1;
            if(itemNo>1){
                selecteItem(itemIdx-1);
            }
        }

        var selecteItemNext = function () {
            var smap = searchmap;
            var itemIdx = smap.mapslider.selectedItemIdx;
            var totalNum = parseInt($(".bl-slider-num-total").text());

            var itemNo = itemIdx+1;
            if(itemNo>=totalNum){
                selecteItem(0);
                return;
            }


            // 読み込み
            var $blItems = $(".map-bl-list__inner .bl-item");
            if(itemNo<totalNum){
                // 次のページ
                if(itemNo>=$blItems.length){
                    var curPage = itemNo/MAPSLIDER_PER_PAGE;
                    var nxtPage = curPage+1;
                    updatePage(nxtPage);

                // 次のアイテム
                }else{
                    selecteItem(itemIdx+1);
                }
            }
        }

        var selecteItem = function (itemIdx) {

            var smap = searchmap;

            var $blListInner = $(".map-bl-list__inner");
            var $blItems = $(".map-bl-list__inner .bl-item");
            var itemWidth = $($blItems[0]).outerWidth(false);
            var outerWidth = $($blItems[0]).outerWidth(true);
            var itemOrigin = ($(window).width() - itemWidth )/2 ;
            var left = itemOrigin - ((outerWidth)*itemIdx);
            var oldItemIdx = smap.mapslider.selectedItemIdx;

            if(smap.mapslider.selectedItemIdx==itemIdx){
                $blListInner.css({'left':left+'px'});
            }else{
                $blListInner.animate({'left':left+'px'},300,function(){
                    $($blItems[oldItemIdx]).removeClass('cu');
                    $($blItems[itemIdx]).addClass('cu');
                });
            }

            $(".bl-slider-num-now").text(itemIdx+1);
            smap.mapslider.selectedItemIdx = itemIdx;
        }



        // 物件スライダーを開く
        var openList = function () {
            var smap = searchmap;
            var $map_bl_list_toggle = smap.el.find('.map-bl-list__toggle');
            var $blListToggleInnerNoselect = $map_bl_list_toggle.find('.no-select').parents('.map-bl-list__toggle__inner');
            var $blListToggleInner = $map_bl_list_toggle.find('.map-bl-list').parents('.map-bl-list__toggle__inner');
            $blListToggleInnerNoselect.hide();
            $blListToggleInner.remove();
            $map_bl_list_toggle.append($(smap.apiData.sidelist));

            $blListToggleInner = $map_bl_list_toggle.find('.map-bl-list').parents('.map-bl-list__toggle__inner');
            var $blListInner = $blListToggleInner.find(".map-bl-list__inner");
            var $blItems = $blListInner.find(".bl-item");
            var totalNum = parseInt($(".bl-slider-num-total").text());

            var $firstItem = $($blItems[0]);
            var width = (($firstItem.outerWidth(true))*totalNum)+4;
            $blListInner.width(width);

            $firstItem.addClass('cu');
            selecteItem(0);
            $('.map-bl-list__btn').addClass('opened');
            $map_bl_list_toggle.show();

            smap.mapslider.itemUpdating=false;
            smap.scrollToBottom();
        }

        var updateList = function () {
            var smap = searchmap;
            var $map_bl_list_toggle = smap.el.find('.map-bl-list__toggle');
            var $blListToggleInner = $map_bl_list_toggle.find('.map-bl-list').parents('.map-bl-list__toggle__inner');

            var $apiDataSidelistItems = $(smap.apiData.sidelist).find(".bl-item");
            $map_bl_list_toggle.find('.map-bl-list__inner_scroll').append($apiDataSidelistItems);

            var $blListInner = $blListToggleInner.find(".map-bl-list__inner");
            var $blItems = $blListInner.find(".bl-item");
            var totalNum = parseInt($(".bl-slider-num-total").text());

            var $firstItem = $($blItems[0]);
            var width = (($firstItem.outerWidth(true))*totalNum)+4;
            $blListInner.width(width);

            selecteItemNext();

            smap.mapslider.itemUpdating=false;
            smap.scrollToBottom();
        }

        var updatePage = function (page) {
            var smap = searchmap;
            if(smap.mapslider.itemUpdating){
                return;
            }
            smap.mapslider.itemUpdating=true;

            var data = {
                'type_ct':smap.shumoku,
                'per_page': MAPSLIDER_PER_PAGE,
                'page': page,
                'bukken_id':smap.mapslider.bukkenIds
            };
            smap.api.mapslider(data);
        }

        // マップ物件スライダーを更新するコールバック
        var updateSideBukkenListCallback = function (smap) {

            if(smap.mapslider.selectedItemIdx==0){
                openList();
            }else{
                updateList();
            }
            mapsliderEvent();
        }

        // イベント
        var mapsliderEvent = function () {
            var smap = searchmap;
            var touchstart_X=null;
            var touchmove_X;
            var touchStreetViewFlg=false;
            var touchDetailFlg=false;
            var diff;

            $('.map-bl-list__inner_scroll').off('touchstart');
            $('.map-bl-list__inner_scroll').on('touchstart', function(e){
                var smap = searchmap;
                touchstart_X = event.changedTouches[0].pageX;

                //ストリートビューボタン
                if($(e.target).hasClass("bl-item__sview") || $(e.target).parent().hasClass("bl-item__sview") &&
                   $(e.target).parents().hasClass("cu")){
                    touchStreetViewFlg = true;

                } else if ($(e.target).parents().hasClass('bl-item') && $(e.target).parents().hasClass("cu")){
                    touchDetailFlg = true;
                }

                e.preventDefault();
            });

            $('.map-bl-list__inner_scroll').off('touchmove');
            $('.map-bl-list__inner_scroll').on('touchmove', function(e){
                if(touchstart_X===null){
                    return;
                }
                touchmove_X = event.changedTouches[0].pageX;
                diff = touchstart_X - touchmove_X;

                //console.log(diff);
                if(Math.abs(diff)>15){
                    if (diff>0){
                        selecteItemNext();
                    }else if (diff<0){
                        selecteItemPrev();
                    }
                    touchstart_X=null;
                    touchStreetViewFlg=false;
                    touchDetailFlg=false;
                }
            });
            $('.map-bl-list__inner_scroll').off('touchend');
            $('.map-bl-list__inner_scroll').on('touchend', function(e){
                if(touchstart_X===null){
                    return;
                }
                var selectedItem = smap.el.find('.map-bl-list .bl-item.cu');
                if(touchStreetViewFlg){
                    var bukkenId = selectedItem.attr('data-bukken-no');
                    var originBukken = getBukkenFromMarker(bukkenId);
                    smap.streetview.init();
                    smap.streetview.setOriginBukken(originBukken);
                    smap.streetview.run();
                    touchStreetViewFlg=false;
                }else if(touchDetailFlg){
                    var href = selectedItem.find('a')[0].href
                    openDetailPage(href);
                    touchDetailFlg=false;
                }
                return false;
            });


            //ストリートビュー
            $('.map-bl-list__toggle__inner .bl-item .bl-item__sview').off('click');
            $('.map-bl-list__toggle__inner .bl-item .bl-item__sview').on('click', function(){

                //マーカー情報から対象の物件を取得
                var bukkenId = $(this).parents('.bl-item').attr('data-bukken-no');
                var originBukken = getBukkenFromMarker(bukkenId);
                smap.streetview.init();
                smap.streetview.setOriginBukken(originBukken);
                smap.streetview.run();
                return false;

            });

            //詳細ページ
            $('.map-bl-list__toggle__inner .bl-item a').off('click');
            $('.map-bl-list__toggle__inner .bl-item a').on('click', function(){
                var href = $(this)[0].href;
                openDetailPage(href);
                return false;
            });

            $('.map-bl-list__toggle__inner .btn-move .next a').off('click');
            $('.map-bl-list__toggle__inner .btn-move .next a').on('click', function(){
                selecteItemNext();
                return false;
            });

            $('.map-bl-list__toggle__inner .btn-move .prev a').off('click');
            $('.map-bl-list__toggle__inner .btn-move .prev a').on('click', function(){
                selecteItemPrev();
                return false;
            });


        }


        /** 物件に紐ずくマーカーを取得する
         *
         */
        var getBukkenFromMarker = function (bukkenId) {
            var smap = searchmap;

            // マーカー情報
            var markers = smap.mappingData.markers;
            var marker;

            var i, j;
            for (i = 0; i < markers.length; i++) {
                marker = markers[i];
                var markedBukkens = marker['bukkens'];
                for (j = 0; j<markedBukkens.length; j++)  {
                    var bukken = markedBukkens[j];
                    if(bukkenId == bukken['bukkenId']){
                        return bukken;
                    }
                }
            }
            return null;
        }
    }//物件スライダー





    /** ストリートビュー
     *
     */
    this.streetview = new function () {

        this.origin = {};
        this.streetviewPano;

        this.init = function () {

        }

        this.setOriginBukken = function (originBukken) {
            this.origin.originBukken = originBukken;
        }

        this.run = function () {
            var smap = searchmap;
            var street = new google.maps.StreetViewService();
            street.getPanoramaByLocation(
                this.origin.originBukken.latlng,
                50, smap.streetview.callbackStreetUpdate
            );
        }

        this.callbackStreetUpdate = function (results, status) {

            var smap = searchmap;

            if(status == google.maps.StreetViewStatus.OK){

                smap.scrollToTop(300);
                smap.el.find('.floatbox').remove();
                smap.el.find('.box-overlay').after(
                    '<div class="floatbox sview" style="display:none;"></div>'
                );
                smap.el.find('.floatbox.sview').append(
                    '<div class="sview-iframe" style="display:none;"></div>'+
                    '<div class="btn-bottom" style="display:none;"><p class="btn-modal-close">閉じる</p></div>'
                );

                // ストリートビューオブジェクト生成
                var streetviewPano = new google.maps.StreetViewPanorama(
                    smap.el.find('.sview-iframe')[0]
                );

                // ストリートビューオブジェクト詳細設定
                streetviewPano.setPov({heading: -20, pitch: 0, zoom: 0});
                streetviewPano.setVisible(false);

                smap.gmap.setStreetView(streetviewPano);
                smap.streetview.streetviewPano = streetviewPano;

                var latlng = results.location.latLng;
                smap.streetview.streetviewPano.setPosition(latlng);

                $('.box-overlay').fadeIn();
                $('.floatbox.sview').fadeIn();
                $('.sview-iframe').fadeIn();
                $('.btn-bottom').fadeIn();
                smap.streetview.streetviewPano.setVisible(true);

                // 終了
                $('.floatbox.sview .btn-bottom p').on('click', function () {
                    $('.box-overlay').fadeOut();
                    $('.floatbox.sview').fadeOut();
                    smap.el.find('.floatbox.sview').remove();
                    smap.scrollToBottom(100);
                });


            }else if(status == google.maps.StreetViewStatus.ZERO_RESULTS){

                smap.modal.dispMessageModal(
                    'ストリートビュー未対応',
                    '<p>こちらの物件はストリートビューに対応しておりません。</p>'
                );

            }else{
                smap.modal.dispMessageModal(
                    'ストリートビューエラー',
                    '<p>ストリートビューの取得に失敗しました。</p>'
                );
            }
        }

    }


    /** 検索条件・市区郡変更
     *
     */
    this.condition = new function () {

        var listSetting = {};

        this.init = function () {

            // イベント
            eventListner();

        }

        // イベント
        var eventListner = function () {
            var smap = searchmap;

            // 条件を絞り込む
            $($('.map-option__change li')[0]).find('a').on('click', function () {
                var parseUrl = smap.app.location.parseUrl();
                var center = smap.gmap.center;

                var href;
                if(smap.isSpecial){
                    href = parseUrl.protocol+'//'+parseUrl.host+'/'+smap.specialPath+'/'+smap.location.ken+'/condition/';
                }else{
                    href = parseUrl.protocol+'//'+parseUrl.host+'/'+smap.shumoku+'/'+smap.location.ken+'/condition/';
                }
                var data={};
                data.from_map_result=true;
                data.center = center.lat()+':'+center.lng();
                data.zoom   = smap.gmap.getZoom();
                data.back_path=smap.pathname;

                app.request.postForm(href, data);
                return false;
            });

            //市区郡を変更
            $($('.map-option__change li')[1]).find('a').on('click', function () {
                var parseUrl = smap.app.location.parseUrl();

                var href;
                if(smap.isSpecial){
                    href = parseUrl.protocol+'//'+parseUrl.host+'/'+smap.specialPath+"/"+smap.location.ken+"/map.html";
                }else {
                    href = parseUrl.protocol+'//'+parseUrl.host+'/'+smap.shumoku+"/"+smap.location.ken+"/map.html";
                }
                var data={};
                data.from_map_result=true;
                data.zoom   = smap.gmap.getZoom();
                data.back_path=smap.pathname;

                app.request.postForm(href, data);

                return false;
            });

        }
    }

    this.loading = new function () {

        var $loading;

        this.init = function () {
            var smap = searchmap;
            $loading=smap.el.find('.loading');

            $loading.css({
                'position': 'position:fixed',
                ' z-index': '9999',
                'left': '50%',
                'top': '50%',
                'margin-left': '-30px'
            });

        }

        this.show = function () {
            //$loading.show();

        }
        this.hide = function () {
            //   $loading.hide();
        }

    }

    this.userevent = new function () {

        this.listener = {};
        this.addListener = function (event, listener) {
            switch(event){
                case 'estate_list':
                    this.listener.estateList =listener;
                    break;
            }
        }

        $(function(){

            //アサイド条件を変更するボタン
            $('.btn__map-change').on('click', function(){
                $('.toggle__body_l').animate({
                    width: 'toggle',
                    overflow: 'hidden'
                });
                $('.map-change').toggleClass('is-open');
            });

            // ヘッダ
            $(window).on('load', function(){

                var mapsHeaderHeight = $('.maps-header').outerHeight();
                $('.contents-map').css({
                    'top': mapsHeaderHeight + 'px'
                });
            });

            $('.btn__gnav_toggle').on('click', function(){
                $('.page-header-liquid').toggleClass('close');
                $('.gnav').toggle();
                $('.page-header .inner:nth-of-type(2)').toggle();
                $('.page-header-top .link2').toggle();
                $('.page-header-top .tx-explain').toggle();
                $('.page-header-top .logo-s').toggle();
                $('.page-header-top .tel-s').toggleClass('show');
                $('.btn__gnav_toggle').toggleClass('open');
                $('.maps-header .link li:first-of-type').toggleClass('show');

                // var headerHeight = $('.page-header').outerHeight();
                // var elementGnav = document.getElementsByClassName('gnav');
                // var gnavHeight = elementGnav[0].offsetHeight;
                // var gnavToggleTop = headerHeight + gnavHeight;
                var mapsHeaderHeight = $('.maps-header').outerHeight();
                $('.contents-map').css({
                    'top': mapsHeaderHeight + 'px'
                });
            });
        });
    }


    /**
     *  物件APIへの連携用
     *
     */
    this.api = new function () {

        this.API_MAP_CENTER   = 'mapcenter';
        this.API_MAP_UPDATE   = 'updatemap';
        this.API_MAP_SIDELIST = 'mapsidelist';
        this.API_MAP_LIST     = 'maplist';

        this.updateTimeoutId = null;

        this.callback = {};
        this.addCallback = function (api, callback) {
            switch(api){
                case 'mapCenterByCity':
                    this.callback.mapCenterByCity =callback;
                    break;
                case 'mapUpdate':
                    this.callback.mapUpdate =callback;
                    break;
                case 'maplist':
                    this.callback.maplist =callback;
                    break;
                case 'mapsidelist':
                    this.callback.mapslider =callback;
                    break;

            }
        }

        var getApiUrl = function (apiName) {

            var smap = searchmap;

            var apiUrl = '/api/';
            if(smap.isSpecial){
                apiUrl+=smap.specialPath+'/';
            }
            apiUrl+=apiName+'/';
            return apiUrl;
        }


        /** API：地図の中心位置を取得する
         *
         */
        this.mapCenterByCity = function (data) {

            var smap = searchmap;

            if(smap.isSpecial){
                data.special_path=smap.specialPath;
            }

            var apiUrl = getApiUrl(this.API_MAP_CENTER);

            post(apiUrl, data, function (res) {
                smap.apiData.mapCenterData = res.coordinate;
                smap.api.callback.mapCenterByCity(smap);
            });

        }


        /** API：マップを更新する
         *
         */
        this.mapUpdate = function (data) {

            var smap = searchmap;


            if(smap.shumoku){
                data.type_ct = smap.shumoku;
            }
            if(smap.isSpecial){
                data.special_path=smap.specialPath;
            }

            var bounds   = smap.gmap.getBounds();
            var swLatlng = bounds.getSouthWest();
            var neLatlng = bounds.getNorthEast();
            data.sw_lat_lan = swLatlng.lat()+':'+swLatlng.lng();
            data.ne_lat_lan = neLatlng.lat()+':'+neLatlng.lng();

            var condition = smap.aside.collectCondition();
            data.condition_side = condition.condition_side;
            data.condition_modal = condition.condition_modal;
            data.side_or_modal   ='modal';
            data.map_initialized = smap.status.initialized ? 1 : 0;

            var apiUrl = getApiUrl(this.API_MAP_UPDATE);

            if (smap.api.updateTimeoutId) {
                clearTimeout(smap.api.updateTimeoutId);
                //console.log('clear time out: '+smap.api.updateTimeoutId);
            }

            smap.api.updateTimeoutId = setTimeout(function () {
                smap.app.customConsoleLog('post: '+smap.api.updateTimeoutId);

                post(apiUrl, data, function (res) {
                    // success
                    smap.apiData.coordinates = res.content.coordinates;
                    smap.apiData.coordinates_total_count = res.content.total_count;
                    smap.apiData.aside = res.aside;
                    smap.apiData.hidden = res.hidden;
                    smap.apiData.detachedHouse = res.detachedHouse;
                    smap.api.callback.mapUpdate(smap);
                });
                smap.api.updateTimeoutId = null;
            }, WAIT_MS);

        }
        /** API：地図上の物件リストを取得する
         *
         */
        this.maplist = function (data) {
            var smap = searchmap;

            if(smap.isSpecial){
                data.special_path=smap.specialPath;
            }

            var apiUrl = getApiUrl(this.API_MAP_LIST);

            post(apiUrl, data, function (res) {
                // success
                smap.apiData.maplist = res.content;
                smap.api.callback.maplist(smap);
            });
        }

        /** API：マーカーに紐づく物件スライダーを取得する
         *
         */
        this.mapslider = function (data) {

            var smap = searchmap;

            if(smap.isSpecial){
                data.special_path=smap.specialPath;
            }

            var apiUrl = getApiUrl(this.API_MAP_SIDELIST);

            post(apiUrl, data, function (res) {
                // success
                smap.apiData.sidelist = res.content;
                smap.api.callback.mapslider(smap);
            });
        }

    };

    var post = function (apiUrl,data,callback) {
        var smap = searchmap;

        data.s_type = '5';

        $.ajax({
            type: 'POST',
            url: apiUrl,
            data: data,
            timeout: 120 * 1000,
            dataType: 'json'

        }).done(function (res) {

            smap.app.customConsoleLog('----- ajax response -----');
            smap.app.customConsoleLog(res);
            smap.app.customConsoleLog('----- ajax response end -----');

            // success
            var status = apiErrorHandler(res);
            if (status == 'abort') {
                return;
            }

            callback(res);

        }).fail(function (res) {

            smap.app.customConsoleLog('----- ajax failed -----');
            smap.app.customConsoleLog(res);
            smap.app.customConsoleLog('----- ajax failed end -----');

            // error
            var status = apiErrorHandler(res);
            if (status == 'abort') {
                return;
            }

        });

    }

    var apiErrorHandler = function (res) {
        var smap = searchmap;

        var status = 'success';
        if(res.success==true){
            return status;
        }

        status = 'abort';
        if(res.status_code=='404'){
            alert('システムエラー');
        }

        if(status == 'abort'){
            smap.status.updating = false;
            smap.event.mapupdate = EVENT_MAPUPDATE_FOR_DEFAULT;
            smap.mapslider.itemUpdating=false;
        }

        return status;
    }

}

selectMapCity = new function () {

    this.run = function (_app,search,config) {
        var smc = selectMapCity;
        smc.app     = _app;
        smc.search  = search;
        smc.searchConfig   = config.search_config;
        smc.el             = config.$el;
        eventListner();
    }

    var eventListner = function (smap) {
        var smc = selectMapCity;

        $('.list-select-set a').on('click', function(){
            var href = $(this)[0].href;
            var data={};
            data.from_map_city_select=true;
            if ($(':hidden[name="from_map_result"]').val() == 'true'){
                data.for_change = true;
            }

            app.request.postForm(href, data);

            return false;
        });

    }
}