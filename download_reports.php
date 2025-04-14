<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch complaints
$complaint_query = "SELECT * FROM complaints";
$complaint_result = $conn->query($complaint_query);

// Export CSV
if (isset($_POST['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="complaints_report.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['Complaint ID', 'User ID', 'Issue', 'Status', 'Technician ID']);

    while ($row = $complaint_result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Download Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white flex justify-between">
        <h1 class="text-2xl font-bold">Admin Panel</h1>
        <ul class="flex space-x-6">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_technicians.php">Manage Technicians</a></li>
            <li><a href="download_reports.php" class="font-bold">Download Reports</a></li>
            <li><a href="logout.php" class="bg-red-500 px-3 py-1 rounded">Logout</a></li>
        </ul>
    </nav>

    <div class="container mx-auto p-6 bg-white shadow-md mt-6 text-center">
        <h2 class="text-2xl font-bold mb-4">Download Reports</h2>
        <form method="POST">
            <button type="submit" name="export" class="bg-green-500 text-white px-6 py-3 rounded shadow">Download CSV</button>
        </form>
    </div>
</body>
</html>
