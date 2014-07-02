function initialize() {
	var coordinates = [];
	jQuery('tr[data-lng]').each(function(){
		coordinates.push({
			lng: jQuery(this).attr('data-lng'),
			lat: jQuery(this).attr('data-lat')
		});
	});

	var options = {
		mapTypeId: google.maps.MapTypeId.TERRAIN
	};

	var map = new google.maps.Map(document.getElementById('tracking-route'), options);

	var line   = [];
	var bounds = new google.maps.LatLngBounds();
	
	for(i = coordinates.length-1; i>=0; i--){
		var latlng = new google.maps.LatLng(coordinates[i].lat, coordinates[i].lng)
		line.push(latlng);
		bounds.extend(latlng);
		
		new google.maps.Marker({
		    position: latlng,
		    icon: {
		      path: google.maps.SymbolPath.CIRCLE,
		      scale: 2
		    },
		    strokeColor: '#46B8DA',
		    draggable: false,
		    map: map
		});
	}
	
	var lineSymbol = {
		    path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW
		  };
  
	var graph = new google.maps.Polyline({
	    path: line,
	    geodesic: true,
	    strokeColor: '#F00',
	    strokeOpacity: 1.0,
	    strokeWeight: 2,
	    icons: [{
	        icon: lineSymbol,
	        offset: '100%'
	      }],
	});

	graph.setMap(map);
	map.fitBounds(bounds);
}

google.maps.event.addDomListener(window, 'load', initialize);