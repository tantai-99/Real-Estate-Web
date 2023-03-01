$(function () {
  'use strict';

    var date = JSON.parse($("#graph-area-data-date").text());
    var upv  = JSON.parse($("#graph-area-data-upv").text());
    var ccnt = JSON.parse($("#graph-area-data-ccnt").text());

    var data = [
            [ [Number(date[0].substr(5))+'月', parseInt(upv[date[0]])], [Number(date[1].substr(5))+'月', parseInt(upv[date[1]])] , [Number(date[2].substr(5))+'月', parseInt(upv[date[2]])] ],
            [ [Number(date[0].substr(5))+'月', parseInt(ccnt[date[0]])] , [Number(date[1].substr(5))+'月', parseInt(ccnt[date[1]])] , [Number(date[2].substr(5))+'月', parseInt(ccnt[date[2]])] ]
        ];

    var options = {
            series:[
                {
                    label: 'ユーザー数',
                    renderer: jQuery . jqplot . BarRenderer
                },
                {
                    label: 'お問い合わせ数',
                    yaxis: 'y2axis'
                }
            ],
            seriesColors: [ "#82baf1", "#e5bf26"],
            seriesDefaults : {
                shadow: false,
                markerOptions: {shadow: false},
                rendererOptions: {
                    barMargin: 100
                }
            },
            grid:{background: "#fff",gridLineColor: '#eee',shadow: false , borderWidth: 1},
            axes: {
                xaxis: {
                    renderer: jQuery . jqplot . CategoryAxisRenderer,
                },
                x2axis: {
                    renderer: jQuery . jqplot . CategoryAxisRenderer,
                },
                yaxis: {
                    numberTicks: 5,  
                    min:0,
                },
                y2axis: {
                    numberTicks: 5,  
                    min:0,
                },
            },
            highlighter: {
              show: true,
              sizeAdjust: 7.5,
              tooltipAxes: 'y'
            },            
            gridPadding: {
              right: 130
            },
            legend:{
                show: true,
                placement: 'outside',
                location: 'ne',
                renderer: jQuery . jqplot . EnhancedLegendRenderer,
                rendererOptions: {
                    numberRows: 5,
                }
            },
        };

    //件数が少ないときは、小数点をさせないようにする
    if(parseInt(ccnt[date[0]]) <= 1 && parseInt(ccnt[date[1]]) <= 1 && parseInt(ccnt[date[2]]) <= 1) {
        options.axes.y2axis.ticks = [ 0,1,2,3,4 ];
        options.axes.y2axis.tickOptions = {formatString: '%d'};
        console.log(options.axes.y2axis);
    }

    var jq = $.jqplot('access-contact-graph', data, options);
    //ブラウザのリサイズが発生したときに見た目を整える
    window.onresize = function( event) {
        jq.replot();
    }
});
