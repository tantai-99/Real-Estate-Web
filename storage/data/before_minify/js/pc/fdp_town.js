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

    var gData = {};
    gData.sessionKey = "";
    gData.urlBase    = "";
    var townElement, ken_ct, lat, lng; 
    Chart.defaults.global.plugins.datalabels.display = false;
    var has_run_chart = [false, false, false, false,false, false];
    this.run = function (_app,search,$el){
        var fdp = fdptown;
        fdp.app = _app;
        fdp.search  = search;
        fdp.el  = $el;
        townElement = fdp.el.find('div.item-detail-tab-body.article-town');
        ken_ct = townElement.data('ken-cd');
        lat = townElement.data('gmap-pin-lat');
        lng = townElement.data('gmap-pin-long');
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
            var worker = new Worker('/pc/js/fdp/fdp_data.js');
            worker.postMessage({
                town: true,
                house: JSON.stringify({lat: lat, lng: lng}),
                gData: gData,
            });
            var towns;
            worker.addEventListener('message',  function(e){
                $('.article-town .wrapper-chart').each(function() {
                    var type = parseInt($(this).data('type'));
                    var elem = $(this);
                    showChartTown(type, elem, e.data);
                });
            });
       });
    }

    var showChartTown = function (type, elem, towns) {
        var fdp = fdptown;
        var worker = new Worker('/pc/js/fdp/fdp_data.js?v=20191121');
        worker.postMessage({
            chart: true,
            towns: towns,
            ken_ct: ken_ct,
            type_chart: type,
            gData: gData
        });
        worker.addEventListener('message',  function(e){
            var fdp = fdptown;
            elem.find(".se-pre-con").fadeOut("slow");
            if (e.data.town == false || e.data.pref == false) {
                errorMessageChart(type);
                return;
            }
            switch (type) {
                case RESIDENT_TYPE:
                    fdp.el.find('.population-chart .household-bar .text-value').each(function (i, element) {
                        $(this).text(e.data.town[i].toFixed(1) + '%');
                    });
                    break;
                case HOUSEHOLD_TYPE:
                    fdp.el.find('.households-chart .household-bar .text-value').each(function (i, element) {
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
    }

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

    var errorMessageChart = function(type) {
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
        
        var maxBarContainerWidth = 260;
        var maxBarWidthRatio = 190 / maxBarContainerWidth;

        function minBarWidthLimiter(widthRatio) {
            return "".concat(maxBarWidthRatio * widthRatio * 100, "%");
        }
        
        $(".process2").html(age2.toFixed(1)+'%');
        $(".process4").html(age4.toFixed(1)+'%');
        $(".process6").html(age6.toFixed(1)+'%');
        $(".process8").html(age8.toFixed(1)+'%');

        var percent_array = [age1, age2, age3, age4, age5, age6, age7, age8];
        var maxInNumbers = Math.max.apply(Math, percent_array);
        age1 = minBarWidthLimiter(data.town[0]/maxInNumbers);
        age2 = minBarWidthLimiter(data.pref[0]/maxInNumbers);
        age3 = minBarWidthLimiter(data.town[1]/maxInNumbers);
        age4 = minBarWidthLimiter(data.pref[1]/maxInNumbers);
        age5 = minBarWidthLimiter(data.town[2]/maxInNumbers);
        age6 = minBarWidthLimiter(data.pref[2]/maxInNumbers);
        age7 = minBarWidthLimiter(data.town[3]/maxInNumbers);
        age8 = minBarWidthLimiter(data.pref[3]/maxInNumbers);

        $(".process1").css("width",age1);
        $(".process2").css("width",age2);
        $(".process3").css("width",age3);
        $(".process4").css("width",age4);
        $(".process5").css("width",age5);
        $(".process6").css("width",age6);
        $(".process7").css("width",age7);
        $(".process8").css("width",age8);

        $(".full1").animate({
            width: '100%'
        }, 2000);
    };

    var households_chart = function(data) {
        var fdp = fdptown;
        var households1 = data.town[0];
        var households2 = data.pref[0];
        var households3 = data.town[1];
        var households4 = data.pref[1];
        var households5 = data.town[2];
        var households6 = data.pref[2];

        var maxBarContainerWidth = 260;
        var maxBarWidthRatio = 190 / maxBarContainerWidth;

        function minBarWidthLimiter(widthRatio) {
            return "".concat(maxBarWidthRatio * widthRatio * 100, "%");
        }

        $(".households2").html(households2.toFixed(1)+'%');
        $(".households4").html(households4.toFixed(1)+'%');
        $(".households6").html(households6.toFixed(1)+'%');

        var percent_array = [households1, households2, households3, households4, households5, households6];
        var maxInNumbers = Math.max.apply(Math, percent_array);

        households1 = minBarWidthLimiter(data.town[0]/maxInNumbers);
        households2 = minBarWidthLimiter(data.pref[0]/maxInNumbers);
        households3 = minBarWidthLimiter(data.town[1]/maxInNumbers);
        households4 = minBarWidthLimiter(data.pref[1]/maxInNumbers);
        households5 = minBarWidthLimiter(data.town[2]/maxInNumbers);
        households6 = minBarWidthLimiter(data.pref[2]/maxInNumbers);

        $(".households1").css("width",households1);
        $(".households2").css("width",households2);
        $(".households3").css("width",households3);
        $(".households4").css("width",households4);
        $(".households5").css("width",households5);
        $(".households6").css("width",households6);

        $(".full2").animate({
            width: '100%'
        }, 2000);

    };

    var helpers = Chart.helpers;
    Chart.controllers.doughnut = Chart.controllers.doughnut.extend({
      // function to increase inner charts diameter
        update: function (reset) {
            var me = this;

            if (me.index === 0) {// Outer chart
                var chart = me.chart,
                    chartArea = chart.chartArea,
                    opts = chart.options,
                    arcOpts = opts.elements.arc,
                    availableWidth = chartArea.right - chartArea.left - arcOpts.borderWidth,
                    availableHeight = chartArea.bottom - chartArea.top - arcOpts.borderWidth,
                    minSize = Math.min(availableWidth, availableHeight),
                    offset = {
                        x: 0,
                        y: 0
                    },
                    meta = me.getMeta(),
                    cutoutPercentage = 80,
                    circumference = opts.circumference;

                chart.borderWidth = me.getMaxBorderWidth(meta.data);
                chart.outerRadius = Math.max((minSize - chart.borderWidth) / 2, 0);
                chart.innerRadius = Math.max(cutoutPercentage ? (chart.outerRadius / 100) * (cutoutPercentage) : 0, 0);
                chart.radiusLength = ((chart.outerRadius - chart.innerRadius) / chart.getVisibleDatasetCount());
                chart.offsetX = offset.x * chart.outerRadius;
                chart.offsetY = offset.y * chart.outerRadius;

                meta.total = me.calculateTotal();

                me.outerRadius = chart.outerRadius - (chart.radiusLength * me.getRingIndex(me.index));
                me.innerRadius = Math.max(me.outerRadius - chart.radiusLength, 0);
            } 
            else if (me.index === 1) { // Inner chart
                var chart = me.chart;
                opts = chart.options,
                    meta = me.getMeta(),
                    cutoutPercentage = opts.cutoutPercentage,
                    circumference = opts.circumference;
                meta.total = me.calculateTotal();
                me.outerRadius = 50;
                me.innerRadius = 100;

                // factor in the radius buffer if the chart has more than 1 dataset
                /*if (me.index > 0) {
                    me.outerRadius = 70;
                }*/
            }
            helpers.each(meta.data, function (arc, index) {
                me.updateElement(arc, index, reset);
            });
        }
    });

    var sexRatio_chart_update = function(data) {
        var fdp = fdptown;
        myDoughnut_config.data.datasets[0].data[0]= data.pref[0];
        myDoughnut_config.data.datasets[0].data[1]= 0;
        myDoughnut_config.data.datasets[0].data[2]= data.pref[1];
        myDoughnut_config.data.datasets[1].data[0]= data.town[0];
        myDoughnut_config.data.datasets[1].data[1]= 0;
        myDoughnut_config.data.datasets[1].data[2]= data.town[1];
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
        myDoughnut.update();
    }

    var sexRatio_chart = function() {
        var town= [0,100,0];
        var pref= [0,100,0];
        myDoughnut_config = {
            type: "doughnut",
            data: {
                datasets: [{
                    data: town,
                    explodeSection: 0,
                    backgroundColor: ["#15243D", "transparent", "#5B2925"],
                    borderWidth: [2, 0, 2],
                    borderColor: ["white", "transparent", "white"],
                    label: "Outer Data"
                }, {
                    data: pref,
                    explodeSection: 0,
                    backgroundColor: ["#84A0CA", "transparent", "#DFBBB9"],
                    borderWidth: [2, 0, 2],
                    borderColor: ["white", "transparent", "white"],
                    label: "Inner Data"
                }],
                labels: town
            },
            options: {
                hover: {
                    mode: false
                },
                legend: {
                    display: false
                },
                title: {
                    display: false
                },
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 0,
                tooltips: {
                    enabled: false
                },
                animation: {
                    duration: 2000,
                    animateScale: true,
                    animateRotate: true
                },
                plugins: {
                    labels: [{
                        render: function(data) {
                            return ""
                        },
                        position: "outside",
                        fontSize: 16,
                        fontColor: ["#15243D", "transparent", "#5B2925"],
                    }, {
                        render: function(data) {
                            return ""
                        },
                        fontSize: 12,
                        position: "default",
                        fontColor: ["#fff","transparent", "#fff"]
                    }],
                    legend: {
                        display: false,
                        position: "bottom"
                    },
                    datalabels: {
                        display: false
                    }
                }
            }
        };
        var ctx = document.getElementById("chart_doughnut").getContext("2d");
        myDoughnut = new Chart(ctx,myDoughnut_config);
    };

    var originalLineDraw = Chart.controllers.horizontalBar.prototype.draw;
    Chart.helpers.extend(Chart.controllers.horizontalBar.prototype, {
        draw: function () {
            originalLineDraw.apply(this, arguments);

            var chart = this.chart;
            var ctx = chart.chart.ctx;

            var index = chart.config.options.lineAtIndex;
            if (index) {
                var xaxis = chart.scales['x-axis-0'];
                var yaxis = chart.scales['y-axis-0'];

                var x1 = xaxis.getPixelForValue(index);                       
                var y1 = yaxis.top;                                                   

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
        }
    });

    var bedtown_chart = function(data) {
        var fdp = fdptown;
        var town = Math.round((100 - data.town[0])*10)/10;
        var pref = Math.round((100 - data.pref[0])*10)/10;
        var config = {
            type: 'horizontalBar',
            data: {
                labels: [""],
                datasets: [{
                    label: "My First dataset",
                    backgroundColor: "#FFC000",
                    borderColor: "#FFC000",
                    data: [town],
                }, {
                    label: "My Second dataset",
                    backgroundColor: "#7F7F7F",
                    borderColor: "#7F7F7F",
                    data: [pref]
                }],
            },
            options: {
                hover: {
                    mode: false
                },
                maintainAspectRatio: false,
                // lineAtIndex: 0.01,
                legend: {
                    display: false
                },
                title: {
                    display: false
                },
                responsive: true,
                tooltips: {
                    enabled: false
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            display: false,
                            beginAtZero: false,
                            min: -25,
                            max: 25,
                            stepSize: 5,
                            callback: function(value) {
                                return (100 - value)
                            }
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            display: false
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false,
                        },
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }]
                }
            }
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
                }
            }
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

        var ctx = document.getElementById("bedtown-canvas").getContext("2d");
        new Chart(ctx, config);
    }

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
        var fullSize = scale.isHorizontal() ? scale.width : scale.height;
        var tickSize = fullSize / scale.ticks.length;
        var categorySize = tickSize * options.categoryPercentage;
        var fullBarSize = categorySize / stackCount;
        var barSize = fullBarSize * options.barPercentage;

        barSize = Math.min(
          helpers.getValueOrDefault(options.barThickness, barSize),
          helpers.getValueOrDefault(options.maxBarThickness, Infinity)
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
        var base = scale.getPixelForValue(null, index, datasetIndex, isCombo);
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

            if (Math.round(currentRect._model.x) + 45 !== chartWidth) {
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
            var offset_town = 37;
            var offset_pref = 37;
            var off_town = 37;
            var off_pref = 37;
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
        if ((label_town + 5) >= width_town) {
            $('#container1 .owner-percent .owner-percent-town').eq(i).css({width: ''});
            $('#container1 .owner-percent .owner-percent-town').eq(i).css({left: (offset_town + (i? (37-label_town):0))});
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

        if ((label_pref + 5) >= width_pref) {
            $('#container1 .owner-percent .owner-percent-pref').eq(i).css({width: ''});
            $('#container1 .owner-percent .owner-percent-pref').eq(i).css({left: (offset_pref + (i? (37-label_pref):0))});
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
        size: 16,
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
        labels: ['#1', '#2'],
        datasets: [
          {
            label: '#1',
            data: [data.town[0], data.pref[0]],
            weights: [0.8, 0.6],
            backgroundColor: ['#FFDE75', '#D6D6D6'],
            borderColor: ['#FFDE75', '#D6D6D6'],
            borderWidth: 1,
            datalabels: {
              display: true,
              color: ['#C99700', '#414141'],
              anchor: 'start',
              align: 'end',
              Offset: 20,
              font: fontConfig,
              padding: {
                left: -5,
            }
            },
          },
          {
            label: '#2',
            data: [data.town[1], data.pref[1]],
            weights: [0.8, 0.6],
            backgroundColor: ['#FFC000', '#7F7F7F'],
            borderColor: ['#FFC000', '#7F7F7F'],
            borderWidth: 1,
            datalabels: {
              display: true,
              color: ['#C99700', '#414141'],
              anchor: 'end',
              align: 'start',
              Offset: 20,
              font: fontConfig,
              padding: {
                right: -5,
            }
            },
          },
        ],
      },
      options: {
        layout: {
            padding: {
                right: 45,
                left: 35,
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
          mode: false
        },
        legend: {
          display: false,
        },
        title: {
          display: false,
        },
        responsive: false,
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
      plugins: [percentOwnDepartment],
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
            var offset_town = 39;
            var offset_pref = 39;
            var next_town = 0;
            var next_pref = 0;
            for (var i = 0; i < data.length; i++) {
                var width_town = (data[i].data[0] * width)/100;
                var width_pref = (data[i].data[1] * width)/100;
                $('#container2 .percent-chart .percent-town').eq(i).html(data[i].data[0].toFixed(1) + '%').css({left: offset_town});
                $('#container2 .percent-chart .percent-pref').eq(i).html(data[i].data[1].toFixed(1) + '%').css({left: offset_pref});

                var content_width_town = $('.percent-town').eq(i).outerWidth();
                var content_width_pref = $('.percent-pref').eq(i).outerWidth();
                var line_width_town = Math.round(offset_town + width_town/2 - 5);
                var line_width_pref = Math.round(offset_pref + width_pref/2 - 5);
                var left_content_town = Math.round(offset_town + data[i].data[0] + 5);
                var left_content_pref = Math.round(offset_pref + data[i].data[1] + 2);
                if ((content_width_town + 8) > width_town && i != (data.length - 1)) {
                    if (next_town == 1) {
                        left_content_town += Math.ceil(($('.percent-town').eq(i-1).outerWidth() - (data[i-1].data[0] * width)/100 + 2 * data[i-1].data[0]));
                        line_width_town += 12;
                    }
                    (function (i, line_width_town, offset_town, left_content_town, next_town) {
                        setTimeout(function () {
                            $('#container2 .percent-chart .percent-town').eq(i).css({top: '-10px'});
                            $('#container2 .percent-chart .percent-town').eq(i).css({left: left_content_town});
                            $('#container2 .percent-chart .percent-town').eq(i).css({transform: 'translate(-50%, 0px)'});
                            $('#container2 .percent-chart .near-line-town').eq(i).css({left: line_width_town});
                            $('#container2 .percent-chart .near-line-town').eq(i).css({top: (next_town? '12px':'17px')});
                            $('#container2 .percent-chart .near-line-town').eq(i).css({height: (next_town? '35px':'25px')});
                            $('#container2 .percent-chart .near-line-town').eq(i).css({transform: (next_town? 'rotate(45deg)':'rotate(0deg)')});
                        }, 1800);
                    })(i, line_width_town, offset_town, left_content_town, next_town);
                    next_town++;
                }else {
                    $('#container2 .percent-chart .percent-town').eq(i).css({left: offset_town });
                }


                if ((content_width_pref + 5) > width_pref && i != (data.length - 1)) {
                    if (next_pref == 1) {
                        left_content_pref += Math.ceil(($('.percent-pref').eq(i-1).outerWidth() - (data[i-1].data[1] * width)/100 + 2 * data[i-1].data[1]));
                        line_width_pref += 6;
                    }
                    (function (i, line_width_pref, offset_pref, left_content_pref, next_pref) {
                        setTimeout(function () {
                            $('#container2 .percent-chart .percent-pref').eq(i).css({bottom: '60px'});
                            $('#container2 .percent-chart .percent-pref').eq(i).css({left: left_content_pref});
                            $('#container2 .percent-chart .percent-pref').eq(i).css({transform: 'translate(-50%, 0px)'});
                            $('#container2 .percent-chart .near-line-pref').eq(i).css({left: line_width_pref});
                            $('#container2 .percent-chart .near-line-pref').eq(i).css({bottom: (next_pref? '49px':'51px')});
                            $('#container2 .percent-chart .near-line-pref').eq(i).css({height: (next_pref? '19px':'15px')});
                            $('#container2 .percent-chart .near-line-pref').eq(i).css({transform: (next_pref? 'rotate(45deg)':'rotate(0deg)')});
                        }, 1800);
                    })(i, line_width_pref, offset_pref, left_content_pref, next_pref);
                    next_pref++;
                } else {
                    $('#container2 .percent-chart .percent-pref').eq(i).css({left: offset_pref});
                }

                offset_town += width_town;
                offset_pref += width_pref;
            }
        }
    };
    
  var nearbyStayTime = function(data) {
    var fontConfig = [
      {
        family: 'Hiragino Kaku Gothic ProN',
        size: 16,
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
        labels: ['#1', '#2'],
        borderColor: 'rgb(112, 112, 112)',
            borderWidth: 1,
        datasets: [
          {
            label: '#1',
            data: [data.town[0], data.pref[0]],
            weights: [0.8, 0.6],
            backgroundColor: ['#FFDE75', '#D6D6D6'],
            datalabels: {
              display: true,
              color: ['#C99700', '#414141'],
              anchor: 'start',
              align: 'end',
              Offset: 20,
              font: fontConfig,
              padding: {
                  left: -5,
              }
            },
          },
          {
            label: '#2',
            data: [data.town[1], data.pref[1]],
            weights: [0.8, 0.6],
            backgroundColor: ['#FFC000', '#7F7F7F'],
            datalabels: {
              display: true,
              color: ['#C99700', '#414141'],
              anchor: 'start',
              align: 'end',
              Offset: 20,
              font: fontConfig,
              padding: {
                left: -5,
            }
            },
          },
          {
            label: '#',
            data: [data.town[2], data.pref[2]],
            weights: [0.8, 0.6],
            backgroundColor: ['#E6AF00', '#404040'],
            datalabels: {
              display: true,
              color: ['#FFDE75', '#D6D6D6'],
              anchor: 'start',
              align: 'end',
              Offset: 20,
              font: fontConfig,
              padding: {
                left: -5,
            }
            },
          },
        ],
      },
      options: {
          layout: {
            padding: {
                right: 45,
                left: 33,
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
        plugins: {
          datalabels: {
            formatter: function(value) {
              // return (Math.round(value*10)/10).toFixed(1) + '%';
              return "";
            },
          },
        },
        hover: {
          mode: false
        },
        legend: {
          display: false,
        },
        title: {
          display: false,
        },
        responsive: false,
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
      plugins: [percentNearByStayTime],
    });
  }

    var post = function (apiUrl,data,callback) {
        var fdp = fdptown;
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
}