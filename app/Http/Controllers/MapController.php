<?php

namespace App\Http\Controllers;

use App\Library\Polyline;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $keralaCities = [
            ['name' => 'Thiruvananthapuram', 'lat' => 8.5241, 'long' => 76.9366],
            ['name' => 'Kochi', 'lat' => 9.9312, 'long' => 76.2673],
            ['name' => 'Kozhikode', 'lat' => 11.2588, 'long' => 75.7804],
            ['name' => 'Kollam', 'lat' => 8.8932, 'long' => 76.6141],
            ['name' => 'Thrissur', 'lat' => 10.5276, 'long' => 76.2144],
            ['name' => 'Alappuzha', 'lat' => 9.4981, 'long' => 76.3388],
            ['name' => 'Kannur', 'lat' => 11.8745, 'long' => 75.3704],
            ['name' => 'Kottayam', 'lat' => 9.5916, 'long' => 76.5222],
            ['name' => 'Palakkad', 'lat' => 10.7867, 'long' => 76.6548],
            ['name' => 'Malappuram', 'lat' => 11.0720, 'long' => 76.0741],
            ['name' => 'Tirur', 'lat' => 10.9167, 'long' => 75.9245],
            ['name' => 'Pathanamthitta', 'lat' => 9.2642, 'long' => 76.7871],
            ['name' => 'Idukki', 'lat' => 9.8498, 'long' => 76.9683],
            ['name' => 'Wayanad', 'lat' => 11.6850, 'long' => 76.1325],
        ];

        $apiEndpoint = 'https://maps.googleapis.com/maps/api/directions/json';
        $origin = $keralaCities[1];
        $destination = $keralaCities[2];
        $apiKey = env('MAP_API_KEY');

        // Create a Guzzle client
        $client = new Client();

        // Make the API request
        $response = $client->get($apiEndpoint, [
            'query' => [
                'origin' => $origin['lat'] ."," . $origin['long'],
                'destination' => $destination['lat'] ."," . $destination['long'],
                'key' => $apiKey,
                'mode' => "DRIVING"
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        // dd( $data);
        $overview_polyline = $data['routes'][0]['overview_polyline']['points'];
        // dd( $overview_polyline);
        $waypoints = Polyline::decode2($overview_polyline);

       

        $nearestCities = [];

        for ($j = 0; $j < count($waypoints); $j += 20) {
            // Loop through cities
            foreach ($keralaCities as $city) {
                $cityLocation = [
                    'lat' => $city['lat'],
                    'lng' => $city['long'],
                ];

                $distance = $this->computeDistance(
                    ['lat' => $waypoints[$j][0], 'lng' => $waypoints[$j][1]],
                    $cityLocation
                );
                if ($distance < 20000) {
                    $cityIndex = array_search($city['name'], array_column($nearestCities, 'name'));
                    if ($cityIndex === false) {
                        $nearestCities[] = $city;
                    }
                }
            }
        }

        $data = [
            'origin' => $origin,
            'destination' =>  $destination,
            'waypoints' => $nearestCities
        ];

        return View::make('map', compact('data'));
     
    }

    private function computeDistance($point1, $point2)
    {
        $lat1 = deg2rad($point1['lat']);
        $lon1 = deg2rad($point1['lng']);
        $lat2 = deg2rad($point2['lat']);
        $lon2 = deg2rad($point2['lng']);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $radius = 6371; // Earth radius in kilometers. You can change this value if needed.

        $distance = $radius * $c;

        return $distance * 1000; // Convert to meters
    }
}
