<?php
include 'db.php';
include 'utils.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Invalid CSRF token.'];
        header("Location: manage_users.php");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'User deleted successfully!'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error deleting user: ' . $stmt->error];
    }

    $stmt->close();
}

header("Location: manage_users.php");
exit();
