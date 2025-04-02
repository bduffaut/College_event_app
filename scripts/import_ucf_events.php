<?php
include '../db/connect.php';
include '../config/load_env.php';
loadEnv();
$apikey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? null;

if (!$apikey) {
    exit("❌ Google Maps API key not found. Check .env setup.");
} else {
    echo "<p style='color:green;'>✅ Google Maps API key loaded: " . htmlspecialchars(substr($apikey, 0, 10)) . "****</p>";
}

// Places API helper
function geocodeWithPlaces($locationName, $apikey) {
    $query = urlencode("UCF " . $locationName); // Prepend UCF for better accuracy
    $url = "https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=$query&inputtype=textquery&fields=geometry&key=$apikey";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data['candidates']) && isset($data['candidates'][0]['geometry']['location'])) {
        $loc = $data['candidates'][0]['geometry']['location'];
        return [(float)$loc['lat'], (float)$loc['lng']];
    } else {
        echo "<p style='color:orange;'>⚠️ Places API could not find location: $locationName. Defaulting to UCF center.</p>";
        return [28.6024, -81.2001]; // fallback to UCF
    }
}

// Load UCF feed
$feed_url = 'https://events.ucf.edu/feed.xml';
$xml = @simplexml_load_file($feed_url);
if (!$xml || !isset($xml->event)) {
    exit("❌ Failed to load or parse feed.");
}

foreach ($xml->event as $event) {
    $title = trim((string) $event->title);
    $desc = trim((string) $event->description);
    $location_name = trim((string) $event->location);
    $contact_email = trim((string) $event->contact_email);
    $start_date = date('Y-m-d', strtotime((string) $event->start_date));
    $start_time = date('H:i:s', strtotime((string) $event->start_date));
    $end_time = date('H:i:s', strtotime((string) $event->end_date));

    // Check for duplicate
    $dupCheck = $conn->prepare("SELECT event_id FROM events WHERE name = ? AND event_date = ?");
    $dupCheck->bind_param("ss", $title, $start_date);
    $dupCheck->execute();
    $dupCheck->store_result();
    if ($dupCheck->num_rows > 0) {
        $dupCheck->close();
        continue;
    }
    $dupCheck->close();

    $location_id = null;

    if (!empty($location_name)) {
        [$lat, $lng] = geocodeWithPlaces($location_name, $apikey);
        sleep(1); // Respect API usage limits

        $locStmt = $conn->prepare("INSERT INTO locations (name, address, latitude, longitude) VALUES (?, ?, ?, ?)");
        $locStmt->bind_param("ssdd", $location_name, $location_name, $lat, $lng);
        if ($locStmt->execute()) {
            $location_id = $locStmt->insert_id;
        }
        $locStmt->close();
    }

    $category = "UCF Feed";
    $event_type = "public";
    $university_id = 1;
    $contact_phone = "";
    $approved = 1;
    $status = "approved";

    $stmt = $conn->prepare("INSERT INTO events 
        (name, description, category, event_type, university_id, rso_id, event_date, start_time, end_time, location_id, contact_email, contact_phone, approved, status)
        VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissssssis", $title, $desc, $category, $event_type, $university_id,
        $start_date, $start_time, $end_time, $location_id, $contact_email, $contact_phone, $approved, $status);
    $stmt->execute();
    $stmt->close();
}

echo "<p style='color:green;'>✅ Events imported using Google <strong>Places</strong> API geocoding!</p>";
?>
