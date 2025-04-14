<?php
// db.php - Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "preventive_maintenance";

$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// utils.php - Utility Functions
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function get_status_badge($status) {
    $status_map = [
        'pending' => ['text' => 'Pending', 'color' => 'bg-yellow-100 text-yellow-800'],
        'assigned' => ['text' => 'Assigned', 'color' => 'bg-blue-100 text-blue-800'],
        'in_progress' => ['text' => 'In Progress', 'color' => 'bg-indigo-100 text-indigo-800'],
        'resolved' => ['text' => 'Resolved', 'color' => 'bg-green-100 text-green-800'],
        'rejected' => ['text' => 'Rejected', 'color' => 'bg-red-100 text-red-800']
    ];
    
    $status = strtolower($status);
    $status_data = $status_map[$status] ?? ['text' => ucfirst($status), 'color' => 'bg-gray-100 text-gray-800'];
    
    return '<span class="px-3 py-1 rounded-full text-xs font-semibold ' . $status_data['color'] . '">' . $status_data['text'] . '</span>';
}
?>