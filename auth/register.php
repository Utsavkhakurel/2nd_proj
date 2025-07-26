<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../client/client-dash.php");
    exit();
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register | NepBay</title>
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/auth.css">
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

            <?php if (!empty($error)): ?>
                <div class="alert" style="color: red;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="register-handle.php" method="POST">
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
