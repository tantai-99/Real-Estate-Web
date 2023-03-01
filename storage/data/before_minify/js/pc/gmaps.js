jQuery(function () {
    'use strict';
  
    var ZOOM_DEFAULT = 15;
  
    $('.element-map-canvas, .parts_map_canvas, .map-article').each(function () {
  
      var data = $(this).data();
  
      var options = {
        zoom: typeof data.gmapZoom === 'undefined' ? ZOOM_DEFAULT : data.gmapZoom,
        center: new google.maps.LatLng(data.gmapCenterLat, data.gmapCenterLong),
        position: new google.maps.LatLng(data.gmapPinLat, data.gmapPinLong)
      };
  
      var balloon = $(this).html().replace(/\s+/g, '');
  
      if (balloon.length > 0) {
        options.content = balloon;
      }
  
      var map = new google.maps.Map(this, options);
      var marker = new google.maps.Marker({
        position: options.position,
        map: map,
        clickable: false
      });
  
      if (options.content) {
        var infoWindow = new google.maps.InfoWindow({
          content: options.content
        });
        infoWindow.open(map, marker);
  
        google.maps.event.addListener(infoWindow, "closeclick", function () {
          google.maps.event.addListenerOnce(marker, "click", function () {
            infoWindow.open(map, marker);
          });
        });
      }
  
    });
  });
  