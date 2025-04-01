<?php
include '../auth/session.php';
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

<h2><?= htmlspecialchars($rso['name']) ?></h2>
<p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($rso['description'])) ?></p>
<a href="rso_membership.php">🔙 Back to RSO List</a>
