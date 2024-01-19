<!-- resources/views/maps.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Google Maps Directions</title>
    <script src="https://maps.googleapis.com/maps/api/js?key={{env('MAP_API_KEY')}}&libraries=places"></script>
</head>
<body style="margin: 0; padding: 0;">
    <div id="map" style="width: 100%; height: 100vh;"></div>

    <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 10.0, lng: 76.0},
                zoom: 8
            });

            var directionsService = new google.maps.DirectionsService();
            var directionsRenderer = new google.maps.DirectionsRenderer({map: map});

            var data = @json($data);

            var waypoints = data.waypoints.map(function(waypoint) {
                return {
                    location: new google.maps.LatLng(waypoint.lat, waypoint.long),
                    stopover: true
                };
            });

            var request = {
                origin: new google.maps.LatLng(data.origin.lat, data.origin.long),
                destination: new google.maps.LatLng(data.destination.lat, data.destination.long),
                waypoints: waypoints,
                travelMode: google.maps.TravelMode.DRIVING
            };

            directionsService.route(request, function(response, status) {
                if (status == 'OK') {
                    directionsRenderer.setDirections(response);
                } else {
                    alert('Directions request failed due to ' + status);
                }
            });
        }

        google.maps.event.addDomListener(window, 'load', initMap);
    </script>
</body>
</html>
