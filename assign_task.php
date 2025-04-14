<?php
// assign_task.php - Now with advanced UI/UX
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Get all unassigned complaints
$sql_complaints = "SELECT c.id, u.name as user_name, c.description 
                   FROM complaints c
                   JOIN users u ON c.user_id = u.id
                   WHERE c.status IS NULL OR c.status = 'pending'
                   ORDER BY c.created_at DESC";
$complaints_result = $conn->query($sql_complaints);

if (!$complaints_result) {
    die("Error fetching complaints: " . $conn->error);
}

// Get all technicians
$sql_techs = "SELECT id, name FROM users WHERE role = 'technician'";
$techs_result = $conn->query($sql_techs);

if (!$techs_result) {
    die("Error fetching technicians: " . $conn->error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $complaint_id = $_POST['complaint_id'];
    $tech_id = $_POST['tech_id'];

    $stmt = $conn->prepare("UPDATE complaints SET status = 'assigned', assigned_to = ? WHERE id = ?");
    $stmt->bind_param("ii", $tech_id, $complaint_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Complaint #$complaint_id has been assigned successfully!"];
        header("Location: assign_task.php");
        exit();
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Error assigning complaint: " . $stmt->error];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Complaints | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <?php include 'admin_nav.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 animate_animated animate_fadeInDown">
                        <i class="fas fa-tasks mr-2 text-blue-600"></i> Assign Complaints
                    </h1>
                    <p class="text-gray-600 mt-1 animate_animated animatefadeInDown animate_delay-1s">Assign pending complaints to available technicians</p>
                </div>
                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all animate_animated animatefadeInDown animate_delay-1s">
                    <i class="fas fa-arrow-left mr-2 text-gray-500"></i> Back to Dashboard
                </a>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="mb-6 rounded-md <?= $SESSION['flash_message']['type'] === 'success' ? 'bg-green-50 border-l-4 border-green-500 p-4' : 'bg-red-50 border-l-4 border-red-500 p-4' ?> animateanimated animate_fadeIn">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="<?= $_SESSION['flash_message']['type'] === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500' ?> h-5 w-5"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm <?= $_SESSION['flash_message']['type'] === 'success' ? 'text-green-700' : 'text-red-700' ?>">
                                <?= htmlspecialchars($_SESSION['flash_message']['message']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <!-- Assignment Form -->
            <div class="card bg-white rounded-lg overflow-hidden mb-8 animate_animated animate_fadeInUp">
                <div class="px-6 py-5 border-b border-gray-200 bg-white">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-user-cog mr-2 text-blue-500"></i> Assignment Form
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" class="space-y-6" id="assignmentForm">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-exclamation-circle mr-2 text-blue-500"></i>Select Complaint
                            </label>
                            <div class="select-wrapper mt-1">
                                <select name="complaint_id" class="custom-select block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                    <option value="">Select a Complaint</option>
                                    <?php if ($complaints_result->num_rows > 0): ?>
                                        <?php while ($complaint = $complaints_result->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($complaint['id']) ?>">
                                                Complaint #<?= htmlspecialchars($complaint['id']) ?> - 
                                                <?= htmlspecialchars($complaint['user_name']) ?> - 
                                                <?= htmlspecialchars(substr($complaint['description'], 0, 50)) ?><?= strlen($complaint['description']) > 50 ? '...' : '' ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="" disabled class="text-gray-500">No unassigned complaints available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-user-cog mr-2 text-blue-500"></i>Assign To Technician
                            </label>
                            <div class="select-wrapper mt-1">
                                <select name="tech_id" class="custom-select block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                    <option value="">Select a Technician</option>
                                    <?php while ($tech = $techs_result->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($tech['id']) ?>">
                                            <?= htmlspecialchars($tech['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full btn-primary bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md shadow-sm flex items-center justify-center relative overflow-hidden">
                            <span id="submitText"><i class="fas fa-user-check mr-2"></i> Assign Complaint</span>
                            <span id="spinner" class="hidden ml-2">
                                <i class="fas fa-circle-notch fa-spin"></i>
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Assignments -->
            <div class="card bg-white rounded-lg overflow-hidden animate_animated animatefadeInUp animate_delay-1s">
                <div class="px-6 py-5 border-b border-gray-200 bg-white">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-history mr-2 text-blue-500"></i> Recent Assignments
                        </h3>
                        <button id="refreshAssignments" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="assignmentsContainer">
                        <?php
                        $sql_recent = "SELECT c.id, t.name as tech_name, u.name as user_name, c.created_at 
                                     FROM complaints c
                                     JOIN users t ON c.assigned_to = t.id
                                     JOIN users u ON c.user_id = u.id
                                     WHERE c.status = 'assigned'
                                     ORDER BY c.created_at DESC LIMIT 5";
                        $recent_result = $conn->query($sql_recent);
                        ?>

                        <?php if ($recent_result->num_rows > 0): ?>
                            <div class="space-y-4">
                                <?php while ($assignment = $recent_result->fetch_assoc()): ?>
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition transform hover:scale-[1.01]">
                                        <div class="mb-2 sm:mb-0">
                                            <span class="font-medium text-gray-900">Complaint #<?= htmlspecialchars($assignment['id']) ?></span>
                                            <span class="text-gray-500 text-sm ml-0 sm:ml-4 block sm:inline-block mt-1 sm:mt-0">
                                                <i class="fas fa-user mr-1 text-gray-400"></i> <?= htmlspecialchars($assignment['user_name']) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 mr-3">
                                                <i class="fas fa-user-shield mr-1 text-blue-400"></i> <?= htmlspecialchars($assignment['tech_name']) ?>
                                            </span>
                                            <span class="text-xs text-gray-400 tooltip" data-tooltip="<?= date('M j, Y g:i A', strtotime($assignment['created_at'])) ?>">
                                                <?= time_elapsed_string($assignment['created_at']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-inbox text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No recent assignments</h3>
                                <p class="text-gray-500">Assign some complaints to see them listed here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating action button -->
    <button id="scrollToTop" class="fab bg-blue-600 text-white hidden">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script>
        // Form submission with loading state
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const submitText = document.getElementById('submitText');
            const spinner = document.getElementById('spinner');
            
            submitText.textContent = "Processing...";
            spinner.classList.remove('hidden');
            submitBtn.disabled = true;
        });
        
        // Refresh assignments
        document.getElementById('refreshAssignments').addEventListener('click', function() {
            const container = document.getElementById('assignmentsContainer');
            const refreshBtn = this;
            
            // Add rotation animation to refresh button
            refreshBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Refreshing';
            refreshBtn.disabled = true;
            
            // Simulate loading (in a real app, you'd fetch new data here)
            setTimeout(() => {
                container.classList.add('animate_animated', 'animate_fadeIn');
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Refresh';
                refreshBtn.disabled = false;
                
                // Remove animation class after it completes
                setTimeout(() => {
                    container.classList.remove('animate_animated', 'animate_fadeIn');
                }, 1000);
            }, 1500);
        });
        
        // Scroll to top button
        window.addEventListener('scroll', function() {
            const scrollBtn = document.getElementById('scrollToTop');
            if (window.pageYOffset > 300) {
                scrollBtn.classList.remove('hidden');
                scrollBtn.classList.add('animate_animated', 'animate_fadeIn');
            } else {
                scrollBtn.classList.add('hidden');
                scrollBtn.classList.remove('animate_animated', 'animate_fadeIn');
            }
        });
        
        document.getElementById('scrollToTop').addEventListener('click', function() {
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
                    el.classList.add('animate_animated', 'animate_fadeInUp');
                }
            });
        };
        
        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Run once on page load
    </script>
</body>
</html>

<?php
// Helper function to show elapsed time
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}