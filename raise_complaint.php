<?php
session_start();
include 'db.php'; // Ensure the database connection is included

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $description = $_POST['description'];

    // Insert complaint into database
    $stmt = $conn->prepare("INSERT INTO complaints (user_id, description) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $description);
    
    if ($stmt->execute()) {
        header("Location: user_dashboard.php?success=Complaint raised successfully");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raise Complaint</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #ec4899;
            --accent: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            color: white;
        }
        
        .complaint-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }
        
        .complaint-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        
        .complaint-header {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            padding: 1.5rem;
            color: white;
            text-align: center;
            margin: -2rem -2rem 2rem -2rem;
        }
        
        .complaint-title {
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .input-field {
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.15);
            font-weight: 500;
            color: white;
            backdrop-filter: blur(5px);
        }
        
        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            color: white;
        }
        
        textarea.input-field {
            min-height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            background-size: 200% 200%;
            transition: all 0.5s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            border: none;
            color: white;
            text-transform: uppercase;
        }
        
        .submit-btn:hover {
            background-position: right center;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .submit-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: all 0.5s ease;
        }
        
        .submit-btn:hover::after {
            left: 100%;
        }
        
        .form-label {
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
            display: block;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        
        /* Floating particles effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float-particle linear infinite;
        }
        
        @keyframes float-particle {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <!-- Background particles -->
    <div class="particles" id="particles-js"></div>
    
    <div class="complaint-container p-8 w-full max-w-lg">
        <div class="complaint-header">
            <h1 class="complaint-title">Report an Issue</h1>
        </div>
        
        <form action="raise_complaint.php" method="POST" class="space-y-5">
            <div>
                <label class="form-label">DESCRIBE YOUR ISSUE</label>
                <textarea name="description" placeholder="Please describe your issue in detail..." 
                    class="w-full px-4 py-3 rounded-lg input-field focus:outline-none" required></textarea>
            </div>

            <button type="submit" class="w-full py-3 rounded-lg submit-btn mt-6">
                SUBMIT COMPLAINT
            </button>
        </form>
    </div>

    <script>
        // Create floating particles
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles-js');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random properties
                const size = Math.random() * 5 + 2;
                const posX = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 20 + 10;
                
                particle.style.width = ${size}px;
                particle.style.height = ${size}px;
                particle.style.left = ${posX}%;
                particle.style.bottom = -10px;
                particle.style.animationDelay = ${delay}s;
                particle.style.animationDuration = ${duration}s;
                
                particlesContainer.appendChild(particle);
            }
        });
    </script>
</body>
</html>