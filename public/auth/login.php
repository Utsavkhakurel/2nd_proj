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
    <title>Login | NepBay</title>
    <link rel="stylesheet" href="../../assets/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Login to NepBay</h1>
        <?php if(isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <form action="login-handle.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>