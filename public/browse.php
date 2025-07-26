<?php
session_start();
include "../database/db-conn.php";

// Get all active listings
$listings = mysqli_query($conn, "SELECT p.*, u.name AS seller_name, c.name AS category_name 
                                FROM products p 
                                JOIN users u ON p.seller_id = u.id 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                WHERE p.status = 'active' 
                                ORDER BY p.end_time ASC");

// Get categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Listings | NepBay</title>
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/client.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>NepBay</h1>
            <nav>
                <a href="index.php">Home</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="client/client-dash.php">Dashboard</a>
                    <a href="auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Browse Listings</h2>
        
        <div class="filters">
            <form method="GET" action="">
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="q" placeholder="Search listings...">
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-blue">Filter</button>
            </form>
        </div>
        
        <?php if(mysqli_num_rows($listings) > 0): ?>
            <div class="listing-grid">
                <?php while($item = mysqli_fetch_assoc($listings)): ?>
                    <div class="listing-card">
                        <img src="../<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <h4><?= htmlspecialchars($item['title']) ?></h4>
                        <p>Category: <?= htmlspecialchars($item['category_name']) ?></p>
                        <p>Price: ₹<?= number_format($item['current_price'], 2) ?></p>
                        <p>Ends: <?= date('M d, Y H:i', strtotime($item['end_time'])) ?></p>
                        <a href="view-au.php?id=<?= $item['id'] ?>" class="btn btn-red">Place Bid</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No listings found matching your criteria.</p>
        <?php endif; ?>
    </div>
</body>
</html>