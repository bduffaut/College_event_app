

<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION['role'] !== 'superadmin') {
    echo "<p class='error'>Access denied.</p>";
    exit;
}

$result = $conn->query("SELECT * FROM universities");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Universities</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container">
<h2>📚 All Universities</h2>

<?php if ($result->num_rows > 0): ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>Name</th>
            <th>Location</th>
            <th>Description</th>
            <th>Student Count</th>
            <th>Picture</th>
        </tr>
        <?php while ($uni = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($uni['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($uni['location'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($uni['description'] ?? '')) ?></td>
                <td><?= (int)($uni['student_count'] ?? 0) ?></td>
                <td>
                    <?php if (!empty($uni['picture_url'])): ?>
                        <img src="<?= htmlspecialchars($uni['picture_url']) ?>" alt="University Image" style="max-width:100px; border-radius:6px; margin-top:5px;">
                    <?php else: ?>
                        <em>N/A</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No universities found.</p>
<?php endif; ?>

<p>
    <a href="../dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
</p>

</div>
</body>
</html>
