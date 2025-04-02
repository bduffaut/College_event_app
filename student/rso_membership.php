

<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION["role"] !== "student" ) {
    echo "Access denied.";
    exit;
}

$uid = $_SESSION["uid"];

// Get user's university ID
$userQuery = $conn->query("SELECT university_id FROM Users WHERE UID = $uid");
$user = $userQuery->fetch_assoc();
$university_id = $user["university_id"];

// Handle Join
if (isset($_GET['join'])) {
    $rso_id = intval($_GET['join']);

    $rsoCheck = $conn->query("SELECT university_id FROM rsos WHERE rso_id = $rso_id");
    if ($rsoCheck && $rsoCheck->num_rows > 0) {
        $rso_univ = $rsoCheck->fetch_assoc()["university_id"];
        if ($rso_univ == $university_id) {
            $conn->query("INSERT IGNORE INTO rso_members (rso_id, user_id) VALUES ($rso_id, $uid)");
            echo "<p class='success'>✅ Joined RSO ID $rso_id!</p>";
        } else {
            echo "<p class='error'>❌ You cannot join RSOs outside your university.</p>";
        }
    }
}

// Handle Leave
if (isset($_GET['leave'])) {
    $rso_id = intval($_GET['leave']);
    $conn->query("DELETE FROM rso_members WHERE rso_id = $rso_id AND user_id = $uid");
    echo "<p class='warning'>🚪 Left RSO ID $rso_id.</p>";
}

// Get all RSOs at the student's university
$rsos = $conn->query("
    SELECT rsos.rso_id, rsos.name, rsos.description,
        (SELECT COUNT(*) FROM rso_members WHERE rso_id = rsos.rso_id AND user_id = $uid) AS is_member
    FROM rsos
    WHERE university_id = $university_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RSO Memberships</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container">

<h2>RSO Memberships</h2>

<?php if ($rsos->num_rows > 0): ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>RSO Name</th>
            <th>Actions</th>
        </tr>
        <?php while ($rso = $rsos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($rso['name']) ?></td>
                <td>
                    <?php if ($rso['is_member']): ?>
                        <a href="?leave=<?= $rso['rso_id'] ?>" class="btn btn-danger" onclick="return confirm('Leave this RSO?')">🚪 Leave</a>
                    <?php else: ?>
                        <a href="?join=<?= $rso['rso_id'] ?>" class="btn" onclick="return confirm('Join this RSO?')">➕ Join</a>
                    <?php endif; ?>
                    <a href="rso_details.php?id=<?= $rso['rso_id'] ?>" class="btn btn-secondary">📄 More Details</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No RSOs available at your university.</p>
<?php endif; ?>

</div>
</body>
</html>
