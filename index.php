<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use prepared statements for better security
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Compare the entered password with the stored password (plain text)
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == "admin") {
                header("Location: dashboard.php");
            } elseif ($user['role'] == "technician") {
                header("Location: technician_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid Password!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }

    // Close the statement
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c63ff;
            --secondary: #ff6584;
            --accent: #42b6ff;
            --dark: #2d3748;
            --light: #f7fafc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .logo-container {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
        }
        
        .logo-container img {
            height: 60px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            transition: all 0.3s ease;
        }
        
        .logo-container img:hover {
            transform: scale(1.05);
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .login-container:hover {
            transform: translateY(-5px) scale(1.005);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
        }
        
        .form-title {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            position: relative;
        }
        
        .form-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 2px;
        }
        
        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            background: rgba(247, 250, 252, 0.8);
        }
        
        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
            background: white;
        }
        
        .login-btn {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            background-size: 200% 200%;
            transition: all 0.5s ease;
            box-shadow: 0 4px 6px rgba(108, 99, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(108, 99, 255, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.3),
                rgba(255, 255, 255, 0)
            );
            transform: rotate(45deg);
            transition: all 0.5s ease;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .signup-link {
            color: var(--dark);
            transition: all 0.3s ease;
        }
        
        .signup-link:hover {
            color: var(--primary);
            text-shadow: 0 0 5px rgba(108, 99, 255, 0.2);
        }
        
        .floating-bg {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            z-index: -1;
        }
        
        .bg-1 {
            background: var(--primary);
            top: -100px;
            right: -100px;
            animation: float 8s ease-in-out infinite;
        }
        
        .bg-2 {
            background: var(--secondary);
            bottom: -100px;
            left: -100px;
            animation: float 10s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(20px, 30px) rotate(5deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }
    </style>
</head>
<body class="flex items-center justify-center">
    <div class="logo-container">
        <img src="cropped-logo.png" alt="Company Logo">
    </div>
    
    <div class="floating-bg bg-1"></div>
    <div class="floating-bg bg-2"></div>
  
    <div class="login-container p-8 w-96">
        <h2 class="text-3xl font-bold text-center mb-8 form-title">Welcome Back</h2>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" placeholder="Enter your email" 
                    class="w-full px-4 py-3 rounded-lg input-field focus:outline-none focus:ring-0" required>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" name="password" placeholder="Enter your password" 
                    class="w-full px-4 py-3 rounded-lg input-field focus:outline-none focus:ring-0" required>
            </div>

            <button type="submit" 
                class="w-full py-3 rounded-lg text-white font-bold login-btn">
                Login
            </button>
        </form>

        <p class="mt-6 text-center">
            <span class="text-gray-600">Don't have an account?</span>
            <a href="signup.php" class="font-medium signup-link ml-2">Sign up</a>
        </p>
    </div>
</body>
</html>