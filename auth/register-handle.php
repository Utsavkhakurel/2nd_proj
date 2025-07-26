<?php
session_start();
if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: register.php");
    exit();
}

include "../../database/db-conn.php";

// Get form data
$name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
$email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
$errors = [];

if(empty($name)) {
    $errors[] = "Name is required";
}

if(empty($email)) {
    $errors[] = "Email is required";
} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

if(empty($password)) {
    $errors[] = "Password is required";
} elseif(strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
}

if($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Check if email exists
if(empty($errors)) {
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) > 0) {
        $errors[] = "Email already registered";
    }
}

// Handle errors or create user
if(!empty($errors)) {
    $error_string = implode("|", $errors);
    header("Location: register.php?error=" . urlencode($error_string));
    exit();
}

// Hash password and create user
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashed_password);

if(mysqli_stmt_execute($stmt)) {
    $user_id = mysqli_insert_id($conn);
    
    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user';
    
    header("Location: ../../client/client-dash.php");
    exit();
} else {
    header("Location: register.php?error=Registration failed. Please try again.");
    exit();
}
?>