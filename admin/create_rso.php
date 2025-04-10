<?php
include '../auth/session.php';
include '../db/connect.php';

if ($_SESSION["role"] !== "admin" && $_SESSION["role"] !== "superadmin") {
    echo "<p class='error'>Access denied.</p>";
    exit;
}

$uid = $_SESSION["uid"];

// Get the admin's university
$univQuery = $conn->query("SELECT university_id FROM Users WHERE UID = $uid");
$university_id = $univQuery->fetch_assoc()["university_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    // Check if the RSO name already exists
    $checkStmt = $conn->prepare("SELECT rso_id FROM rsos WHERE name = ?");
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $errorMessage = "❌ An RSO with the name '$name' already exists. Please choose a different name.";
    } else {
        // Insert RSO
        $stmt = $conn->prepare("INSERT INTO rsos (name, description, university_id, admin_uid) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $name, $description, $university_id, $uid);

        if ($stmt->execute()) {
            $rso_id = $stmt->insert_id;

            // Automatically add admin as first member
            $memberStmt = $conn->prepare("INSERT INTO rso_members (rso_id, user_id) VALUES (?, ?)");
            $memberStmt->bind_param("ii", $rso_id, $uid);
            $memberStmt->execute();
            $memberStmt->close();

            $successMessage = "✅ RSO '$name' created successfully and you were added as the first member!";
        } else {
            $errorMessage = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create RSO</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

<?php include '../assets/navbar.php'; ?>

<div class="container">
    <h2>Create a New RSO</h2>

    <?php if (isset($successMessage)) echo "<p class='success'>$successMessage</p>"; ?>
    <?php if (isset($errorMessage)) echo "<p class='error'>$errorMessage</p>"; ?>

    <form method="POST">
        <label>RSO Name:</label>
        <input type="text" name="name" required>

        <label>Description:</label>
        <textarea name="description" rows="5" required></textarea>

        <button type="submit" class="btn">Create RSO</button>
    </form>

    <br>
    <a href="../dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
</div>

</body>
</html>
