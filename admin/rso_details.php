
<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION["role"] !== "admin") {
    echo "Access denied.";
    exit;
}

$rso_id = $_GET['id'] ?? null;
if (!$rso_id || !is_numeric($rso_id)) {
    echo "Invalid RSO ID.";
    exit;
}

$rso_id = intval($rso_id);

// Fetch RSO details
$stmt = $conn->prepare("SELECT * FROM RSOs WHERE rso_id = ?");
$stmt->bind_param("i", $rso_id);
$stmt->execute();
$rso = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rso || $rso['admin_uid'] != $_SESSION["uid"]) {
    echo "Access denied to this RSO.";
    exit;
}

// Handle removing a member
if (isset($_GET['remove_user']) && is_numeric($_GET['remove_user'])) {
    $remove_id = intval($_GET['remove_user']);
    $conn->query("DELETE FROM RSO_Members WHERE rso_id = $rso_id AND user_id = $remove_id");
    echo "<p class='success'>✅ Member removed.</p>";
}

// Handle adding a new member
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $userRes = $conn->query("SELECT UID FROM Users WHERE email = '$email'");
    if ($userRes && $userRes->num_rows > 0) {
        $uid = $userRes->fetch_assoc()['UID'];
        $conn->query("INSERT IGNORE INTO RSO_Members (rso_id, user_id) VALUES ($rso_id, $uid)");
        echo "<p class='success'>✅ User added to the RSO.</p>";
    } else {
        echo "<p class='error'>❌ No user found with that email.</p>";
    }
}

// Fetch members
$members = $conn->query("
    SELECT Users.UID, Users.name, Users.email
    FROM RSO_Members
    JOIN Users ON RSO_Members.user_id = Users.UID
    WHERE RSO_Members.rso_id = $rso_id
");

// Fetch events
$events = $conn->query("
    SELECT * FROM Events
    WHERE rso_id = $rso_id
    ORDER BY event_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage RSO</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container">


<h2>Manage RSO: <?= htmlspecialchars($rso['name']) ?></h2>
<p><strong>Description:</strong> <?= htmlspecialchars($rso['description']) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($rso['status']) ?></p>

<hr>

<h3>Events by this RSO</h3>
<?php if ($events->num_rows > 0): ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Category</th>
            <th>View</th>
        </tr>
        <?php while ($event = $events->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($event['name']) ?></td>
                <td><?= $event['event_date'] ?></td>
                <td><?= $event['start_time'] ?> - <?= $event['end_time'] ?></td>
                <td><?= ucfirst($event['category']) ?></td>
                <td>
                    <a href="../student/event_details.php?id=<?= $event['event_id'] ?>" class="btn btn-secondary">🔍 View Details</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No events found.</p>
<?php endif; ?>

<hr>

<h3>Members (<?= $members->num_rows ?>)</h3>
<?php if ($members->num_rows > 0): ?>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Remove</th>
        </tr>
        <?php while ($m = $members->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td>
                    <a href="?id=<?= $rso_id ?>&remove_user=<?= $m['UID'] ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Remove this member?')">❌ Remove</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No members in this RSO.</p>
<?php endif; ?>

<h4>Add Member by Email</h4>
<form method="POST">
    <input type="email" name="email" placeholder="user@example.com" required>
    <button type="submit" class="btn">➕ Add Member</button>
</form>

<p><a href="manage_rsos.php" class="btn btn-secondary">⬅️ Back to My RSOs</a></p>

</div>
</body>
</html>
