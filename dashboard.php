<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch all complaints
$sql = "SELECT c.id, u.name AS user_name, c.description, c.status, c.assigned_to, t.name AS tech_name, c.created_at 
        FROM complaints c 
        JOIN users u ON c.user_id = u.id
        LEFT JOIN users t ON c.assigned_to = t.id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching complaints: " . $conn->error);
}

// Count complaints by status for stats
$stats = [
    'total' => 0,
    'pending' => 0,
    'assigned' => 0,
    'in_progress' => 0,
    'resolved' => 0
];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stats['total']++;
        if ($row['status'] == 'pending') $stats['pending']++;
        if ($row['status'] == 'assigned') $stats['assigned']++;
        if ($row['status'] == 'in_progress') $stats['in_progress']++;
        if ($row['status'] == 'resolved') $stats['resolved']++;
    }
    // Reset pointer for main display
    $result->data_seek(0);
}

// Get technician count
$tech_count_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='technician'");
if (!$tech_count_result) {
    die("Error fetching technician count: " . $conn->error);
}
$tech_count = $tech_count_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <head>
    <style>
        :root {
            --primary: 59, 130, 246;
            --secondary: 107, 114, 128;
            --success: 16, 185, 129;
            --danger: 239, 68, 68;
            --warning: 245, 158, 11;
            --info: 59, 130, 246;
        }

        [data-theme="dark"] {
            --primary: 96, 165, 250;
            --secondary: 156, 163, 175;
            --success: 52, 211, 153;
            --danger: 248, 113, 113;
            --warning: 252, 211, 77;
            --info: 96, 165, 250;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(to bottom, #f8fafc 0%, #f1f5f9 100%);
            color: rgba(var(--secondary), 1);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        [data-theme="dark"] body {
            background: linear-gradient(to bottom, #111827 0%, #1f2937 100%);
        }

        .card {
            background: linear-gradient(to bottom right, #ffffff, #f3f4f6);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        [data-theme="dark"] .card {
            background: linear-gradient(to bottom right, #1f2937, #374151);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .status-pending {
            background-color: rgba(254, 243, 199, 1);
            color: rgba(146, 64, 14, 1);
        }

        .status-assigned {
            background-color: rgba(219, 234, 254, 1);
            color: rgba(30, 64, 175, 1);
        }

        .status-in_progress {
            background-color: rgba(224, 231, 255, 1);
            color: rgba(55, 48, 163, 1);
        }

        .status-resolved {
            background-color: rgba(209, 250, 229, 1);
            color: rgba(4, 120, 87, 1);
        }

        .nav-link {
            transition: all 0.2s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }

        .nav-link:hover {
            background-color: rgba(var(--primary), 0.1);
        }

        [data-theme="dark"] .nav-link:hover {
            background-color: rgba(var(--primary), 0.2);
        }

        .theme-toggle {
            position: relative;
            width: 3rem;
            height: 1.5rem;
            border-radius: 9999px;
            background-color: rgba(var(--secondary), 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .theme-toggle::after {
            content: '';
            position: absolute;
            top: 0.25rem;
            left: 0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background-color: white;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .theme-toggle {
            background-color: rgba(var(--primary), 0.5);
        }

        [data-theme="dark"] .theme-toggle::after {
            transform: translateX(1.5rem);
            background-color: #1e293b;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        [data-theme="dark"] ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        [data-theme="dark"] ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        [data-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Ripple effect */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Floating action button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 50;
        }

        .fab:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Tooltip */
        .tooltip {
            position: relative;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: 120px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        /* Table row hover effect */
        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
</head>
<body data-theme="<?= isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light' ?>">
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 dark:from-gray-800 dark:to-gray-900 p-4 text-white shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2 animate__animated animate__fadeIn">
                <i class="fas fa-tools text-2xl"></i>
                <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            </div>
            <ul class="flex space-x-4 items-center">
                <li><a href="dashboard.php" class="nav-link flex items-center space-x-1"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="manage_users.php" class="nav-link flex items-center space-x-1"><i class="fas fa-users"></i> <span>Users</span></a></li>
                <li><a href="manage_technicians.php" class="nav-link flex items-center space-x-1"><i class="fas fa-user-cog"></i> <span>Technicians</span></a></li>
                <li><a href="assign_task.php" class="nav-link flex items-center space-x-1"><i class="fas fa-tasks"></i> <span>Assign Tasks</span></a></li>
                <li><a href="download_reports.php" class="nav-link flex items-center space-x-1"><i class="fas fa-file-download"></i> <span>Reports</span></a></li>
                <li>
                    <div class="flex items-center space-x-2 ml-4">
                        <span class="text-sm hidden sm:inline">Theme</span>
                        <div class="theme-toggle" id="themeToggle"></div>
                    </div>
                </li>
                <li><a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded flex items-center space-x-1 transition duration-200 ml-4 ripple"><i class="fas fa-sign-out-alt"></i> <span class="hidden sm:inline">Logout</span></a></li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mx-auto p-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate__animated animate__fadeIn">
            <!-- Total Complaints -->
            <div class="card bg-gradient-to-r from-blue-500 to-blue-400 dark:from-blue-700 dark:to-blue-600 text-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-blue-700 font-semibold">Total Complaints</p>
                        <h3 class="text-4xl font-extrabold text-blue-500"><?= htmlspecialchars($stats['total']) ?></h3>
                    </div>
                    <i class="fas fa-clipboard-list text-5xl opacity-80 text-blue-500"></i>
                </div>
            </div>

            

            <!-- Assigned Complaints -->
            <div class="card bg-gradient-to-r from-indigo-500 to-indigo-400 dark:from-indigo-700 dark:to-indigo-600 text-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-indigo-700 font-semibold">Assigned</p>
                        <h3 class="text-4xl font-extrabold text-indigo-500"><?= htmlspecialchars($stats['assigned']) ?></h3>
                    </div>
                    <i class="fas fa-user-check text-5xl opacity-80 text-indigo-500"></i>
                </div>
            </div>

            <!-- Resolved Complaints -->
            <div class="card bg-gradient-to-r from-green-500 to-green-400 dark:from-green-700 dark:to-green-600 text-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-1">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-700 font-semibold">Resolved</p>
                        <h3 class="text-4xl font-extrabold text-green-500"><?= htmlspecialchars($stats['resolved']) ?></h3>
                    </div>
                    <i class="fas fa-check-circle text-5xl opacity-80 text-green-500"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate__animated animate__fadeInUp">
            <a href="assign_task.php" class="card bg-white dark:bg-slate-800 p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-1 flex items-center space-x-4 group">
                <div class="bg-blue-100 dark:bg-blue-900/50 p-3 rounded-full group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition duration-200">
                    <i class="fas fa-user-plus text-blue-600 dark:text-blue-300 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">Assign Tasks</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Assign pending complaints to technicians</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-gray-400 group-hover:text-blue-500 transition duration-200"></i>
            </a>
            
            <a href="manage_technicians.php" class="card bg-white dark:bg-slate-800 p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-1 flex items-center space-x-4 group">
                <div class="bg-purple-100 dark:bg-purple-900/50 p-3 rounded-full group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition duration-200">
                    <i class="fas fa-user-cog text-purple-600 dark:text-purple-300 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">Manage Technicians</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($tech_count) ?> technicians available</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-gray-400 group-hover:text-purple-500 transition duration-200"></i>
            </a>
            
            <a href="raise_complaint.php" class="card bg-white dark:bg-slate-800 p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 transform hover:-translate-y-1 flex items-center space-x-4 group">
                <div class="bg-green-100 dark:bg-green-900/50 p-3 rounded-full group-hover:bg-green-200 dark:group-hover:bg-green-800 transition duration-200">
                    <i class="fas fa-plus-circle text-green-600 dark:text-green-300 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200">Create Complaint</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Add a new maintenance request</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-gray-400 group-hover:text-green-500 transition duration-200"></i>
            </a>
        </div>

        <!-- Complaints Table -->
        <div class="card bg-white dark:bg-slate-800 rounded-lg shadow-md overflow-hidden animate__animated animate__fadeInUp">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold flex items-center text-gray-800 dark:text-gray-200">
                        <i class="fas fa-list-alt mr-2 text-blue-600 dark:text-blue-400"></i>
                        Recent Complaints
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Latest maintenance requests and their status</p>
                </div>
                <a href="raise_complaint.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center space-x-1 transition duration-200 ripple">
                    <i class="fas fa-plus"></i>
                    <span>New Complaint</span>
                </a>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="py-3 px-4 text-left">ID</th>
                                <th class="py-3 px-4 text-left">User</th>
                                <th class="py-3 px-4 text-left">Description</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-left">Assigned To</th>
                                <th class="py-3 px-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="table-row-hover bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 transition duration-150">
                                    <td class="py-4 px-4 font-mono text-gray-800 dark:text-gray-200">#<?= htmlspecialchars($row['id']) ?></td>
                                    <td class="py-4 px-4 font-medium text-gray-800 dark:text-gray-200"><?= htmlspecialchars($row['user_name']) ?></td>
                                    <td class="py-4 px-4 text-gray-600 dark:text-gray-300">
                                        <div class="tooltip" data-tooltip="<?= htmlspecialchars($row['description']) ?>">
                                            <?= htmlspecialchars(substr($row['description'], 0, 50)) ?>
                                            <?= strlen($row['description']) > 50 ? '...' : '' ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="status-badge status-<?= htmlspecialchars($row['status']) ?>">
                                            <i class="fas fa-circle"></i>
                                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($row['status']))) ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php if ($row['tech_name']): ?>
                                            <span class="bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-xs font-semibold">
                                                <?= htmlspecialchars($row['tech_name']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500 dark:text-gray-400">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex space-x-2">
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <a href="assign_task.php?id=<?= $row['id'] ?>" 
                                                   class="bg-blue-100 dark:bg-blue-900/50 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-600 dark:text-blue-300 p-2 rounded transition duration-200 tooltip" 
                                                   data-tooltip="Assign Technician">
                                                    <i class="fas fa-user-tag"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="#" 
                                               class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 p-2 rounded transition duration-200 tooltip" 
                                               data-tooltip="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center animate__animated animate__fadeIn">
                    <div class="mx-auto w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-clipboard-check text-3xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">No Complaints Found</h3>
                    <p class="text-gray-500 dark:text-gray-400">There are currently no complaints in the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating action button -->
    <button id="scrollToTop" class="fab bg-blue-600 hover:bg-blue-700 text-white hidden ripple">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white py-6">
        <div class="container mx-auto px-6 text-center">
            <p>© <?= date('Y') ?> Maintenance Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Theme toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const html = document.documentElement;
            const currentTheme = localStorage.getItem('theme') || 'light';
            
            // Set initial theme
            html.setAttribute('data-theme', currentTheme);
            
            // Theme toggle click handler
            themeToggle.addEventListener('click', function() {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                document.cookie = `theme=${newTheme}; path=/; max-age=31536000; SameSite=Lax`;
            });

            // Ripple effect for buttons
            document.querySelectorAll('.ripple').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple-effect');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                    
                    // If it's a link, navigate after animation
                    if (this.tagName === 'A') {
                        setTimeout(() => {
                            window.location.href = this.href;
                        }, 300);
                    }
                });
            });

            // Scroll to top button
            const scrollToTopBtn = document.getElementById('scrollToTop');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.remove('hidden');
                    scrollToTopBtn.classList.add('animate__animated', 'animate__fadeIn');
                } else {
                    scrollToTopBtn.classList.add('hidden');
                    scrollToTopBtn.classList.remove('animate__animated', 'animate__fadeIn');
                }
            });
            
            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Tooltip initialization
            document.querySelectorAll('.tooltip').forEach(el => {
                const tooltipText = el.getAttribute('data-tooltip');
                if (tooltipText) {
                    const tooltip = document.createElement('span');
                    tooltip.className = 'tooltip-text';
                    tooltip.textContent = tooltipText;
                    el.appendChild(tooltip);
                }
            });

            // Animate elements on scroll
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.animate-on-scroll');
                elements.forEach(el => {
                    const elementPosition = el.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementPosition < windowHeight - 100) {
                        el.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on page load
        });
    </script>
</body>
</html>