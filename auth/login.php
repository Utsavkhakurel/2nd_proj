//<?php
//session_start();
//if(isset($_SESSION['user_id'])) {
  //  header("Location: ../client/client-dash.php");
    //exit();
//}

//$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | NepBay</title>
    <link rel="stylesheet" href="/Nepbay/assets/main.css">
    <link rel="stylesheet" href="/Nepbay/assets/auth.css">

</head>
<body>
    <div class="header">
        <div class="container">
            <h1>NepBay</h1>
        </div>
    </div>

    <div class="container">
        <div class="auth-container">
            <h1>Login</h1>
            <?php if($error): ?>
                <div class="alert" style="color: var(--primary-red);"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form action="login-handle.php" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-red">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>