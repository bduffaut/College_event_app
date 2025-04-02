<?php include 'auth/session.php'; ?>
<?php include 'assets/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        body {
            background-color: #f2f2f2;
        }

        .dashboard {
            max-width: 960px;
            margin: 40px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .dashboard h2 {
            font-size: 32px;
            color: #2b6777;
            margin-bottom: 10px;
        }

        .dashboard p {
            margin-bottom: 30px;
            font-size: 16px;
            color: #555;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .button-grid a {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            background-color: #2b6777;
            transition: background-color 0.3s ease;
        }

        .button-grid a:hover {
            background-color: #52ab98;
        }

        .logout-btn {
            background-color: #d9534f;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background-color: #c9302c;
        }

        @media (max-width: 768px) {
            .button-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .button-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="dashboard container">

    <h2>Welcome, <?= htmlspecialchars($_SESSION['role']) ?>!</h2>
    <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>.</p>

    <div class="button-grid">
        <?php if ($role === 'student'): ?>
            <a href='student/view_events.php'>🎉 View Events</a>
            <a href='student/rso_membership.php'>🏫 My RSOs</a>
        <?php endif; ?>

        <?php if ($role === 'admin' || $role === 'superadmin'): ?>
            <a href='admin/create_event.php'>📝 Create Event</a>
            <a href='admin/create_rso.php'>🏫 New RSO</a>
            <a href='admin/manage_rsos.php'>🔍 Manage RSOs</a>
        <?php endif; ?>

        <?php if ($role === 'superadmin'): ?>
            <a href='superadmin/approve_event.php'>✅ Approve Events</a>
            <a href='superadmin/create_university.php'>🏫 Add Univ</a>
            <a href='superadmin/view_universities.php'>📋 Univ List</a>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
