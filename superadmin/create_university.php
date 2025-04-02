

<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';

if ($_SESSION['role'] !== 'superadmin') {
    echo "<p class='error'>Access denied.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $student_count = intval($_POST['student_count']);
    $picture_url = trim($_POST['picture_url']); // Optional

    $stmt = $conn->prepare("INSERT INTO universities (name, location, description, student_count, picture_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $name, $location, $description, $student_count, $picture_url);
    $stmt->execute();
    $stmt->close();

    echo "<p class='success'>✅ University created!</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create University</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<div class="container">
<h2>Create University</h2>

<form method="POST">
    <label>Name:
        <input type="text" name="name" required>
    </label><br>

    <label>Location:
        <input type="text" name="location" required>
    </label><br>

    <label>Description:<br>
        <textarea name="description" rows="4" required></textarea>
    </label><br>

    <label>Student Count:
        <input type="number" name="student_count" required>
    </label><br>

    <label>Picture URL (optional):
        <input type="url" name="picture_url">
    </label><br>

    <button type="submit" class="btn">Create</button>
</form>

<p>
    <a href="../dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
</p>

</div>
</body>
</html>
