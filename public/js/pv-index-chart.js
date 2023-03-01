(function ($) {
  'use strict';

  $(function () {
    $('.pv-point-chart').each(function () {
      var MAX = $(this).data('plot-max-point');
      var pv = $(this).data('plot-pv');
      var index = $(this).data('plot-point');

      var pointMaxMin;
      var max = 0, min = 0;
      $.each([pv, index], function(k, values){
        $.each(values, function(k, v){
          if (v[1] < min){
            min = v[1];
          }else if (v[1] > max){
            max = v[1]
          }
        });
      });
      pointMaxMin = [max, min];

      var id = $(this).prop('id') || $(this).prop('id', 'plot-chart-' + (Math.floor(Math.random() * 100 + 100))).prop('id');

      $.jqplot(id, [pv, index], {
        seriesColors: ['#82baf1', '#e5bf26'],
        legend: {show: true, location: 'ne', placement:'outside'},
        axes: {
          xaxis: {
            renderer: $.jqplot.DateAxisRenderer,
            tickOptions: {formatString: '%#m月'},
            min: pv[0][0],
            max: pv[pv.length - 1][0],
            tickInterval: '1 month'
          },
          yaxis: {
            min: 0,
            max: parseInt(MAX) + 120 > pointMaxMin[0] ? Math.ceil((parseInt(MAX) + 120) / 100) * 100 : undefined
          }
        },
        seriesDefaults: {
          lineWidth: 2,
          shadow: false,
          markerOptions: {style: 'filledCircle', shadow: false}
        },
        series: [
          {label: 'PV数'},
          {label: '総合点数'}
        ],

        grid: {
          backgroundColor: '#FFF',
          gridLineColor: '#EEE',
          borderColor: '#CCC',
          shadow: false
        },

        canvasOverlay: {
          show: true,
          objects: [
            {dashedHorizontalLine: {
              showTooltip: true,
              tooltipFormatString: String(MAX),
              showTooltipPrecision: 0.5,
              name: 'bam-bam',
              y: MAX,
              lineWidth: 1,
              dashPattern: [5],
              xOffset: 0,
              color: '#96A2B9',
              shadow: false
            }}
          ]
        },

        highlighter: {
          show: true,
          sizeAdjust: 7.5,
          tooltipAxes: 'y'
        },

        gridPadding: {
          top: 0,
          bottom: 40,
          right: 100,
          left: 40
        },
      });
    });
  });
})(jQuery);
