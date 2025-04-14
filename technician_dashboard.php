<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'technician') {
    header("Location: index.php");
    exit();
}

$technician_id = $_SESSION['user_id'];
$sql = "SELECT * FROM complaints WHERE assigned_to='$technician_id'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Technician Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .glass-header {
            background: rgba(37, 99, 235, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        
        .btn-action {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .status-badge {
            transition: all 0.2s ease;
        }
        
        .table-row:hover {
            background-color: rgba(249, 250, 251, 0.9) !important;
        }
    </style>
</head>
<body class="text-gray-800">

    <!-- Header -->
    <div class="glass-header text-white p-4 shadow-lg flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Technician Dashboard</h1>
            <p class="text-sm opacity-90">Welcome back, <?= $_SESSION['name'] ?? 'Technician' ?></p>
        </div>
        <a href="logout.php" 
           class="bg-red-500 hover:bg-red-600 transition-all btn-action px-5 py-2 rounded-lg shadow-md text-sm font-medium">
           Logout
        </a>
    </div>

    <!-- Main Container -->
    <div class="p-6 max-w-6xl mx-auto">
        <div class="glass-card p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Assigned Complaints</h2>
                <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium">
                    Total: <?= $result->num_rows ?> complaint(s)
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="p-4 rounded-tl-xl">Complaint ID</th>
                            <th class="p-4">Description</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 rounded-tr-xl">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="bg-white/80 hover:bg-white table-row border-b border-gray-100 last:border-0 transition">
                                    <td class="p-4 font-medium">#<?= $row['id'] ?></td>
                                    <td class="p-4"><?= htmlspecialchars($row['description']) ?></td>
                                    <td class="p-4">
                                        <span class="status-badge px-3 py-1 rounded-full text-sm font-medium 
                                            <?= $row['status'] == 'resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($row['status'] == 'assigned'): ?>
                                            <a href="update_status.php?id=<?= $row['id'] ?>" 
                                               class="btn-action bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg shadow-md text-sm font-medium inline-block">
                                               Mark as Resolved
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm font-medium">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-lg">No complaints assigned to you yet</p>
                                        <p class="text-sm mt-1">Check back later or contact your supervisor</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>