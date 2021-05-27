<?php
$location = '';
$lat = '';
$lng = '';

$mapType = isset($_POST['map_type']) ? $_POST['map_type'] : 'google';

if (!empty($_POST['location'])) {
    $location = trim($_POST['location']);
    $coordinates = getGeoCoordinates($mapType, trim($_POST['location']));
    $lat = $coordinates['lat'];
    $lng = $coordinates['lng'];
}

function getGeoCoordinates(string $serviceType, string $location): array
{
    switch ($serviceType) {
        case 'google':
            // Change this Google key!
            $GOOGLE_MAP_KEY = 'YOUR_API_KEY';

            $lat = '';
            $lng = '';
            if ($contents = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($location) . '&sensor=false&key=' . $GOOGLE_MAP_KEY)) {
                $json = json_decode($contents);
                if (isset($json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'})) {
                    $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                }
                if (isset($json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'})) {
                    $lng = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
                }
            }

            return [
                'lat' => $lat,
                'lng' => $lng,
            ];
        case 'open':
            $search_url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($location) . "&format=json";

            $httpOptions = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: Nominatim-Test"
                ]
            ];

            $streamContext = stream_context_create($httpOptions);
            $json = file_get_contents($search_url, false, $streamContext);
            $decoded = json_decode($json, true);
            $lat = $decoded[0]["lat"];
            $lng = $decoded[0]["lon"];

            return [
                'lat' => $lat,
                'lng' => $lng
            ];
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
</head>
<body>
<form method="post">
    <?php if (!empty($lat) && !empty($lng)): ?>
        <h2>Lat: <?php echo $lat; ?>, Lng: <?php echo $lng; ?>, </h2>
    <?php endif; ?>
    <p>
        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>">
        <small>Ex: 13B Pine Tree Rd, Nantucket, MA 02554, US</small>
    </p>
    <p>
        <select name="map_type">
            <option value="google" <?php echo ($mapType === 'google' ? 'selected' : '') ?>>Google Maps</option>
            <option value="open" <?php echo ($mapType === 'open' ? 'selected' : '') ?>>OpenStreetMap</option>
        </select>
    </p>
    <p>
        <input type="submit" value="submit">
    </p>
</form>
</body>
</html>
