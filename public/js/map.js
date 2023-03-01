(function () {

  var gmap = window.gmap = {};
  
  gmap.initialize = function (mapId, searchBtnId, lat, lng) {

    var mapOptions = {
      disableDefaultUI: true,
      center: new google.maps.LatLng(lat, lng),
      zoom: 15,
      zoomControl: true,
      zoomControlOptions: {
        style: google.maps.ZoomControlStyle.DEFAULT,
        position: google.maps.ControlPosition.RIGHT_BOTTOM
      },
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      mapTypeControl: false,
      panControl: false,
      scaleControl: false,
      streetViewControl: false,
      overviewMapControl: false
    };

    var map = new google.maps.Map(document.getElementById(mapId), mapOptions);

    var markerOptions = {
      position: new google.maps.LatLng(lat, lng),
      map: map,
      draggable: true,
      title: '中心位置'
    };

    var marker = new google.maps.Marker(markerOptions);

    gmap.setValue(map, marker);

    // click map
    google.maps.event.addListener(map, 'click', function (e) {
      marker.setPosition(e.latLng);
    });

    // shift pin
    google.maps.event.addListener(marker, 'position_changed', function (e) {
      gmap.setValue(map, marker);
    });

    // search
    var service = new google.maps.places.PlacesService(map);

    var callback = function (results, status) {

      if (status !== google.maps.places.PlacesServiceStatus.OK) {
        return;
      }

      var place = results[0].geometry.location;
      marker.setPosition(place);
      map.setCenter(place);
      map.setZoom(15);
    }

    $('#'+searchBtnId).click(function () {

          var searchBtn = $('#'+searchBtnId);
          var val = searchBtn.closest('.item-add').find(':text').val();
          if (val.length == 0) {
            return;
          }
          var request = {
            query: val,
            location: marker.getPosition(),
            radius: '30000'
          }
          service.textSearch(request, callback);
        }
    )

  };

  gmap.setValue = function (map, marker) {
    var position = marker.getPosition();
    $(map.getDiv())
        .next().attr('value', position.lat())
        .next().attr('value', position.lng());
  }

})();