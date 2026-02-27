<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) { 
    die("DB Error: " . $conn->connect_error); 
}
?>
