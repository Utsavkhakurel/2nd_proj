<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

include "../database/db-conn.php";

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (empty($name) || empty($email) || empty($password)) {
    header("Location: register.php?error=All fields are required");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=Invalid email format");
    exit();
}

// Check if email already exists
$query = "SELECT id FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    header("Location: register.php?error=Email already registered");
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit();
}
mysqli_stmt_close($stmt);

// Insert new user
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['user_id'] = mysqli_insert_id($conn);
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user';

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    header("Location: ../client/client-dash.php");
    exit();
} else {
    $error = urlencode("Registration failed: " . mysqli_error($conn));
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: register.php?error=$error");
    exit();
}
