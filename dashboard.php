<?php
include 'auth/session.php';

$role = $_SESSION['role'];

echo "<h2>Welcome!</h2>";
echo "<p>You are logged in as <strong>$role</strong>.</p>";

// View Events link for both students and admins
if ($role === 'student') {
    echo "<a href='student/view_events.php'>🎉 View Events</a><br>";
    echo "<a href='student/rso_membership.php'>🏫 Manage RSO Memberships</a>";
}


// Only admins can create events
if ($role === 'admin') {
    echo "<a href='admin/create_event.php'>📝 Create Event</a><br>";
    echo "<a href='admin/create_rso.php'>🏫 Create RSO</a><br>";
    echo "<a href='admin/manage_rsos.php'>🔍 Manage RSOs</a><br>";
}

// Only superadmins can approve events
if ($role === 'superadmin') {
    echo "<a href='superadmin/approve_event.php'>✅ Approve Public Events</a><br>";
}

echo "<br><a href='auth/logout.php'>Logout</a>";
?>
