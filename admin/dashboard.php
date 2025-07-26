<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../../public/auth/login.php');
    exit();
}

include '../../database/db-conn.php';

// Get pending listings count
$query = "SELECT COUNT(*) AS count FROM products WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
$pending_count = mysqli_fetch_assoc($result)['count'];

// Get active listings count
$query = "SELECT COUNT(*) AS count FROM products WHERE status = 'active'";
$result = mysqli_query($conn, $query);
$active_count = mysqli_fetch_assoc($result)['count'];

// Get total users count
$query = "SELECT COUNT(*) AS count FROM users";
$result = mysqli_query($conn, $query);
$users_count = mysqli_fetch_assoc($result)['count'];

// Get recent pending listings
$query = "SELECT p.id, p.title, p.created_at, u.name AS seller_name 
          FROM products p 
          JOIN users u ON p.seller_id = u.id 
          WHERE p.status = 'pending' 
          ORDER BY p.created_at DESC 
          LIMIT 5";
$pending_listings = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | NepBay</title>
    <link rel="stylesheet" href="../../assets/admin.css">
</head>
<body>
    <?php include '../../includes/admin-header.php'; ?>
    
    <main class="container">
        <h2>Admin Dashboard</h2>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Pending Listings</h3>
                <p class="count"><?php echo $pending_count; ?></p>
                <a href="request.php" class="btn">View Requests</a>
            </div>
            
            <div class="stat-card">
                <h3>Active Listings</h3>
                <p class="count"><?php echo $active_count; ?></p>
                <a href="listings.php" class="btn">Manage Listings</a>
            </div>
            
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="count"><?php echo $users_count; ?></p>
                <a href="users.php" class="btn">Manage Users</a>
            </div>
        </div>
        
        <div class="recent-pending">
            <h3>Recent Pending Listings</h3>
            <?php if (mysqli_num_rows($pending_listings) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Seller</th>
                            <th>Date Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($listing = mysqli_fetch_assoc($pending_listings)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                <td><?php echo htmlspecialchars($listing['seller_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($listing['created_at'])); ?></td>
                                <td>
                                    <a href="request.php?action=approve&id=<?php echo $listing['id']; ?>" class="btn approve">Approve</a>
                                    <a href="request.php?action=reject&id=<?php echo $listing['id']; ?>" class="btn reject">Reject</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending listings at this time.</p>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>