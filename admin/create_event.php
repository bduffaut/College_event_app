<?php
include '../auth/session.php';
include '../assets/navbar.php';


include '../db/connect.php';
include '../config/load_env.php';
loadEnv();

if ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "superadmin") {
    echo "Access denied.";
    exit;
}

$admin_id = $_SESSION["uid"];

// Get admin's university
$univResult = $conn->query("SELECT university_id FROM Users WHERE UID = $admin_id");
$university_id = $univResult->fetch_assoc()["university_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $desc = $_POST["description"];
    $category = $_POST["category"];
    $event_type = $_POST["event_type"];
    $event_date = $_POST["event_date"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $contact_email = $_POST["contact_email"];
    $contact_phone = $_POST["contact_phone"];
    $rso_id = $_POST["rso_id"] ?: null;

    // Location info
    $location_name = $_POST["location_name"];
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];

    // Insert location
    $locStmt = $conn->prepare("INSERT INTO locations (name, latitude, longitude) VALUES (?, ?, ?)");
    $locStmt->bind_param("sdd", $location_name, $latitude, $longitude);
    $locStmt->execute();
    $location_id = $locStmt->insert_id;
    $locStmt->close();

    $approved = ($event_type === 'public' && !$rso_id) ? 0 : 1;

    // Insert event
    $stmt = $conn->prepare("INSERT INTO events 
        (name, description, category, event_type, university_id, rso_id, event_date, start_time, end_time, location_id, contact_email, contact_phone, approved, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssiiisssssi", $name, $desc, $category, $event_type, $university_id, $rso_id,
        $event_date, $start_time, $end_time, $location_id, $contact_email, $contact_phone, $approved);

    if ($stmt->execute()) {
        echo "<p class='success'>✅ Event created successfully!</p>";
    } else {
        echo "<p class='error'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Get RSOs this admin owns
$rsos = $conn->query("SELECT rso_id, name FROM rsos WHERE admin_uid = $admin_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="container">
    <h2>Create a New Event</h2>

    <form method="POST">
        <label>Event Name:</label>
        <input type="text" name="name" required>

        <label>Description:</label>
        <textarea name="description" required></textarea>

        <label>Category:</label>
        <select name="category" required>
            <option value="social">Social</option>
            <option value="fundraising">Fundraising</option>
            <option value="tech">Tech</option>
            <option value="other">Other</option>
        </select>

        <label>Event Type:</label>
        <select name="event_type" required>
            <option value="public">Public</option>
            <option value="private">Private</option>
            <option value="rso">RSO</option>
        </select>

        <label>Date:</label>
        <input type="date" name="event_date" required>

        <label>Start Time:</label>
        <input type="time" name="start_time" required>

        <label>End Time:</label>
        <input type="time" name="end_time" required>

        <h3>Pick Location on the Map</h3>
        <div id="map" style="height: 400px; margin-bottom: 10px;"></div>

        <label>Location Name:</label>
        <input type="text" name="location_name" id="location_name" required>

        <label>Latitude:</label>
        <input type="text" name="latitude" id="latitude" required readonly>

        <label>Longitude:</label>
        <input type="text" name="longitude" id="longitude" required readonly>

        <label>RSO (if applicable):</label>
        <select name="rso_id">
            <option value="">None</option>
            <?php while ($r = $rsos->fetch_assoc()): ?>
                <option value="<?= $r['rso_id'] ?>"><?= $r['name'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Contact Email:</label>
        <input type="email" name="contact_email">

        <label>Contact Phone:</label>
        <input type="text" name="contact_phone">

        <button type="submit" class="btn">Create Event</button>
    </form>

    <br>
    <a href="../dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
</div>

<!-- Leaflet.js for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

<script>
    const map = L.map('map').setView([28.6024, -81.2001], 13); // UCF center default

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data © OpenStreetMap contributors'
    }).addTo(map);

    let marker;

    map.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
</script>

</body>
</html>
