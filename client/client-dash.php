<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/auth/login.php');
    exit();
}

include '../database/db-conn.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT name FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | NepBay</title>
    <link rel="stylesheet" href="../../assets/client.css">
</head>
<body>
    <header>
        <h1>NepBay</h1>
        <nav>
            <ul>
                <li><a href="client-dash.php">Dashboard</a></li>
                <li><a href="listing/create.php">Create Listing</a></li>
                <li><a href="listing/manage.php">Manage Listings</a></li>
                <li><a href="bids/history.php">Bid History</a></li>
                <li><a href="../../public/auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="welcome">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
            <p>What would you like to do today?</p>
        </section>
        
        <section class="actions">
            <div class="card">
                <h3>Create New Listing</h3>
                <p>Sell an item by listing it for auction</p>
                <a href="listing/create.php" class="btn">Create Listing</a>
            </div>
            
            <div class="card">
                <h3>Manage Listings</h3>
                <p>View and edit your existing listings</p>
                <a href="listing/manage.php" class="btn">Manage Listings</a>
            </div>
            
            <div class="card">
                <h3>View Bids</h3>
                <p>See your bidding history and status</p>
                <a href="bids/history.php" class="btn">View Bids</a>
            </div>
        </section>
        
        <section class="active-listings">
            <h3>Your Active Listings</h3>
            <?php
            $query = "SELECT id, title, start_price, end_time, image_path 
                      FROM products 
                      WHERE seller_id = ? AND status = 'active'
                      ORDER BY created_at DESC
                      LIMIT 5";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                while ($listing = mysqli_fetch_assoc($result)) {
                    echo '<div class="listing">';
                    echo '<img src="../../' . htmlspecialchars($listing['image_path']) . '" alt="' . htmlspecialchars($listing['title']) . '">';
                    echo '<div class="details">';
                    echo '<h4>' . htmlspecialchars($listing['title']) . '</h4>';
                    echo '<p>Current Price: ₹' . number_format($listing['start_price'], 2) . '</p>';
                    echo '<p>Ends: ' . date('M d, Y H:i', strtotime($listing['end_time'])) . '</p>';
                    echo '<a href="listing/manage.php?product_id=' . $listing['id'] . '" class="btn">View Details</a>';
                    echo '</div></div>';
                }
            } else {
                echo '<p>You have no active listings. <a href="listing/create.php">Create one now!</a></p>';
            }
            ?>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 NepBay. All rights reserved.</p>
    </footer>
</body>
</html>