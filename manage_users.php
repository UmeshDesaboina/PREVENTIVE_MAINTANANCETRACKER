<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch users
$user_query = "SELECT * FROM users WHERE role='user'";
$user_result = $conn->query($user_query);

// Delete user
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);

    // Delete associated complaints first
    $stmt1 = $conn->prepare("DELETE FROM complaints WHERE user_id = ?");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();

    // Now delete user
    $stmt2 = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt2->bind_param("i", $user_id);
    
    if ($stmt2->execute()) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'User and related complaints deleted successfully!'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error deleting user'];
    }

    header("Location: manage_users.php");
    exit();
}

// Display flash message if exists
$flash_message = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users | Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }

        .content-wrapper {
            background-color: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            min-height: calc(100vh - 80px);
        }

        /* Navigation Bar */
        nav {
            background: rgba(79, 70, 229, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Card styling */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Table styling */
        thead {
            background: rgba(243, 244, 246, 0.95);
        }

        th {
            color: #374151;
            font-weight: 600;
        }

        .table-row {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(243, 244, 246, 0.8);
        }

        .table-row:hover {
            background-color: rgba(249, 250, 251, 0.9);
        }

        /* Action buttons */
        .action-btn {
            transition: all 0.2s ease;
        }

        .delete-btn {
            background-color: rgba(254, 226, 226, 0.9);
            color: #ef4444;
        }

        .delete-btn:hover {
            background-color: rgba(254, 202, 202, 0.9);
        }

        /* Flash messages */
        .flash-message {
            border-left: 4px solid;
        }

        .flash-message.success {
            background-color: rgba(209, 250, 229, 0.9);
            border-color: #10b981;
            color: #065f46;
        }

        .flash-message.error {
            background-color: rgba(254, 226, 226, 0.9);
            border-color: #ef4444;
            color: #991b1b;
        }

        /* Empty state */
        .empty-state {
            background: rgba(249, 250, 251, 0.9);
        }

        .empty-icon {
            background: rgba(224, 231, 255, 0.9);
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="p-4 text-white">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 md:mb-0">
                <i class="fas fa-user-shield text-2xl"></i>
                <h1 class="text-2xl font-bold">Admin Panel</h1>
            </div>
            <ul class="flex flex-wrap justify-center gap-4 md:gap-6 items-center">
                <li><a href="dashboard.php" class="hover:underline flex items-center space-x-1"><i class="fas fa-tachometer-alt"></i> <span class="hidden md:inline">Dashboard</span></a></li>
                <li><a href="manage_users.php" class="hover:underline font-semibold flex items-center space-x-1"><i class="fas fa-users"></i> <span class="hidden md:inline">Manage Users</span></a></li>
                <li><a href="manage_technicians.php" class="hover:underline flex items-center space-x-1"><i class="fas fa-user-cog"></i> <span class="hidden md:inline">Technicians</span></a></li>
                <li><a href="download_reports.php" class="hover:underline flex items-center space-x-1"><i class="fas fa-file-download"></i> <span class="hidden md:inline">Reports</span></a></li>
                <li><a href="logout.php" class="hover:underline bg-red-500 hover:bg-red-600 px-3 py-1 rounded flex items-center space-x-1 transition duration-200"><i class="fas fa-sign-out-alt"></i> <span class="hidden md:inline">Logout</span></a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container mx-auto p-4 md:p-6">
            <!-- Flash Message -->
            <?php if ($flash_message): ?>
                <div class="flash-message <?= $flash_message['type'] === 'success' ? 'success' : 'error' ?> mb-6 p-4 rounded-lg flex items-center">
                    <i class="<?= $flash_message['type'] === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle' ?> mr-3 text-lg"></i>
                    <?= $flash_message['message'] ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-users mr-2 text-indigo-600"></i>
                        User Management
                    </h2>
                    <p class="text-gray-600">Create, view, and manage system users</p>
                </div>
                <div class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm">
                    Total Users: <span class="font-bold text-indigo-600"><?= $user_result->num_rows ?></span>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 flex items-center">
                        <i class="fas fa-list-alt mr-2 text-indigo-500"></i>
                        User List
                    </h3>
                </div>
                
                <?php if ($user_result->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 text-left">ID</th>
                                    <th class="py-3 px-4 text-left">Name</th>
                                    <th class="py-3 px-4 text-left">Email</th>
                                    <th class="py-3 px-4 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $user_result->fetch_assoc()): ?>
                                    <tr class="table-row">
                                        <td class="py-4 px-4 font-mono text-gray-600">#<?= $user['id'] ?></td>
                                        <td class="py-4 px-4 font-medium text-gray-800"><?= htmlspecialchars($user['name']) ?></td>
                                        <td class="py-4 px-4 text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                                        <td class="py-4 px-4">
                                            <a href="?delete=<?= $user['id'] ?>" 
                                               class="action-btn delete-btn p-2 rounded-lg" 
                                               title="Delete User"
                                               onclick="return confirm('Are you sure you want to delete this user and all their complaints?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state p-12 text-center m-4 rounded-lg">
                        <div class="empty-icon mx-auto w-20 h-20 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-user-slash text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">No Users Found</h3>
                        <p class="text-gray-500 mb-6">There are currently no regular users in the system.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-6 mt-8 bg-indigo-600 text-white">
            <div class="container mx-auto px-6 text-center">
                <p>© <?= date('Y') ?> Complaint Management System. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>