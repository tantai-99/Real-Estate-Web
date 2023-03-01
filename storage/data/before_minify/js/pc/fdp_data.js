'use strict';

var RESIDENT_TYPE = 1;
var HOUSEHOLD_TYPE = 2;
var BEDTOWN_TYPE = 3;
var GENDER_TYPE = 4;
var OWNERSHIP_TYPE = 5;
var RESIDENCE_TYPE = 6;
var MAX_POINT = 11;
var R = 6378.1;
var TO_RAD = Math.PI/180;
var TO_DEG = 180/Math.PI;

self.onmessage = function(e)
{
    var gData = e.data.gData;
    if (e.data.chart != 'undefined' && e.data.chart) { // chart statistics
        var ken_ct = e.data.ken_ct;
        var type_chart = e.data.type_chart;
        var towns = e.data.towns;
            towns = towns.map(function(x) {return x.substring(0, 8)});
            towns = towns.filter(function(value, index){ return towns.indexOf(value) == index });
        var dataPrefs = dataStatisticsPref([ken_ct], type_chart, gData);
        var dataTowns = dataStatisticsTown(towns, type_chart, gData);
            self.postMessage({
                town: dataTowns,
                pref: dataPrefs
            });
    } else {
        if (e.data.town != 'undefined' && e.data.town) { // list town
            var house = JSON.parse(e.data.house);
            self.postMessage(getListTowns(house, gData));
        } else { // chart elevation
            var response = JSON.parse(e.data.response);
            var categorys = JSON.parse(e.data.category);
            var house = JSON.parse(e.data.house);
            var station = JSON.parse(e.data.station);
            var distance, overview_path, points, elevations, facilitys;
            distance = response.routes[0].legs[0].distance.value;
            overview_path = response.routes[0].overview_path;
            points = getPoint(distance, overview_path, house, station, gData);
            elevations = elavationApi(points, distance, gData);
            facilitys = getFacility(points, distance, categorys, gData);
    
            self.postMessage({
                points: points,
                elevations: elevations,
                facilitys: facilitys,
            });
        }
    }
}

var townChartApi = [
    // 	近隣の住民
    [
        'contents/parea/stat/population/town/37/35/',
        'contents/parea/stat/population/pref/37/35/',
    ],
    // 近隣の世帯数
    [
        'contents/parea/stat/population/town/64/63/',
        'contents/parea/stat/population/pref/64/63/',
    ],
    // 	近隣のベッドタウン傾向
    [
        'contents/parea/stat/officelink/town/',
        'contents/parea/stat/officelink/pref/',
    ],
    // 近隣の男女比
    [
        'contents/parea/stat/population/town/31/30/',
        'contents/parea/stat/population/pref/31/30/',
    ],
    // 近隣の持ち家比率
    [
        [
            [
                'contents/parea/stat/population/town/52/44/',
                'contents/parea/stat/population/town/52/46/',
                'contents/parea/stat/population/town/52/47/'
            ],
            ['contents/parea/stat/population/town/52/45/']
        ],
        [
            [
                'contents/parea/stat/population/pref/52/44/',
                'contents/parea/stat/population/pref/52/46/',
                'contents/parea/stat/population/pref/52/47/'
            ],
            ['contents/parea/stat/population/pref/52/45/']
        ]
    ],
    // 近隣の居住期間
    [
        'contents/parea/stat/population/town/92/91/',
        'contents/parea/stat/population/pref/92/91/',
    ]
];

var getListTowns = function(point, gData) {
    var url = 'contents/parea/boundaries/town';
    var params = {lat: point.lat, lon: point.lng, rad: 2000, sort: 'A:distance'};
    var towns = [];
    api(url, params, gData, function(res) {
        if (res.status == '0' && res.count > 0) {
            res.data.forEach(function(town) {
                towns.push(town.properties.val_govcod);
            });
        }
    });
    return towns;
}

/**
 * get data statistics pref
 * 
 * @param {*} params 
 * @param {*} type 
 * @param {*} gData 
 */
var dataStatisticsPref = function(params, type, gData) {
    var data = [];
    if (type == OWNERSHIP_TYPE) {
        return getDataOwnerShip(params, gData, 1);
    }
    var url = townChartApi[type -1][1] + params.join(':');
    api(url, null, gData, function(res) {
        if (res.status == -1) {
            return false;
        }
        var arr = res.data[0].properties;
        var data1, data2, data3, data4, data5, total;
        switch (type) {
            case RESIDENT_TYPE: // 隣の住民
                if (arr[7] == '') arr[7] = '0';
                if (arr[109] == '') arr[109] = '0';
                total = parseInt(arr[7]) - parseInt(arr[109]);
                if (total == 0) return false;
                // 若年層 20 代以下
                data1 = Math.round(sum(arr.slice(8, 38).map(function(x) {if(x == ''){x = 0}return parseInt(x)}))/total*1000)/10;
                // 壮年層 30/40以
                data2 = Math.round(sum(arr.slice(38, 58).map(function(x) {if(x == ''){x = 0}return parseInt(x)}))/total*1000)/10;
                // 中年層 50/60以
                data3 = Math.round(sum(arr.slice(58, 78).map(function(x) {if(x == ''){x = 0}return parseInt(x)}))/total*1000)/10;
                // 高齢層 70代以上
                // data4 = Math.round(sum(arr.slice(78, 109).map(function(x) {return parseInt(x)}))/total*1000)/10;
                data4 = Math.round((100 - data1 - data2 - data3)*10)/10;

                data.push(data1, data2, data3, data4);
                break;
            case HOUSEHOLD_TYPE: //  近隣の世帯数
                if (arr[7] == '') arr[7] = '0';
                if (arr[8] == '') arr[8] = '0';
                if (arr[9] == '') arr[9] = '0';
                if (parseInt(arr[7]) == 0) return false;
                // シングル世帯
                data1 = Math.round(parseInt(arr[8])/parseInt(arr[7])*1000)/10;
                // 	2人世帯
                data2 = Math.round(parseInt(arr[9])/parseInt(arr[7])*1000)/10;
                // ファミリー世帯
                // data3 = Math.round(sum(Array.from(arr.slice(10, 14), x => parseInt(x)))/parseInt(arr[7])*1000)/10;
                data3 = Math.round((100 - (data1 + data2))*10)/10;
                data.push(data1, data2, data3);
                break;
            case BEDTOWN_TYPE: // 近隣のベッドタウン傾向
                if (arr[9] == '') arr[9] = '0';
                data.push(parseFloat(arr[9]));
                break;
            case GENDER_TYPE: // 近隣の男女比
                if (arr[7] == '') arr[7] = '0';
                if (arr[8] == '') arr[8] = '0';
                if (arr[9] == '') arr[9] = '0';
                if (parseInt(arr[7]) == 0) return false;
                // 男性
                data1 = Math.round(parseInt(arr[8])/parseInt(arr[7])*1000)/10;
                // 女性
                // data2 = Math.round(arr[9]/arr[7]*1000)/10;
                data2 = Math.round((100 - data1)*10)/10;
                data.push(data2, data1);
                break;
            // case OWNERSHIP_TYPE: // 近隣の居住期間
            //     if (res.sum.col16_val == '') res.sum.col16_val = '0';
            //     if (res.sum.col17_val == '') res.sum.col16_val = '0';
            //     total = parseInt(res.sum.col16_val) + parseInt(res.sum.col17_val);
            //     if (total == 0) return false;
            //     // 持ち家
            //     data1 = Math.round(parseInt(res.sum.col16_val)/total*1000)/10;
            //     // 賃貸
            //     data2 = Math.round((100 - data1)*10)/10;
            //     data.push(data2, data1);
            //     break;
            case RESIDENCE_TYPE: // 近隣の居住期間
                if (arr[9] == '') arr[9] = '0';
                if (arr[10] == '') arr[8] = '0';
                if (arr[11] == '') arr[9] = '0';
                if (arr[12] == '') arr[7] = '0';
                if (arr[13] == '') arr[8] = '0';
                // if (parseInt(arr[7]) == 0) return false;
                total = parseInt(arr[9]) + parseInt(arr[10]) + parseInt(arr[11]) + parseInt(arr[12]) + parseInt(arr[13]);
                if (total == 0) return false;
                // 居住5年未満
                data1 = Math.round((parseInt(arr[9]) + parseInt(arr[10]))/total*1000)/10;
                // 居住6年〜19年
                data2 = Math.round((parseInt(arr[11]) + parseInt(arr[12]))/total*1000)/10;
                // 居住20年以上
                data3 = Math.round(parseInt(arr[13])/total*1000)/10;
                // data3 = Math.round((100 - (data1 + data2))*10)/10;
                data.push(data1, data2, data3);
                break;
        }
    });
    return data;
}
/**
 * get data statistics town
 * 
 * @param {*} params 
 * @param {*} type 
 * @param {*} gData 
 */
var dataStatisticsTown = function(params, type, gData) {
    if (type == OWNERSHIP_TYPE) {
        return getDataOwnerShip(params, gData, 0);
    }
    var data = [];
    params = chunkArray(params, 200);
    var data1 = 0, data2 = 0, data3 = 0, data4 = 0, total = 0;
    params.forEach(function(param, index) {
        var url = townChartApi[type -1][0] + param.join(':') + '?limit=1000';
        api(url, null, gData, function(res) {
            if (res.status == -1) {
                return false;
            }
            switch (type) {
                // 隣の住民
                case RESIDENT_TYPE: // 隣の住民
                    res.data.forEach(function(arr) {
                        if (arr.properties[7] == '') arr.properties[7] = '0';
                        if (arr.properties[109] == '') arr.properties[109] = '0';
                        total += parseInt(arr.properties[7]) - parseInt(arr.properties[109]);
                        // 若年層 20 代以下
                        data1 += sum(arr.properties.slice(8, 38).map(function(x) {if(x == ''){x = 0}return parseInt(x)}));
                        // 壮年層 30/40以
                        data2 += sum(arr.properties.slice(38, 58).map(function(x) {if(x == ''){x = 0}return parseInt(x)}));
                        // 中年層 50/60以
                        data3 += sum(arr.properties.slice(58, 78).map(function(x) {if(x == ''){x = 0}return parseInt(x)}));
                        // 高齢層 70代以上
                        data4 += sum(arr.properties.slice(78, 109).map(function(x) {if(x == ''){x = 0}return parseInt(x)}));
                    });
                    break;
                case HOUSEHOLD_TYPE: // 近隣の世帯数
                    res.data.forEach(function(arr) {
                        if (arr.properties[7] == '') arr.properties[7] = '0';
                        if (arr.properties[8] == '') arr.properties[8] = '0';
                        if (arr.properties[9] == '') arr.properties[9] = '0';
                        total += parseInt(arr.properties[7]);
                        // シングル世帯
                        data1 += parseInt(arr.properties[8]);
                        // 	2人世帯
                        data2 += parseInt(arr.properties[9]);
                        // ファミリー世帯
                        data3 += sum(arr.properties.slice(10, 15).map(function(x) {if(x == ''){x = 0}return parseInt(x)}));
                    });
                    break;
                case BEDTOWN_TYPE:
                    res.data.forEach(function(arr) {
                        if (arr.properties[7] == '') arr.properties[7] = '0';
                        if (arr.properties[8] == '') arr.properties[8] = '0';
                        data1 += parseInt(arr.properties[7]);
                        data2 += parseInt(arr.properties[7]) - parseInt(arr.properties[8]);
                    });
                    break;
                case GENDER_TYPE:
                    res.data.forEach(function(arr) {
                        if (arr.properties[7] == '') arr.properties[7] = '0';
                        if (arr.properties[8] == '') arr.properties[8] = '0';
                        if (arr.properties[9] == '') arr.properties[9] = '0';
                        total += parseInt(arr.properties[7]);
                        data1 += parseInt(arr.properties[8]);
                        data2 += parseInt(arr.properties[9]);
                    });
                    break;
                // case OWNERSHIP_TYPE: // 近隣の居住期間
                //     if (res.sum.summary.col16_val == '') res.sum.summary.col16_val = '0';
                //     if (res.sum.summary.col17_val == '') res.sum.summary.col16_val = '0';
                //     total += parseInt(res.sum.summary.col16_val) + parseInt(res.sum.summary.col17_val);
                //     data1 += parseInt(res.sum.summary.col16_val);
                //     data2 += parseInt(res.sum.summary.col17_val);
                //     break;
                case RESIDENCE_TYPE:
                    res.data.forEach(function(arr) {
                        // total
                        // total += parseInt(arr.properties[7]);
                        // 居住5年未満
                        if (arr.properties[9] == '') arr.properties[9] = '0';
                        if (arr.properties[10] == '') arr.properties[8] = '0';
                        if (arr.properties[11] == '') arr.properties[9] = '0';
                        if (arr.properties[12] == '') arr.properties[7] = '0';
                        if (arr.properties[13] == '') arr.properties[8] = '0';
                        data1 += parseInt(arr.properties[9]) + parseInt(arr.properties[10]);
                        // 居住6年〜19年
                        data2 += parseInt(arr.properties[11]) + parseInt(arr.properties[12]);
                        // 居住20年以上
                        data3 += parseInt(arr.properties[13]);
                    });
                    break;
            }
        });
    });
    switch (type) {
        // 隣の住民
        case RESIDENT_TYPE: // 隣の住民
            if (total == 0) return false;
            data1 = Math.round(data1/total*1000)/10;
            data2 = Math.round(data2/total*1000)/10;
            data3 = Math.round(data3/total*1000)/10;
            // data4 = Math.round(data4/total*1000)/10;
            data4 = Math.round((100 - data1 - data2 - data3)*10)/10;
            data.push(data1, data2, data3, data4);
            break;
        case HOUSEHOLD_TYPE: // 近隣の世帯数
            if (total == 0) return false;
            data1 = Math.round(data1/total*1000)/10;
            data2 = Math.round(data2/total*1000)/10;
            // data3 = Math.round(data3/total*1000)/10;
            data3 = Math.round((100 - (data1 + data2))*10)/10;
            data.push(data1, data2, data3);
            break;
        case BEDTOWN_TYPE:
            if (data2 == 0) return false;
            data.push(Math.round(data1/data2*1000)/10);
            break;
        case GENDER_TYPE:
            if (total == 0) return false;
            data1 = Math.round(data1/total*1000)/10;
            // data2 = Math.round(data2/total*1000)/10;
            data2 = Math.round((100 - data1)*10)/10;
            data.push(data2, data1);
            break;
        // case OWNERSHIP_TYPE: // 近隣の居住期間
        //     if (total == 0) return false;
        //     // 持ち家
        //     data1 = Math.round(data1/total*1000)/10;
        //     // 賃貸
        //     data2 = Math.round((100 - data1)*10)/10;
        //     data.push(data2, data1);
        //     break;
        case RESIDENCE_TYPE:
            total = data1 + data2 + data3;
            if (total == 0) return false;
            data1 = Math.round(data1/total*1000)/10;
            data2 = Math.round(data2/total*1000)/10;
            // data3 = Math.round(data3/total*1000)/10;
            data3 = Math.round((100 - (data1 + data2))*10)/10;
            data.push(data1, data2, data3);
            break;
    }
    return data;
}

var getDataOwnerShip = function(params, gData, type) {
    var data = [0, 0];
    var arrUrlApi = townChartApi[4][type];
    params = chunkArray(params, 200);
    arrUrlApi.forEach(function(arrUrl, index) {
        arrUrl.forEach(function(eleUrl) {
            params.forEach(function(param) {
                var url = eleUrl + param.join(':');
                api(url, null, gData, function(res) {
                    if (res.status == -1) {
                        return false;
                    }
                    res.data.forEach(function(arr) {
                        data[index] += parseInt(arr.properties[7]);
                    });
                });
            });
        });
    });
    var total = data.reduce(function (a, b) { return a + b });
    var data1 = Math.round(data[0]/total*1000)/10;
    var data2 = 100 - data1;
    return [data1, data2];
}

/**
 * get facilily form station to house
 * 
 * @param {*} points 
 * @param {*} distance 
 * @param {*} categorys 
 * @param {*} gData 
 */
var getFacility = function(points, distance, categorys, gData) {
    var data = [];
    var check = 0;
    var count = 0;
    var types = '';
    for(var cate in categorys) {
        if (count == 2) break;
        var url  = categorys[cate];
        var params = {lat: points[5].lat, lon: points[5].lng, rad: distance/2 + 240, sort: 'A:distance'};
        api(url, params, gData, function (res) {
            if (res.status == -1 || res.count == 0 || data.length == 2){
                return;
            }
            var apiData = res.data;
            for( var i = 0; i < points.length; i++) {
                var arrDis = [];
                if (check == i || i == 0 || i == points.length - 1 || types == cate) continue;
                for(var j = 0; j < apiData.length; j++) {
                    var lat = apiData[j].geometry.coordinates[1]*TO_RAD;
                    var lon = apiData[j].geometry.coordinates[0]*TO_RAD;
                    var dis = distanceBetweenPoints(points[i].lat*TO_RAD, points[i].lng*TO_RAD, lat, lon)*R*1000;
                    arrDis.push(dis);
                }

                if (arrDis.length > 0) {
                    var min =  Math.min.apply(null, arrDis);
                    if (min <= 240) {
                        var indecFacility = arrDis.indexOf(min);
                        data.push({index: i, name: apiData[indecFacility].properties.col_5, type: cate, id  : apiData[indecFacility].properties.col_1,});
                        types = cate;
                        check = i;
                        count++;
                        return;
                    }
                }
            }
        });
    }
    return data;
}

/**
 * get list elavation
 * 
 * @param {*} points 
 * @param {*} distance 
 * @param {*} gData 
 */
var elavationApi = function(points, distance, gData) {
    var url = 'contents/gsi/elevation';
    var eleStation;
    var evelation = [], dis = [];
    var data = [];
    for (var i = 0; i < points.length; i++) {
        var params = {lat: points[i].lat, lon: points[i].lng, type: 1, position: i};
        api(url, params, gData, function(res) {
            if (i == 0) {
                eleStation = res.data[0].properties.elevation;
                eleStation = parseFloat(eleStation.replace('m', ''));
                evelation.push(0);
                dis.push(0);
            } else {
                var elevation = res.data[0].properties.elevation;
                evelation.push(Math.round((parseFloat(elevation.replace('m', '')) - eleStation)*100)/100);
                dis.push(Math.round(distance*i)/(MAX_POINT - 1) + 'm');
            }
        });
    }
    data.push(evelation);
    data.push(dis);
    return data;
}

/**
 * get list points from station to house
 * 
 * @param {*} distance 
 * @param {*} overview_path 
 * @param {*} house 
 * @param {*} station 
 */
var getPoint = function(distance, overview_path, house, station) {
    var dis = distance/(MAX_POINT - 1);
    var d = 0;
    var pointElevation = [];
    var point = [];
    pointElevation.push({'lat': station.lat, 'lng': station.lng});
    for(var i = 0; i < overview_path.length; i++) {
        if (i < overview_path.length - 1) {
            var lat1 = overview_path[i].lat*TO_RAD;
            var lon1 = overview_path[i].lng*TO_RAD;
            var lat2 = overview_path[i + 1].lat*TO_RAD;
            var lon2 = overview_path[i + 1].lng*TO_RAD;
            back: while(true){
                var disp = Math.round(distanceBetweenPoints(lat1, lon1, lat2, lon2)*R*1000*10)/10;
                if(d > 0) {
                    if (d + disp == dis) {
                        d = 0;
                        pointElevation.push({'lat': overview_path[i + 1].lat, 'lng': overview_path[i + 1].lng});
                        break ;
                    }
                    if (d + disp < dis) {
                        d = d + disp;
                        break;
                    }
                    if(d + disp > dis) {
                        point = pointByDistance(lat1, lon1, lat2, lon2, dis - d);
                        pointElevation.push(point);
                        lat1 = point['lat']*TO_RAD;
                        lon1 = point['lng']*TO_RAD;
                        d = 0;
                        continue back;
                    }
                } else {
                    if (disp == dis) {
                        d = 0;
                        pointElevation.push({lat: overview_path[i + 1].lat, lng: overview_path[i + 1].lng});
                        break ;
                    }
                    if (disp < dis) {
                        d = disp;
                        break ;
                    } 
                    if (disp > dis) {
                        point = pointByDistance(lat1, lon1, lat2, lon2, dis);
                        pointElevation.push(point);
                        lat1 = point['lat']*TO_RAD;
                        lon1 = point['lng']*TO_RAD;
                        continue back;
                    }
                }
            }
        
        }
    }
    if(pointElevation.length < MAX_POINT) {
        pointElevation.push({'lat': house.lat, 'lng': house.lng});
    } else {
        pointElevation[pointElevation.length - 1]= {'lat': house.lat, 'lng': house.lng};
    }
    return pointElevation;
}

/**
 * get point by distance
 * 
 * @param {*} lat1 
 * @param {*} lon1 
 * @param {*} lat2 
 * @param {*} lon2 
 * @param {*} dis 
 */
var pointByDistance = function(lat1, lon1, lat2, lon2, dis) {
    var d = dis/1000/R;
    var tc = Math.atan2(lon1-lon2,Math.log(Math.tan(lat2/2+Math.PI/4)/Math.tan(lat1/2+Math.PI/4)))%(2*Math.PI);
    var lat = Math.asin(Math.sin(lat1)*Math.cos(d)+Math.cos(lat1)*Math.sin(d)*Math.cos(tc));
    var dlon = Math.atan2(Math.sin(tc)*Math.sin(d)*Math.cos(lat1),Math.cos(d)-Math.sin(lat1)*Math.sin(lat));
    var lon = ((lon1-dlon +Math.PI)%(2*Math.PI))-Math.PI;
    return {'lat': lat*TO_DEG, 'lng': lon*TO_DEG};
}

/**
 * get distance between two points
 * 
 * @param {*} lat1 
 * @param {*} lon1 
 * @param {*} lat2 
 * @param {*} lon2 
 */
var distanceBetweenPoints = function(lat1, lon1, lat2, lon2) {
    return 2*Math.asin(Math.sqrt(Math.pow((Math.sin((lat1-lat2)/2)),2) + 
             Math.cos(lat1)*Math.cos(lat2)*Math.pow((Math.sin((lon1-lon2)/2)),2)));
}

var api = function(url, data, gData, fn) {
    var sessionKey=gData.sessionKey;
    if (data != null) {
        data.userid=gData.userid;
        var i = 0;
        for (var key in data) {
            if(i == 0) {
                url += '?' + key + '=' + data[key];
            } else {
                url += '&' + key + '=' + data[key];
            }
            i++;
        }
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            fn && fn(JSON.parse(this.response));
        }
    };
    xhttp.open("GET", gData.urlBase + url, false);
    xhttp.onerror = function () {
        return false;
    };
    xhttp.setRequestHeader("kkc_cds_session", sessionKey);
    xhttp.send();
}
var sum = function(input){
    var total =  0;
    for (var i = 0;i < input.length; i++)
    {                  
        if (isNaN(input[i])){
            continue;
        }
        total += Number(input[i]);
    }
    return total;
}

var chunkArray = function(myArray, size) {
    var results=[];
    while(myArray.length){
        results.push(myArray.splice(0,size));
    }
 
    return results;
}