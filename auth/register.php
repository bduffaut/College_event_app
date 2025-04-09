<?php
include '../db/connect.php';

// Fetch universities for dropdown
$universities = $conn->query("SELECT university_id, name FROM universities");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $university_id = $_POST["university_id"];

    $stmt = $conn->prepare("INSERT INTO Users (name, email, password, role, university_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $email, $password, $role, $university_id);

    if ($stmt->execute()) {
        echo "<p class='success'>✅ Registration successful. <a href='login.php'>Login here</a></p>";
    } else {
        echo "<p class='error'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container" style="max-width: 600px;">
    <h2>Create an Account</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Role:</label>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select>

        <label>Select Your University:</label>
        <select name="university_id" required>
            <option value="">-- Choose from list --</option>
            <?php while ($row = $universities->fetch_assoc()): ?>
                <option value="<?= $row['university_id'] ?>">
                    <?= htmlspecialchars($row['name']) ?> — ID: <?= $row['university_id'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn">Register</button>
    </form>

    <br>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
