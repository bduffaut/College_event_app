

<?php
include '../auth/session.php';
include '../assets/navbar.php';

include '../db/connect.php';
include '../config/load_env.php';
loadEnv();

$googlekey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? null;

$event_id = $_GET['id'] ?? null;
$uid = $_SESSION['uid'];

if (!$event_id) {
    echo "❌ No event specified.";
    exit;
}

// Fetch event details
$stmt = $conn->prepare("
    SELECT Events.*, Locations.name AS location_name, Locations.latitude, Locations.longitude 
    FROM Events 
    LEFT JOIN Locations ON Events.location_id = Locations.location_id 
    WHERE event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "❌ Event not found.";
    exit;
}

// Handle comment/rating form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_comment'])) {
        $text = trim($_POST['comment_text']);
        $stmt = $conn->prepare("INSERT INTO comments (user_id, event_id, text, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $uid, $event_id, $text);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update_comment'])) {
        $text = trim($_POST['comment_text']);
        $comment_id = intval($_POST['comment_id']);
        $stmt = $conn->prepare("UPDATE comments SET text = ?, created_at = NOW() WHERE comment_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $text, $comment_id, $uid);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_comment'])) {
        $comment_id = intval($_POST['comment_id']);
        $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $uid);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['rating'])) {
        $stars = intval($_POST['rating']);
        $stmt = $conn->prepare("REPLACE INTO ratings (user_id, event_id, stars, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $uid, $event_id, $stars);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: event_details.php?id=$event_id");
    exit;
}

// Fetch comments
$comments = $conn->query("
    SELECT c.*, u.email AS username 
    FROM comments c 
    JOIN users u ON c.user_id = u.UID 
    WHERE c.event_id = $event_id 
    ORDER BY c.created_at DESC
");

// User and average rating
$userRating = $conn->query("SELECT stars FROM ratings WHERE user_id = $uid AND event_id = $event_id")
    ->fetch_assoc()['stars'] ?? null;

$avgRow = $conn->query("SELECT AVG(stars) AS avg_rating FROM ratings WHERE event_id = $event_id")
    ->fetch_assoc();
$avgRating = $avgRow['avg_rating'] !== null ? round($avgRow['avg_rating'], 2) : null;

$eventUrl = "http://localhost/College_event_app/student/event_details.php?id=" . $event_id;
$shareText = urlencode("Check out this event: " . $event['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container">

<h2><?= htmlspecialchars($event['name']) ?></h2>
<p><strong>Date:</strong> <?= $event['event_date'] ?></p>
<p><strong>Time:</strong> <?= $event['start_time'] ?> - <?= $event['end_time'] ?></p>
<p><strong>Category:</strong> <?= htmlspecialchars($event['category']) ?></p>
<p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($event['description'])) ?></p>

<?php if (!empty($googlekey) && !empty($event['latitude']) && !empty($event['longitude'])): ?>
    <h3>Event Location</h3>
    <div id="map" style="height: 400px; margin-bottom: 20px;"></div>

    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $googlekey ?>"></script>
    <script>
        function initMap() {
            const location = { lat: <?= $event['latitude'] ?>, lng: <?= $event['longitude'] ?> };
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 16,
                center: location
            });
            new google.maps.Marker({
                position: location,
                map: map
            });
        }
        window.onload = initMap;
    </script>
<?php else: ?>
    <p><em>❌ Google Maps API key not found or coordinates missing. No map available for this event.</em></p>
<?php endif; ?>

<p><strong>Average Rating:</strong> <?= $avgRating !== null ? $avgRating : 'Not rated yet' ?></p>

<h3>⭐ Rate This Event</h3>
<form method="POST">
    <select name="rating" required>
        <option value="">-- Rate 1-5 --</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?= $i ?>" <?= ($userRating == $i) ? 'selected' : '' ?>>
                <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
            </option>
        <?php endfor; ?>
    </select>
    <button type="submit" class="btn">Submit Rating</button>
</form>

<h3>💬 Comments</h3>
<form method="POST">
    <textarea name="comment_text" required></textarea>
    <input type="hidden" name="new_comment" value="1">
    <button type="submit" class="btn">Add Comment</button>
</form>

<?php while ($comment = $comments->fetch_assoc()): ?>
    <div style="border: 1px solid #ccc; margin: 10px 0; padding: 6px; border-radius: 4px;">
        <strong><?= htmlspecialchars($comment['username']) ?></strong> @ <?= $comment['created_at'] ?><br>
        <p><?= nl2br(htmlspecialchars($comment['text'])) ?></p>

        <?php if ($comment['user_id'] == $uid): ?>
            <form method="POST" style="display:inline">
                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                <input type="hidden" name="delete_comment" value="1">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete comment?')">Delete</button>
            </form>

            <form method="POST" style="display:inline">
                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                <input type="text" name="comment_text" value="<?= htmlspecialchars($comment['text']) ?>" required>
                <input type="hidden" name="update_comment" value="1">
                <button type="submit" class="btn btn-secondary">Edit</button>
            </form>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

<h3>📢 Share This Event</h3>
<a href="https://twitter.com/intent/tweet?url=<?= urlencode($eventUrl) ?>&text=<?= $shareText ?>" target="_blank" class="btn btn-secondary">Share on Twitter</a>
<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($eventUrl) ?>" target="_blank" class="btn btn-secondary">Share on LinkedIn</a>

<p>
    <a href="view_events.php" class="btn btn-secondary">⬅️ Back to Events</a>
</p>

</div>
</body>
</html>
