

<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION["role"] !== "student" && $_SESSION["role"] !== "admin" && $_SESSION["role"] !== "superadmin") {
    echo "Access denied.";
    exit;
}

$uid = $_SESSION["uid"];

// Get user's university ID
$userQuery = $conn->query("SELECT university_id FROM Users WHERE UID = $uid");
$user = $userQuery->fetch_assoc();
$university_id = $user["university_id"];

// Get RSO memberships
$rsoResult = $conn->query("SELECT rso_id FROM RSO_Members WHERE user_id = $uid");
$rso_ids = [];
while ($row = $rsoResult->fetch_assoc()) {
    $rso_ids[] = $row["rso_id"];
}
$rso_ids_string = implode(",", $rso_ids);

// Build the event query with JOIN on Locations
$eventQuery = "
    SELECT Events.*, Locations.name AS location_name, Locations.address AS location_address
    FROM Events
    LEFT JOIN Locations ON Events.location_id = Locations.location_id
    WHERE 
        (event_type = 'public' AND approved = 1)
        OR (event_type = 'private' AND university_id = $university_id)
        " . (!empty($rso_ids) ? "OR (event_type = 'rso' AND rso_id IN ($rso_ids_string))" : "") . "
    ORDER BY event_date, start_time
";

$events = $conn->query($eventQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Events</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="container">
<h2>Available Events</h2>

<?php if ($events && $events->num_rows > 0): ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Event Name</th>
            <th>Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Category</th>
            <th>Location</th>
            <th>Action</th>
        </tr>
        <?php while ($event = $events->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($event['name']) ?></td>
                <td><?= ucfirst($event['event_type']) ?></td>
                <td><?= $event['event_date'] ?></td>
                <td><?= $event['start_time'] ?> - <?= $event['end_time'] ?></td>
                <td><?= ucfirst($event['category']) ?></td>
                <td><?= htmlspecialchars($event['location_name'] ?? '') ?></td>
                <td>
                    <a href="event_details.php?id=<?= $event['event_id'] ?>" class="btn">🔍 View Details</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p>
        <a href="../dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
    </p>

<?php else: ?>
    <p>No events available.</p>
<?php endif; ?>

</div>
</body>
</html>
