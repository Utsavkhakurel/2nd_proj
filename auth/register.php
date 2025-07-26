
<?php
//session_start();
//if (isset($_SESSION['user_id'])) {
  //  header("Location: ../auth/login.php");
    //exit();
//}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register | NepBay</title>
    <link rel="stylesheet" href="/Nepbay/assets/main.css">
    <link rel="stylesheet" href="/Nepbay/assets/auth.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert" style="color: var(--primary-red);">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert" style="color: green;">
                    Registration successful! Please log in.
                </div>
            <?php endif; ?>

            <form action="register-handle.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-red">Register</button>
            </form>
            
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>