<!DOCTYPE html> 
<html> 
<head> 
	<title>Take Me - An HTML5 Direction App</title> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="css/jquery.mobile-1.2.0.min.css" />
	<link rel="stylesheet" href="css/font-awesome.css" />
	<link rel="stylesheet" href="css/style.css" />
	<script src="js/jquery.js"></script>
	<script src="js/jquery.mobile-1.2.0.min.js"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<script type ="text/javascript" src="http://www.geocodezip.com/scripts/v3_epoly.js"></script>
	<script type="text/javascript">
  
  var map;
  var directionDisplay;
  var directionsService;
  var stepDisplay;
  var markerArray = [];
  var position;
  var marker = null;
  var polyline = null;
  var poly2 = null;
  var speed = 0.000005, wait = 1;
  var infowindow = null;
  
    var myPano;   
    var panoClient;
    var nextPanoId;
  var timerHandle = null;
	
	
function createMarker(latlng, label, html) {
// alert("createMarker("+latlng+","+label+","+html+","+color+")");
    var contentString = '<b>'+label+'</b><br>'+html;
    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title: label,
        zIndex: Math.round(latlng.lat()*-100000)<<5
        });
        marker.myname = label;
        // gmarkers.push(marker);

    google.maps.event.addListener(marker, 'click', function() {
        infowindow.setContent(contentString); 
        infowindow.open(map,marker);
        });
    return marker;
}


function initialize() {
	
  infowindow = new google.maps.InfoWindow(
    { 
      size: new google.maps.Size(150,50)
    });
    // Instantiate a directions service.
    directionsService = new google.maps.DirectionsService();
    
    // Create a map and center it on Manhattan.
    var myOptions = {
      zoom: 13,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

    address = 'new york'
    geocoder = new google.maps.Geocoder();
	geocoder.geocode( { 'address': address}, function(results, status) {
       map.setCenter(results[0].geometry.location);
	});
    
    // Create a renderer for directions and bind it to the map.
    var rendererOptions = {
      map: map
    }
    directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
    
    // Instantiate an info window to hold step text.
    stepDisplay = new google.maps.InfoWindow();

    polyline = new google.maps.Polyline({
	path: [],
	strokeColor: '#FF0000',
	strokeWeight: 3
    });
    poly2 = new google.maps.Polyline({
	path: [],
	strokeColor: '#FF0000',
	strokeWeight: 3
    });
  }

  
  
	var steps = []

	function calcRoute(){

if (timerHandle) { clearTimeout(timerHandle); }
if (marker) { marker.setMap(null);}
polyline.setMap(null);
poly2.setMap(null);
directionsDisplay.setMap(null);
    polyline = new google.maps.Polyline({
	path: [],
	strokeColor: '#FF0000',
	strokeWeight: 3
    });
    poly2 = new google.maps.Polyline({
	path: [],
	strokeColor: '#FF0000',
	strokeWeight: 3
    });
    // Create a renderer for directions and bind it to the map.
    var rendererOptions = {
      map: map
    }
directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);

	    var start = document.getElementById("start").value;
	    var end = document.getElementById("end").value;
		var travelMode = google.maps.DirectionsTravelMode.DRIVING

	    var request = {
	        origin: start,
	        destination: end,
	        travelMode: travelMode
	    };

		// Route the directions and pass the response to a
		// function to create markers for each step.
  directionsService.route(request, function(response, status) {
    if (status == google.maps.DirectionsStatus.OK){
	directionsDisplay.setDirections(response);

        var bounds = new google.maps.LatLngBounds();
        var route = response.routes[0];
        startLocation = new Object();
        endLocation = new Object();

        // For each route, display summary information.
	var path = response.routes[0].overview_path;
	var legs = response.routes[0].legs;
        for (i=0;i<legs.length;i++) {
          if (i == 0) { 
            startLocation.latlng = legs[i].start_location;
            startLocation.address = legs[i].start_address;
            // marker = google.maps.Marker({map:map,position: startLocation.latlng});
            marker = createMarker(legs[i].start_location,"start",legs[i].start_address,"green");
          }
          endLocation.latlng = legs[i].end_location;
          endLocation.address = legs[i].end_address;
          var steps = legs[i].steps;
          for (j=0;j<steps.length;j++) {
            var nextSegment = steps[j].path;
            for (k=0;k<nextSegment.length;k++) {
              polyline.getPath().push(nextSegment[k]);
              bounds.extend(nextSegment[k]);



            }
          }
        }

        polyline.setMap(map);
        map.fitBounds(bounds);
//        createMarker(endLocation.latlng,"end",endLocation.address,"red");
	map.setZoom(18);
	startAnimation();
    }                                                    
 });
}
  

  
      var step = 50; // 5; // metres
      var tick = 100; // milliseconds
      var eol;
      var k=0;
      var stepnum=0;
      var speed = "";
      var lastVertex = 1;


//=============== animation functions ======================
      function updatePoly(d) {
        // Spawn a new polyline every 20 vertices, because updating a 100-vertex poly is too slow
        if (poly2.getPath().getLength() > 20) {
          poly2=new google.maps.Polyline([polyline.getPath().getAt(lastVertex-1)]);
        }

        if (polyline.GetIndexAtDistance(d) < lastVertex+2) {
           if (poly2.getPath().getLength()>1) {
             poly2.getPath().removeAt(poly2.getPath().getLength()-1)
           }
           poly2.getPath().insertAt(poly2.getPath().getLength(),polyline.GetPointAtDistance(d));
        } else {
          poly2.getPath().insertAt(poly2.getPath().getLength(),endLocation.latlng);
        }
      }


      function animate(d) {
        if (d>eol) {
          map.panTo(endLocation.latlng);
          marker.setPosition(endLocation.latlng);
          return;
        }
        var p = polyline.GetPointAtDistance(d);
        map.panTo(p);
        marker.setPosition(p);
        updatePoly(d);
        timerHandle = setTimeout("animate("+(d+step)+")", tick);
      }


function startAnimation() {
        eol=polyline.Distance();
        map.setCenter(polyline.getPath().getAt(0));
        poly2 = new google.maps.Polyline({path: [polyline.getPath().getAt(0)], strokeColor:"#0000FF", strokeWeight:10});
        setTimeout("animate(50)",2000);  // Allow time for the initial map display
}


//=============== ~animation funcitons =====================
function lineDistance( point1, point2 )
{
  var xs = 0;
  var ys = 0;
 
  xs = point2.x - point1.x;
  xs = xs * xs;
 
  ys = point2.y - point1.y;
  ys = ys * ys;
 
  return Math.sqrt( xs + ys );
}

jQuery(document).ready( function($) {
		$('#toolsClose').live('click', function(){
				$('#tools').toggle();
			});

		$("#triggerButton").live('click', function(){
			console.log('clicked');

			var starting = $("#start").val(),
				destination = $("#end").val();
			$.ajax({
			  url: "server.php",
			  type: "GET",
			  dataType: 'jsonp',
				jsonpCallback: 'cramming',
			  data: {
					start: starting,
					end: destination
				},
			  success: function(response){
				console.log( response );
				$("#distance").val(response.routes[0].legs[0].distance.text);
				$("#duration").val(response.routes[0].legs[0].duration.text);
				$("#start").val(response.routes[0].legs[0].start_address);
				$("#end").val(response.routes[0].legs[0].end_address);
				calcRoute();
			  },
			  error: function( a, b ){
					console.log( a );
					console.log( b );
			}
			});
		/* var geocoder = new google.maps.Geocoder();
		var address1 = $("#start").val();
		var address2 = $("#end").val();
			geocoder.geocode( { 'address': address1}, function(results, status) {
			  if (status == google.maps.GeocoderStatus.OK)
			  {
				  // do something with the geocoded result
				  //
				  console.log(results);
				
				var i = 0;
				var location = results[0].geometry.location;
									
				for( var nodes in location){
					i++;
					if( i == 1 ){
						$('#lat1').val( location[nodes] );
					}else if( i == 2 ){
						$('#lon1').val( location[nodes] );
					}else{
						break;
					}
				}
			  } else {
				alert('Invalid Address!');
			  }
			});
			geocoder.geocode( { 'address': address2}, function(results, status) {
			  if (status == google.maps.GeocoderStatus.OK)
			  {
				  // do something with the geocoded result
				  //
				  console.log(results);
				
				var i = 0;
				var location = results[0].geometry.location;
									
				for( var nodes in location){
					i++;
					if( i == 1 ){
						$('#lat2').val( location[nodes] );
					}else if( i == 2 ){
						$('#lon2').val( location[nodes] );
					}else{
						break;
					}
				}
			  } else {
				alert('Invalid Address!');
			  }
			});*/
			
		});
});
</script>

</head> 

<body onload="initialize()" id="body-paint"> 
	<div class="ui-header ui-bar-c ui-paint" data-role="header">
		<a href="index.html" data-transition="slidefade"><i class="icon-home"></i></a>
		<h1>Take Me Map Locator</h1>
		<a href="index.html" data-transition="slidefade"><i class="icon-remove"></i></a>
	</div>
	<div class="middle-canvass" data-role="content">
	<button id="toolsClose" data-role="button">Toggle</button>
<div id="tools">
	
	Origin:
	<input type="text" name="start" id="start" value="" placeholder="Enter Origin Address"/>
	Destination:
	<input type="text" name="end" id="end" value="" placeholder="Enter Destination Address" />
	<input type="submit" id="triggerButton" value="submit"/>
	<input type="hidden" id="lat1" value="" />
	<input type="hidden" id="lon1" value="" />
	<input type="hidden" id="lat2" value="" />
	<input type="hidden" id="lon2" value="" />
	Distance: <input type="text" disabled="disabled" id="distance" value="" />
	Duration: <input type="text" disabled="disabled" id="duration" value="" />
	<input type="hidden" id="lon2" value="" />
</div>

	<div id="map_canvas" style="width:100%;height:100%;"></div>
	</div>

</body>

</html>