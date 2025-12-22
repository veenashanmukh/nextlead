<?php
$servername = "localhost";
$username = "root";
$password = "root"; // or "" depending on MAMP settings
$dbname = "voting_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
