<?php
include '../db/connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // secure hashing
    $role = $_POST["role"];
    $university_id = $_POST["university_id"];

    $stmt = $conn->prepare("INSERT INTO Users (name, email, password, role, university_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $email, $password, $role, $university_id);

    if ($stmt->execute()) {
        echo "Registration successful. <a href='login.php'>Login here</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<h2>Register</h2>
<form method="POST">
    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    Role:
    <select name="role" required>
        <option value="student">Student</option>
        <option value="admin">Admin</option>
        <option value="superadmin">Super Admin</option>
    </select><br>
    University ID: <input type="number" name="university_id" required><br>
    <input type="submit" value="Register">
</form>
