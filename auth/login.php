<?php
session_start();
include '../db/connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT UID, password, role FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($uid, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["uid"] = $uid;
            $_SESSION["role"] = $role;
            header("Location: ../dashboard.php");
            exit;
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ User not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | College Events</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        body {
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            max-width: 400px;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 20px;
            color: #1e3a8a;
        }

        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .login-box .btn {
            width: 100%;
            padding: 10px;
        }

        .login-box .error {
            color: #dc2626;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>
