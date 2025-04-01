<?php
include '../auth/session.php';
include '../db/connect.php';

if ($_SESSION["role"] !== "admin") {
    echo "Access denied.";
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

<h2>My RSOs</h2>

<?php if ($rsos->num_rows > 0): ?>
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
                    <a href="rso_details.php?id=<?= $rso['rso_id'] ?>">🔍 Manage</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>You haven't created any RSOs.</p>
<?php endif; ?>

<p><a href="../dashboard.php">⬅️ Back to Dashboard</a></p>
