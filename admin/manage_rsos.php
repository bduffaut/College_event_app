
<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "superadmin") {
    echo "<p class='error'>Access denied.</p>";
    exit;
}

$admin_id = $_SESSION["uid"];
$rsos = $conn->query("
SELECT RSOs.*, 
(SELECT COUNT(*) FROM RSO_Members WHERE RSO_Members.rso_id = RSOs.rso_id) AS member_count,
(SELECT COUNT(*) FROM Events WHERE Events.rso_id = RSOs.rso_id) AS event_count
FROM RSOs
WHERE admin_uid = $admin_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage RSOs</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="container">

<h2>My RSOs</h2>

<?php if ($rsos && $rsos->num_rows > 0): ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Status</th>
            <th># Members</th>
            <th># Events</th>
            <th>Manage</th>
        </tr>
        <?php while ($rso = $rsos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($rso['name']) ?></td>
                <td><?= htmlspecialchars($rso['description']) ?></td>
                <td><?= htmlspecialchars($rso['status']) ?></td>
                <td><?= $rso['member_count'] ?></td>
                <td><?= $rso['event_count'] ?></td>
                <td>
                    <a href="rso_details.php?id=<?= $rso['rso_id'] ?>" class="btn">🔍 Manage</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>You haven't created any RSOs yet.</p>
<?php endif; ?>

<br>
<a href="../dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>

</div>
</body>
</html>
