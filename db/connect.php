<?php
$servername = "localhost";
$username = "root"; // default for WAMP
$password = "";     // default is blank
$dbname = "college_events";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
