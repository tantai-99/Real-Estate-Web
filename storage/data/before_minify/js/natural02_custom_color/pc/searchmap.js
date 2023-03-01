

searchmap = new function () {
    'use strict';

    var MAP_MIN_ZOOM         = 9;  // 地図倍率の最小
    var MAP_MAX_ZOOM         = 19; // 地図倍率の最大
    var MAP_MAPPING_MAX_ZOOM = 15; // 物件をマッピングする最大の地図倍率
    var MAP_DEFAULT_ZOOM     = 17; // 地図

    var DEBUG_DISP_MARKAREA  = false;
    var DEBUG_DISP_ALLBUKKEN = false;


    var EVENT_MAPUPDATE_FOR_DEFAULT           = 1; //
    var EVENT_MAPUPDATE_FOR_PREF              = 2; //
    var EVENT_MAPUPDATE_FOR_CITY              = 3; //
    var EVENT_MAPUPDATE_FOR_SIDE_COND         = 4; //
    var EVENT_MAPUPDATE_FOR_MODAL_COND_RESULT = 5; //
    var EVENT_MAPUPDATE_FOR_MODAL_COND        = 6; //

    var WAIT_MS = 750;

    this.run = function (_app,search,$el) {

        var smap = searchmap;

        smap.app     = _app;
        smap.search  = search;

        smap.el             = $el;
        smap.$loading       = smap.el.find('.loading');
        smap.$overlay       = smap.el.find('.box-overlay');
        smap.$floatbox      = smap.$overlay.next('.floatbox');
        smap.$modalWindows  = smap.$floatbox.find('.contents-iframe');
        smap.$disabledElems = null;

        smap.apiData           ={};
        smap.shumoku           = smap.app.location.currentShumoku();
        smap.isSpecial         = smap.search.pageType.isSpecialCategory();
        smap.specialPath       = smap.app.location.currentSpecialPath();

        smap.selectedMarker    = null;

        smap.location          ={};
        smap.location.ken      = smap.app.location.currentPrefecture();
        smap.location.shikugun = (smap.app.location.currentPathname().split('/')[4]).split('-')[0];


        smap.status =[];
        smap.status.initialized =false;
        smap.status.updating =false;
        smap.status.areaUpdating =false;
        smap.status.aside_area_update_valid =false;

        smap.processingCount =[];
        smap.processingCount.mapupdate=0;

        smap.event={};
        smap.event.mapupdate = EVENT_MAPUPDATE_FOR_DEFAULT;

        smap.mapBukkenlist.init();
        smap.mapsidelist.init();
        smap.arround.init();
        smap.userevent.init();
        smap.modal.init();
        smap.popup.init();

        smap.ua = navigator.userAgent.toLowerCase();

        // マップ中心位置
        mapCenterProc(smap,'init');

    }


    /******************************
     * GoogleMap イベントハンドラ
     ******************************/
        // idle
    var idle = function () {
            //console.log("idle");
            var smap = searchmap;
        }

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
        //console.log("boundsChanged");

        var smap = searchmap;
        if (smap.status.initialized == false) {

            updateMap(smap);
            //smap.arround.updateByMapEvent();
        }
    }

    // ズーム変更
    var zoomChanged = function () {
        var smap = searchmap;

        updateMap(smap);
        smap.mapsidelist.closeList();
        //smap.arround.updateByMapEvent();

        console.log(smap.gmap.getZoom());
    }

    // ドラッグ中
    var drag = function () {
        console.log("drag");
    }

    // ドラッグ終了
    var dragend = function () {
        console.log("dragend");
        var smap = searchmap;
        updateMap(smap);
        smap.mapsidelist.closeList();
        //smap.arround.updateByMapEvent();

    }

    //クリック時
    var click = function () {
        removeClickedMarker();
        var smap = searchmap;
        smap.mapsidelist.closeList();
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
            smap.mapsidelist.updateSideBukkenList(marker);

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
        var anchorPoint = new google.maps.Point(11,anchorPointY);
        var markerLabelOrigin = new google.maps.Point(5, 5);

        markerIcon = {url: "/pc/imgs/icon_map_bukken.png", labelOrigin: markerLabelOrigin};
        markerOption = {map: smap.gmap, anchorPoint: anchorPoint, icon: markerIcon};
        smap.mappingData.markersOption = markerOption;

        markerIcon = {url: "/pc/imgs/icon_map_click.png", labelOrigin: markerLabelOrigin};
        markerOption = {map: smap.gmap, anchorPoint: anchorPoint, icon: markerIcon};

        smap.mappingData.markersOptionClickedMarker = markerOption;
    }

    var mapCenterProc = function (smap,type) {

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

        if(type=='init'){
            smap.api.addCallback('mapCenterByCity', mapCenterInitCallback);
        }else{
            smap.api.addCallback('mapCenterByCity', mapCenterUpdateCallback);
        }

        // 地図の中心位置を取得する
        smap.api.mapCenterByCity(data);

    }

    // 地図中心位置取得ハンドラ(初期化用)
    var mapCenterInitCallback = function (smap) {

        // googl map object
        smap.gmap =createGoogleMap(smap);
        initMappingData(smap);

        smap.status.initialized = false;

    }

    // 地図中心位置取得ハンドラ(更新用)
    //  asidからのエリア変更時
    var mapCenterUpdateCallback = function (smap) {

        smap.status.areaUpdating=true;

        // 地図の中心を移動する
        var mapCenterData = smap.apiData.mapCenterData;
        var mapCenterlatlng = new google.maps.LatLng(mapCenterData.lat, mapCenterData.lng);
        smap.gmap.setCenter(mapCenterlatlng);


        updateMap(smap);
        smap.mapsidelist.closeList();
        //smap.arround.updateByMapEvent();


    }

    /** google map object
     *
     * @param smap
     * @returns {google.maps.Map}
     */
    var createGoogleMap = function (smap) {

        // マップ情報
        var mapZoom = MAP_DEFAULT_ZOOM;
        var mapCenterData = smap.apiData.mapCenterData;

        // 地図中心緯度経度
        var mapCenterlatlng = new google.maps.LatLng(mapCenterData.lat, mapCenterData.lng);

        //キャンバス
        var mapCanvas = smap.el.find('#parts_map_canvas1')[0];

        //オプション設定
        var mapOpts = {};
        mapOpts.zoom = mapZoom;
        mapOpts.center =mapCenterlatlng;
        mapOpts.minZoom = MAP_MIN_ZOOM;
        mapOpts.maxZoom = MAP_MAX_ZOOM;
        mapOpts.mapTypeControl=true;
        mapOpts.panControl=false;
        mapOpts.mapTypeControlOptions={};
        mapOpts.zoomControlOptions={};
        mapOpts.scaleControlOptions={};
        mapOpts.streetViewControlOptions={};
        mapOpts.fullscreenControlOptions={};
        mapOpts.mapTypeControlOptions.position=google.maps.ControlPosition.RIGHT_BOTTOM;
        mapOpts.zoomControlOptions.position=google.maps.ControlPosition.RIGHT_BOTTOM;
        mapOpts.streetViewControlOptions.position=google.maps.ControlPosition.RIGHT_BOTTOM;
        mapOpts.fullscreenControlOptions.position=google.maps.ControlPosition.RIGHT_BOTTOM;
//      mapOpts.streetViewControl=false;

        // mapオブジェクト
        var gmap = new google.maps.Map(mapCanvas, mapOpts);

        // map event listner
        google.maps.event.addListener(gmap, 'zoom_changed', zoomChanged);
        google.maps.event.addListener(gmap, 'dragend', dragend);
        google.maps.event.addListener(gmap, 'bounds_changed', boundsChanged);
        google.maps.event.addListener(gmap, 'tilesloaded', tilesloaded);
        google.maps.event.addListener(gmap, 'idle', idle);
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
            smap.arround.updateByMapEvent();
            smap.status.areaUpdating =false;
            return;
        }
        smap.popup.closeMapsizeoverPopup();

        smap.status.updating = true;


        var data={};

        // 初回は都道府県・市区郡情報を渡す
        smap.status.aside_area_update_valid=false;
        if( !smap.status.initialized || smap.status.areaUpdating){
            data.ken_ct      = smap.location.ken;
            data.shikugun_ct = smap.location.shikugun;
            smap.status.aside_area_update_valid = true;
        }
        if( smap.event.mapupdate == EVENT_MAPUPDATE_FOR_MODAL_COND_RESULT){
            data.side_or_modal = 'modal';
        }

        smap.$loading.show();

        // 物件情報を取得する
        smap.api.addCallback('mapUpdate', mapUpdateCallback);
        smap.api.mapUpdate(data);


    }

    // マップ更新コールバック
    var mapUpdateCallback = function (smap) {
        smap.$loading.hide();

        // ズーム率が規定値以下の場合は更新しない
        if (smap.gmap.getZoom() < MAP_MAPPING_MAX_ZOOM){
            smap.popup.dispMapsizeoverPopup();
            clearMarkers(smap);
            smap.arround.updateByMapEvent();
            smap.status.updating =false;
            smap.status.areaUpdating =false;
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

        // アサイドを更新する
        smap.aside.saveScrollPos();

        smap.el.find('.map-main .tx-nohit').remove();
        if(smap.apiData.detachedHouse) {
            smap.el.find('.map-main #parts_map_canvas1').after(smap.apiData.detachedHouse);
        }

        // アサイドをまるっと更新
        if(smap.status.aside_area_update_valid){
            var $aside = smap.el.find('.map-change__inner');
            var $asideNew = $(smap.apiData.aside).find('.map-change__inner');
            $asideNew.find('.articlelist-side-section .select-term').find('input[type="checkbox"]:checked, input[type="radio"]:checked').siblings('label').addClass('checked');
            $asideNew.find('.articlelist-side-section .select-term label.tx-disable').siblings('input[type="checkbox"]:not(:checked), input[type="radio"]:not(:checked)').prop('disabled',true);

            $aside.html($asideNew.html());

        // アサイドのエリア以外を更新
        }else{
            var $aside = smap.el.find('.map-change__inner');

            var $asideSideSectionsOld = smap.el.find('.articlelist-side-section');
            var $asideSideSectionsNew = $(smap.apiData.aside).find('.articlelist-side-section');

            // チェックボックスの選択状態を更新前のアサイドに合わせる
            var oldCheckboxes  = $($asideSideSectionsOld).find('input[type="checkbox"]');
            var oldCheckbox, newCheckbox, dataId;
            for (var i = 0; i<oldCheckboxes.length; i++) {
                oldCheckbox = oldCheckboxes[i];
                dataId = $(oldCheckbox).attr('data-id');
                newCheckbox = $($asideSideSectionsNew).find('[data-id='+dataId+']')[0];
                $(newCheckbox).prop("checked", oldCheckbox.checked);
            }

            //「選択中」と「非活性」のclassを設定する
            $asideSideSectionsNew.find('.select-term').find('input[type="checkbox"]:checked, input[type="radio"]:checked').siblings('label').addClass('checked');
            $asideSideSectionsNew.find('.select-term label.tx-disable').siblings('input[type="checkbox"]:not(:checked), input[type="radio"]:not(:checked)').prop('disabled',true);

            //　asideを入れ替える
            $($asideSideSectionsOld[1]).remove();
            $aside.find('.map-change__scroll_inner').append($asideSideSectionsNew[1]);

            // freeword element position change
            var $freewordElement = smap.el.find('.element-input-search-result');
            if ($freewordElement.find('input').length > 0) {
                if ($freewordElement.find('input').val().trim() == '') {
                    $freewordElement.before($asideSideSectionsNew[1]);
                }
            }

        }


        //アサイドのポップアップを更新する
        var $hidden = $(smap.apiData.hidden);
        if(smap.el.find('.floatbox').length <= 0){
            smap.el.find('.box-overlay').after($('<div class="floatbox" style="display: none;"></div>'));
            smap.el.find('.floatbox').append('<div class="floatbox__map"></div>');
        }

        if(smap.status.aside_area_update_valid){
            smap.el.find('.floatbox__map .contents-iframe.search-modal-prefecture').remove();
            smap.el.find('.floatbox__map').append($hidden.find('.contents-iframe.search-modal-prefecture'));

            smap.el.find('.floatbox__map .contents-iframe.search-modal-area').remove();
            smap.el.find('.floatbox__map').append($hidden.find('.contents-iframe.search-modal-area'));
        }

        smap.el.find('.floatbox__map .contents-iframe.search-modal-detail').remove();
        smap.el.find('.floatbox__map').append($hidden.find('.contents-iframe.search-modal-detail'));

        eventListnerBoxOverlay();

        smap.aside.eventListner();
        smap.aside.restoreScrollPos();


        // こだわりselected用クラスとdisabledの設定
        smap.el.find('.search-modal-detail .element-detail-table').find('input[type="checkbox"]:checked, input[type="radio"]:checked').siblings('label').addClass('checked');
        smap.el.find('.search-modal-detail .element-detail-table label.tx-disable').siblings('input:not(:checked)').prop('disabled',true);

        var tooltip = new smap.search.common.tooltip(smap.el);
        tooltip.run();

        // 物件件数の吹き出し
        ajastMarkerSummaryInfo();

        // 周辺環境
        smap.arround.updateByMapEvent();

        smap.status.initialized = true;
        smap.status.updating = false;
        smap.status.areaUpdating = false;
        smap.event.mapupdate = EVENT_MAPUPDATE_FOR_DEFAULT;


    }


    /**マッピングする
     *
     * @param mapData
     * @param bukkens
     */
    var mapping = function (smap) {
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

    var eventListnerBoxOverlay = function () {

        var smap = searchmap;
        $('.box-overlay').off('click');
        $('.box-overlay').on('click', function () {
            smap.modal.closeModal();
            smap.aside.closeDialog();
        });

    }


    // モーダルクラス
    this.modal = new function () {
        this.msgModal = null;
        this.init = function () {
        }

        this.closeModal = function () {
            var smap = searchmap;
            smap.el.find('.box-overlay').fadeOut();
            if( smap.modal.msgModal){
                smap.modal.msgModal.fadeOut();
                smap.modal.msgModal.remove();
            }
        }

        this.dispMessageModal = function(msg){

            var smap = searchmap;
            var modalName = 'modal-streetview__error';

            smap.el.find('.box-overlay').after(
                '<div class='+modalName+'>'+ msg +
                '<p class="btn-close"><a href="#">閉じる</a></p>'
            );

            smap.el.find('.box-overlay').fadeIn();

            this.msgModal = smap.el.find('.'+modalName);
            this.msgModal.fadeIn();

            this.msgModal.find('.btn-close').off('click');
            this.msgModal.find('.btn-close').on('click',function(){
                smap.el.find('.box-overlay').fadeOut();
                smap.modal.msgModal.fadeOut();
                smap.modal.msgModal.remove();
            });

        }
    }

    // ポップアップ
    this.popup = new function () {
        this.mapsizeoverPopup = null;
        this.init = function () {
        }

        this.dispMapsizeoverPopup = function(){

            var smap = searchmap;
            if(smap.popup.mapsizeoverPopup){
                return;
            }

            var popuplName = 'modal-streetview__error';
            var msg = '地図を拡大すると物件情報が表示されます';

            smap.el.find('.box-overlay').after(
                '<div class='+popuplName+'>'+ msg +'</div>'
            );

            this.mapsizeoverPopup = smap.el.find('.'+popuplName);
            this.mapsizeoverPopup.fadeIn();

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

        this.closeDialog = function () {
            $('.box-overlay').fadeOut();
            $('.floatbox').fadeOut();
            $('.floatbox__map .contents-iframe').fadeOut();
            $('.search-modal-detail').fadeOut();
        }

        this.openDialog = function (dialogClass) {

            var smap = searchmap;
            if(smap.el.find('.floatbox .btn-close').length == 0) {
                smap.el.find('.floatbox').append('<p class="btn-close">閉じる</p>');
            }

            var left=10;
            if($('body').width()>smap.el.find('.floatbox').width()){
                left = ($('body').width()-smap.el.find('.floatbox').width())/2
            }
            smap.el.find('.floatbox').css({'left':left,'top':'40px'});
            smap.el.find('.floatbox').css({'bottom':'' });

            smap.aside.eventListner();


            $('.box-overlay').fadeIn();
            $('.floatbox').fadeIn();
            $(dialogClass).fadeIn();

            if(smap.el.find('.floatbox').height() >= $(window).height() ) {
                smap.el.find('.floatbox').css({'bottom': '40px'});
            }

        }

        this.eventListner = function () {
            var smap = searchmap;

            $('.floatbox .btn-close').off('click');
            $('.floatbox .btn-close').on('click', function () {
                smap.aside.closeDialog();
            });

            // 都道府県ダイアログ
            $('.map-change .change-area1 .btn-change a').off('click');
            $('.map-change .change-area1 .btn-change a').on('click', function () {

                smap.aside.openDialog('.search-modal-prefecture');

                $('.search-modal-prefecture a').on('click', function () {
                    var href = $(this)[0].href;
                    smap.location.ken = (href.split('/')[4]).split('-')[0];
                    smap.aside.closeDialog();

                    openEditedSikugunDialog();
                    return false;
                });
            });

            // 市区郡ダイアログ
            $('.map-change .change-area2 .btn-change a').off('click');
            $('.map-change .change-area2 .btn-change a').on('click', function () {

                //smap.aside.openDialog('.search-modal-area');
                openEditedSikugunDialog();

                $('.search-modal-area a').off('click');
                $('.search-modal-area a').on('click', function () {
                    var href = $(this)[0].href;
                    smap.location.shikugun = (href.split('/')[6]).split('-')[0];
                    smap.aside.closeDialog();
                    mapCenterProc(smap,'update');
                    //updateAside();
                    return false;
                });
            });

            // すべてのこだわり条件ボタン
            $('.map-change .link-more-term a').off('click');
            $('.map-change .link-more-term a').on('click', function () {
                if(smap.status.updating){
                    return false;
                }
                smap.aside.openDialog('.search-modal-detail');
                return false;
            });

            // こだわり条件モーダルの検索ボタン
            $('.search-modal-detail .btn-search a').off('click');
            $('.search-modal-detail .btn-search a').on('click', function () {
                smap.aside.closeDialog();

                // 選択中のこだわり条件の非選択を削除
                var notSelectedItems = $($('.articlelist-side-section .select-term.detail-side')[1]).find('input[type="hidden"]:not(:checked)');
                for (var i = 0; i<notSelectedItems.length; i++)  {
                    $(notSelectedItems[i]).remove();
                }

                // こだわり条件を同期させる(modal → side)
                syncKodawari('modal','ModalToSide');


                smap.event.mapupdate = EVENT_MAPUPDATE_FOR_MODAL_COND_RESULT;
                updateAside();
                setEvent();
                return false;
            });

            // こだわり条件モーダルの条件チェックボックス
            $('.list-check :checkbox').off('change');
            $('.list-check :checkbox').on('change', function () {

                $(this).siblings('label').toggleClass('checked');

                // こだわり条件を同期させる(modal → side)
                syncKodawari('modal','ModalToSide');

                updateSearchDetailModal();
            });

            // 検索条件変更
            $('.map-change .articlelist-side-section').find('input').off('click');
            $('.map-change .articlelist-side-section').find('input').on('click', function () {

                $(this).siblings('label').toggleClass('checked');

                // こだわり条件を同期させる(side → modal)
                syncKodawari('side','SideToModal');

                updateAside();
                setEvent();
            });
            $('.map-change .articlelist-side-section').find('select').off('change');
            $('.map-change .articlelist-side-section').find('select').on('change', function () {

                updateAside();
                setEvent();
            });
            
            $('.map-change').find('.search-freeword').off('click');
            $('.map-change').find('.search-freeword').on('click', function () {

                updateAside();
            });
            $('.map-change').find('.form-search-freeword').on('submit', function (e) {
                e.preventDefault();
                updateAside();
                setEvent();
            });

        }


        /**
         * 変更用の市区郡ダイアログを開く
         *
         */
        var openEditedSikugunDialog = function () {
            var smap = searchmap;

            if (smap.status.updating){
                return;
            }
            smap.status.updating = true;
            smap.aside.saveScrollPos();

            var data={};
            data.ken_ct =smap.location.ken;

            // 物件情報を取得する
            smap.api.addCallback('mapUpdate', openEditedSikugunDialogCallback);
            smap.api.mapUpdate(data);
        }


        //
        var openEditedSikugunDialogCallback = function () {
            var smap = searchmap;

            //変更用の市区郡ダイアログ
            var $hidden = $(smap.apiData.hidden);
            if(smap.el.find('.floatbox').length <= 0){
                smap.el.find('.box-overlay').after($('<div class="floatbox" style="display: none;"></div>'));
                smap.el.find('.floatbox').append('<div class="floatbox__map"></div>');
            }
            smap.el.find('.floatbox__map .search-modal-area.edit').remove();
            smap.el.find('.floatbox__map').append($hidden.find('.contents-iframe.search-modal-area').addClass('edit'));

            smap.status.updating = false;

            smap.aside.openDialog('.search-modal-area.edit');

            $('.search-modal-area.edit a').on('click', function () {
                var href = $(this)[0].href;
                smap.location.shikugun = (href.split('/')[6]).split('-')[0];
                smap.aside.closeDialog();
                mapCenterProc(smap,'update');
                return false;
            });
        }

        /**
         * こだわり条件モーダルの更新
         *
         */
        var updateSearchDetailModal = function () {
            var smap = searchmap;

            /*
             if (smap.status.updating){
             return;
             }
             */
            smap.status.updating = true;
            smap.aside.saveScrollPos();


            var data={};
            data.side_or_modal = 'modal';

            // 物件情報を取得する
            smap.api.addCallback('mapUpdate', updateSearchDetailModalCallback);
            smap.api.mapUpdate(data);
        }


        var updateSearchDetailModalCallback = function () {
            var smap = searchmap;

            if(smap.processingCount.mapupdate >= 2){
                smap.app.customConsoleLog("ignore this detailmodal response. cause still have response. queueCount:"+smap.processingCount.mapupdate);
                return;
            }

            //変更用の市区郡ダイアログ
            var $hidden = $(smap.apiData.hidden);
            if(smap.el.find('.floatbox').length <= 0){
                smap.el.find('.box-overlay').after($('<div class="floatbox" style="display: none;"></div>'));
                smap.el.find('.floatbox').append('<div class="floatbox__map"></div>');
            }

            // チェックボックスの選択状態を更新前のこだわり条件モーダルに合わせる
            var oldCheckboxes  = smap.el.find('.search-modal-detail').find('input[type="checkbox"]');
            var newDitailModal = $hidden.find('.contents-iframe.search-modal-detail');
            var oldCheckbox, newCheckbox, dataId;
            for (var i = 0; i<oldCheckboxes.length; i++) {
                oldCheckbox = oldCheckboxes[i];
                dataId = $(oldCheckbox).attr('data-id');
                newCheckbox = $(newDitailModal).find('[data-id='+dataId+']')[0];
                $(newCheckbox).prop("checked", oldCheckbox.checked);
            }

            //更新前のこだわり条件モーダルを削除して、新しいこだわり条件モーダルに入れ替える
            smap.el.find('.floatbox__map .search-modal-detail').remove();
            smap.el.find('.floatbox__map').append($hidden.find('.contents-iframe.search-modal-detail').show());

            // 新しいこだわり条件モーダルのチェックボックスに「選択中」と「非活性」のclassを付与する
            smap.el.find('.search-modal-detail .element-detail-table').find('input[type="checkbox"]:checked, input[type="radio"]:checked').siblings('label').addClass('checked');
            smap.el.find('.search-modal-detail .element-detail-table label.tx-disable').siblings('input:not(:checked)').prop('disabled',true);

            if(smap.el.find('.floatbox .btn-close').length == 0) {
                smap.el.find('.floatbox').append('<p class="btn-close">閉じる</p>');
            }

            smap.aside.eventListner();
            smap.status.updating = false;
        }





        var updateAside = function () {
            var smap = searchmap;

            // マップを更新する
            updateMap(smap);
            smap.mapsidelist.closeList();

        }


        this.collectCondition = function () {
            var smap = searchmap;

            var condition  = {};
            var $input  = smap.el.find('.articlelist-side-section').find('input').clone().prop('disabled', false);
            var $select = smap.el.find('.articlelist-side-section').find('select');
            var $searchText = smap.el.find('.element-input-search-result').find('input');
            condition.condition_side = $input.add($select).add($searchText).serialize();
            condition.condition_modal = smap.el.find('.search-modal-detail').find('input').clone().prop('disabled', false).serialize();
            return condition;
        }

        // サイドの人気のこだわり条件と選択中のこだわり条件の選択状態を、こだわり条件モーダルと同期させる
        var syncKodawari = function (base,type) {
            var smap = searchmap;
            // sideベース
            if(base == 'side'){
                var sideDetail = smap.el.find('.articlelist-side-section .select-term.detail-side').find('input[type="checkbox"]');
                var dataId, modalDeatilItem, sideDetailItem;
                $.each(sideDetail, function (i, val) {
                    dataId = $(val).attr('data-id');
                    sideDetailItem  = $(val)[0];
                    modalDeatilItem = $('.search-modal-detail [data-id='+dataId+']')[0];
                    //  side → modal
                    if(type=='SideToModal'){
                        $(modalDeatilItem).prop("checked", sideDetailItem.checked);
                        //  modal → side
                    }else if(type=='ModalToSide'){
                        $(sideDetailItem).prop("checked", modalDeatilItem.checked);
                    }
                });
                // modalベース
            }else{
                var modalDeati = smap.el.find('.search-modal-detail').find('input[type="checkbox"]');
                var dataId, modalDeatilItem, sideDetailItem;
                $.each(modalDeati, function (i, val) {
                    dataId = $(val).attr('data-id');
                    modalDeatilItem  = $(val)[0];
                    sideDetailItem = $('.articlelist-side-section .select-term.detail-side [data-id='+dataId+']')[0];
                    //  side → modal
                    if(type=='SideToModal'){
                        $(modalDeatilItem).prop("checked", sideDetailItem.checked);
                        //  modal → side
                    }else if(type=='ModalToSide'){
                        if($(sideDetailItem).length>0){
                            $(sideDetailItem).prop("checked", modalDeatilItem.checked);
                        }
                    }
                });
            }
        }

        this.saveScrollPos = function () {
            var smap = searchmap;
            savedScrollPos = smap.el.find('.map-change__scroll').scrollTop();
        }

        this.restoreScrollPos = function () {
            var smap = searchmap;
            smap.el.find('.map-change__scroll').scrollTop(savedScrollPos);
        }
        var setEvent = function () {
            var smap = searchmap;
            var $searchText = smap.el.find('.element-input-search-result input');
            $searchText.unbind().data('plugin_fulltextCount', null).data('plugin_fulltextSuggest', null);

            var data = {
                'prefecture': smap.location.ken,
                'city': smap.location.shikugun
            };
            if(smap.shumoku){
                data.shumoku = smap.shumoku;
            }
            if(smap.isSpecial){
                data.special_path=smap.specialPath;
            }

            data.fulltext = $searchText.val();
            var condition = smap.aside.collectCondition();
            data.condition_side = condition.condition_side;
            data.condition_modal = condition.condition_modal;

            suggest(data);

        }
        var suggest = function (params) {
            var criteria = $('input[name="search_filter[fulltext_fields]"]');
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
                      );
                    },
                    complete: function(data, query) {
                        if(criteria.val() !== '') {
                            $(this).focus();
                        }
                    }
                });
            }
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

            listSetting.perPage = 10;
            listSetting.page = 1;
            listSetting.swlatlan = swLatlng.lat()+':'+swLatlng.lng();
            listSetting.nelatlan = neLatlng.lat()+':'+neLatlng.lng();

            var data = {
                'type_ct':smap.shumoku,
                'per_page': listSetting.perPage,
                'page': listSetting.page,
                'sw_lat_lan': listSetting.swlatlan,
                'ne_lat_lan': listSetting.nelatlan
            };

            var condition = smap.aside.collectCondition();
            data.condition_side = condition.condition_side;
            data.condition_modal = condition.condition_modal;


            // 物件情報を取得する
            smap.api.maplist(data);

        }

        var updatelist = function () {
            var smap = searchmap;

            var data = {
                'type_ct':smap.shumoku,
                'per_page': listSetting.perPage,
                'page': listSetting.page,
                'pic': listSetting.pic,
                'sw_lat_lan': listSetting.swlatlan,
                'ne_lat_lan': listSetting.nelatlan

            };
            var condition = smap.aside.collectCondition();
            data.condition_side = condition.condition_side;
            data.condition_modal = condition.condition_modal;
            smap.api.maplist(data);
        }

        // マップ物件リストを更新するコールバック
        var updateBukkenListCallback = function (smap) {

            smap.el.find('.floatbox .floatbox__map .search-modal-bl-all ').remove();

            if($(smap.apiData.maplist).find('.search-modal-bl-all .tx-nohit').length>=1){
                var btn_request_anchor = smap.el.find('p.btn-request a').clone();
                if (smap.el.find("p.btn-request-none").children('a').length === 0) {
                    smap.modal.dispMessageModal(
                        '<p>地図上に物件がないため一覧での表示ができません。</p>'+
                        '<p>物件一覧にて確認する場合は物件が地図上で表示される地域にてご利用ください。</p>'+
                        '<p class="btn-request-none"></p>'
                    );
                    smap.el.find("p.btn-request-none").append(btn_request_anchor);
                    smap.el.find("p.btn-request-none a").css({
                        "background": "url(/pc/imgs/btn_req.png) no-repeat",
                        "display": "block",
                        "width": "180px",
                        "height": "36px",
                        "margin": "10px auto 0",
                        "text-indent": "-9999px",
                    });
                }
                return;
            }

            smap.el.find('.floatbox .floatbox__map').append($(smap.apiData.maplist).find('.search-modal-bl-all'));
            if(smap.el.find('.floatbox .btn-close').length == 0) {
                smap.el.find('.floatbox').append('<p class="btn-close">閉じる</p>');
            }

            var left=10;
            if($('body').width()>smap.el.find('.floatbox').width()){
                left = ($('body').width()-smap.el.find('.floatbox').width())/2
            }
            smap.el.find('.floatbox').css({'left':left,'top':'40px'});
            smap.el.find('.floatbox').css({'bottom':'40px' });
            smap.el.find('.floatbox__map').animate({ scrollTop: 0 }, '1');

            $('.box-overlay').fadeIn();
            $('.floatbox').fadeIn();


            maplistEventLisner();

        }

        this.closeDialog = function () {
            $('.box-overlay').fadeOut();
            $('.floatbox').fadeOut();
            $('.floatbox__map .contents-iframe').fadeOut();
            $('.search-modal-detail').fadeOut();
        }

        var maplistEventLisner = function () {

            var smap = searchmap;

            $('.box-overlay,.floatbox .btn-close').on('click', function () {
                smap.mapBukkenlist.closeDialog();
            });
            $('.floatbox .btn-close').on('click', function () {
                smap.mapBukkenlist.closeDialog();
            });

            //物件一覧ページャー
            $('.floatbox__map .article-pager a').on('click', function(e){
                var $clicked, $btn, $pager, total, per_page, current, go_to, last ;

                if (e.target.nodeName.toLowerCase() !== 'a') {
                    return false;
                }

                var search = new smap.search.list.search(smap.el);
                search.abort();
                search.showLoading();

                $clicked = $(e.target);
                $btn = $clicked.closest('li');
                $pager = $btn.closest('.article-pager');

                total = parseInt($pager.closest('.count-wrap').find('.total-count span').text().replace(/,/g, ''));
                per_page = smap.el.find('.sort-select select').first().find(':selected').val() ?
                    smap.el.find('.sort-select select').first().find(':selected').val() :
                    listSetting.perPage;

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

                listSetting.page = go_to;
                updatelist();
                return false;
            });

            //詳細ページ
            $('.floatbox__map .btn-detail a').on('click', function(){
                var href = $(this)[0].href;
                openDetailPage(href);
                return false;
            });


            // 表示件数と並び替え
            var $selections = $('.floatbox__map .sort-select select');
            $selections.on('change', function(){
                var $select = $(this);
                // 表示変数
                if($select.get(0) == $selections.get(0)) {
                    listSetting.page = 1;
                    listSetting.perPage = $select.val();
                    smap.app.cookie.updateSearch({total: listSetting.perPage});

                }
                // 並び替え
                else if($select.get(0) == $selections.get(1)){
                    listSetting.page = 1;
                    listSetting.sort = $select.val();
                    smap.app.cookie.updateSearch({sort: listSetting.sort});

                }
                updatelist();
            });

            // テーブルヘッダのソート
            $('.floatbox__map .sort-table a').on('click', function (e) {
                var search = new smap.search.list.search(smap.el),
                    $target;

                search.abort();
                search.showLoading();
                $target = $(e.target);
                // pic
                if ($target.closest('th').hasClass('cell1')) {
                    listSetting.pic = $target.attr('class') === 'floor-plan' ? 2 : 1;
                }
                // sort
                else {
                    smap.app.cookie.updateSearch({sort: $target.closest('span').data('value')});
                }
                listSetting.page = 1;
                updatelist();
                return false;
            });

            //お気に入り
            var favorite = new smap.search.common.favorite(smap.el);
            favorite._init();
            favorite._add('.floatbox__map .btn-fav');

            //全選択
            var $checkAll = $('.collect-processing :checkbox');
            $checkAll.on('change', function (e) {
                var checked = $(e.target).prop('checked');
                smap.el.find('.article-object-wrapper .object-check :checkbox').prop('checked', checked);
                $checkAll.prop('checked', checked);
            });
            smap.el.find('.object-check :checkbox').on('change', function (e) {
                var $target, $wrapper, $all, $checked;
                $target = $(e.target);
                $wrapper = $target.closest('.article-object-wrapper');
                $all = $wrapper.find('.object-check :checkbox');
                $checked = $all.filter(function (i) {
                    return $all.eq(i).prop('checked');
                });
                $checkAll.prop('checked', $all.length === $checked.length);
            });

            //情報の見方とページャーの挙動
            var list =　new smap.search.list.list(smap.el);
            list.run();

            //閲覧済み
            var history = new smap.search.common.history(smap.el);
            history.run();

            //お問い合わせ
            var contact =　new smap.search.common.contact(smap.el);
            contact.run();

        }

    }//地図物件一覧




    /** 地図サイド物件一覧
     *
     */
    this.mapsidelist = new function () {

        this.bukkenIds;

        var MAPSIDELIST_PER_PAGE = 30;

        this.init = function () {
            var smap = searchmap;
            //地図検索APIのコールバックを登録
            smap.api.addCallback('mapsidelist', updateSideBukkenListCallback);
            toggleListEventHandler();

        }

        // アサイド物件一覧表示/非表示イベント
        var toggleListEventHandler = function () {
            //アサイド物件一覧
            var $blList = $('.btn__bl-list');
            $('.btn__bl-list').on('click', function(){
                $('.toggle__body_r').animate({
                    width: 'toggle',
                    overflow: 'hidden'
                });
                $('.map-bl-list').toggleClass('is-open');
            });
        }

        // 物件サイドリストを開く
        var openList = function () {
            var smap = searchmap;
            var $map_bl_list = smap.el.find('.map-bl-list');
            $map_bl_list.find('.no-select').parents('.toggle__inner').hide();
            $map_bl_list.find('.map-bl-list__header').parents('.toggle__inner').remove();
            $('.toggle__body_r').append($(smap.apiData.sidelist).find('.toggle__inner'));
            $('.toggle__body_r').show(500);
            $('.map-bl-list').removeClass('is-open');

            $('.map__pager span').removeClass('is-open');

            var curPage = parseInt($('.map__pager span').text());
            var prevPage = parseInt($('.map__pager .link__prev a').attr('data-page'));
            var nextPage = parseInt($('.map__pager .link__next a').attr('data-page'));

            if(curPage==prevPage){
                $('.map__pager .link__prev').hide();
            }
            if(curPage==nextPage){
                $('.map__pager .link__next').hide();
            }
        }

        // 物件サイドリストを閉じる
        this.closeList = function () {
            var smap = searchmap;
            var $map_bl_list = smap.el.find('.map-bl-list');
            $map_bl_list.find('.map-bl-list__header').parents('.toggle__inner').remove();
            $map_bl_list.find('.no-select').parents('.toggle__inner').show();
            $('.toggle__body_r').hide(500);
            $('.map-bl-list').addClass('is-open');
        }

        // マップサイド物件リストを更新する
        this.updateSideBukkenList = function (marker) {
            var smap = searchmap;

            // マーカーの物件情報を表示する
            var markedBukkens = marker['bukkens'];

            var bukkenIds='';
            for (var i = 0; i<markedBukkens.length; i++)  {
                bukkenIds += (i == 0) ? '': ',' ;
                bukkenIds += markedBukkens[i]['bukkenId'];
            }
            var data = {
                'type_ct':smap.shumoku,
                'per_page': MAPSIDELIST_PER_PAGE,
                'page': 1,
                'bukken_id':bukkenIds,
                'fulltext'  : smap.el.find('input[name="search_filter[fulltext_fields]"]').val(),
            };
            smap.mapsidelist.bukkenIds = bukkenIds;
            smap.api.mapsidelist(data);
        }

        var updatePage = function (page) {
            var smap = searchmap;

            var data = {
                'type_ct':smap.shumoku,
                'per_page': MAPSIDELIST_PER_PAGE,
                'page': page,
                'bukken_id':smap.mapsidelist.bukkenIds,
                'fulltext'  : smap.el.find('input[name="search_filter[fulltext_fields]"]').val(),
            };
            smap.api.mapsidelist(data);

        }

        // マップサイド物件リストを更新するコールバック
        var updateSideBukkenListCallback = function (smap) {
            openList();
            mapsidelistEvent();
            // run see more highlight
            setSeeMoreHighlight();
        }

        // イベント
        var mapsidelistEvent = function () {
            var smap = searchmap;

            //ルート検索リンク
            $('.bl-item__btn_route span').on('click', function(){
                var $blItemRoute = $(this).next('.bl-item__route');
                $blItemRoute.toggle();

                $blItemRoute.find('input[type=radio]:eq(0)').prop('checked', true);
                $blItemRoute.find('.route-error').hide();
                $blItemRoute.find('input[type=text]').val("");
                smap.searchRout.setBlItemRoute($blItemRoute);

                // 表示ボタン押下
                $blItemRoute.find('input[type=submit]').off('click');
                $blItemRoute.find('input[type=submit]').on('click', function(){

                    // ルート検索アイテム
                    var $blItemRoute = $(this).parents('.bl-item__route');

                    //マーカー情報から対象の物件を取得
                    var bukkenId = $blItemRoute.parents('.bl-item').attr('data-bukken-no');
                    var originBukken = getBukkenFromMarker(bukkenId);

                    // ルート検索
                    smap.searchRout.setOriginBukken(originBukken);
                    smap.searchRout.search();
                });
            });

            $('.route__btn_close').on('click', function(){
                var $blItemRoute = $(this).parents('.bl-item__route');
                $blItemRoute.hide();
                $blItemRoute.find('input[type=radio]:eq(0)').prop('checked', true);
                $blItemRoute.find('.route-error').hide();
                $blItemRoute.find('input[type=text]').val("");
            });


            //ストリートビュー
            $('.bl-item__btn_street span').on('click', function(){

                //マーカー情報から対象の物件を取得
                var bukkenId = $(this).parents('.bl-item').attr('data-bukken-no');
                var originBukken = getBukkenFromMarker(bukkenId);

                smap.streetview.init();
                smap.streetview.setOriginBukken(originBukken);
                smap.streetview.run();

            });

            //物件サイドページャー
            $('.map__pager a').on('click', function(){
                var page = $(this).data('page');
                updatePage(page);
            });

            //詳細ページ
            $('.bl-item__btn_detail a').on('click', function(){
                var href = $(this)[0].href;
                openDetailPage(href);
                return false;
            });

            //お気に入り
            var favorite = new smap.search.common.favorite(smap.el);
            favorite._init();
            favorite._add('.map-bl-list .bl-item__btn_fav');


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

        // see more highlight
        var setSeeMoreHighlight = function () {
            var smap = searchmap;
            var $highlightArea = smap.el.find('.highlightsArea');
            if ($highlightArea.length > 0) {
                $highlightArea.each(function () {
                    if ($(this).height() > 120) {
                        $(this).find('.grad-btn').removeClass('hide').addClass('show');
                        $(this).find('.grad-item').addClass('blind');
                    };
                });
                $highlightArea.find('.grad-btn').click( function (event) {
                    $(this).parent().find('.grad-item').removeClass('blind');
                    $(this).removeClass('show').addClass('hide');
                });
            }
        }
    }//マップサイドリスト




    /** 周辺環境
     *
     */
    this.arround = new function () {

        //キーはHTMLのクラス名を参照
        var apiInfo = [];
        /*apiInfo['station'] = {'api':'contents/giken/commuters','icon':'icon_j_h_school.png'};//駅*/
        apiInfo['scool']  = {'api':'contents/parea/school','icon':'icon_scool.png'};//学校
        apiInfo['hospital'] = {'api':'contents/parea/medical','icon':'icon_hospital.png'};//病院・診療所
        /*apiInfo['hazard']  = {'api':'contents/parea/hazard/point','icon':'icon_park.png'};//避難所*/
        apiInfo['care']    = {'api':'contents/parea/care','icon':'icon_care.png'};//介護施設
        apiInfo['conveni'] = {'api':'contents/ipc/poi/2354','icon':'icon_conveni.png'};//コンビニ
        apiInfo['police']  = {'api':'contents/ipc/poi/2145:2477:2146','icon':'icon_police.png'};//警察・消防署
        apiInfo['bank']    = {'api':'contents/ipc/poi/2423:2348:2350:2351:2352:2353','icon':'icon_bank.png'};//金融機関
        apiInfo['depart']= {'api':'contents/ipc/poi/2339:2340:2341:2342:2343:2344','icon':'icon_depart.png'};//ショッピング施設
        apiInfo['life']  = {'api':'contents/ipc/poi/2492:2493:2891','icon':'icon_life.png'};//スーパーマーケット(日常品取り扱い店)

        var gData = {};
        gData.sessionKey = "";
        gData.urlBase    = "";
        gData.arround ={};
        gData.arround.isDisp = false;
        gData.arround.curInfoWindow = null;


        this.init = function () {

            gData.arround.types=[];
            for(var type in apiInfo){
                gData.arround.types[type]={};
                gData.arround.types[type].markers=[];
            }
            //gData.markers = [];
            loginApi();
            arroundSettingLisner();
            update()

        };


        var arroundSettingLisner=function () {

            //周辺環境をすべて見る
            $('.map-option__all').on('click', function(){

                $('.map-option__around_all').toggle();
                $(this).toggleClass('open');
                $('.map-option__around_all').toggleClass('hidden');
                var $mapiconlistHeight = $('.map-option__around_all').height();
                var $mapiconlistBottom = $mapiconlistHeight + 10;
                $('.map-option__around_all').css({
                    'height': $mapiconlistHeight + 'px',
                    'bottom': '-' + $mapiconlistBottom + 'px'
                });
            });

        }

        this.updateSetting = function () {
            gData.arround.isDisp = !gData.arround.isDisp;

        }

        this.updateByMapEvent = function () {
            var i;
            var types = $('input[name="around"]:checked').map(function(){
                return $(this).closest('li').attr('class');
            }).get();

            for (i = 0; i < types.length; i++) {
                clearArrounds(types[i]);
                updateArround(types[i]);
            }
        };

        var update = function () {
            $('input[name="around"]').click(function() {
                var type = $(this).closest('li').attr('class');
                if ($(this).prop('checked')) {
                    updateArround(type);
                }else{
                    clearArrounds(type);
                }
            })
        };

        var loginApi = function () {
            var smap = searchmap;
            var self = this;


            //サーバサイドから国際航業APIにログインする
            var url      = '/api/mapkkauth';

            smap.api.addCallback('mapkkauth', function (smap) {

                // session IDを保持する
                gData.sessionKey = smap.apiData.mapkkauth.sessionid;
                gData.urlBase    = smap.apiData.mapkkauth.url_base;
                gData.userid     = smap.apiData.mapkkauth.userid;

            });

            var data = {};
            smap.api.mapkkauth(data);

        };


        /* 周辺環境をクリアする
         *
         */
        var clearArrounds = function (type) {
            var markers = gData.arround.types[type].markers;
            for (var i = 0; i < markers.length; i++) {
                markers[i]['marker'].setMap(null);
                if (markers[i]['rectangle']){
                    markers[i]['rectangle'].setMap(null);
                }
            }
        }

        /* 周辺環境を更新する
         *
         */
        var updateArround = function (type) {
            var smap = searchmap;

            // ズーム率が規定値以下の場合は更新しない
            if (smap.gmap.getZoom() < MAP_MAPPING_MAX_ZOOM){
                return;
            }

            var mapBbounds = smap.gmap.getBounds();
            var mapSouthWestLat = mapBbounds.getSouthWest().lat();
            var mapSouthWestLng = mapBbounds.getSouthWest().lng();
            var mapNorthEastLat = mapBbounds.getNorthEast().lat();
            var mapNorthEastLng = mapBbounds.getNorthEast().lng();

            var url		= gData.urlBase + apiInfo[type].api;
            var param = {bbox:mapSouthWestLng+':'+mapSouthWestLat+':'+mapNorthEastLng+':'+mapNorthEastLat};

            api(url, param, function (res) {
                if (res.status == -1 || res.count <= 0) {
                    return ;
                }

                var resData = res.data;
                var i;
                var anchorPoint = new google.maps.Point(0, -24);
                var markerLabelOrigin = new google.maps.Point(25, 0);

                var device = 'pc';
                var icon = '/'+device+'/imgs/'+ apiInfo[type].icon;
                var markerIcon = {url: icon, labelOrigin: markerLabelOrigin};

                var latlng;
                for (i = 0; i < resData.length; i++) {
                    var apiData = resData[i];

                    latlng = new google.maps.LatLng(apiData.geometry.coordinates[1],apiData.geometry.coordinates[0]);

                    var markerOption = {map: smap.gmap, position: latlng, anchorPoint: anchorPoint, icon: markerIcon};
                    var markInf=[];
                    markInf['marker'] = new google.maps.Marker(markerOption);
                    markInf['apidata'] = apiData;

                    gData.arround.types[type].markers[i] = markInf;
                    arroundMarkerEvent(markInf['marker'],type,i);
                }

            }).fail(function () {
                console.log("schoolApi.fail()");
            });
        }

        // マーカクリック
        var arroundMarkerEvent = function (marker, type, markerIdx) {
            var smap = searchmap;

            var data = gData;
            //クリックされたマーカー
            var markerInfo = data.arround.types[type].markers[markerIdx];
            var marker =  markerInfo['marker'];
            var apiData =  markerInfo['apidata'];
            var gmap = smap.gmap;

            var content="";
            switch(type){
                case 'scool':
                    var address = apiData.properties.address.replace(/\s+/g, "");

                    /* 先頭要素を削除する場合
                     var address = apiData.properties.address.split("　");
                     address.shift();//先頭削除
                     address = address.join('');
                     */

                    content = apiData.properties.schoolname + "<br>" + address;
                    break;
                /*case 'station':
                 content = apiData.properties.col_2+"-"+apiData.properties.col_4;
                 break;*/
                case 'hospital':
                    content = apiData.properties.kanjiname + "<br>" +
                        apiData.properties.addr_city + apiData.properties.addr_town;
                    break;
                /*case 'hazard':
                 content = apiData.properties.meisho;
                 break;*/
                case 'care':
                    content = apiData.properties.shisetumei + "<br>" +
                        apiData.properties.addr_city + apiData.properties.addr_town;
                    break;
                case 'bank':
                case 'conveni':
                case 'police':
                case 'depart':
                case 'life':
                    content = apiData.properties.col_5 + "<br>" + apiData.properties.address;
                    break;
            }

            var infoWindowOption = {disableAutoPan:"false",	zIndex:1000};
            infoWindowOption.content = content;
            var infoWindow = new google.maps.InfoWindow(infoWindowOption);
            google.maps.event.addListener( marker , 'click' , function(){
                if(gData.arround.curInfoWindow){
                    gData.arround.curInfoWindow.close();
                }
                infoWindow.open(gmap, marker);
                gData.arround.curInfoWindow=infoWindow;

                google.maps.event.addListener(infoWindow, 'closeclick', function (event) {
                    gData.arround.curInfoWindow = null;
                });
            });
        }


        /* 国際航業API
         *
         */
        var api = function(url, data, fn) {
            var smap = searchmap;
            var sessionKey=gData.sessionKey;

            data.userid=gData.userid;

            var defer = $.ajax(url, {
                dataType: 'json',
                method: 'GET',
                headers: {
                    'kkc_cds_session': sessionKey
                },
                data: data
            });

            defer.success(function (res) {

                fn && fn(res);

            })
                .fail(function (xhr, statusText) {
                    if (app.unload) {
                        return;
                    }
                    if (statusText === 'abort') {
                        return;
                    }
                    smap.modal.dispMessageModal(
                        '<p></p>'+
                        '<p>通信に失敗しました。</p>'
                    );
                    return;

                });
            return defer;
          }



    }


    /** ルート検索 クラス
     *
     */
    this.searchRout = new function () {

        this.$blItemRoute=null;
        this.origin = {};
        this.goal = {};
        this.result ={};
        this.result.rendreres=[];
        this.result.infoWindows=[];


        this.setBlItemRoute = function ($blItemRoute) {
            if(this.$blItemRoute!=null&&this.$blItemRoute.get(0)!=$blItemRoute.get(0)){
                this.$blItemRoute.hide();
            }
            this.$blItemRoute = $blItemRoute;
        }

        this.setOriginBukken = function (originBukken) {
            this.origin.originBukken = originBukken;
        }


        this.search = function () {

            this.clearRoute();

            // 目的地
            this.goal.placeName = this.$blItemRoute.find('input[type=text]').val();

            // 移動手段
            var how = null;
            if (this.$blItemRoute.find('input[type=radio]:checked').val()=='0'){
                how = google.maps.DirectionsTravelMode.WALKING;
            } else if (this.$blItemRoute.find('input[type=radio]:checked').val()=='1'){
                how = google.maps.DirectionsTravelMode.DRIVING;
            }

            var msg = "";
            if(this.goal.placeName==null || this.goal.placeName==""){
                msg= "目的地が見つかりません";
            }else if(how==null){
                msg="移動手段が選択されていません";
            }
            this.errorMsg(msg);


            var directionService = new google.maps.DirectionsService();
            directionService.route(
                {
                    origin: this.origin.originBukken.latlng, // 出発元は対象の物件
                    destination: this.goal.placeName,
                    provideRouteAlternatives: false,
                    travelMode:how,
                },
                callbackResult
            );

        }

        // エラーメッセージを表示する
        this.errorMsg = function (msg) {
            if (msg==null||msg==""){
                this.$blItemRoute.find('.route-error').hide();
            }else{
                this.$blItemRoute.find('.route-error').show();
            }
            this.$blItemRoute.find('.route-error').text(msg);
        }


        // ルートを削除する
        this.clearRoute = function (msg) {
            var smap = searchmap;

            // すでに表示されているマーカーがあったら消す
            var i;
            var rendreres = smap.searchRout.result.rendreres;
            for (i = 0; i < rendreres.length; i++) {
                rendreres[i].setMap(null);
            }

            var infoWindows = smap.searchRout.result.infoWindows;
            for (i = 0; i < infoWindows.length; i++) {
                infoWindows[i].close();
            }

            smap.searchRout.result.rendreres = [];
            smap.searchRout.result.infoWindows = [];

        }

        //
        var callbackResult = function (routeData, status) {
            var smap = searchmap;

            if (status != google.maps.DirectionsStatus.OK) {
                smap.searchRout.errorMsg("目的地が見つかりません");
                return;
            }

            var polyLineOptionsMain = {strokeWeight: 8, strokeColor: "#4169E1"};
            var renderOptionMain    = {map:smap.gmap, suppressMarkers:false, preserveViewport:true, polylineOptions:polyLineOptionsMain};

            var polyLineOptionsSub = {strokeWeight: 8, strokeColor: "#2F4F4F", strokeOpacity: "0.5"};
            var renderOptionSub    = {map:smap.gmap, suppressMarkers:true, preserveViewport:true, polylineOptions:polyLineOptionsSub};

            // sub→mainの順にレンダリングする
            for (var i =routeData.routes.length-1 ; i >= 0 ; i--) {
                var renderOption = (i==0) ? renderOptionMain : renderOptionSub;
                var renderer = new google.maps.DirectionsRenderer(renderOption);
                renderer.setDirections(routeData);
                renderer.setRouteIndex(i);

                smap.searchRout.result.rendreres.push(renderer);
                smap.searchRout.result.infoWindows.push();

                // 吹き出し：ルートの中間地点あたりに全体の距離と時間を表示する
                var route = routeData.routes[i];
                var leg = route.legs[0];
                var stepDistans = 0;
                var steplen = leg.steps.length;
                var infoWPos;
                if(i==0){
                    infoWPos = leg.distance.value*0.0;
                }else if(i==1){
                    infoWPos = leg.distance.value*0.3;
                }else{
                    infoWPos = leg.distance.value*0.5;
                }

                var infoWindowOption = { disableAutoPan:"false"};
                for (var j =0 ; j < steplen; j++) {
                    var step = leg.steps[j];
                    stepDistans += step.distance.value;
                    if(stepDistans >= infoWPos ){
                        var infoWindows = new google.maps.InfoWindow(infoWindowOption);
                        infoWindows.setContent(leg.distance.text+"<br>"+leg.duration.text );
                        infoWindows.setPosition(step.start_location);
                        infoWindows.open(smap.gmap);
                        smap.searchRout.result.infoWindows.push(infoWindows);
                        break;
                    }
                }
            }
        }
    }



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

                smap.el.find('.floatbox .modal-streetview').remove();

                smap.el.find('.floatbox__map').append(
                    '<div class="contents-iframe modal-streetview"></div>'
                );

                if(smap.el.find('.floatbox .btn-close').length == 0){
                    smap.el.find('.floatbox__map').after('<p class="btn-close">閉じる</p>');
                }
                var left=10;
                if($('body').width()>smap.el.find('.floatbox').width()){
                    left = ($('body').width()-smap.el.find('.floatbox').width())/2
                }

                smap.el.find('.floatbox').css({'top':'40px','bottom':'40px','left':'40px','right':'40px','width':'auto'});
                $('.contents-iframe.modal-streetview').css({'top':'0px','bottom':'0px','left':'0px','height':'100%'});

                // ストリートビューオブジェクト生成
                var streetviewPano = new google.maps.StreetViewPanorama(
                    smap.el.find('.contents-iframe.modal-streetview')[0]
                );

                // ストリートビューオブジェクト詳細設定
                streetviewPano.setPov({heading: -20, pitch: 0, zoom: 0});
                streetviewPano.setVisible(true);

                smap.gmap.setStreetView(streetviewPano);
                smap.streetview.streetviewPano = streetviewPano;

                var latlng = results.location.latLng;
                smap.streetview.streetviewPano.setPosition(latlng);


                smap.el.find('.box-overlay').fadeIn();
                smap.el.find('.floatbox').fadeIn();
                smap.el.find('.floatbox__map').fadeIn();
                smap.el.find('.contents-iframe.modal-streetview').fadeIn();

                // 閉じる
                $('.box-overlay, .btn-close').off();
                $('.box-overlay, .btn-close').on('click', function () {

                    $('.box-overlay').fadeOut();
                    $('.floatbox').fadeOut();
                    $('.search-modal-detail').fadeOut();
                    $('.floatbox__map .contents-iframe').fadeOut();
                    smap.el.find('.floatbox .modal-streetview').remove();
                    smap.el.find('.floatbox').css({'width':''});
                });


            }else if(status == google.maps.StreetViewStatus.ZERO_RESULTS){

                smap.modal.dispMessageModal(
                    '<p>こちらの物件はストリートビューに対応しておりません。</p>'
                );
            }else{
                smap.modal.dispMessageModal(
                    '<p>ストリートビューの取得に失敗しました。</p>'
                );
            }
        }
    }


    this.userevent = new function () {

        this.init = function () {

            //アサイド条件を変更するボタン
            $('.btn__map-change').on('click', function(){
                $('.toggle__body_l').animate({
                    width: 'toggle',
                    overflow: 'hidden'
                });
                $('.map-change').toggleClass('is-open');
            });


            // グロナビ
            $('.btn__gnav_toggle').on('click', function(){
                if( !($('#twitter-sapn').length ) ) {
	            	$('.twitter-tweet-button').attr( 'style','position: static; visibility: visible; width: 79px; height: 20px;' ) ;
	            	$('.twitter-tweet-button').wrapAll('<span id="twitter-sapn" style="vertical-align: bottom; width: 80px; height: 20px;">');
                }

                $('.page-header-liquid').toggleClass('close');
                $('.gnav').toggle();
                $('.page-header .inner:nth-of-type(2)').toggleClass('show');
                $('.page-header-top .link2').toggle();
                $('.page-header-top .tx-explain').toggle();
                $('.page-header-top .logo-s').toggle();
                $('.page-header-top .tel-s').toggleClass('show');
                $('.page-header-top .company-img').toggleClass('show');
                $('.btn__gnav_toggle').toggleClass('open');
                $('.maps-header .link li:first-of-type').toggleClass('show');
                var mapsHeaderHeight = $('.maps-header').outerHeight() ;
                $('.contents-map').css({
                    'top': mapsHeaderHeight + 'px'
                });
            });

        }

        $(function(){
            // ヘッダ
            $(window).on('load', function(){
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
        this.API_MAP_KK_AUTH  = 'mapkkauth';

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
                    this.callback.mapsidelist =callback;
                    break;
                case 'mapkkauth':
                    this.callback.mapkkauth =callback;
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
            if(data.side_or_modal==undefined){
                data.side_or_modal   ='side';
            }
            data.map_initialized = smap.status.initialized ? 1 : 0;

            var apiUrl = getApiUrl(this.API_MAP_UPDATE);


            if (smap.api.updateTimeoutId) {
                clearTimeout(smap.api.updateTimeoutId);
                console.log('clear time out: '+smap.api.updateTimeoutId);
            }

            smap.api.updateTimeoutId = setTimeout(function () {
                console.log('post: '+smap.api.updateTimeoutId);
                smap.processingCount.mapupdate++;
                //smap.app.customConsoleLog('smap.processingCount.mapupdate=' + smap.processingCount.mapupdate + " @process start");

                post(apiUrl, data, function (res) {
                    // success
                    smap.apiData.coordinates = res.content.coordinates;
                    smap.apiData.coordinates_total_count = res.content.total_count;
                    smap.apiData.aside = res.aside;
                    smap.apiData.hidden = res.hidden;
                    smap.apiData.detachedHouse = res.detachedHouse;
                    smap.api.callback.mapUpdate(smap);

                    if(smap.processingCount.mapupdate>0){
                        smap.processingCount.mapupdate--;
                        //smap.app.customConsoleLog('smap.processingCount.mapupdate=' + smap.processingCount.mapupdate + " @process end");
                    }

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

        /** API：マーカーに紐づく物件のサイドリストを取得する
         *
         */
        this.mapsidelist = function (data) {

            var smap = searchmap;

            if(smap.isSpecial){
                data.special_path=smap.specialPath;
            }

            var apiUrl = getApiUrl(this.API_MAP_SIDELIST);

            post(apiUrl, data, function (res) {
                // success
                smap.apiData.sidelist = res.content;
                smap.api.callback.mapsidelist(smap);
            });
        }

        /** API：国際航業ログイン
         *
         */
        this.mapkkauth = function (data) {
            var smap = searchmap;

            if(smap.isSpecial){
                data.special_path=smap.specialPath;
            }

            var apiUrl = getApiUrl(this.API_MAP_KK_AUTH);

            post(apiUrl, data, function (res) {
                // success
                smap.apiData.mapkkauth = res;
                smap.api.callback.mapkkauth(smap);
            });
        }



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
                smap.$loading.hide();
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
                smap.$loading.hide();
                var status = apiErrorHandler(res);
                if (status == 'abort') {
                    return;
                }
                // abort
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
                smap.$loading.hide();
                smap.status.updating = false;
                smap.processingCount.mapupdate=0;
                smap.event.mapupdate = EVENT_MAPUPDATE_FOR_DEFAULT;
            }

            return status;
        }

    };

}