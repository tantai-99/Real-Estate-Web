fdpmap = new function() {
    'use strict';

    var SMALL_CIRCLE = 800;
    var BIG_CIRCLE = 1600;
    var DEFAULT_ZOOM = 14;
    var MESSAGE_ERROR = '現在通信エラーが発生し、ご利用できません。<br>しばらく後に再度読み込みをおこなってください。';
    var MESSAGE_NOT_ROUTE = '申し訳ございません。ルートの取得ができません';

    var LIMIT_FACILITY = 3;
    var MAX_FACILITY = 5;
    var RAD_FACILITY = 2000;
    var SORT = 'A:distance';
    var ANIMATE_TIME = 500;

    var map;
    var markers = {};
    var gData = {};
    gData.sessionKey = "";
    gData.urlBase    = "";
    var apiData = {};
    var apiInfo = {
        currentlocation: {icon: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png'},
        currentStation: {icon: '/sp/imgs/fdp/pin_station.svg'},
        house: {icon: '/sp/imgs/fdp/house.svg'},
        station: {api: 'contents/ipc/eki/', icon: '/sp/imgs/fdp/station.svg'},
        greenmode: {api: 'contents/surroundings/green.geojson'},
        supermaket: {api: ['contents/ipc/poi/2493']},
        conviencestore: {api: ['contents/ipc/poi/2354']},
        drugstore: {api: ['contents/ipc/poi/2891']},
        discountstore: {api: ['contents/ipc/poi/2343:2920']},
        restaurent: {api: ['contents/ipc/poi/2356:2312:2317']},
        department: {api: ['contents/ipc/poi/2339:2342:2340']},
        shopping: {api: ['contents/ipc/poi/2491:2923:2344:2492:2895']},
        park: { api: ['contents/ipc/poi/2179']},
        public: {api: ['contents/ipc/poi/2893:2141:2142:2143:2144:2145:2146:2477']},
        financial: {api: ['contents/ipc/poi/2348:2350:2351:2352:2353:2346']},
        hospital: {api: ['contents/ipc/poi/2150']},
        // carecenter: {api: 'contents/parea/2017/care', faIcon: 'fa fa-shopping-basket'},
        nurseryschool: {api: ['contents/parea/school', 'contents/kyouikusol/nursery_school']},
        highschool: {api: ['contents/ipc/poi/2173:2171']},
        university: {api: ['contents/ipc/poi/2169:2166:2167:2168']},
        coinparking: {api: ['contents/ipc/poi/9901']},
        car: {api: ['contents/ipc/poi/2335:9902']},
        busstop: {api: ['/contents/jorudan/busstops']},
    };

    this.run = function (_app,search,$el){
        var fdp = fdpmap;
        fdp.app = _app;
        fdp.search  = search;
        fdp.el  = $el;
        apiInfo["currentlocation"].position = new google.maps.LatLng(latlng.lat, latlng.lng);
        apiInfo['currentlocation'].scaledSize = new google.maps.Size(20, 20); // scaled size
        fdp.initMap.init();
        fdp.facility.init();
        loginApi();
    }

    var loginApi = function () {
        var url      = '/api/mapkkauth';
        var data = {};
        post(url, data, function (res) {
            // session IDを保持する
            gData.sessionKey = res.sessionid;
            gData.urlBase    = res.url_base;
            gData.userid     = res.userid;
        });
    }
    
    var api = function(url, data, fn) {
        var fdp = fdpmap;
        var sessionKey=gData.sessionKey;

        data.userid=gData.userid;

        var defer = $.ajax(url, {
            async: false,
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
                fdp.facility.isErrorOrNoCount(true);
                popupError(MESSAGE_ERROR);
                return;

            });
        return defer;
      }
    
    var post = function (apiUrl,data,callback) {
        var fdp = fdpmap;
        $.ajax({
            type: 'POST',
            url: apiUrl,
            data: data,
            timeout: 120 * 1000,
            dataType: 'json'

        }).done(function (res) {


            fdp.app.customConsoleLog('----- ajax response -----');
            fdp.app.customConsoleLog(res);
            fdp.app.customConsoleLog('----- ajax response end -----');

            callback(res);

        }).fail(function (res) {

            fdp.app.customConsoleLog('----- ajax failed -----');
            fdp.app.customConsoleLog(res);
            fdp.app.customConsoleLog('----- ajax failed end -----');

            if (status == 'abort') {
                return;
            }
        });
    }

    this.initMap = new function() {
        var circlemap;
        var flag = 0;
        var mapCircle;
        var houseCircle =[];
        var circleLabels = [];
        var directionsDisplay, markerStation;
        this.init = function() {
            map = new google.maps.Map(document.getElementById('map-canvas-fdp'), {
                center: apiInfo["currentlocation"].position,
                zoom: DEFAULT_ZOOM,
                disableDefaultUI: true
            });
            circlemap = {
                circle_a: {
                    center: apiInfo["currentlocation"].position,
                    size: SMALL_CIRCLE
                },
                circle_b: {
                    center: apiInfo["currentlocation"].position,
                    size: BIG_CIRCLE
                }
            }
            google.maps.event.addListener(map, 'idle', eventChanged);
            // google.maps.event.addListener(map, 'idle', dragend);
            pinHouse();
            drawButton(); 
        }

        var customPin = function(latlng, map, imageSrc, typePin ) {
            var view = new google.maps.OverlayView();
            view.onAdd = function() {
                view.draw = function() {
                    var self = this;
                    var div = self.div_;
                    if (!div) {
                        div = self.div_ = document.createElement('div');
                        var classPin;
                        if (typePin == true) {
                            classPin = 'customPinHouse';
                        } else {
                            classPin = 'customPinStation';
                        }
                        div.className = classPin;
                        var img = document.createElement("img");
                        img.src = imageSrc;
                        div.appendChild(img);
                        var panes = self.getPanes();
                        panes.overlayImage.appendChild(div);

                    }
                    var point = self.getProjection().fromLatLngToDivPixel(latlng);
                    if (point) {
                        div.style.left = point.x + 'px';
                        div.style.top = point.y + 'px';
                    }
                };
            }
          
            view.remove = function() {
                // Check if the overlay was on the map and needs to be removed.
                if (this.div_) {
                    this.div_.parentNode.removeChild(this.div_);
                    this.div_ = null;
                }
            };
          
            view.getPosition = function() {
                return latlng;
            };
            view.setMap(map);
            return view;
        }

        var pinHouse = function() {
            var fdp = fdpmap;
            customPin(apiInfo["currentlocation"].position, map, apiInfo["currentlocation"].icon, true);
        }
    
        var pinStation = function() {
            markerStation = customPin(apiInfo["station"].position, map, apiInfo["currentStation"].icon, false);
        }

        var drawButton = function() {
            var fdp = fdpmap;
            addGeoJsonDefault();
            fdp.el.find('.map-back-position').on('click', function (e) {
                map.panTo(apiInfo["currentlocation"].position);
                return false;
              });
            fdp.el.find('.btn-mode').on('click',function(e) {
                e.preventDefault();
                var menuMode = fdp.el.find('.menu-mode');
                if(menuMode.height() === 0){
                    displayViewFacility();
                    autoHeightAnimate(menuMode, ANIMATE_TIME);
                    checkDefaultActive();
                } else {
                    menuMode.stop().animate({ height: '0' }, ANIMATE_TIME);
                }
            });

            fdp.el.find('.menu-mode .close_list').on('click', function(e) {
                e.preventDefault();
                fdp.el.find('.menu-mode').stop().animate({ height: '0' }, ANIMATE_TIME);
            });

            // green mode
            fdp.el.find('.btn-map-green-mode').on('click',function(e) {
                e.preventDefault();
                if ($(this).hasClass('active')) {
                    if (!(fdp.el.find('.fdp-error').length > 0)) {
                        if (typeof map.data.getStyle() == 'undefined' || (typeof map.data.getStyle().visible && map.data.getStyle().visible == false )) {
                            if (!(fdp.el.find('.fdp-error-green').length > 0)) {
                                return;
                            }
                        }
                    }
                    clearGreenMode();
                    fdp.el.find('.fdp-error-green').remove();
                    fdp.el.find('.btn-map-green-mode').removeClass('active');
                    fdp.el.find('.green-map-note').removeClass('show');
                    checkDefaultActive();
                } else {
                    $(this).addClass('active');
                    fdp.el.find('.btn-map-default').removeClass('active');
                    fdp.el.find('.green-map-note').addClass('show');
                    fdp.el.find('.green-map-note').css('bottom', fdp.el.find('.fdp-annotation').outerHeight(true) + 10);
                    greenMode();
                }
                fdp.el.find('.fdp-error').remove();
            });
            // click button to display root
            fdp.el.find('.btn-map-route').on('click', (function(e)  {
                e.preventDefault();
                fdp.el.find('.fdp-error').remove();
                if ($(this).hasClass('active')) {
                    clearRoute();
                    checkDefaultActive();
                } else {
                    $(this).addClass('active');
                    fdp.el.find('.btn-map-default').removeClass('active');
                    getPositionStation($(this));
                }
                return;
            }));
    
            // click button to display circle
            fdp.el.find('.btn-map-circle').on('click', (function(e)  {
                e.preventDefault();
                fdp.el.find('.fdp-error').remove();
                if($(this).hasClass('active') == false) {
                    $(this).addClass('active');
                    fdp.el.find('.btn-map-default').removeClass('active');
                    displayCircle();
                } else {
                    clearCircle();
                    checkDefaultActive();
                }
                return;
                
            }));
    
            // click button to back origin map
            fdp.el.find('.btn-map-default').on('click', (function(e)  {
                e.preventDefault();
                fdp.el.find('.fdp-error').remove();
                $(this).addClass('active');
                clearCircle();
                clearRoute();
                clearGreenMode();
                fdp.el.find('.btn-map-green-mode').removeClass('active');
                fdp.el.find('.green-map-note').removeClass('show');
                // fdp.el.find('.facility-map ul li.active').each(function() {
                //     fdp.facility.clearMarkers($(this).attr('id'));
                // });
            }));
    
        }

        var lastZoom = DEFAULT_ZOOM;
        var eventChanged = function () {
            var fdp = fdpmap;

            if (fdp.el.find('.btn-map-circle').hasClass('active') && map.zoom != lastZoom) {
                displayCircle();
                lastZoom = map.zoom;
            }

            if (fdp.el.find('.btn-map-green-mode').hasClass('active')) {
                setTimeout(function() {
                    greenMode();
                }, 800);
            }
        }
        var dragend = function () {
            var fdp = fdpmap;
            if (fdp.el.find('.btn-map-green-mode').hasClass('active')) {
                setTimeout(function() {
                    greenMode();
                }, 800);
            }
        }

        var getPositionStation = function(elem) {
            var fdp = fdpmap;
            var params = {lat: latlng.lat, lon: latlng.lng, rad: 10000, limit: 1, sort: SORT};
            var url = gData.urlBase + apiInfo['station'].api;
            api(url, params, function(res) {
                if (res.status == -1) {
                    popupError(MESSAGE_ERROR);
                    return;
                }
                if (res.count == 0) {
                    popupError(MESSAGE_NOT_ROUTE);
                    return;
                }
                var apiData = res.data[0];
                apiInfo['station'].position = new google.maps.LatLng(apiData.geometry.coordinates[1], apiData.geometry.coordinates[0]);
                pinStation();
                calculateRoute(elem);
            });
        }

        var displayCircle = function() {
            //Add the circle to the map.
            for (var i = 0; i < houseCircle.length; i++) {
                houseCircle[i].setMap(null);
            }
            houseCircle = [];
            for (var circle in circlemap) {
                // Construct the circle for each value in citymap.
                if (map.zoom < 16) {
                    var meterPerPx  = 156543.03392 * Math.cos(latlng.lat * Math.PI / 180) / Math.pow(2, map.zoom);
                    var radius = circlemap[circle].size;
                    var isPX = radius * 2 / meterPerPx;
                    houseCircle.push(new google.maps.Marker({
                        position: apiInfo["currentlocation"].position,
                        map: map,
                        icon: { 
                            path :circlePath(isPX/2),
                            strokeColor: '#FF0000',
                            strokeWeight: 2,
                             origin: new google.maps.Point(0, 0),
                            // The anchor for this image is the base of the flagpole at (0, 32).
                            anchor: new google.maps.Point(isPX/2, isPX/2)
                          },
                        clickable:false
                      }));
                } else {
                    houseCircle.push(new google.maps.Circle({
                        strokeColor: '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '',
                        fillOpacity: 0,
                        map: map,
                        center: circlemap[circle].center,
                        radius: circlemap[circle].size,
                        zIndex: 1000,
                        clickable: false,
                    }));
                }
            }
            displayLabelCircle();
        }
        function circlePath(r){
            return 'M '+r+' '+r+' m -'+r+', 0 a '+r+','+r+' 0 1,0 '+(r*2)+',0 a '+r+','+r+' 0 1,0 -'+(r*2)+',0';
        }

        var displayLabelCircle = function() {
            if (circleLabels.length > 0) {
                for(var i = 0; i < circleLabels.length; i++) {
                    circleLabels[i].setMap(null);
                }
                circleLabels = [];
            }
            // extend map function to calculate coordinate through distant
            Number.prototype.toRad = function() {
                return this * Math.PI / 180;
             }
        
             Number.prototype.toDeg = function() {
                return this * 180 / Math.PI;
             }
        
             google.maps.LatLng.prototype.destinationPoint = function(brng, dist) {
                dist = dist / 6371;  
                brng = brng.toRad();  
        
                var lat1 = this.lat().toRad(), lon1 = this.lng().toRad();
        
                var lat2 = Math.asin(Math.sin(lat1) * Math.cos(dist) + 
                                     Math.cos(lat1) * Math.sin(dist) * Math.cos(brng));
        
                var lon2 = lon1 + Math.atan2(Math.sin(brng) * Math.sin(dist) *
                                             Math.cos(lat1), 
                                             Math.cos(dist) - Math.sin(lat1) *
                                             Math.sin(lat2));
        
                if (isNaN(lat2) || isNaN(lon2)) return null;
        
                return new google.maps.LatLng(lat2.toDeg(), lon2.toDeg());
             }
            var housingCurrent = new google.maps.LatLng(circlemap.circle_a.center.lat(),circlemap.circle_a.center.lng());
            var big = 1.605;
            var small = 0.805;
            if (map.getZoom() <= 19) {
                big = 1.75 - (map.getZoom() - 14)*0.025;
                small = 0.95 - (map.getZoom() - 14)*0.025;
            }
            circleLabels.push(customLabelCircle(housingCurrent.destinationPoint(0, big), map, '徒歩20分圏内の目安'));
            circleLabels.push(customLabelCircle(housingCurrent.destinationPoint(0, small), map, '徒歩10分圏内の目安'))
        }

        var customLabelCircle = function(latlng, map, content ) {
            var view = new google.maps.OverlayView();
            view.onAdd = function() {
                view.draw = function() {
                    var self = this;
                    var div = self.div_;
                    if (!div) {
                        div = self.div_ = document.createElement('div');
                        div.className = 'customLabelCircle';
                        var divChild = document.createElement("div");
                        divChild.textContent = content;
                        div.appendChild(divChild);
                        var panes = self.getPanes();
                        panes.overlayImage.appendChild(div);

                    }
                    var point = self.getProjection().fromLatLngToDivPixel(latlng);
                    if (point) {
                        div.style.left = point.x + 'px';
                        div.style.top = point.y + 'px';
                    }
                };
            }
            view.remove = function() {
                // Check if the overlay was on the map and needs to be removed.
                if (this.div_) {
                    this.div_.parentNode.removeChild(this.div_);
                    this.div_ = null;
                }
            };
            view.getPosition = function() {
                return latlng;
            };
            view.setMap(map);
            return view;
        }

        var clearCircle = function() {
            var fdp = fdpmap;
            if (houseCircle.length > 0) {
                for (var i = 0; i < houseCircle.length; i++) {
                    houseCircle[i].setMap(null);
                    circleLabels[i].setMap(null);
                    
                }
                houseCircle = [];
                circleLabels = [];
            }
            fdp.el.find('.btn-map-circle').removeClass('active');
        }

        var clearRoute = function() {
            var fdp = fdpmap;
            if (markerStation != null) {
                markerStation.setMap(null);
                markerStation = null;
            }

            if (directionsDisplay != null) {
                directionsDisplay.setMap(null);
                directionsDisplay = null;
            }
            fdp.el.find('.btn-map-route').removeClass('active');
        }

        var clearGreenMode = function() {
            var fdp = fdpmap;
            map.data.setStyle({visible: false});
            map.data.forEach(function (feature) {
                map.data.remove(feature);
            });
            google.maps.event.clearListeners(map, 'zoom_changed');
            google.maps.event.clearListeners(map, 'dragend');
        }

        var calculateRoute = function(elem) {
            // Instantiate a directions service.
            var directionsService = new google.maps.DirectionsService;
            directionsService.route({
                origin: apiInfo["station"].position,
                destination: apiInfo["currentlocation"].position,
                avoidTolls: true,
                avoidHighways: false,
                travelMode: google.maps.TravelMode.WALKING
            }, function (response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    displayRoute(elem, response);
                } else {
                    window.alert('Directions request failed due to ' + status);
                }
            });
        }
    
        var displayRoute = function (elem, response) {
            if (directionsDisplay == null) {
                directionsDisplay = new google.maps.DirectionsRenderer({
                    map: map,
                    draggable: true,
                    preserveViewport: true,
                    /*polylineOptions: {
                        strokeColor: "#6FB3F4"
                    }*/
                    zIndex: 1000,
                });
                var suppressMarkers = directionsDisplay.setOptions({ suppressMarkers: true});
                directionsDisplay.setDirections(response);
                var fdp = fdpmap;
                fdp.el.find('.map-back-original').addClass('active');
                elem.find('a').addClass('active');
            }
        }
    
        var greenMode = function() {
            var fdp = fdpmap;
            var mapBbounds = map.getBounds();
            var mapSouthWestLat = mapBbounds.getSouthWest().lat();
            var mapSouthWestLng = mapBbounds.getSouthWest().lng();
            var mapNorthEastLat = mapBbounds.getNorthEast().lat();
            var mapNorthEastLng = mapBbounds.getNorthEast().lng();
            var bbox = mapSouthWestLng+':'+mapSouthWestLat+':'+mapNorthEastLng+':'+mapNorthEastLat;
            var area = mapSouthWestLng+':'+mapSouthWestLat+':'+mapNorthEastLng+':'+mapNorthEastLat;
            var url = gData.urlBase + apiInfo['greenmode'].api;
            var params = {bbox: bbox, area: area};
            api(url, params, function(res) {
                if (res.status == -1) {
                    popupError(MESSAGE_ERROR);
                    return;
                }
                if (fdp.el.find('.btn-map-green-mode').hasClass('active')) {
                    map.data.forEach(function (feature) {
                        map.data.remove(feature);
                    });
                    map.data.addGeoJson(res);
                    map.data.setStyle(function(feature){
                        var zoku_c = feature.getProperty('zoku_c');
                        var color = getColor(zoku_c);
                        var fillOpacity = 0.6;
                        if (zoku_c == 1) {
                            fillOpacity = 0;
                        }
                        return {
                            fillColor: color,
                            fillOpacity: fillOpacity,
                            strokeColor:color,
                            strokeOpacity: 1,
                            strokeWeight: 1,
                            visible: true,
                            zIndex: 10
                        }
                    });
                }
            }).fail(function() {
                popupError(MESSAGE_ERROR);
            });
        }
    
        var getColor = function (zoku_c) {
            var color;
            switch(zoku_c) {
                case 1:
                    color = '#EAE3CF';
                    break;
                case 2:
                    color= '#F3E1ED';
                    break;
                case 3:
                    color= '#E9ED91';
                    break;
                case 4:
                    color= '#E3BA7D';
                    break;
                case 5:
                    color= '#7CB870';
                    break
                case 6:
                    color= '#79BEE7';
                    break;
                case 51:
                    color= '#DEECD2';
                    break;
            }
            return color;
        }

        var addGeoJsonDefault = function() {
            map.data.addGeoJson({
                "type": "FeatureCollection",
                "features": [
                    {
                        "type": "Feature",
                        "properties": {
                            "mesh2_c": "533945",
                            "gid": 2078203,
                            "ken_c": null,
                            "hanrei_c": "580100",
                            "zoku_c": 1
                        },
                        "geometry": {
                            "type": "MultiPolygon",
                            "coordinates": [
                                [
                                    [
                                        [139.64505083628015, 35.66170354642502],
                                        [139.64490921214212, 35.66133332814454],
                                        [139.64452052625668, 35.661364433264],
                                        [139.6445973584104, 35.66174512371179],
                                        [139.64505083628015, 35.66170354642502],
                                    ]
                                ]
                            ]
                        }
                    }
                ]
            });
            map.data.setStyle(function(feature) {
                return {
                    fillColor: 'white',
                    fillOpacity: 0,
                    strokeColor:'white',
                    strokeOpacity: 0,
                    strokeWeight: 0,
                    zIndex: 10
                }
            })
        }
    }

    this.facility = new function() {
        var isErrorOrNoCount = false;
        var lastInfoWindow = null;
        var distanceMatrix = {};
        this.isErrorOrNoCount = function(flag) {
            isErrorOrNoCount = flag;
        };
        this.init = function() {
            var fdp = fdpmap;
            var facilityMenu = fdp.el.find('.facility-map ul');
            $('.facility-map ul li').click(function() {
                fdp.el.find('.fdp-error').remove();
                var type = $(this).attr('id');
                if ($(this).hasClass('active')) {
                    if (markers[type] == null && isErrorOrNoCount == false) {
                        return;
                    }
                    clearMarkers(type);
                } else {
                    if($('.facility-map li.active').length >= MAX_FACILITY) {
                        alert("選択できる件数は最大５件までです。");
                        return false;
                    }
                    $(this).addClass('active');
                    fdp.el.find('.btn-map-default').removeClass('active');
                    showMarker(type, $(this));
                }
            });

            fdp.el.find('.folding-menu div').on('click', function(e) {
                var classMenu = $(this).attr('class');
                $(this).removeClass('show');
                switch (classMenu) {
                    case 'btn-display-facility show' :
                    case 'btn-main-facility show' :
                        fdp.el.find('.btn-close-facility1').addClass('show');
                        fdp.el.find('.btn-close-facility2').removeClass('show');
                        fdp.el.find('.btn-all-facility').addClass('show');
                        fdp.el.find('.facility-map li.hidden').removeClass('show');
                        autoHeightAnimate(facilityMenu, ANIMATE_TIME);
                        break;
                    case 'btn-close-facility1 show' :
                        fdp.el.find('.btn-all-facility').removeClass('show');
                        fdp.el.find('.btn-display-facility').addClass('show');
                        facilityMenu.stop().animate({ height: '0' }, ANIMATE_TIME);
                        break;
                    case 'btn-close-facility2 show' :
                        fdp.el.find('.btn-main-facility').removeClass('show');
                        fdp.el.find('.btn-display-facility').addClass('show');
                        facilityMenu.stop().animate({ height: '0' }, ANIMATE_TIME);
                        break;
                    case 'btn-all-facility show' :
                        fdp.el.find('.btn-close-facility1').removeClass('show');
                        fdp.el.find('.btn-main-facility').addClass('show');
                        fdp.el.find('.btn-close-facility2').addClass('show');
                        fdp.el.find('.facility-map li.hidden').addClass('show');
                        autoHeightAnimate(facilityMenu, ANIMATE_TIME);
                        break;
                }
            });
            $(window).on('resize', function() {
                var fdp = fdpmap;
                if (facilityMenu.height() !== 0) {
                    facilityMenu.css('height', 'auto');
                }
                fdp.el.find('.green-map-note').css('bottom', fdp.el.find('.fdp-annotation').outerHeight(true) + 10);
            });
        }

        this.clearMarkers = function(type) {
            clearMarkers(type)
        }
        var clearMarkers = function(type) {
            $('#'+type).removeClass('active');
            if (markers[type]) {
                for (var i = 0; i < markers[type].length; i++) {
                    markers[type][i].setMap(null);
                }
                delete markers[type];
            }
        }
        var CustomMarker = function(param, elem, typeName, number) {
            var fdp = fdpmap
            var imageSrc = elem.find('img').attr('src');
            // imageSrc = imageSrc.replace('url(','').replace(')','').replace(/\"/gi, "");
            var latlng = new google.maps.LatLng(param.feature.geometry.coordinates[1], param.feature.geometry.coordinates[0]);
            var infoWindow = new google.maps.InfoWindow({
                content: showInfo(param, typeName),
                position: latlng,
                options: {
                    pixelOffset: new google.maps.Size(-7, -10)
                }
            });
            
            var view = new google.maps.OverlayView();
            view.onAdd = function() {
                view.draw = function() {
                    // Check if the div has been created.
                    var self = this;
                    var div = self.div_;
                    if (!div) {
                        // Create a overlay text DIV
                        div = self.div_ = document.createElement('div');
                        var img = document.createElement("img");
                        var divCircleNumber = document.createElement("div");
                        var divNumber = document.createElement("div");
                        var panes = self.getPanes();
                        img.src = imageSrc;
                        // Create the DIV representing our CustomMarker
                        div.className = "customMarker ";
                        div.style.zIndex = markers[typeName] - number;
                        divCircleNumber.className = 'circle-number '+ typeName;
                        divNumber.className = 'number';
                        divNumber.textContent = number;
                        divCircleNumber.appendChild(divNumber);
                        div.appendChild(divCircleNumber);
                        div.appendChild(img);

                        google.maps.event.addDomListener(div, "touchstart", function(event) {
                            event.stopPropagation();
                        });

                        google.maps.event.addDomListener(div, "touchend", function(event) {
                            event.stopPropagation();
                        });
                        google.maps.event.addDomListener(div, "click", function(event) {
                            if (infoWindow.getMap() == null || typeof infoWindow.getMap() == "undefined") {
                                if(lastInfoWindow) lastInfoWindow.close();
                                infoWindow.open(map);
                                lastInfoWindow = infoWindow;
                            } else {
                                infoWindow.close(map);
                                lastInfoWindow = null;
                            }
                            event.stopPropagation();
                        });
              
                        // Then add the overlay to the DOM
                        panes.overlayImage.appendChild(div);
                    }
              
                    // Position the overlay 
                    var point = self.getProjection().fromLatLngToDivPixel(latlng);
                    if (point) {
                        div.style.left = point.x + 'px';
                        div.style.top = point.y + 'px';
                    }
                };
            }
          
            view.remove = function() {
                // Check if the overlay was on the map and needs to be removed.
                if (this.div_) {
                    this.div_.parentNode.removeChild(this.div_);
                    this.div_ = null;
                    infoWindow.close(map);
                }
            };
          
            view.getPosition = function() {
                return latlng;
            };
            view.setMap(map);
            return view;
        }

        var showMarker = function(typeName, elem) {
            markers[typeName] = [];
            if (typeof distanceMatrix[typeName] == 'undefined' || distanceMatrix[typeName].length == 0) {
                distanceMatrix[typeName] = [];
                apiInfo[typeName].destination = [];
                apiInfo[typeName].api.forEach(function(apiUrl, index) {
                    var params = {lat: latlng.lat, lon: latlng.lng, rad: RAD_FACILITY};
                    if (typeName == 'nurseryschool') {
                        if (index == 0) {
                            params.filter = 'typecode:60';
                        } else {
                            params.sort = SORT;
                        }
                        params.limit = 10;
                    } else {
                        params.sort = SORT;
                        params.limit = 20;
                    }
                    var url = gData.urlBase + apiUrl;
                    api(url, params, function(res) {
                        if (res.status == -1) {
                            isErrorOrNoCount = true;
                            popupError(MESSAGE_ERROR);
                            return;
                        }
                        apiData[typeName] = res.data;
                        for (var i = 0, feature; feature = apiData[typeName][i]; i++) {
                            apiInfo[typeName].destination.push( new google.maps.LatLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]));
                            distanceMatrix[typeName].push({feature: feature});
                        }
                    }).fail(function() {
                        isErrorOrNoCount = true;
                        popupError(MESSAGE_ERROR);
                        return;
                    });
                });
                if(distanceMatrix[typeName].length <= 0) {
                    isErrorOrNoCount = true;
                    alert('半径2Kmに該当する施設はありません');
                    return;
                }
                var service = new google.maps.DistanceMatrixService();
                        service.getDistanceMatrix(
                        {
                            origins: [apiInfo['currentlocation'].position],
                            destinations: apiInfo[typeName].destination,
                            travelMode: 'WALKING',
                        }, function(response) {
                            response.rows[0].elements.forEach(function(value, index) {
                                distanceMatrix[typeName][index].element = value;
                            });
                            distanceMatrix[typeName] = distanceMatrix[typeName].sort(function(a, b) {return a.element.distance.value - b.element.distance.value}).slice(0, LIMIT_FACILITY);
                            distanceMatrix[typeName].forEach(function(param, index) {
                                markers[typeName].push(CustomMarker(param,  elem, typeName, index + 1));
                            });
                        });
            } else {
                distanceMatrix[typeName].forEach(function(param, index) {
                    markers[typeName].push(CustomMarker(param,  elem, typeName, index + 1));
                });
            }
        }
        var showInfo = function(param, typeName) {
            var info = '';
            switch(typeName) {
                // case 'carecenter':
                //     info = feature.properties.shisetumei;
                //     break;
                case 'nurseryschool':
                    if (typeof param.feature.properties.schoolname != 'undefined') {
                        name = param.feature.properties.schoolname;
                    } else {
                        name = param.feature.properties.col_2;
                    }
                    break;
                case 'busstop':
                    info = param.feature.properties.bus_stop_name;
                    break;
                default:
                    info = param.feature.properties.col_5;
                    break;
            }
            return info;
        }
    }

    var autoHeightAnimate = function(element, time) {
        var curHeight = element.height(), // Get Default Height
        autoHeight = element.css('height', 'auto').height(); // Get Auto Height
        element.height(curHeight); // Reset to Default Height
        element.stop().animate({ height: autoHeight }, time); 
    }

    var popupError = function (message) {
        var fdp = fdpmap;

        fdp.el.find('.fdp-error').remove();
        fdp.el.find('.fdp-error-green').remove();
        fdp.el.append(
            '<div class="fdp-error">' +
                '<div class="box-overlay"></div>' +
                '<div class="error">'+ message +'</div>' +
            '</div>'
        );
        fdp.el.append('<div class="fdp-error-green"></div>');

        fdp.el.find('.fdp-error .box-overlay').fadeIn();
    }

    // 4671 hide facily when click メニュー
    var displayViewFacility = function() {
        if ($('.btn-close-facility2').hasClass('show')) {
            $('.btn-close-facility2').trigger('click');
        } else if ($('.btn-close-facility1').hasClass('show')) {
            $('.btn-close-facility1').trigger('click');
        }
    }
    var checkDefaultActive = function() {
        if (!$('.menu-mode li').hasClass('active')) {
            $(".btn-map-default").trigger( "click" );
        }
    }
}