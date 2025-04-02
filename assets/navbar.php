<?php
include_once __DIR__ . '/../auth/session.php';

$role = $_SESSION['role'];
$base = "/College_event_app"; // Update this if hosted in a different folder
?>

<div class="navbar">
    <div class="nav-left">
        <a href="<?= $base ?>/dashboard.php" class="nav-logo">🎓 College Events</a>
    </div>

    <div class="nav-links">
        <?php if ($role === 'student'): ?>
            <a href="<?= $base ?>/student/view_events.php">🎉 Events</a>
            <a href="<?= $base ?>/student/rso_membership.php">🏫 My RSOs</a>
        <?php endif; ?>

        <?php if ($role === 'admin' || $role === 'superadmin'): ?>
            <a href="<?= $base ?>/admin/create_event.php">📝 Create Event</a>
            <a href="<?= $base ?>/admin/create_rso.php">🏫 New RSO</a>
            <a href="<?= $base ?>/admin/manage_rsos.php">🔍 My RSOs</a>
        <?php endif; ?>

        <?php if ($role === 'superadmin'): ?>
            <a href="<?= $base ?>/superadmin/approve_event.php">✅ Approve</a>
            <a href="<?= $base ?>/superadmin/create_university.php">🏛 Add Univ</a>
            <a href="<?= $base ?>/superadmin/view_universities.php">📋 Univ List</a>
        <?php endif; ?>

        <a href="<?= $base ?>/auth/logout.php" class="btn btn-danger">🚪 Logout</a>
    </div>
</div>
