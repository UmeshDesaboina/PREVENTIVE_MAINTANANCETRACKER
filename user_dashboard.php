<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM complaints WHERE user_id='$user_id'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden animate__animated animate__fadeIn">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold">👋 Welcome Back!</h2>
                        <p class="text-blue-100">Manage your complaints and track their status</p>
                    </div>
                    <a href="logout.php" class="text-white hover:text-blue-200 transition">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="p-6">
                <!-- Action Button -->
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-semibold text-gray-700">📋 Your Complaints</h3>
                    <a href="raise_complaint.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-md transition transform hover:scale-105 flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i> Raise New Complaint
                    </a>
                </div>
                
                <!-- Complaints Table -->
                <?php if ($result->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-blue-500 text-white">
                                    <th class="p-3 text-left rounded-tl-lg">#️⃣ Complaint ID</th>
                                    <th class="p-3 text-left">📝 Description</th>
                                    <th class="p-3 text-left rounded-tr-lg">🔄 Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $status_color = '';
                                    $status_icon = '';
                                    switch($row['status']) {
                                        case 'pending':
                                            $status_color = 'bg-yellow-100 text-yellow-800';
                                            $status_icon = '⏳';
                                            break;
                                        case 'resolved':
                                            $status_color = 'bg-green-100 text-green-800';
                                            $status_icon = '✅';
                                            break;
                                        case 'in_progress':
                                            $status_color = 'bg-blue-100 text-blue-800';
                                            $status_icon = '🚀';
                                            break;
                                        case 'rejected':
                                            $status_color = 'bg-red-100 text-red-800';
                                            $status_icon = '❌';
                                            break;
                                        default:
                                            $status_color = 'bg-gray-100 text-gray-800';
                                            $status_icon = 'ℹ️';
                                    }
                                ?>
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-3 font-medium">#<?= $row['id'] ?></td>
                                    <td class="p-3"><?= htmlspecialchars($row['description']) ?></td>
                                    <td class="p-3">
                                        <span class="<?= $status_color ?> px-3 py-1 rounded-full text-sm font-medium">
                                            <?= $status_icon ?> <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                        <h4 class="text-xl font-medium text-gray-500">No complaints found</h4>
                        <p class="text-gray-400 mt-2">You haven't raised any complaints yet</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 text-center text-gray-500 text-sm">
                <p>Need help? <a href="#" class="text-blue-500 hover:underline">Contact support</a></p>
            </div>
        </div>
    </div>
    
    <!-- Floating action button for mobile -->
    <div class="md:hidden fixed bottom-6 right-6">
        <a href="raise_complaint.php" class="bg-green-500 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-2xl hover:bg-green-600 transition transform hover:scale-110">
            <i class="fas fa-plus"></i>
        </a>
    </div>
</body>
</html>