<?php
include '../db/connect.php';

// Geocoding helper using OpenStreetMap Nominatim
function geocodeAddress($address) {
    $encoded = urlencode($address);
    $url = "https://nominatim.openstreetmap.org/search?q=$encoded&format=json&limit=1";

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: CollegeEventsApp/1.0"
        ]
    ];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    $data = json_decode($response, true);
    if (!empty($data) && isset($data[0]["lat"], $data[0]["lon"])) {
        return [(float)$data[0]["lat"], (float)$data[0]["lon"]];
    }
    return [28.6024, -81.2001]; // fallback: center of UCF
}

// Load the UCF event feed
$feed_url = 'https://events.ucf.edu/feed.xml';
$xml = @simplexml_load_file($feed_url);

if (!$xml || !isset($xml->event)) {
    exit("❌ Failed to load or parse feed properly.");
}

foreach ($xml->event as $event) {
    $title = trim((string) $event->title);
    $desc = trim((string) $event->description);
    $location_name = trim((string) $event->location);
    $contact_email = trim((string) $event->contact_email);
    $start_date = date('Y-m-d', strtotime((string) $event->start_date));
    $start_time = date('H:i:s', strtotime((string) $event->start_date));
    $end_time = date('H:i:s', strtotime((string) $event->end_date));

    // Check for duplicate event (same name + date)
    $dupCheck = $conn->prepare("SELECT event_id FROM events WHERE name = ? AND event_date = ?");
    $dupCheck->bind_param("ss", $title, $start_date);
    $dupCheck->execute();
    $dupCheck->store_result();
    if ($dupCheck->num_rows > 0) {
        $dupCheck->close();
        continue; // Skip duplicate
    }
    $dupCheck->close();

    // Geocode location name
    [$lat, $lng] = geocodeAddress($location_name);
    sleep(1); // Be polite to the API

    // Insert location
    $locStmt = $conn->prepare("INSERT INTO locations (name, address, latitude, longitude) VALUES (?, ?, ?, ?)");
    $locStmt->bind_param("ssdd", $location_name, $location_name, $lat, $lng); // using location_name for address too
    $locStmt->execute();
    $location_id = $locStmt->insert_id;
    $locStmt->close();

    // Defaults
    $category = "UCF Feed";
    $event_type = "public";
    $university_id = 1;
    $contact_phone = "";
    $approved = 1;
    $status = "approved";

    // Insert event
    $stmt = $conn->prepare("INSERT INTO events 
        (name, description, category, event_type, university_id, rso_id, event_date, start_time, end_time, location_id, contact_email, contact_phone, approved, status)
        VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissssssis", $title, $desc, $category, $event_type, $university_id,
        $start_date, $start_time, $end_time, $location_id, $contact_email, $contact_phone, $approved, $status);
    $stmt->execute();
    $stmt->close();
}

echo "✅ UCF events imported with geocoded locations!";
?>