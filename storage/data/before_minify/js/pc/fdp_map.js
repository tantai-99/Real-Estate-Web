fdpmap = new function() {
    'use strict';

    var SMALL_CIRCLE = 800;
    var BIG_CIRCLE = 1600;
    var DEFAULT_ZOOM = 14;

    var LIMIT_FACILITY = 3;
    var MAX_FACILITY = 5;
    var RAD_FACILITY = 2000;
    var SORT = 'A:distance';
    var MESSAGE_ERROR = '現在通信エラーが発生し、ご利用ができません。<br>しばらく後に再度読み込みをおこなってください。';
    var MESSAGE_NOT_ROUTE = '申し訳ございません。ルートの取得ができません';

    var map;
    var markers = {};
    var lat;
    var lng;
    var gData = {};
    gData.sessionKey = "";
    gData.urlBase    = "";
    var apiData = {};
    var route;
    var apiInfo = {
        currentlocation: {icon: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png'},
        currentStation: {icon: '/pc/imgs/fdp/pin_station.svg'},
        house: {icon: '/pc/imgs/fdp/house.svg'},
        station: {api: 'contents/ipc/eki/', icon: '/pc/imgs/fdp/station.svg'},
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
    var circleLabels= [];
    var lastInfoWindow = null;

    this.run = function (_app,$el){
        var fdp = fdpmap;
        fdp.app = _app;
        fdp.el  = $el;
        var elementMap = fdp.el.find('.section-map .map-facility');
        var elementChart = fdp.el.find('.chart-area');
        var element = elementMap;
        if (elementMap.length == 0) {
            element = elementChart  
        }
        if (element) {
            lat = element.attr('data-gmap-pin-lat');
            lng = element.attr('data-gmap-pin-long');
            apiInfo["currentlocation"].position = new google.maps.LatLng(lat, lng);
            apiInfo['currentlocation'].scaledSize = new google.maps.Size(20, 20); // scaled size
            if (elementMap.length > 0) {
                fdp.initMap.init();
                fdp.marker.init();
            }
            if (elementChart.length > 0) {
                fdp.chart.init();
            }
            loginApi();
        }
    }

    var loginApi = function () {
        var url      = '/api/mapkkauth';
        var data = {};
        post(url, data, function (res) {
            // session IDを保持する
            gData.sessionKey = res.sessionid;
            gData.urlBase    = res.url_base;
            gData.userid     = res.userid;
            getPositionStation();
        });
    }
    
    var getPositionStation = function() {
        var fdp = fdpmap;
        var params = {lat: lat, lon: lng, rad: 10000, limit: 1, sort: SORT};
        var url = gData.urlBase + apiInfo['station'].api;
        fdp.notExitStation = false;
        fdp.errorStation = false;
        api(url, params, function(res) {
            if (res.status == -1) {
                fdp.errorStation = true;
                fdp.chart.chartAreaError();
                return;
            }
            if (res.count == 0) {
                fdp.notExitStation = true;
                fdp.el.find('.link-direct .goto-chart').remove();
                fdp.el.find('.chart-area').remove();
                return;
            }
            var apiData = res.data[0];
            apiInfo['station'].position = new google.maps.LatLng(apiData.geometry.coordinates[1], apiData.geometry.coordinates[0]);
            apiInfo['station'].name = apiData.properties.col_14;
            route = fdp.initMap.calculateRoute();
        }).fail(function() {
            fdp.chart.chartAreaError();
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
        var directionsDisplay, markerStation;
        var circlemap;
        var flag = 0;
        var mapCircle;
        var houseCircle =[];
        this.init = function() {
            var fdp = fdpmap;
            var mapOpts = {
                center: apiInfo["currentlocation"].position,
                zoom: DEFAULT_ZOOM,
            };
            mapOpts.mapTypeControlOptions={};
            mapOpts.fullscreenControlOptions={};
            mapOpts.zoomControlOptions={};
            mapOpts.streetViewControlOptions={};
            if (fdp.el.find('.has-facility').length > 0) {
                mapOpts.mapTypeControl = false;
                // mapOpts.mapTypeId = google.maps.MapTypeId.ROADMAP,
                // mapOpts.mapTypeControlOptions.mapTypeIds = [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE];
                // mapOpts.mapTypeControlOptions.position = google.maps.ControlPosition.BOTTOM_LEFT;
                mapOpts.streetViewControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
                mapOpts.fullscreenControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
                mapOpts.zoomControlOptions.position = google.maps.ControlPosition.LEFT_BOTTOM;
            }
            map = new google.maps.Map(document.getElementById('map'), mapOpts);
            pinHouse();
            drawButton(); 
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

            google.maps.event.addListener(map, 'zoom_changed', zoomChanged);
            google.maps.event.addListener(map, 'dragend', dragend);
            google.maps.event.addListener(map, 'idle', changeGoogleArea);
        }

        var zoomChanged = function () {
            var fdp = fdpmap;
            if (fdp.el.find('.circle-display-btn').hasClass('active')) {
                displayCircle();
            }
            if (fdp.el.find('.green-mode-btn').hasClass('active')) {
                setTimeout(function() {
                    greenMode();
                }, 800);
            }
        }
        var dragend = function () {
            var fdp = fdpmap;
            if (fdp.el.find('.green-mode-btn').hasClass('active')) {
                setTimeout(function() {
                    greenMode();
                }, 800);
            }
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
                        google.maps.event.addDomListener(div, "click", function(event) {
                            event.stopPropagation();
                        })
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
            var fdp = fdpmap
            var greenModeBtn = document.getElementById('green_mode');
            var routeBtn = document.getElementById('route_display');
            var circleBtn = document.getElementById('circle_display');
            var defaultBtn = document.getElementById('map_default');
            var satelliteBtn = document.getElementById('map_satellite');

            if (defaultBtn) {
                map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(defaultBtn);
                defaultBtn.addEventListener("click",function(event) {
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active');
                        fdp.el.find('.map-satellite-btn').removeClass('active');
                        map.setMapTypeId('roadmap');
                    }
                    closeMapError();
                    clearCricle();
                    clearGreenMode();
                    clearRoute();
                });
            }

            if (satelliteBtn) {
                map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(satelliteBtn);
                satelliteBtn.addEventListener("click",function(event) {
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active');
                        fdp.el.find('.map-default-btn').removeClass('active');
                        map.setMapTypeId('satellite');
                    }
                    closeMapError();
                    clearCricle();
                    clearGreenMode();
                    clearRoute();
                });
            }
            
            if (greenModeBtn) {
                addGeoJsonDefault();
                map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(greenModeBtn);
                // green mode
                greenModeBtn.addEventListener("click",function(event) {
                    if($(this).hasClass('active')) {
                        if (typeof map.data.getStyle() != 'undefined' 
                        && (typeof map.data.getStyle().visible && map.data.getStyle().visible != false ) 
                        || fdp.el.find('.gm-style-error').length > 0 || fdp.el.find('.green-map-note').hasClass('show')) {
                            clearGreenMode();
                        }
                    } else {
                        $(this).addClass('active');
                        greenMode();
                        setTimeout(function(){
                            $(".green-map-note").addClass("show");
                            // map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(mode_note);
                        }, 1100);
                    }
                    closeMapError();
                },
                false
                );
            }
            if (routeBtn) {
                map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(routeBtn);
                // click button to display root
                routeBtn.addEventListener("click", (function()  {
                    closeMapError();
                    if ($(this).hasClass('active')) {
                        clearRoute()
                    } else {
                        $(this).addClass('active');
                        if (fdp.notExitStation) {
                            showMapError(MESSAGE_NOT_ROUTE);
                            return;
                        }
                        else{
                            if (fdp.errorStation || route == null) {
                                showMapError(MESSAGE_ERROR);
                                return;
                            }
                        }
                        pinStation();
                        displayRoute();
                    }
                }));
            }
            if (circleBtn) {
                map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(circleBtn);
                // click button to display circle
                circleBtn.addEventListener("click", (function()  {
                    closeMapError();
                    if($(this).hasClass('active')){ 
                        clearCricle();
                    } else {
                        $(this).addClass('active');
                        displayCircle();
                    }
                }));
            }
            if (fdp.el.find('.has-facility').length > 0) {
                setTimeout(function(){
                    satelliteBtn.style.display = 'table-cell';
                    circleBtn.style.display = 'table-cell';
                    routeBtn.style.display = 'table-cell';
                    greenModeBtn.style.display = 'table-cell';
                    defaultBtn.style.display = 'table-cell';
                }, 1500);
            }
        }
    
        var displayCircle = function() {
            //Add the circle to the map.
            for (var i = 0; i < houseCircle.length; i++) {
                houseCircle[i].setMap(null);
            }
            houseCircle = [];
            for (var circle in circlemap) {
                // Construct the circle for each value in citymap.
                if (map.zoom < 17) {
                    var meterPerPx  = 156543.03392 * Math.cos(lat * Math.PI / 180) / Math.pow(2, map.zoom);
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

        this.calculateRoute = function() {
            var fdp = fdpmap;
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
                    route = response;
                    if ($('.chart-area').length > 0) {
                        fdp.chart.chart_area(response);
                    }
                } else {
                    if ($('.chart-area').length > 0) {
                        fdp.chart.chartAreaError();
                    }
                }
            });
        }
    
        var displayRoute = function () {
            directionsDisplay = new google.maps.DirectionsRenderer({
                map: map, 
                draggable: true, 
                preserveViewport: true,
                /*polylineOptions: {
                    strokeColor: "#6FB3F4"
                }*/
                zIndex: 1000,
                clickable: false,
            });
            var suppressMarkers = directionsDisplay.setOptions({ suppressMarkers: true});
            directionsDisplay.setDirections(route);
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
                    showMapError(MESSAGE_ERROR);
                    return;
                }
                if (fdp.el.find('.green-mode-btn').hasClass('active')) {
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
                            zIndex: 10,
                            clickable: false,
                        }
                    });
                }
            }).fail(function() {
                showMapError(MESSAGE_ERROR);
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

        var clearGreenMode = function() {
            var fdp = fdpmap;
            fdp.el.find(".green-mode-btn").removeClass("active");
            fdp.el.find(".green-map-note").removeClass("show");
            map.data.setStyle({visible: false});
            map.data.forEach(function (feature) {
                map.data.remove(feature);
            });
        }

        var clearCricle = function() {
            var fdp = fdpmap;
            fdp.el.find('.circle-display-btn').removeClass('active');
            for (var i = 0; i < houseCircle.length; i++) {
                houseCircle[i].setMap(null);
                circleLabels[i].setMap(null);
            }
            houseCircle = [];
            circleLabels = [];
        }

        var clearRoute = function() {
            var fdp = fdpmap;
            fdp.el.find('.route-display-btn').removeClass('active');
            if (markerStation != null) {
                markerStation.setMap(null);
                markerStation = null;
            }
            if(directionsDisplay != null) {
                directionsDisplay.setMap(null);
                directionsDisplay = null;
            }
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

        var showMapError = function(message) {
            var fdp = fdpmap;
            fdp.el.find('.map-facility .gm-style>div:nth-child(1) .gm-style-error').remove();
            fdp.el.find('.map-facility .gm-style>div:nth-child(1)').append('<div class="gm-style-error"><p>'+message+'</p></div>');
        }
        var closeMapError = function() {
            var fdp = fdpmap;
            fdp.el.find('.map-facility .gm-style>div:nth-child(1) .gm-style-error').remove(); 
        }

        // 4722 click icon Pegman (Map)
        var changeGoogleArea = function () {
            var checkGoogleArea = setInterval(function() {
                var anchors = $('#map a');
                for (var i = 0; i < anchors.length; i++) {
                    if (anchors[i].href.indexOf('maps.google.com/maps?') !== -1) {
                        $(anchors[i]).parent().css({"z-index": "0"});
                        clearInterval(checkGoogleArea);
                    }
                }
            }, 200);
        }
    }

    this.marker = new function () {
        var lastInfoWindow = null;
        var distanceMatrix = {};
        this.init = function() {
            $('.tag-menu li').click(function() {
                var type = $(this).attr('id');
                if ($(this).hasClass('active')) {
                    if ($('.wrap-box .wrapper-table[data-type="'+type+'"]').length == 0) {
                        return;
                    }
                    clearMarkers(type);
                    $('.wrap-box .wrapper-table[data-type="'+type+'"] .close_list').trigger('click');
                } else {
                    showMarker(type, $(this));
                }
            });
            $(".folding-menu").click(function () {
                $(".tag-menu ul li.hidden").toggleClass("show");
                $(".rotate").toggleClass("down");
                if ($(".rotate").hasClass('down')) {
                    $(this).html('主要施設情報のみ表示する <div class="rotate down"></div>');
                } else {
                    $(this).html('全ての施設情報を表示する <div class="rotate"></div>');
                }
            });
        }

        var CustomMarker = function(param, elem, typeName, number) {
            var fdp = fdpmap
            var imageSrc = elem.find('i').css('background-image');
            imageSrc = imageSrc.replace('url(','').replace(')','').replace(/\"/gi, "");
            var latlng = new google.maps.LatLng(param.feature.geometry.coordinates[1], param.feature.geometry.coordinates[0])
            var infoWindow = new google.maps.InfoWindow({
                content: showInfowindow(param, elem, typeName, number - 1),
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
        var clearMarkers = function(type) {
            $('#'+type).removeClass('active');
            if (markers[type]) {
                for (var i = 0; i < markers[type].length; i++) {
                    markers[type][i].setMap(null);
                }
                delete markers[type];
            }
        }
        var showMarker = function(typeName, elem) {
            markers[typeName] = [];
            var  info = '';
            if($('.tag-menu li.active').length >= MAX_FACILITY) {
                alert("選択できる件数は最大５件までです。");
                return false;
            }
            elem.addClass('active');
            if (typeof distanceMatrix[typeName] == 'undefined' || distanceMatrix[typeName].length == 0) {
                distanceMatrix[typeName] = [];
                apiInfo[typeName].destination = [];
                apiInfo[typeName].api.forEach(function(apiUrl, index) {
                    var params = {lat: lat, lon: lng, rad: RAD_FACILITY};
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
                            htmlInfo(typeName, elem, '<tr><td>'+ MESSAGE_ERROR + '</td></tr>');
                            return;
                        } 
                        apiData[typeName] = res.data;
                        // add markers
                        // push to global var
                        for (var i = 0, feature; feature = apiData[typeName][i]; i++) {
                            apiInfo[typeName].destination.push( new google.maps.LatLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]));
                            distanceMatrix[typeName].push({feature: feature});
                        }
                    }).fail(function() {
                        htmlInfo(typeName, elem, '<tr><td>'+ MESSAGE_ERROR + '</tr></td>');
                        return;
                    });
                })
                if(distanceMatrix[typeName].length <= 0) {
                    htmlInfo(typeName, elem, '<tr><td>半径2Kmに該当する施設はありません</td></tr>');
                    return;
                }
                var service = new google.maps.DistanceMatrixService();
                    service.getDistanceMatrix(
                    {
                        origins: [apiInfo['currentlocation'].position],
                        destinations: apiInfo[typeName].destination,
                        travelMode: 'WALKING',
                    }, function(response, status) {
                        if (status == google.maps.DirectionsStatus.OK) {
                            response.rows[0].elements.forEach(function(value, index) {
                                distanceMatrix[typeName][index].element = value;
                            });
                            distanceMatrix[typeName] = distanceMatrix[typeName].sort(function(a, b) {return a.element.distance.value - b.element.distance.value}).slice(0, LIMIT_FACILITY);
                            distanceMatrix[typeName].forEach(function(param, index) {
                                markers[typeName].push(CustomMarker(param,  elem, typeName, index + 1));
                                info += showInfo(param, index + 1, typeName);
                            });
                            htmlInfo(typeName, elem, info);
                        } else {
                            htmlInfo(typeName, elem, '<tr><td>'+ MESSAGE_ERROR + '</tr></td>');
                        }
                    });
            } else {
                distanceMatrix[typeName].forEach(function(param, index) {
                    markers[typeName].push(CustomMarker(param,  elem, typeName, index + 1));
                    info += showInfo(param, index + 1, typeName);
                });
                htmlInfo(typeName, elem, info);
            }
        }
    
        var htmlInfo = function(typeName, elem, info) {
            var facilityInfo = '<div class="wrapper-table show" data-type="' + typeName + '">' +
                                        '<div class="close_list"><img class="close-btn" src="/pc/imgs/fdp/close.png"></div>' +
                                         '<table class="section-listbox">' +
                                            '<tr>' +
                                                '<th>' +
                                                    '<i class="wrap-icon-'+typeName+'"></i>' +
                                                    '<span> ' + elem.text() + '</span>' +
                                                '</th>' +
                                            '</tr>' +
                                            info +
                                            '</table>' +
                                        '</div>' +
                                    '</div>';
            $('.wrap-box-left').append(facilityInfo);
            $('.wrap-box .close_list').click(function() {
                clearMarkers($(this).parent().data('type'));
                $(this).parent().remove();
            }); 
        }

        var getNameFacility = function(param, typeName) {
            var name;
            switch(typeName) {
                // case 'carecenter':
                //     name = feature.properties.shisetumei;
                //     break;
                case 'nurseryschool':
                    if (typeof param.feature.properties.schoolname != 'undefined') {
                        name = param.feature.properties.schoolname;
                    } else {
                        name = param.feature.properties.col_2;
                    }
                    break;
                case 'busstop':
                    name = param.feature.properties.bus_stop_name;
                    break;
                default:
                    name = param.feature.properties.col_5;
                    break;
            }
            return name;
        }

        var getMinuteWalking = function(item) {
            var distance = Math.round(item.element.distance.value);
            return Math.ceil(distance/80);
        }
        var showInfo = function(param, i, typeName) {
            var minuteWalking = getMinuteWalking(param);
            var distance = getDistance(param);
            var info = '<tr>' +
                        '<td>'+ getLogoFacility(param, i, typeName) +'</td>' +
                        '<td>' + getNameFacility(param, typeName) +'</td>' +
                        '<td>徒歩'+ minuteWalking +'分<span>(' + distance + ')</span></td>' +
                        categoryWaking(minuteWalking) +
                        '</tr>';
            return info;
        }

        var getDistance = function(item) {
            var distance = Math.round(item.element.distance.value);
            if (distance >= 1000) {
                return Math.round(distance/100)/10 + 'km';
            }
            return Math.round(distance) + 'm';
        }

        var categoryWaking = function(minuteWalking) {
            var result = '<td></td>';
            var category;
            if (minuteWalking <= 5) {
                category = '徒歩5分圏内';
            } else{
                if (minuteWalking <= 10) {
                    category = '徒歩10分圏内';
                }
            }
            if (category) {
                result = '<td>' + category + '</td>';
            }
            return result;
        }

        var showInfowindow = function(param, elem, typeName, i) {
            return '<div class="infowindow-facility">' +
                        '<p class="'+typeName+'">【'+ elem.text() +'】</p>' +
                        '<p>' + getNameFacility(param, typeName) +'</p>' +
                        '<p>徒歩'+ getMinuteWalking(param) +'分</p>' +
                    '</div>';
        }

        var getLogoFacility = function(param, number, typeName) {
            var logo = '<i class="facility-number-'+number+'"></i>';
            if (typeName == 'busstop') {
                logo += '<img src="/pc/imgs/fdp/BUS_28x28.bmp">';
            } else {
                if (typeName == 'nurseryschool') {
                    logo += '<img src="/pc/imgs/fdp/nurseryschool_28x28.bmp">';
                } else {
                    if(param.feature.properties.logo_id && param.feature.properties.logo_id != '') {
                        var fdp = fdpmap;
                        var parseUrl = fdp.app.location.parseUrl(gData.urlBase);
                        var src = parseUrl.protocol + '//' + parseUrl.hostname + '/poi/LGM0006/data/28x28/' + param.feature.properties.logo_id + '.28x28B.BMP';
                        logo += '<img src="'+ src +'">';
                    } else {
                        logo += '<img src="/pc/imgs/fdp/'+ typeName +'_28x28.bmp">';
                    }
                }
            }
            
            return logo;
        }
    }

    // BEGIN AREA DATA ============================================
    this.chart = new function() {
        var yAxesticks = [];
        var hightArea = [];
        var distance, points, elevations, facilitys, max, min, ymin, ymax, ystep;
        this.init = function(){};
        
        this.chart_area = function(response) {
            var fdp = fdpmap;
            var worker = new Worker('/pc/js/fdp/fdp_data.js');
            worker.postMessage({
                response: JSON.stringify(response),
                category: JSON.stringify(getCategoryApi()),
                house: JSON.stringify(apiInfo["currentlocation"].position),
                station: JSON.stringify(apiInfo["station"].position),
                gData: gData
            });
            worker.addEventListener('message',  function(e){
                distance = response.routes[0].legs[0].distance.value;
                points = e.data.points;
                fdp.app.customConsoleLog('----- response point start -----');
                fdp.app.customConsoleLog(points);
                fdp.app.customConsoleLog('----- response point end -----');
                elevations = e.data.elevations;
                facilitys = e.data.facilitys;
                if (points == false || elevations == false || (typeof facilitys != "object" && facilitys == false)) {
                    chartAreaError();
                }
                $(".se-pre-con").fadeOut("slow");
                facilitys.forEach(function(element) {
                    hightArea.push(elevations[1][element.index]);
                });
                max = Math.max.apply(null, elevations[0]);
                min = Math.min.apply(null, elevations[0]);
                calculateYChart(min, max);

                var annotations = hightArea.map(function(date, index) {
                    return {
                        type: 'line',
                        id: 'vline' + index,
                        mode: 'vertical',
                        scaleID: 'x-axis-0',
                        value: date,
                        borderColor: '#FFC440',
                        borderWidth: 2,
                        borderDash: [7, 4],
                        zIndex: 0,
                        label: {
                            enabled: true,
                            position: "center",
                        },
                    }
                });

                var config = {
                    type: 'line',
                    data: {
                        labels: elevations[1],
                        datasets: [{
                            labels: ['10m', '20m', '30m', '40m', '50m', '60m'],
                            data: elevations[0],
                            fill: 'start',
                            lineTension: 0,
                            backgroundColor: 'rgb(253, 243, 150)',
                            borderColor: 'rgb(232, 226, 171)'
                        }],
                        lineAtIndex: elevations[0].indexOf(max),
                    },
                    // Configuration options go here
                    options: {
                        responsive: true,
                        tooltips: {
                            yAlign: 'bottom',
                            xAlign: 'center',
                        },
                        layout: {
                            padding: {
                                right: 10,
                            }
                        },
                        legend: {
                            display: false
                        },
                        title: {
                            display: false,
                            position: 'bottom',
                            text: '駅から物件まで０m（<img src="/pc/imgs/fdp/footprint.png">徒歩９分）'
                        },
                        scales: {
                            xAxes: [{
                                ticks: {
                                    display: false,
                                },
                                gridLines: {
                                    display: true,
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    display: false,
                                    min: ymin,
                                    max: ymax,
                                    beginAtZero: true,
                                    stepSize: ystep,
                                    callback: function(value, index, values) {
                                        yAxesticks = values;
                                        return value;
                                    }
                                },

                                gridLines: {
                                    display: true,
                                    /*borderDash: [2, 3],
                                    color: "#ADADAD"*/
                                }
                            }]
                        },
                        annotation: {
                            drawTime: 'beforeDatasetsDraw',
                            annotations: annotations
                        },
                    },
                };
                var originalLineDraw = Chart.controllers.line.prototype.draw;
                Chart.helpers.extend(Chart.controllers.line.prototype, {
                draw: function() {
                    originalLineDraw.apply(this, arguments);

                    var maxHeightX, maxHeightY;
                    var chart = this.chart;
                    var ctx = chart.chart.ctx;
                    // var maxDataPoint = chart.getMaxDataPoint(chart, chart.config.options);
                    var indexMax = chart.config.data.lineAtIndex;
                    var indexMin = elevations[0].indexOf(min);
                    var meta = chart.getDatasetMeta(0),max;
                    ctx.save();
                    ctx.strokeStyle = chart.config.options.scales.xAxes[0].gridLines.color;
                    ctx.lineWidth = chart.config.options.scales.xAxes[0].gridLines.lineWidth;
                    ctx.beginPath();
                    ctx.fillStyle = 'black';
                    meta.data.forEach(function(e) {
                        if (indexMax == e._index) {
                            ctx.moveTo(e._model.x, meta.dataset._scale.bottom);
                            ctx.lineTo(e._model.x, e._model.y);
                            if (indexMax == 0) {
                                maxHeightX = e._model.x + 50;
                                if (e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 - 15 < meta.data[indexMax + 1]._model.y) {
                                    maxHeightY = meta.data[indexMax + 1]._model.y + 110;
                                } else {
                                    maxHeightY = e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 + 90;
                                }
                            } else {
                                if (indexMax == 10) {
                                    maxHeightX = e._model.x - 30;
                                    if (e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 - 15 < meta.data[indexMax - 1]._model.y) {
                                        maxHeightY = meta.data[indexMax - 1]._model.y + 110;
                                    } else {
                                        maxHeightY = e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 + 90;
                                    }
                                } else {
                                    maxHeightX = e._model.x + 10;
                                    maxHeightY = e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 + 90;
                                }
                            }
                            $('.maximum-height').css({'left': maxHeightX, 'top': maxHeightY});
                        }
                    });
                    ctx.strokeStyle = chart.config.options.scales.xAxes[0].gridLines.color;
                    ctx.lineWidth = chart.config.options.scales.xAxes[0].gridLines.lineWidth;
                    ctx.setLineDash([7, 4]);
                    ctx.lineWidth = 2;
                    // ctx.beginPath();
                    ctx.textBaseline = 'top';
                    ctx.textAlign = 'right';
                    // c.ctx.fillText('Max value: ' + max, c.width - 10, 10);
                    ctx.stroke();
                    ctx.restore();
                }
                });
                var ctx = document.getElementById("chart-area").getContext("2d");
                new Chart(ctx, config);
                htmlChartEvelation(distance, facilitys, max, min, elevations[0][elevations[0].length - 1]);
                // $('.maximum-height').addClass('lineindex' + elevations[0].indexOf(max));

            })
        }

        var calculateYChart = function(min, max) {
            var maxHeight = Math.round((max - min)*100)/100;
            if (maxHeight <= 20) {
                ystep = 4;
            } else {
                ystep = Math.round((maxHeight + 10)/6);
            }
            var i = Math.ceil(Math.abs(min)/ystep);
            if (min >= 0) {
                ymin = -ystep;
                ymax = ystep*(6 - i);
            } else {
                ymin = -ystep*(i + 1);
                ymax = ystep*(6 - i);
            }
        }

        var getCategoryApi = function() {
            return {
                department: apiInfo['department'].api, 
                supermaket: apiInfo['supermaket'].api, 
                conviencestore: apiInfo['conviencestore'].api, 
                // discountstore: apiInfo['discountstore'].api,
                drugstore: apiInfo['drugstore'].api,
                restaurent: apiInfo['restaurent'].api
            };
        }

        var htmlChartEvelation = function(distance, facilitys, max, min,  house) {
            $('.chart-area .title').html('駅から物件まで'+distance+'m（徒歩'+Math.ceil(distance/80)+'分）<span>※80ｍ=1分換算</span>');
            switch (facilitys.length) {
                case 0:
                    $('.chart-area .facility-1').remove();
                    $('.chart-area .facility-2').remove();
                    break;
                case 1:
                    $('.chart-area .facility-1').addClass('pointdata-' + facilitys[0].index).html('<p class="icon-'+facilitys[0].type+'-title">'+facilitys[0].name+'</p></div>');
                    $('.chart-area .facility-1').append('<i class="icon-'+facilitys[0].type+'"></i>');
                    $('.chart-area .facility-2').remove();
                    break;
                case 2:
                    var facility1 = $('.chart-area .facility-1');
                    var facility2 = $('.chart-area .facility-2');
                    facility1.addClass('pointdata-' + facilitys[0].index).html('<p class="icon-'+facilitys[0].type+'-title">'+facilitys[0].name+'</p></div>').append('<i class="icon-'+facilitys[0].type+'"></i>');
                    facility2.addClass('pointdata-' + facilitys[1].index).html('<p class="icon-'+facilitys[1].type+'-title">'+facilitys[1].name+'</p></div>').append('<i class="icon-'+facilitys[1].type+'"></i>');
                    if (((facility1.position().left +  facility1.width() + 5) >= facility2.position().left) && facility2.position().top != 5) {
                        facility1.addClass('uptop');
                    }
                    break;
            }
            $('.chart-area .maximum-height').html('最大高低差</br><span>' + Math.round((max - min)*100)/100 + '</span>m');
            $('.chart-area .building p').html('高低差<br>' + house + 'm');
            $('.chart-area .building img').attr('src', apiInfo['house'].icon);
            $('.chart-area .station p').html(apiInfo['station'].name);
            $('.chart-area .station p').addClass('icon-station-title');
            $('.chart-area .canvas-x').empty();
            elevations[1].forEach(function (dis) {
                $('.chart-area .canvas-x').append('<div>'+ dis +'</div>')
            })
            $('.chart-area .canvas-y').empty();
            for(var i = ymax; i >= ymin; i = i - ystep) {
                $('.chart-area .canvas-y').append('<div>'+ i +'</div>');
            }
            positionFacilityTitle();
            positionStationFacility();
        }

        this.chartAreaError = function() {
            chartAreaError();
        }
        var chartAreaError = function() {
            var fdp = fdpmap;
            fdp.el.find('.chart-area').append('<div class="error">'+MESSAGE_ERROR+'</div>').find('.chart-container').remove();
        }

        var positionFacilityTitle = function() {
            var facility1 = 0;
            var facility2 = 0;
            if ($('.chart-area .facility-1').length > 0) {
                facility1 = document.querySelector('.facility-1 p').getBoundingClientRect().left + $('.facility-1 p').width() + 20;
            }
            if ($('.chart-area .facility-2').length > 0) {
                facility2 = document.querySelector('.facility-2 p').getBoundingClientRect().left + $('.facility-2 p').width() + 20;
            }
            var info = document.querySelector('.around-info').getBoundingClientRect().right;
            if (facility1 > info) {
                var value = info - facility1 - 40;
                $('.chart-area .facility-1 p').css({"margin-left": value + "px"});
            }
            if (facility2 > info) {
                var value = info - facility2 - 40;
                $('.chart-area .facility-2 p').css({"margin-left": value + "px"});
            }
        }
        var positionStationFacility = function() {
            var station = 0;
            var facility = 0;
            if ($('.chart-area .station p').length > 0) {
                station = document.querySelector(".station p").getBoundingClientRect().right;
            }
            if ($('.chart-area .facility-2').length > 0) {
                facility =  document.querySelector(".facility-2 p").getBoundingClientRect().left;
                if (station > facility) {
                    var value = station - facility + 5;
                    $(".chart-area .facility-2 p").css({
                        "margin-left": value + "px"
                    })
                }
            }
            if (($('.chart-area .facility-1').length > 0) && ($('.chart-area .facility-1').position().top != 5)) {
                facility =  document.querySelector(".facility-1 p").getBoundingClientRect().left;
                if (station > facility) {
                    var value = station - facility + 5;
                    $(".chart-area .facility-1 p").css({
                        "margin-left": value + "px"
                    })
                }
            }
        }
    } 
}