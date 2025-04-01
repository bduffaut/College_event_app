<?php
include '../db/connect.php';

$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    echo "❌ No event specified.";
    exit;
}

$stmt = $conn->prepare("
    SELECT Events.*, Locations.name AS location_name, Locations.latitude, Locations.longitude
    FROM Events
    LEFT JOIN Locations ON Events.location_id = Locations.location_id
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "❌ Event not found.";
    exit;
}
?>

<h2><?= htmlspecialchars($event['name']) ?></h2>
<p><strong>Type:</strong> <?= htmlspecialchars(ucfirst($event['event_type'])) ?></p>
<p><strong>Date:</strong> <?= $event['event_date'] ?></p>
<p><strong>Time:</strong> <?= $event['start_time'] ?> - <?= $event['end_time'] ?></p>
<p><strong>Category:</strong> <?= htmlspecialchars($event['category']) ?></p>
<p><strong>Description:</strong><br> <?= $event['description'] ?></p>
<p><strong>Location:</strong> <?= htmlspecialchars($event['location_name']) ?></p>

<?php if (!empty($event['latitude']) && !empty($event['longitude']) && $event['latitude'] != 28.6024 && $event['longitude'] != -81.2001): ?>
    <h3>Event Location</h3>
    <div id="map" style="height: 400px;"></div>

    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    <script>
        const map = L.map('map').setView([<?= $event['latitude'] ?>, <?= $event['longitude'] ?>], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data © OpenStreetMap contributors'
        }).addTo(map);
        L.marker([<?= $event['latitude'] ?>, <?= $event['longitude'] ?>]).addTo(map);
    </script>
<?php else: ?>
    <p><em>No map available for this event.</em></p>
<?php endif; ?>

