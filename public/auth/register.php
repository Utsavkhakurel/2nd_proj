<?php
session_start();
if(isset($_SESSION['user_id'])){
    header('Location: ../../client/client-dash.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register | NepBay</title>
    <link rel="stylesheet" href="../../assets/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Create Account</h1>
        <?php if(isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <form action="register-handle.php" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>