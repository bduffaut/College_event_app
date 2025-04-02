

<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION["role"] !== "superadmin") {
    echo "Access denied.";
    exit;
}

// ✅ Approve
if (isset($_GET['approve'])) {
    $event_id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE Events SET approved = 1, status = 'approved' WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();
    echo "<p class='success'>✅ Event ID $event_id approved successfully!</p>";
}

// ❌ Decline
if (isset($_GET['decline'])) {
    $event_id = intval($_GET['decline']);
    $stmt = $conn->prepare("UPDATE Events SET status = 'declined' WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();
    echo "<p class='warning'>❌ Event ID $event_id was declined.</p>";
}

// 🗑 Delete
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();
    echo "<p class='error'>🗑 Event ID $event_id was permanently deleted.</p>";
}

// Load events
$pendingEvents = $conn->query("
    SELECT Events.*, Locations.name AS location_name, Locations.address AS location_address
    FROM Events
    LEFT JOIN Locations ON Events.location_id = Locations.location_id
    WHERE event_type = 'public' AND approved = 0 AND (status IS NULL OR status = 'pending')
    ORDER BY event_date, start_time
");

$declinedEvents = $conn->query("
    SELECT Events.*, Locations.name AS location_name, Locations.address AS location_address
    FROM Events
    LEFT JOIN Locations ON Events.location_id = Locations.location_id
    WHERE event_type = 'public' AND status = 'declined'
    ORDER BY event_date, start_time
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Events</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="container">
<h2>Pending Public Events for Approval</h2>

<?php if ($pendingEvents->num_rows > 0): ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Category</th>
            <th>Description</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>
        <?php while ($event = $pendingEvents->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($event['name']) ?></td>
                <td><?= $event['event_date'] ?></td>
                <td><?= $event['start_time'] ?> - <?= $event['end_time'] ?></td>
                <td><?= htmlspecialchars($event['category']) ?></td>
                <td><?= htmlspecialchars($event['description']) ?></td>
                <td>
                    <?= $event['location_name'] !== null ? htmlspecialchars($event['location_name']) : 'N/A' ?><br>
                    <?= $event['location_address'] !== null ? htmlspecialchars($event['location_address']) : 'N/A' ?>
                </td>
                <td>
                    <a href="?approve=<?= $event['event_id'] ?>" class="btn" onclick="return confirm('Approve this event?')">✅ Approve</a><br>
                    <a href="?decline=<?= $event['event_id'] ?>" class="btn btn-secondary" onclick="return confirm('Decline this event?')">❌ Decline</a><br>
                    <a href="?delete=<?= $event['event_id'] ?>" class="btn btn-danger" onclick="return confirm('PERMANENTLY delete this event?')">🗑 Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No pending public events.</p>
<?php endif; ?>

<h2 style="margin-top: 40px;">Declined Public Events</h2>

<?php if ($declinedEvents->num_rows > 0): ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Category</th>
            <th>Description</th>
            <th>Location</th>
            <th>Delete</th>
        </tr>
        <?php while ($event = $declinedEvents->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($event['name']) ?></td>
                <td><?= $event['event_date'] ?></td>
                <td><?= $event['start_time'] ?> - <?= $event['end_time'] ?></td>
                <td><?= htmlspecialchars($event['category']) ?></td>
                <td><?= htmlspecialchars($event['description']) ?></td>
                <td>
                    <?= htmlspecialchars($event['location_name']) ?><br>
                    <?= htmlspecialchars($event['location_address']) ?>
                </td>
                <td>
                    <a href="?delete=<?= $event['event_id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this declined event?')">🗑 Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No declined events.</p>
<?php endif; ?>

<p style="margin-top: 30px;">
    <a href="../dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
</p>

</div>
</body>
</html>
