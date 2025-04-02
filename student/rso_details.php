
<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION["role"] !== "student") {
    echo "Access denied.";
    exit;
}

if (!isset($_GET['id'])) {
    echo "RSO not found.";
    exit;
}

$rso_id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM rsos WHERE rso_id = $rso_id");

if ($result->num_rows === 0) {
    echo "RSO not found.";
    exit;
}

$rso = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RSO Details</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="container">

<h2><?= htmlspecialchars($rso['name']) ?></h2>
<p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($rso['description'])) ?></p>

<a href="rso_membership.php" class="btn btn-secondary">⬅️ Back to RSO List</a>

</div>
</body>
</html>
