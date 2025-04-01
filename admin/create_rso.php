<?php
include '../auth/session.php';
include '../db/connect.php';

if ($_SESSION["role"] !== "admin") {
    echo "Access denied.";
    exit;
}

$uid = $_SESSION["uid"];

// Get the admin's university
$univQuery = $conn->query("SELECT university_id FROM Users WHERE UID = $uid");
$university_id = $univQuery->fetch_assoc()["university_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $description = $_POST["description"];

    $stmt = $conn->prepare("INSERT INTO rsos (name, description, university_id, admin_uid) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $name, $description, $university_id, $uid);

    if ($stmt->execute()) {
        echo "<p>✅ RSO '$name' created successfully!</p>";
    } else {
        echo "<p>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<h2>Create a New RSO</h2>
<form method="POST">
    RSO Name: <input type="text" name="name" required><br>
    Description: <br><textarea name="description" rows="5" cols="50" required></textarea><br>
    <input type="submit" value="Create RSO">
</form>
