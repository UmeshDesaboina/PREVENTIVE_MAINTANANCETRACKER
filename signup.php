<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Signup Successful!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <form class="bg-white p-8 rounded shadow-md w-96" method="POST">
        <h2 class="text-xl font-bold mb-4">Signup</h2>
        <input type="text" name="name" placeholder="Name" class="w-full border p-2 mb-3" required>
        <input type="email" name="email" placeholder="Email" class="w-full border p-2 mb-3" required>
        <input type="password" name="password" placeholder="Password" class="w-full border p-2 mb-3" required>
        <select name="role" class="w-full border p-2 mb-3">
            <option value="user">User</option>
            <option value="technician">Technician</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Signup</button>
    </form>
</body>
</html>
