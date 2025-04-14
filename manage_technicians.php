<?php
include 'db.php';
session_start();

// Enable error reporting for debugging (remove this in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch technicians
$tech_query = "SELECT * FROM users WHERE role='technician'";
$tech_result = $conn->query($tech_query);

// Add new technician
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'], $_POST['email'], $_POST['password'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepared insert statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'technician')");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Technician Added!'); window.location.href='manage_technicians.php';</script>";
    } else {
        echo "<script>alert('Error adding technician: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Delete technician
if (isset($_GET['delete'])) {
    $tech_id = intval($_GET['delete']);

    if (empty($tech_id)) {
        echo "<script>alert('Invalid Technician ID.');</script>";
        exit();
    }

    // First, delete related complaints (set assigned_to to NULL)
    $stmt1 = $conn->prepare("DELETE FROM complaints WHERE assigned_to = ?");
    if ($stmt1) {
        $stmt1->bind_param("i", $tech_id);
        if ($stmt1->execute()) {
            $stmt1->close();
        } else {
            echo "<script>alert('Error deleting related complaints: " . $stmt1->error . "');</script>";
            exit();
        }
    }

    // Then delete the technician
    $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt2) {
        $stmt2->bind_param("i", $tech_id);
        if ($stmt2->execute()) {
            echo "<script>alert('Technician Deleted!'); window.location.href='manage_technicians.php';</script>";
        } else {
            echo "<script>alert('Error deleting technician: " . $stmt2->error . "');</script>";
        }
        $stmt2->close();
    } else {
        echo "<script>alert('Error preparing delete statement for technician: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Technicians</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .nav-glass {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans">
    <nav class="p-4 text-white flex justify-between items-center nav-glass">
        <h1 class="text-2xl font-bold text-gray-800">Admin Panel</h1>
        <ul class="flex space-x-6">
            <li><a href="dashboard.php" class="text-gray-700 hover:text-gray-900 font-medium">Dashboard</a></li>
            <li><a href="manage_users.php" class="text-gray-700 hover:text-gray-900 font-medium">Manage Users</a></li>
            <li><a href="manage_technicians.php" class="text-blue-600 font-bold">Manage Technicians</a></li>
            <li><a href="download_reports.php" class="text-gray-700 hover:text-gray-900 font-medium">Download Reports</a></li>
            <li><a href="logout.php" class="btn-danger px-4 py-2 rounded-md text-white font-medium">Logout</a></li>
        </ul>
    </nav>

    <div class="container mx-auto p-8 max-w-6xl">
        <div class="card-glass p-8">
            <h2 class="text-3xl font-bold mb-6 text-gray-800">Manage Technicians</h2>

            <!-- Add Technician Form -->
            <form method="POST" class="mb-8 bg-white bg-opacity-50 p-6 rounded-lg space-y-4 shadow-sm">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Add New Technician</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" placeholder="Technician Name" required 
                               class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" placeholder="Email" required 
                               class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" placeholder="Password" required 
                               class="p-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <button type="submit" class="btn-gradient text-white px-6 py-3 rounded-lg font-medium mt-4">
                    Add Technician
                </button>
            </form>

            <!-- Technicians Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="p-3 text-left rounded-tl-lg">#</th>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left rounded-tr-lg">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if there are technicians to display
                        if ($tech_result->num_rows > 0) {
                            // Initialize serial number
                            $serial_no = 1;
                            while ($tech = $tech_result->fetch_assoc()) {
                                echo '<tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3">' . $serial_no++ . '</td>
                                        <td class="p-3">' . htmlspecialchars($tech['name']) . '</td>
                                        <td class="p-3">' . htmlspecialchars($tech['email']) . '</td>
                                        <td class="p-3">
                                            <a href="?delete=' . $tech['id'] . '" 
                                               class="btn-danger px-4 py-2 text-white rounded-md font-medium" 
                                               onclick="return confirm(\'Are you sure you want to delete this technician?\')">
                                               Delete
                                            </a>
                                        </td>
                                      </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center p-6 text-gray-500">No technicians found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>