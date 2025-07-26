<?php
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: ../../client/client-dash.php");
    exit();
}

include "../../database/db-conn.php";

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if(mysqli_stmt_num_rows($stmt) > 0){
        $error = "Email already registered";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $password);
        
        if(mysqli_stmt_execute($stmt)){
            $user_id = mysqli_insert_id($conn);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';
            header("Location: ../../client/client-dash.php");
            exit();
        } else {
            $error = "Registration failed";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register | NepBay</title>
    <link rel="stylesheet" href="../../assets/main.css">
    <link rel="stylesheet" href="../../assets/auth.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>NepBay</h1>
        </div>
    </div>

    <div class="container">
        <div class="auth-container">
            <h1>Create Account</h1>
            <?php if($error): ?>
                <div class="alert" style="color: var(--primary-red);"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-red">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>