<?php
session_start();
if(!isset($_SESSION['is_Loggedin'])) {
    header("Location: auth/login.php");
    exit();
}

include '../config/db-conn.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | NepBay</title>
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <h1>Welcome to Your Dashboard</h1>
    
    <div class="actions">
        <a href="listings/create.php">Create New Listing</a>
        <a href="auth/logout.php">Logout</a>
    </div>

    <?php
    $query = "SELECT * FROM listings WHERE user_id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $listings = mysqli_stmt_get_result($stmt);
    
    while($listing = mysqli_fetch_assoc($listings)): ?>
    <div class="listing">
        <h3><?php echo $listing['title']; ?></h3>
        <p><?php echo $listing['description']; ?></p>
    </div>
    <?php endwhile; ?>
</body>
</html>