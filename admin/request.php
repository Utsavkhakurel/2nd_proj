<?php
//session_start();
//if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin'){
   // header("Location: ../../public/auth/login.php");
   // exit();
//}

include __DIR__ . '/../database/db-conn.php';

// Handle approval/rejection
if(isset($_GET['approve']) || isset($_GET['reject'])){
    $product_id = (int)($_GET['approve'] ?? $_GET['reject']);
    $status = isset($_GET['approve']) ? 'active' : 'rejected';
    
    $sql = "UPDATE products SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status, $product_id);
    mysqli_stmt_execute($stmt);
    
    header("Location: request.php?success=Listing $status");
    exit();
}

// Get pending listings
$listings = mysqli_query($conn, "SELECT p.*, u.name AS seller_name, c.name AS category_name 
                                FROM products p 
                                JOIN users u ON p.seller_id = u.id 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                WHERE p.status = 'pending' 
                                ORDER BY p.created_at DESC");

$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Listing Requests | NepBay</title>
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
        <h2>Listing Approval Requests</h2>
        
        <?php if($success): ?>
            <div class="alert" style="color: var(--primary-blue);">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($listings) > 0): ?>
            <div class="listing-requests">
                <?php while($listing = mysqli_fetch_assoc($listings)): ?>
                    <div class="listing-card">
                        <div class="listing-image">
                            <img src="../../<?= $listing['image_path'] ?>" alt="<?= htmlspecialchars($listing['title']) ?>">
                        </div>
                        <div class="listing-details">
                            <h3><?= htmlspecialchars($listing['title']) ?></h3>
                            <p><strong>Seller:</strong> <?= htmlspecialchars($listing['seller_name']) ?></p>
                            <p><strong>Category:</strong> <?= htmlspecialchars($listing['category_name']) ?></p>
                            <p><strong>Price:</strong> ₹<?= number_format($listing['start_price'], 2) ?></p>
                            <p><strong>Ends:</strong> <?= date('M d, Y H:i', strtotime($listing['end_time'])) ?></p>
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($listing['description'])) ?></p>
                            
                            <div class="actions">
                                <a href="request.php?approve=<?= $listing['id'] ?>" class="btn btn-blue">Approve</a>
                                <a href="request.php?reject=<?= $listing['id'] ?>" class="btn btn-red">Reject</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No pending listing requests</p>
        <?php endif; ?>
    </div>
</body>
</html>