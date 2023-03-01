fdptown = new function() {
    'use strict';
    var myDoughnut;
    var myDoughnut_config;
    var RESIDENT_TYPE = 1;
    var HOUSEHOLD_TYPE = 2;
    var BEDTOWN_TYPE = 3;
    var GENDER_TYPE = 4;
    var OWNERSHIP_TYPE = 5;
    var RESIDENCE_TYPE = 6;
    var MESG = '現在通信エラーが発生し、ご利用ができません。<br>しばらく後に再度読み込みをおこなってください。';
    var THE_FIRST = 1800;
    var gData = {};
    gData.sessionKey = '';
    gData.urlBase = '';
    var townElement , elevationElem , ken_ct, lat, lng;

    this.run = function(_app, $el) {
        var fdp = fdptown;
        fdp.app = _app;
        fdp.el = $el;
        townElement = fdp.el.find('div.item-detail-tab-body.article-town');
        elevationElem = fdp.el.find('.chart-area');
        ken_ct = townElement.data('ken-cd');
        if (townElement.length > 0) {
            lat = townElement.data('gmap-pin-lat');
            lng = townElement.data('gmap-pin-long');
        } else {
            lat = elevationElem.data('gmap-pin-lat');
            lng = elevationElem.data('gmap-pin-long');
        }
        if (fdp.el.find('.chart-area').length > 0) {
            fdp.elevation.init();
        }
        fdp.town.init();
        loginApi();
    };

    var loginApi = function() {
        var fdp = fdptown;
        var url = '/api/mapkkauth';
        var data = {};
        post(url, data, function(res) {
            // session IDを保持する
            gData.sessionKey = res.sessionid;
            gData.urlBase = res.url_base;
            gData.userid = res.userid;
            fdp.elevation.getPositionStation();
            var worker = new Worker('/sp/js/fdp/fdp_data.js');
            worker.postMessage({
                town: true,
                house: JSON.stringify({ lat: lat, lng: lng }),
                gData: gData,
            });
            var towns;
            worker.addEventListener('message', function(e) {
                $('.article-town .wrapper-chart').each(function() {
                    var type = parseInt($(this).data('type'));
                    var elem = $(this);
                    fdp.town.showChartTown(type, elem, e.data);
                });
            });
        });
    };

    var post = function(apiUrl, data, callback) {
        var fdp = fdptown;
        $.ajax({
            type: 'POST',
            url: apiUrl,
            data: data,
            timeout: 120 * 1000,
            dataType: 'json',
        })
            .done(function(res) {
                fdp.app.customConsoleLog('----- ajax response -----');
                fdp.app.customConsoleLog(res);
                fdp.app.customConsoleLog('----- ajax response end -----');

                callback(res);
            })
            .fail(function(res) {
                fdp.app.customConsoleLog('----- ajax failed -----');
                fdp.app.customConsoleLog(res);
                fdp.app.customConsoleLog('----- ajax failed end -----');

                if (status == 'abort') {
                    return;
                }
            });
    };

    this.town = new function() {
        Chart.defaults.global.plugins.datalabels.display = false;
        var has_run_chart = [false, false, false, false,false, false];
        var intersectLinePlugin = {
            afterDraw: function(chart, options) {
                if (chart.tempData) {
                    chart.intersectData = chart.tempData;
                    chart.tempData = null;
                    if (chart.initialized) {
                        var data = chart.intersectData;
                        var ctx = chart.ctx;
                        data.forEach(function(item) {
                            var lineFrom = item[0],
                                lineTo = item[1];
                            ctx.beginPath();
                            ctx.strokeStyle = '#D8D8D8';
                            ctx.lineWidth = 2;
                            ctx.moveTo(lineFrom[0], lineFrom[1]);
                            ctx.lineTo(lineTo[0], lineTo[1]);
                            ctx.stroke();
                        });
                    }
                }
            },
        };
        // extends bar chart
        !(function(Chart) {
            Chart.defaults.barw = {
                hover: {
                    mode: 'index',
                    axis: 'y',
                },

                scales: {
                    xAxes: [
                        {
                            type: 'linear',
                            position: 'bottom',
                        },
                    ],

                    yAxes: [
                        {
                            type: 'category',
                            position: 'left',
                            categoryPercentage: 0.8,
                            barPercentage: 0.9,
                            offset: true,
                            gridLines: {
                                offsetGridLines: true,
                            },
                        },
                    ],
                },

                elements: {
                    rectangle: {
                        borderSkipped: 'left',
                    },
                },

                tooltips: {
                    mode: 'index',
                    axis: 'y',
                },
            };

            Chart.controllers.barw = Chart.controllers.horizontalBar.extend({
                /**
                 * @private
                 */
                getRuler: function() {
                    var me = this;
                    var scale = me.getIndexScale();
                    var options = scale.options;
                    var stackCount = me.getStackCount();
                    var fullSize = scale.isHorizontal()
                        ? scale.width
                        : scale.height;
                    var tickSize = fullSize / scale.ticks.length;
                    var categorySize = tickSize * options.categoryPercentage;
                    var fullBarSize = categorySize / stackCount;
                    var barSize = fullBarSize * options.barPercentage;

                    barSize = Math.min(
                        helpers.getValueOrDefault(options.barThickness, barSize),
                        helpers.getValueOrDefault(
                            options.maxBarThickness,
                            Infinity
                        )
                    );

                    return {
                        fullSize: fullSize,
                        stackCount: stackCount,
                        tickSize: tickSize,
                        categorySize: categorySize,
                        categorySpacing: tickSize - categorySize,
                        fullBarSize: fullBarSize,
                        barSize: barSize,
                        barSpacing: fullBarSize - barSize,
                        scale: scale,
                    };
                },

                /**
                 * @private
                 */
                calculateBarIndexPixels: function(datasetIndex, index, ruler) {
                    var me = this;
                    var scale = ruler.scale;
                    var options = scale.options;
                    var isCombo = me.chart.isCombo;
                    var stackIndex = me.getStackIndex(datasetIndex);
                    var base = scale.getPixelForValue(
                        null,
                        index,
                        datasetIndex,
                        isCombo
                    );
                    var size = ruler.barSize;

                    var dataset = me.chart.data.datasets[datasetIndex];
                    if (dataset.weights) {
                        var total = dataset.weights.reduce(function(m, x) {
                            return m + x;
                        }, 0);
                        var perc = dataset.weights[index] / total;
                        var offset = 0;
                        for (var i = 0; i < index; i++) {
                            offset += dataset.weights[i] / total;
                        }
                        var pixelOffset = Math.round(ruler.fullSize * offset);
                        var base = scale.isHorizontal() ? scale.left : scale.top;
                        base += pixelOffset;

                        size = Math.round(ruler.fullSize * perc);
                        size -= ruler.categorySpacing;
                        size -= ruler.barSpacing;
                    }

                    base -= isCombo ? ruler.tickSize / 2 : 0;
                    base += ruler.fullBarSize * stackIndex;
                    base += ruler.categorySpacing / 2;
                    base += ruler.barSpacing / 2;

                    return {
                        size: size,
                        base: base,
                        head: base + size,
                        center: base + size / 2,
                    };
                },
                draw: function() {
                    var me = this;
                    var chart = me.chart;
                    var scale = me.getValueScale();
                    var rects = me.getMeta().data;
                    var dataset = me.getDataset();
                    var ilen = rects.length;
                    var i = 0;
                    // position
                    var lineFrom = 0;
                    var lineTo = 0;
                    helpers.canvas.clipArea(chart.ctx, chart.chartArea);
                    var chartWidth = Math.round(chart.width);
                    for (; i < ilen; ++i) {
                        if (!isNaN(scale.getRightValue(dataset.data[i]))) {
                            var currentRect = rects[i];
                            currentRect.draw();
                            if (Math.round(currentRect._model.x) + 33 !== chartWidth) {
                                var model = currentRect._model;
                                if (lineFrom)
                                    lineTo = [model.x, model.y - model.height / 2];
                                else
                                    lineFrom = [
                                        model.x,
                                        model.y + model.height / 2,
                                    ];
                            }
                            if (lineFrom && lineTo) {
                                if (!chart.tempData) chart.tempData = [];
                                chart.tempData.push([lineFrom, lineTo]);
                            }
                        }
                    }

                    helpers.canvas.unclipArea(chart.ctx);
                },
            });
        })(Chart);

        this.init = function() {};

        this.showChartTown = function(type, elem, towns) {
            var fdp = fdptown;
            var worker = new Worker('/sp/js/fdp/fdp_data.js');
            var screen_h = $(window).height();
            worker.postMessage({
                chart: true,
                towns: towns,
                ken_ct: ken_ct,
                type_chart: type,
                gData: gData,
            });
            worker.addEventListener('message', function(e) {
                elem.find('.se-pre-con').fadeOut('slow');
                if (e.data.town == false || e.data.pref == false) {
                    errorChartTown(type);
                    return;
                }
                switch (type) {
                    case RESIDENT_TYPE:
                        fdp.el
                            .find('.population-chart .household-bar .text-value')
                            .each(function(i, element) {
                                $(this).text(e.data.town[i].toFixed(1) + '%');
                            });
                        break;
                    case HOUSEHOLD_TYPE:
                        fdp.el
                            .find('.households-chart .household-bar .text-value')
                            .each(function(i, element) {
                                $(this).text(e.data.town[i].toFixed(1) + '%');
                            });
                        break;
                    case BEDTOWN_TYPE:
                        break;
                    case GENDER_TYPE:
                        sexRatio_chart();
                        break;
                    case OWNERSHIP_TYPE:
                        break;
                    case RESIDENCE_TYPE:
                        break;
                }
    
                if (has_run_chart[type - 1] == false) {
                    runChart(elem, type, e.data);
                    $(window).scroll(function() {
                        runChart(elem, type, e.data);
                    });
                }
            });
        };

        var runChart = function(elem, type, data) {
            var timeoutChart = setTimeout(function() {
                var screen_h = $(window).height();
                var offset1 = 0, offset2 = 0;
                // if (screen_h < 650) {
                //     offset1 = elem.offset().top - 350;
                // } else {
                //     offset1 = elem.offset().top - 600;
                // }
                var scrollTop = $(window).scrollTop();
                var scrollBottom = $(window).scrollTop() + $(window).height();
                offset1 = elem.offset().top ;
                offset2 = elem.offset().top + elem.outerHeight();
                if (has_run_chart[type -1] == false && (scrollBottom >= offset1) && (scrollTop <= offset2) ) {
                    switch (type) {
                        case RESIDENT_TYPE:
                            population_chart(data);
                            break;
                        case HOUSEHOLD_TYPE:
                            households_chart(data);
                            break;
                        case BEDTOWN_TYPE:
                            bedtown_chart(data);
                            // bedtown_chart_125_75(data);
                            break;
                        case GENDER_TYPE:
                            /*elem.find(".girl").addClass("show");
                            elem.find(".boy").addClass("show");*/
                            sexRatio_chart_update(data);
                            break;
                        case OWNERSHIP_TYPE:
                            ownDepartmentRatio(data);
                            break;
                        case RESIDENCE_TYPE:
                            nearbyStayTime(data);
                            break;
                    }
                    has_run_chart[type -1] = true;
                    clearTimeout(timeoutChart);
                }
            }, 1000);
        }

        // BEGIN POLAR DATA ============================================
        var population_chart = function(data) {
            var fdp = fdptown;
            var age1 = data.town[0];
            var age2 = data.pref[0];
            var age3 = data.town[1];
            var age4 = data.pref[1];
            var age5 = data.town[2];
            var age6 = data.pref[2];
            var age7 = data.town[3];
            var age8 = data.pref[3];

            var valueWidth = 45;
            var maxBarContainerWidth = document.querySelector('.bar-household').offsetWidth;
            var maxBarWidthRatio = (maxBarContainerWidth - valueWidth) / maxBarContainerWidth;

            function minBarWidthLimiter(widthRatio) {
                return "".concat(maxBarWidthRatio * widthRatio * 100, "%");
            }

            $('.process2').html(
                "<span class='process-value'>" + age2.toFixed(1) + '%' + '</span>'
            );
            $('.process4').html(
                "<span class='process-value'>" + age4.toFixed(1) + '%' + '</span>'
            );
            $('.process6').html(
                "<span class='process-value'>" + age6.toFixed(1) + '%' + '</span>'
            );
            $('.process8').html(
                "<span class='process-value'>" + age8.toFixed(1) + '%' + '</span>'
            );

            var percent_array = [age1, age2, age3, age4, age5, age6, age7, age8];
            var maxInNumbers = Math.max.apply(Math, percent_array);
            age1 = minBarWidthLimiter(data.town[0] / maxInNumbers);
            age2 = minBarWidthLimiter(data.pref[0] / maxInNumbers);
            age3 = minBarWidthLimiter(data.town[1] / maxInNumbers);
            age4 = minBarWidthLimiter(data.pref[1] / maxInNumbers);
            age5 = minBarWidthLimiter(data.town[2] / maxInNumbers);
            age6 = minBarWidthLimiter(data.pref[2] / maxInNumbers);
            age7 = minBarWidthLimiter(data.town[3] / maxInNumbers);
            age8 = minBarWidthLimiter(data.pref[3] / maxInNumbers);

            $(".process1").css("width",age1);
            $(".process2").css("width",age2);
            $(".process3").css("width",age3);
            $(".process4").css("width",age4);
            $(".process5").css("width",age5);
            $(".process6").css("width",age6);
            $(".process7").css("width",age7);
            $(".process8").css("width",age8);

            $('.full1').animate(
                {
                    width: '100%',
                },
                2000
            );
        };

        var households_chart = function(data) {
            var fdp = fdptown;
            var households1 = data.town[0];
            var households2 = data.pref[0];
            var households3 = data.town[1];
            var households4 = data.pref[1];
            var households5 = data.town[2];
            var households6 = data.pref[2];
    
            var valueWidth = 45;
            var maxBarContainerWidth = document.querySelector('.bar-household').offsetWidth;
            var maxBarWidthRatio = (maxBarContainerWidth - valueWidth) / maxBarContainerWidth;
    
            function minBarWidthLimiter(widthRatio) {
                return "".concat(maxBarWidthRatio * widthRatio * 100, "%");
            }
    
            $('.households2').html(
                "<span class='process-value'>" + households2.toFixed(1) + '%' + '</span>'
            );
            $('.households4').html(
                "<span class='process-value'>" + households4.toFixed(1) + '%' + '</span>'
            );
            $('.households6').html(
                "<span class='process-value'>" + households6.toFixed(1) + '%' + '</span>'
            );
            
            var percent_array = [
                households1,
                households2,
                households3,
                households4,
                households5,
                households6,
            ];
            var maxInNumbers = Math.max.apply(Math, percent_array);
    
            households1 = minBarWidthLimiter(data.town[0] / maxInNumbers);
            households2 = minBarWidthLimiter(data.pref[0] / maxInNumbers);
            households3 = minBarWidthLimiter(data.town[1] / maxInNumbers);
            households4 = minBarWidthLimiter(data.pref[1] / maxInNumbers);
            households5 = minBarWidthLimiter(data.town[2] / maxInNumbers);
            households6 = minBarWidthLimiter(data.pref[2] / maxInNumbers);
    
            $(".households1").css("width",households1);
            $(".households2").css("width",households2);
            $(".households3").css("width",households3);
            $(".households4").css("width",households4);
            $(".households5").css("width",households5);
            $(".households6").css("width",households6);
    
            $('.full2').animate(
                {
                    width: '100%',
                },
                2000
            );
        };

        var helpers = Chart.helpers;
    Chart.controllers.doughnut = Chart.controllers.doughnut.extend({
        // function to increase inner charts diameter

        update: (function(reset) {
            var outMostRing1, outMostRing2;
            return function(reset) {
                var me = this;
                var chart = me.chart,
                    chartArea = chart.chartArea,
                    opts = chart.options,
                    arcOpts = opts.elements.arc,
                    availableWidth =
                        chartArea.right - chartArea.left - arcOpts.borderWidth,
                    availableHeight =
                        chartArea.bottom - chartArea.top - arcOpts.borderWidth,
                    minSize = Math.min(availableWidth, availableHeight),
                    offset = {
                        x: 0,
                        y: 0,
                    },
                    meta = me.getMeta(),
                    cutoutPercentage = 80,
                    circumference = opts.circumference;
                if (me.index === 0) {
                    // Outer chart
                    chart.borderWidth = me.getMaxBorderWidth(meta.data);
                    chart.outerRadius = outMostRing1 =
                        Math.max((minSize - chart.borderWidth) / 2, 0) - 20;
                    chart.innerRadius = outMostRing2 =
                        Math.max(
                            cutoutPercentage
                                ? (chart.outerRadius / 100) * cutoutPercentage
                                : 0,
                            0
                        ) - 10;
                    chart.radiusLength =
                        (chart.outerRadius - chart.innerRadius) /
                        chart.getVisibleDatasetCount();
                    chart.offsetX = offset.x * chart.outerRadius;
                    chart.offsetY = offset.y * chart.outerRadius;

                    meta.total = me.calculateTotal();

                    me.outerRadius =
                        chart.outerRadius -
                        chart.radiusLength * me.getRingIndex(me.index);
                    me.innerRadius = Math.max(
                        me.outerRadius - chart.radiusLength,
                        0
                    );
                } else if (me.index === 1) {
                    // Inner chart
                    var chart = me.chart,
                        opts = chart.options,
                        meta = me.getMeta(),
                        cutoutPercentage = 35,
                        circumference = opts.circumference;
                    meta.total = me.calculateTotal();
                    // me.outerRadius = 60;
                    // me.innerRadius = 105;
                    me.outerRadius = outMostRing1 * 0.75;
                    me.innerRadius = outMostRing2 * 0.40;
                    // factor in the radius buffer if the chart has more than 1 dataset
                    /*if (me.index > 0) {
                    me.outerRadius = 70;
                }*/
                }
                helpers.each(meta.data, function(arc, index) {
                    me.updateElement(arc, index, reset);
                });
            };
        })(),
    });

    var sexRatio_chart_update = function(data) {
        var fdp = fdptown;
        myDoughnut_config.data.datasets[0].data[0] = data.pref[0];
        myDoughnut_config.data.datasets[0].data[1] = 0;
        myDoughnut_config.data.datasets[0].data[2] = data.pref[1];
        myDoughnut_config.data.datasets[1].data[0] = data.town[0];
        myDoughnut_config.data.datasets[1].data[1] = 0;
        myDoughnut_config.data.datasets[1].data[2] = data.town[1];
        var index;
        fdp.el.find('.value-town').each(function(i, element) {
            if (i == 0) {
                index = 2;
            } else {
                index = 0;
            }
            $(element).html(myDoughnut_config.data.datasets[1].data[index].toFixed(1) + '%');
        });
        fdp.el.find('.value-pref').each(function(i, element) {
            if (i == 0) {
                index = 2;
            } else {
                index = 0;
            }
            $(element).html(myDoughnut_config.data.datasets[0].data[index].toFixed(1) + '%');
        });
        if (typeof luxury01 != 'undefined' && luxury01) {
            fdp.el.find('.value-pref').addClass('luxury01-gender-pref');
        }
        myDoughnut.update();
    };

    var sexRatio_chart = function() {
        
        var chartContainer = document.querySelector('.chart-container');
        var canvasWrapper = document.querySelector('.wrapper-canvas');
        var chartContentWidth = chartContainer.offsetWidth - 20;
        var chartWidth = chartContentWidth > 400 ? '400px' : chartContentWidth + 'px';
        canvasWrapper.style.width = chartWidth;
        canvasWrapper.style.height = 'calc('+chartWidth+' - 20px)';
        var town = [0, 100, 0];
        var pref = [0, 100, 0];
        myDoughnut_config = {
            type: 'doughnut',
            data: {
                datasets: [
                    {
                        data: town,
                        explodeSection: 0,
                        backgroundColor: ["#15243D", "transparent", "#5B2925"],
                        borderWidth: [2, 0, 2],
                        borderColor: ['white', 'transparent', 'white'],
                        label: 'Outer Data',
                    },
                    {
                        data: pref,
                        explodeSection: 0,
                        backgroundColor: ["#84A0CA", "transparent", "#DFBBB9"],
                        borderWidth: [2, 0, 2],
                        borderColor: ['white', 'transparent', 'white'],
                        label: 'Inner Data',
                    },
                ],
                labels: town,
            },
            options: {
                hover: {
                    mode: false,
                },
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                },
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    enabled: false,
                },
                animation: {
                    duration: 2000,
                    animateScale: true,
                    animateRotate: true,
                },
                plugins: {
                    labels: [
                        {
                            render: function(data) {
                                return '';
                            },
                            position: 'outside',
                            fontSize: 12,
                            fontColor: ["#15243D", "transparent", "#5B2925"],
                        },
                        {
                            render: function(data) {
                                return "";
                            },
                            fontSize: 9,
                            position: 'default',
                            fontColor: ['#fff', 'transparent', '#fff'],
                        },
                    ],
                    legend: {
                        position: 'bottom',
                    },
                    datalabels: {
                        display: false,
                    },
                },
            },
        };
        var ctx = document.getElementById('chart_doughnut').getContext('2d');
        myDoughnut = new Chart(ctx, myDoughnut_config);
    };

    var originalLineDraw = Chart.controllers.horizontalBar.prototype.draw;
    Chart.helpers.extend(Chart.controllers.horizontalBar.prototype, {
        draw: function() {
            originalLineDraw.apply(this, arguments);
            var chart = this.chart;
            var ctx = chart.chart.ctx;

            var index = chart.config.options.lineAtIndex;
            if (index) {
                var xaxis = chart.scales['x-axis-0'];
                var yaxis = chart.scales['y-axis-0'];
                var x1 = xaxis.getPixelForValue(index);
                var y1 = yaxis.top;
                var descPos = (xaxis.top - 20) + 'px'
                var item1 = document.querySelector('.neighborhood-area');
                var item2 = document.querySelector('.city-town');
                item1.style.top = descPos;
                item2.style.top = descPos;
                var x2 = xaxis.getPixelForValue(index);
                var y2 = yaxis.bottom;

                ctx.save();
                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.strokeStyle = '#5A5A5A';
                ctx.lineTo(x2, y2);
                ctx.stroke();

                ctx.restore();
            }
        },
    });

    var bedtown_chart = function(data) {
        var fdp = fdptown;
        var town = Math.round((100 - data.town[0])*10)/10;
        var pref = Math.round((100 - data.pref[0])*10)/10;
        var config = {
            type: 'horizontalBar',
            data: {
                labels: [''],
                datasets: [
                    {
                        label: 'My First dataset',
                        backgroundColor: '#FFC000',
                        borderColor: '#FFC000',
                        data: [town],
                    },
                    {
                        label: 'My Second dataset',
                        backgroundColor: '#7F7F7F',
                        borderColor: '#7F7F7F',
                        data: [pref],
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                // lineAtIndex: 0.01,
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                },
                responsive: true,
                tooltips: {
                    enabled: false,
                },
                hover: {
                    mode: false,
                },
                scales: {
                    xAxes: [
                        {
                            ticks: {
                                display: false,
                                // beginAtZero: true,
                                // maxTicksLimit: 10.1,
                                lineHeight:1.2,
                                min: -25,
                                max: 25,
                                stepSize: 5,
                                callback: function(value) {
                                    return (100 - value)
                                },
                                fontSize: 8,
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            // fullWidth:true,
                        },
                    ],
                    yAxes: [
                        {
                            ticks: {
                                display: false,
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false,
                            },
                            barPercentage: 0.1,
                            categoryPercentage: 0.5,
                            barThickness: 30,
                        },
                    ],
                },
            },
        };

        if (data.town[0] < 75 || data.town[0] > 125 || data.pref[0] < 75 || data.pref[0] > 125 ) {
            var x;
            if (Math.abs(town) >= Math.abs(pref)) {
                x = town;
            } else {
                x = pref;
            }
            config.data.datasets[0].data = [town];
            config.data.datasets[1].data = [pref];
            config.options.scales.xAxes[0].ticks = {
                display: false,
                beginAtZero: false,
                min: -x,
                max: x,
                stepSize: Math.abs(x/5),
                callback: function(value) {
                    return (100 - value)
                },
                fontSize: 8,
            };
        }
        var div = '';
        if (data.town[0] == 100) {
            config.data.datasets[0].data = [-config.options.scales.xAxes[0].ticks.stepSize/6];
            div += '<div class="town-bar-100"></div>';
        }
        if (data.pref[0] == 100) {
            config.data.datasets[1].data = [-config.options.scales.xAxes[0].ticks.stepSize/6];
            div += '<div class="pref-bar-100"></div>';
        }
        fdp.el.find('.bedtown-chart .wrapper-bedtown-chart .container-chart .axis').append(div);

        var ctx = document.getElementById('bedtown-canvas').getContext('2d');
        new Chart(ctx, config);
    };

    var percentOwnDepartment = {
        afterDraw: function(chart, options) {
            if (chart.tempData) {
                chart.intersectData = chart.tempData;
                chart.tempData = null;
            } 
        },

        afterUpdate: function (chart, option) {
            var area = chart.chartArea;
            var width = area.right - area.left;
            var data = chart.data.datasets;
            var offset_town = 34;
            var offset_pref = 34;
            var off_town = 34;
            var off_pref = 34;
            var line_town = 0;
            var line_pref = 0;
            for (var i = 0; i < data.length; i++) {
                var town = $('#container1 .owner-percent .owner-percent-town').eq(i).html(data[i].data[0].toFixed(1) + '%');
                var pref = $('#container1 .owner-percent .owner-percent-pref').eq(i).html(data[i].data[1].toFixed(1) + '%');

                var width_town = (data[i].data[0] * width)/100;
                var width_pref = (data[i].data[1] * width)/100;
                var line_town = Math.round(off_town + (width_town/2));
                var line_pref = Math.round(off_pref + (width_pref/2));
                var label_town = $('.owner-percent-town').eq(i).width();
                var label_pref = $('.owner-percent-pref').eq(i).width();
                renderTextOwn(i, width, label_town, width_town, line_town, offset_town, label_pref, width_pref, line_pref, offset_pref);
                offset_town = width;
                offset_pref = width;
                off_town += width_town;
                off_pref += width_pref;
            }
        }
    };

    var renderTextOwn = function (
            i, width,
            label_town, width_town, line_town, offset_town,
            label_pref, width_pref, line_pref, offset_pref
        ) {
        if (label_town >= width_town) {
            $('#container1 .owner-percent .owner-percent-town').eq(i).css({width: ''});
            $('#container1 .owner-percent .owner-percent-town').eq(i).css({left: (offset_town + (i? (34-label_town):0))});
            (function (i, line_town, offset_town) {
                setTimeout(function () {
                    $('#container1 .owner-percent .owner-percent-town').eq(i).css({top: '-10px'});
                    $('#container1 .owner-percent .owner-percent-town').eq(i).css({left: offset_town});
                    $('#container1 .owner-percent .owner-percent-town').eq(i).css({left: line_town + 5});
                    $('#container1 .owner-percent .owner-percent-town').eq(i).css({transform: 'translate(-50%, 0px)'});
                    $('#container1 .owner-percent .owner-line-town').eq(i).css({left: line_town});
                    $('#container1 .owner-percent .owner-line-town').eq(i).css({top: '17px'});
                    $('#container1 .owner-percent .owner-line-town').eq(i).css({height: '25px'});
                }, 1800);
            })(i, line_town, offset_town);
        } else {
            $('#container1 .owner-percent .owner-percent-town').eq(i).css({width: width});
        }

        if (label_pref >= width_pref) {
            $('#container1 .owner-percent .owner-percent-pref').eq(i).css({width: ''});
            $('#container1 .owner-percent .owner-percent-pref').eq(i).css({left: (offset_pref + (i? (34-label_pref):0))});
            (function (i, line_pref, offset_pref) {
                setTimeout(function () {
                    $('#container1 .owner-percent .owner-percent-pref').eq(i).css({bottom: '60px'});
                    $('#container1 .owner-percent .owner-percent-pref').eq(i).css({left: offset_pref});
                    $('#container1 .owner-percent .owner-percent-pref').eq(i).css({left: line_pref + 5});
                    $('#container1 .owner-percent .owner-percent-pref').eq(i).css({transform: 'translate(-50%, 0px)'});
                    $('#container1 .owner-percent .owner-line-pref').eq(i).css({left: line_pref});
                    $('#container1 .owner-percent .owner-line-pref').eq(i).css({bottom: '50px'});
                    $('#container1 .owner-percent .owner-line-pref').eq(i).css({height: '15px'});
                }, 1800);
            })(i, line_pref, offset_pref);
        } else {
            $('#container1 .owner-percent .owner-percent-pref').eq(i).css({width: width});
        }
    }

    var ownDepartmentRatio = function(data) {
            var fontConfig = [
                {
                    family: 'Hiragino Kaku Gothic ProN',
                    size: 12,
                },
                {
                    family: 'Hiragino Kaku Gothic ProN',
                    size: 10,
                },
            ];
            var el = document.getElementById('chart1');
            var elContainer = document.getElementById('container1');
            elContainer.style.visibility = 'visible';
            var ctx = el.getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'barw',
                data: {
                    labels: ['Red', 'Blue'],
                    datasets: [
                        {
                            data: [data.town[0], data.pref[0]],
                            weights: [0.5, 0.4],
                            backgroundColor: [
                                '#FFDE75',
                                '#D6D6D6',
                            ],
                            borderColor: [
                                '#FFDE75',
                                '#D6D6D6',
                            ],
                            borderWidth: 1,
                            datalabels: {
                                display: true,
                                color: ['#C99700', '#414141'],
                                anchor: 'start',
                                align: 'end',
                                Offset: 20,
                                font: fontConfig,
                                padding: {
                                    left : -5,
                                },
                            },
                        },
                        {
                            data: [data.town[1], data.pref[1]],
                            weights: [0.5, 0.4],
                            backgroundColor: [
                                '#FFC000',
                                '#7F7F7F',
                            ],
                            borderColor: [
                                '#FFC000',
                                '#7F7F7F',
                            ],
                            borderWidth: 1,
                            datalabels: {
                                display: true,
                                color: ['#C99700', '#414141'],
                                anchor: 'end',
                                align: 'start',
                                Offset: 20,
                                font: fontConfig,
                                padding: {
                                    right : -5,
                                },
                            },
                        },
                    ],
                },
                options: {
                    layout: {
                        padding: {
                            left: 20,
                            right: 33,
                        }
                    },
                    plugins: {
                        datalabels: {
                            formatter: function(value) {
                                // return (Math.round(value*10)/10).toFixed(1) + '%';
                                return "";
                            },
                        },
                    },
                    animation: {
                        duration: 2000,
                        onComplete: function(chart) {
                          if (chart.chart.initialized) return true;
                            var data = this.intersectData;
                            var ctx = this.ctx;
                            if (data) {
                                data.forEach(function(item) {
                                    var lineFrom = item[0],
                                        lineTo = item[1];
                                    ctx.beginPath();
                                    ctx.strokeStyle = '#D8D8D8';
                                    ctx.lineWidth = 2;
                                    ctx.moveTo(lineFrom[0], lineFrom[1]);
                                    ctx.lineTo(lineTo[0], lineTo[1]);
                                    ctx.stroke();
                                });
                            }
                            chart.chart.initialized = true;
                        },
                    },
                    hover: {
                        mode: false,
                    },
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        enabled: false,
                    },
                    scales: {
                        yAxes: [
                            {
                                categoryPercentage: 0.6,
                                stacked: true,
                                ticks: {
                                    display: false,
                                },
                                gridLines: {
                                    display: false,
                                    drawBorder: false,
                                },
                            },
                        ],
                        xAxes: [
                            {
                                stacked: true,
                                barThickness: 12,
                                ticks: {
                                    display: false,
                                    beginAtZero: true,
                                },
                                gridLines: {
                                    display: false,
                                    drawBorder: false,
                                },
                            },
                        ],
                    },
                },
                plugins: [percentOwnDepartment, intersectLinePlugin],
            });
        }

        var percentNearByStayTime = {
            afterDraw: function(chart, options) {
                if (chart.tempData) {
                    chart.intersectData = chart.tempData;
                    chart.tempData = null;
                } 
            },
            
            afterUpdate: function (chart, option) {
                var area = chart.chartArea;
                var width = area.right - area.left;
                var data = chart.data.datasets;
                var offset_town = 36;
                var offset_pref = 36;
                var next_town = 0;
                var next_pref = 0;
                for (var i = 0; i < data.length; i++) {
                    var width_town = (data[i].data[0] * width)/100;
                    var width_pref = (data[i].data[1] * width)/100;
                    $('#container2 .percent-chart .percent-town').eq(i).html(data[i].data[0].toFixed(1) + '%').css({left: offset_town});
                    $('#container2 .percent-chart .percent-pref').eq(i).html(data[i].data[1].toFixed(1) + '%').css({left: offset_pref});

                    var content_width_town = $('.percent-town').eq(i).outerWidth();
                    var content_width_pref = $('.percent-pref').eq(i).outerWidth();
                    var line_width_town = Math.round(offset_town + width_town/2 - 2);
                    var line_width_pref = Math.round(offset_pref + width_pref/2 - 2);
                    var left_content_town = Math.round(offset_town + data[i].data[0] + 2);
                    var left_content_pref = Math.round(offset_pref + data[i].data[1] + 2);
                    if ((content_width_town + 8) > width_town && i != (data.length - 1)) {
                        if (next_town == 1) {
                            line_width_town += 12;
                        }
                        (function (i, line_width_town, offset_town, left_content_town, next_town) {
                            setTimeout(function () {
                                $('#container2 .percent-chart .percent-town').eq(i).css({top: '-10px'});
                                $('#container2 .percent-chart .percent-town').eq(i).css({left: (next_town? left_content_town + 36 : left_content_town)});
                                $('#container2 .percent-chart .percent-town').eq(i).css({transform: 'translate(-50%, 0px)'});
                                $('#container2 .percent-chart .near-line-town').eq(i).css({left: line_width_town});
                                $('#container2 .percent-chart .near-line-town').eq(i).css({top: (next_town? '12px':'17px')});
                                $('#container2 .percent-chart .near-line-town').eq(i).css({height: (next_town? '35px':'25px')});
                                $('#container2 .percent-chart .near-line-town').eq(i).css({transform: (next_town? 'rotate(45deg)':'rotate(0deg)')});
                            }, THE_FIRST);
                        })(i, line_width_town, offset_town, left_content_town, next_town);
                        next_town++;
                    }else {
                        $('#container2 .percent-chart .percent-town').eq(i).css({left: offset_town });
                    }


                    if ((content_width_pref + 5) > width_pref && i != (data.length - 1)) {
                        if (next_pref == 1) {
                            line_width_pref += 8;
                        }
                        (function (i, line_width_pref, offset_pref, left_content_pref, next_pref) {
                            setTimeout(function () {
                                $('#container2 .percent-chart .percent-pref').eq(i).css({bottom: '60px'});
                                $('#container2 .percent-chart .percent-pref').eq(i).css({left: (next_pref? left_content_pref + 21 : left_content_pref)});
                                $('#container2 .percent-chart .percent-pref').eq(i).css({transform: 'translate(-50%, 0px)'});
                                $('#container2 .percent-chart .near-line-pref').eq(i).css({left: line_width_pref});
                                $('#container2 .percent-chart .near-line-pref').eq(i).css({bottom: (next_pref? '49px':'51px')});
                                $('#container2 .percent-chart .near-line-pref').eq(i).css({height: (next_pref? '19px':'15px')});
                                $('#container2 .percent-chart .near-line-pref').eq(i).css({transform: (next_pref? 'rotate(45deg)':'rotate(0deg)')});
                            }, THE_FIRST);
                        })(i, line_width_pref, offset_pref, left_content_pref, next_pref);
                        next_pref++;
                    } else {
                        $('#container2 .percent-chart .percent-pref').eq(i).css({left: offset_pref});
                    }

                    offset_town += width_town;
                    offset_pref += width_pref;
                }
                setTimeout(function() {THE_FIRST = 300;}, THE_FIRST * 2);
            }
        };

        var nearbyStayTime = function(data) {
            var fontConfig = [
                {
                    family: 'Hiragino Kaku Gothic ProN',
                    size: 12,
                },
                {
                    family: 'Hiragino Kaku Gothic ProN',
                    size: 10,
                },
            ];
            var el = document.getElementById('chart2');
            var elContainer = document.getElementById('container2');
            elContainer.style.visibility = 'visible';
            var ctx = el.getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'barw',
                data: {
                    labels: ['Red', 'Blue'],
                    borderColor: 'rgb(112, 112, 112)',
                    borderWidth: 1,
                    datasets: [
                        {
                            label: '#',
                            data: [data.town[0], data.pref[0]],
                            weights: [0.5, 0.4],
                            backgroundColor: [
                                '#FFDE75',
                                '#D6D6D6',
                            ],
                            datalabels: {
                                display: true,
                                color: ['#C99700', '#414141'],
                                anchor: 'start',
                                align: 'end',
                                Offset: 15,
                                font: fontConfig,
                                padding: {
                                    left : -5,
                                },
                            },
                        },
                        {
                            label: '#',
                            data: [data.town[1], data.pref[1]],
                            weights: [0.5, 0.4],
                            backgroundColor: [
                                '#FFC000',
                                '#7F7F7F',
                            ],
                            datalabels: {
                                display: true,
                                color: ['#C99700', '#414141'],
                                anchor: 'start',
                                align: 'end',
                                Offset: 15,
                                font: fontConfig,
                                padding: {
                                    left : -5,
                                },
                            },
                        },
                        {
                            label: '#',
                            data: [data.town[2], data.pref[2]],
                            weights: [0.5, 0.4],
                            backgroundColor: [
                                '#E6AF00',
                                '#404040',
                            ],
                            datalabels: {
                                display: true,
                                color: ['#FFDE75', '#D6D6D6'],
                                anchor: 'start',
                                align: 'end',
                                Offset: 15,
                                font: fontConfig,
                                padding: {
                                    left : -5,
                                },
                            },
                        },
                    ],
                },
                options: {
                  layout: {
                    padding: {
                        left: 20,
                        right: 33,
                    }
                },
                    animation: {
                        duration: 2000,
                        onComplete: function(chart) {
                            if (chart.chart.initialized) return true;
                            var data = this.intersectData;
                            var ctx = this.ctx;
                            if (data) {
                                data.forEach(function(item) {
                                    var lineFrom = item[0],
                                        lineTo = item[1];
                                    ctx.beginPath();
                                    ctx.strokeStyle = '#D8D8D8';
                                    ctx.lineWidth = 2;
                                    ctx.moveTo(lineFrom[0], lineFrom[1]);
                                    ctx.lineTo(lineTo[0], lineTo[1]);
                                    ctx.stroke();
                                });
                            }
                            chart.chart.initialized = true;
                        },
                    },
                    plugins: {
                        datalabels: {
                            formatter: function(value) {
                                // return (Math.round(value*10)/10).toFixed(1) + '%';
                                return "";
                            },
                        },
                    },
                    hover: {
                        mode: false,
                    },
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        enabled: false,
                    },
                    scales: {
                        yAxes: [
                            {
                                categoryPercentage: 0.6,
                                stacked: true,
                                ticks: {
                                    display: false,
                                },
                                gridLines: {
                                    display: false,
                                    drawBorder: false,
                                },
                            },
                        ],
                        xAxes: [
                            {
                                stacked: true,
                                barThickness: 12,
                                ticks: {
                                    display: false,
                                    beginAtZero: true,
                                    max: 100,
                                },
                                gridLines: {
                                    display: false,
                                    drawBorder: false,
                                },
                            },
                        ],
                    },
                },
                plugins: [percentNearByStayTime, intersectLinePlugin],
            });
        }

        /**
         * error chart town
         */
        var errorChartTown = function(type) {
            var fdp = fdptown;
            var mesg = '<div class="error">現在通信エラーが発生し、ご利用ができません。<br>しばらく後に再度読み込みをおこなってください。</div>'
            fdp.el.find('.wrapper-chart[data-type="'+type+'"] p.note').remove();
            switch(type) {
                case RESIDENT_TYPE:
                    fdp.el.find('.population-chart .household-bar').remove();
                    fdp.el.find('.population-chart .chart-container').append(mesg);
                    break;
                case HOUSEHOLD_TYPE:
                    fdp.el.find('.households-chart .household-bar').remove();
                    fdp.el.find('.households-chart .chart-container').append(mesg);
                    break;
                case BEDTOWN_TYPE:
                    fdp.el.find('.bedtown-chart .wrapper-bedtown-chart').remove();
                    fdp.el.find('.bedtown-chart .chart-container').append(mesg);
                    break;
                case GENDER_TYPE:
                    fdp.el.find('.gender-chart .wrapper-canvas').remove();
                    fdp.el.find('.gender-chart .chart-container').append(mesg);
                    break;
                case OWNERSHIP_TYPE:
                    fdp.el.find('.ownership-chart .wrap-chart').remove();
                    fdp.el.find('.ownership-chart .chart-container').append(mesg);
                    break;
                case RESIDENCE_TYPE:
                    fdp.el.find('.residence-chart .wrap-chart').remove();
                    fdp.el.find('.residence-chart .chart-container').append(mesg);
                    break;
            }
        }
    }


    // BEGIN AREA DATA ============================================
    this.elevation = new function() {
        var station = [], houses = [], hightArea = [], yAxesticks = [];
        var distance, points, elevations, facilitys, max, min, ymin, ymax, ystep;
        this.init = function() {
            var fdp = fdptown;
            houses['position'] = new google.maps.LatLng(lat, lng);
            houses['icon'] = '/sp/imgs/fdp/house.svg';
        }

        var chart_area = function(response) {
            var worker = new Worker('/sp/js/fdp/fdp_data.js');
            worker.postMessage({
                response: JSON.stringify(response),
                category: JSON.stringify(getCategoryApi()),
                house: JSON.stringify(houses['position']),
                station: JSON.stringify(station['position']),
                gData: gData
            });
            worker.addEventListener('message',  function(e){
                distance = response.routes[0].legs[0].distance.value;
                points = e.data.points;
                elevations = e.data.elevations;
                facilitys = e.data.facilitys;
                if (points == false || elevations == false || (typeof facilitys != "object" && facilitys == false)) {
                    chartAreaError();
                }
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
                            titleFontSize: 8,
                            bodyFontSize: 9,
                            footerFontSize: 9,
                            xPadding: 2,
                            yPadding: 3,
                        },
                        layout: {
                            padding: {
                                right: 11,
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
                                    fontSize: 7,
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
                                    },
                                    fontSize: 8,
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
                $(".chart-area .se-pre-con").fadeOut("slow");

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
                                maxHeightX = e._model.x + 30;
                                if (e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 - 11 < meta.data[indexMax + 1]._model.y) {
                                    if (min == 0) {
                                        maxHeightY = meta.data[indexMax + 1]._model.y + 62;
                                    } else {
                                        maxHeightY = meta.data[indexMax + 1]._model.y + 65;
                                    }
                                } else {
                                    maxHeightY = e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 + 55;
                                }
                            } else {
                                if (indexMax == 10) {
                                    maxHeightX = e._model.x - 11;
                                    if (e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 - 11 < meta.data[indexMax - 1]._model.y) {
                                        if (min == 0) {
                                            maxHeightY = meta.data[indexMax - 1]._model.y + 62;
                                        } else {
                                            maxHeightY = meta.data[indexMax - 1]._model.y + 65;
                                        }
                                    } else {
                                        maxHeightY = e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 + 55;
                                    }
                                } else {
                                    maxHeightX = e._model.x + 9;
                                    maxHeightY = e._model.y + (meta.dataset._scale.bottom - e._model.y)/2 + 55;
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
                department: 'contents/ipc/poi/2339:2342:2340', 
                supermaket: 'contents/ipc/poi/2493', 
                conviencestore: 'contents/ipc/poi/2354', 
                // discountstore: 'contents/ipc/poi/2343:2920',
                drugstore: 'contents/ipc/poi/2891',
                restaurent: 'contents/ipc/poi/2356:2312:2317'
            };
        }
        var htmlChartEvelation = function(distance, facilitys, max, min, house) {
            $('.wrapper-chart .title').html('駅から物件まで'+distance+'m（徒歩'+Math.ceil(distance/80)+'分）<span>※80ｍ=1分換算</span>');
            switch (facilitys.length) {
                case 0:
                    $('.chart-area .facility-1').remove();
                    $('.chart-area .facility-2').remove();
                    break;
                case 1:
                    $('.chart-area .facility-1').addClass('pointdata-' + facilitys[0].index).html('<p class="icon-'+facilitys[0].type+'-title">'+facilitys[0].name+'</p></div>');
                    $('.chart-area .facility-1').append('<img src="/sp/imgs/fdp/'+facilitys[0].type+'.svg">');
                    $('.chart-area .facility-2').remove();
                    break;
                case 2:
                    var facility1 = $('.chart-area .facility-1');
                    var facility2 = $('.chart-area .facility-2');
                    facility1.addClass('pointdata-' + facilitys[0].index).html('<p class="icon-'+facilitys[0].type+'-title">'+facilitys[0].name+'</p></div>').append('<img src="/pc/imgs/fdp/'+facilitys[0].type+'.svg">');
                    facility2.addClass('pointdata-' + facilitys[1].index).html('<p class="icon-'+facilitys[1].type+'-title">'+facilitys[1].name+'</p></div>').append('<img src="/pc/imgs/fdp/'+facilitys[1].type+'.svg">');
                    if (((facility1.position().left +  facility1.width() + 5) >= facility2.position().left) && facility2.position().top != 5) {
                        facility1.addClass('uptop');
                    }
                    break;
            }
            $('.chart-area .maximum-height').html('最大高低差<br><span>' + Math.round((max - min)*100)/100 + '</span>m');
            $('.chart-area .building p').html('高低差<br>' + house + 'm');
            $('.chart-area .building img').attr('src', houses['icon']);
            $('.chart-area .station p').html(station['name']);
            $('.chart-area .station p').addClass('icon-station-title');

            if (typeof luxury01 != 'undefined' && luxury01) {
                $('.chart-area #chart-area').css({'border-right': '1px solid #161616'});
            }

            renderCanvasX();
            renderCanvasY();
            positionFacilityTitle();
            positionStationFacility();
            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    renderCanvasY();    
                    $(".group-facility p").css({"margin-left": "0px"});
                    positionFacilityTitle();
                    positionStationFacility();
                }, 150);
            });

            
        }

        var renderCanvasX = function() {
            var fdp = fdptown;
            var canvas_x = fdp.el.find('.chart-area .canvas-x');
            canvas_x.empty();
            elevations[1].forEach(function (dis) {
                canvas_x.append('<div>'+ dis +'</div>')
            });
        }

        var renderCanvasY = function() {
            var fdp = fdptown;
            var canvas_y = fdp.el.find('.chart-area .canvas-y');
            canvas_y.empty();
            var height = fdp.el.find('.chart-area #chart-area').height() - 10.5;
            var index = 0;
            for(var i = ymax; i >= ymin; i = i - ystep) {
                var top = 1 + index*height/7;
                canvas_y.append('<div style="top: '+top+'px">'+ i +'</div>');
                index++;
            }
        }

        var positionFacilityTitle = function() {
            var facility1 = 0;
            var facility2 = 0;
            if ($('.chart-area .facility-1').length > 0) {
                facility1 = document.querySelector('.facility-1 p').getBoundingClientRect().left + $('.facility-1 p').width();
            }
            if ($('.chart-area .facility-2').length > 0) {
                facility2 = document.querySelector('.facility-2 p').getBoundingClientRect().left + $('.facility-2 p').width();
            }
            var info = document.querySelector('.chart-container-area').getBoundingClientRect().right - 16;
            if (facility1 > info) {
                var value = info - facility1 - 16;
                $('.chart-area .facility-1 p').css({"margin-left": value + "px"});
            }
            if (facility2 > info) {
                var value = info - facility2 - 16;
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

        this.getPositionStation = function() {
            var fdp = fdptown;
            if (fdp.el.find('.chart-area').length == 0) return;
            var params = {lat: lat, lon: lng, rad: 10000, limit: 1, sort: 'A:distance'};
            var url = gData.urlBase + 'contents/ipc/eki/';
            api(url, params, function(res) {
                if (res.status == -1) {
                    errorChartElevation();
                    return;
                }
                if (res.count == 0) {
                    fdp.el.find('.chart-area').remove();
                    return;
                }
                var apiData = res.data[0];
                station['position'] = new google.maps.LatLng(apiData.geometry.coordinates[1], apiData.geometry.coordinates[0]);
                station['name'] = apiData.properties.col_14;
                calculateRoute();
            }).fail(function() {
                errorChartElevation();
            });
        }

        var calculateRoute = function() {
            var fdp = fdptown;
            // Instantiate a directions service.
            var directionsService = new google.maps.DirectionsService;
            directionsService.route({
                origin: station['position'],
                destination: new google.maps.LatLng(lat, lng),
                avoidTolls: true,
                avoidHighways: false,
                travelMode: google.maps.TravelMode.WALKING
            }, function (response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    if ($('.chart-area').length > 0) {
                        chart_area(response);
                    }
                } else {
                    if ($('.chart-area').length > 0) {
                        errorChartElevation();
                    }
                }
            });
        }

        /**
         * error chart elevations
         */
        var errorChartElevation = function() {
            var fdp = fdptown;
            fdp.el.find('.chart-area').append('<div class="error">'+MESG+'</div>').find('.chart-container-area').remove();
        }

        var api = function(url, data, fn) {
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
                    return;
    
                });
            return defer;
          }
    }
};
