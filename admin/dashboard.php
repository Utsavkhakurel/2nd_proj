<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin'){
    header("Location: ../../public/auth/login.php");
    exit();
}

include "../../database/db-conn.php";

// Get stats
$stats = [
    'pending' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM products WHERE status = 'pending'"))['count'],
    'active' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM products WHERE status = 'active'"))['count'],
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM users"))['count']
];

// Get recent pending listings
$pending = mysqli_query($conn, "SELECT p.id, p.title, p.created_at, u.name AS seller_name 
                               FROM products p 
                               JOIN users u ON p.seller_id = u.id 
                               WHERE p.status = 'pending' 
                               ORDER BY p.created_at DESC 
                               LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | NepBay</title>
    <link rel="stylesheet" href="/Nepbay/assets/main.css">
<link rel="stylesheet" href="/Nepbay/assets/admin.css">

</head>
<body>
    <div class="header">
        <div class="container">
            <h1>NepBay Admin</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="request.php">Requests</a>
                <a href="../../public/auth/logout.php">Logout</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Dashboard Overview</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pending Listings</h3>
                <p class="count"><?= $stats['pending'] ?></p>
                <a href="request.php" class="btn btn-blue">Review</a>
            </div>
            
            <div class="stat-card">
                <h3>Active Listings</h3>
                <p class="count"><?= $stats['active'] ?></p>
                <a href="listings.php" class="btn btn-blue">Manage</a>
            </div>
            
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="count"><?= $stats['users'] ?></p>
                <a href="users.php" class="btn btn-blue">View</a>
            </div>
        </div>
        
        <h3>Recent Pending Listings</h3>
        <?php if(mysqli_num_rows($pending) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Seller</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($listing = mysqli_fetch_assoc($pending)): ?>
                        <tr>
                            <td><?= htmlspecialchars($listing['title']) ?></td>
                            <td><?= htmlspecialchars($listing['seller_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($listing['created_at'])) ?></td>
                            <td>
                                <a href="request.php?approve=<?= $listing['id'] ?>" class="btn btn-blue">Approve</a>
                                <a href="request.php?reject=<?= $listing['id'] ?>" class="btn btn-red">Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending listings</p>
        <?php endif; ?>
    </div>
</body>
</html>