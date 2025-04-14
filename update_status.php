<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'technician') {
    header("Location: index.php");
    exit();
}

$complaint_id = $_GET['id'];
$sql = "UPDATE complaints SET status='resolved' WHERE id='$complaint_id'";
if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Complaint Resolved!'); window.location.href='technician_dashboard.php';</script>";
}
?>
