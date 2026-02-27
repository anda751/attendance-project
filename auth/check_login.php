<?php
session_start();
if (!isset($_SESSION['teacher'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
