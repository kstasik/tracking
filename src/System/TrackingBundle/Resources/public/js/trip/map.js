function initialize() {
	var coordinates = [];
	jQuery('tr[data-lng]').each(function(){
		coordinates.push({
			lng: jQuery(this).attr('data-lng'),
			lat: jQuery(this).attr('data-lat'),
			type: jQuery(this).attr('data-type')
		});
	});

	var options = {
		mapTypeId: google.maps.MapTypeId.TERRAIN
	};

	var map = new google.maps.Map(document.getElementById('tracking-route'), options);

	var line   = [];
	var bounds = new google.maps.LatLngBounds();
	var parking = new google.maps.MarkerImage(
			assetsBaseDir+'img/parking-red.png',
	        new google.maps.Size(15, 15),
	        new google.maps.Point(0,0),
	        new google.maps.Point(7, 7)
	);
	var parkinga = new google.maps.MarkerImage(
			assetsBaseDir+'img/parking.png',
	        new google.maps.Size(15, 15),
	        new google.maps.Point(0,0),
	        new google.maps.Point(7, 7)
	);
	
	var parinkgr = {
			strokeColor: '#247083',
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: '#48A2B9',
			fillOpacity: 0.35,
			map: map,
			radius: jQuery('#tracking-route').attr('data-radius')-0
    };
	
	for(i = coordinates.length-1; i>=0; i--){
		var latlng = new google.maps.LatLng(coordinates[i].lat, coordinates[i].lng)
		
		if(coordinates[i].type == 1){
			line.push(latlng);
			bounds.extend(latlng);
			new google.maps.Marker({
			    position: latlng,
			    icon: parkinga,
			    draggable: false,
			    map: map
			});
			
			var opts = parinkgr;
			opts.center = latlng; 
			new google.maps.Circle(opts);
		}
		else if(coordinates[i].type == 2){
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
			
		}else if(coordinates[i].type == 3){
			line.push(latlng);
			bounds.extend(latlng);
			
			new google.maps.Marker({
			    position: latlng,
			    icon: parking,
			    draggable: false,
			    map: map
			});
			
			var opts = parinkgr;
			opts.center = latlng; 
			new google.maps.Circle(opts);
		}
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