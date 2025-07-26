<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../../public/auth/login.php");
    exit();
}

include "../database/db-conn.php";

// Get user's active listings
$sql = "SELECT * FROM products WHERE seller_id = ? AND status = 'active' ORDER BY end_time ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$listings = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | NepBay</title>
    <link rel="stylesheet" href="/Nepbay/assets/main.css">
<link rel="stylesheet" href="/Nepbay/assets/client.css">

</head>
<body>
    <div class="header">
        <div class="container">
            <h1>NepBay</h1>
            <nav>
                <a href="client-dash.php">Dashboard</a>
                <a href="listing/create.php">Create Listing</a>
                <a href="../../public/auth/logout.php">Logout</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
        
        <div class="card-grid">
            <div class="card">
                <h3>Create New Listing</h3>
                <p>Start selling your items</p>
                <a href="listing/create.php" class="btn btn-blue">Create</a>
            </div>
            
            <div class="card">
                <h3>My Listings</h3>
                <p>Manage your auctions</p>
                <a href="listing/manage.php" class="btn btn-blue">Manage</a>
            </div>
        </div>

        <h3>Active Listings</h3>
        <?php if(mysqli_num_rows($listings) > 0): ?>
            <div class="listing-grid">
                <?php while($listing = mysqli_fetch_assoc($listings)): ?>
                    <div class="listing-card">
                        <img src="../../<?= $listing['image_path'] ?>" alt="<?= htmlspecialchars($listing['title']) ?>">
                        <h4><?= htmlspecialchars($listing['title']) ?></h4>
                        <p>Current: ₹<?= number_format($listing['current_price'], 2) ?></p>
                        <p>Ends: <?= date('M d, Y H:i', strtotime($listing['end_time'])) ?></p>
                        <a href="../public/view-au.php?id=<?= $listing['id'] ?>" class="btn btn-red">View</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>You have no active listings.</p>
        <?php endif; ?>
    </div>
</body>
</html>