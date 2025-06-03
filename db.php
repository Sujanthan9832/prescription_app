<?php
$conn = new mysqli("localhost", "root", "", "prescription_app");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
