<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../../public/auth/login.php');
    exit();
}

include '../../database/db-conn.php';

// Handle approval actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $product_id = $_GET['id'];
    
    // Verify product exists and is pending
    $query = "SELECT id FROM products WHERE id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $new_status = ($action == 'approve') ? 'active' : 'rejected';
        
        $update_query = "UPDATE products SET status = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $product_id);
        mysqli_stmt_execute($update_stmt);
        
        $message = ($action == 'approve') ? 'Listing approved successfully' : 'Listing rejected';
        header("Location: request.php?success=$message");
        exit();
    } else {
        header('Location: request.php?error=Invalid request');
        exit();
    }
}

// Fetch pending listings
$query = "SELECT p.*, u.name AS seller_name, c.name AS category_name 
          FROM products p 
          JOIN users u ON p.seller_id = u.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'pending' 
          ORDER BY p.created_at DESC";
$pending_listings = mysqli_query($conn, $query);

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Requests | NepBay</title>
    <link rel="stylesheet" href="../../assets/admin.css">
</head>
<body>
    <?php include '../../includes/admin-header.php'; ?>
    
    <main class="container">
        <h2>Listing Approval Requests</h2>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (mysqli_num_rows($pending_listings) > 0): ?>
            <div class="pending-listings">
                <?php while ($listing = mysqli_fetch_assoc($pending_listings)): ?>
                    <div class="listing-card">
                        <div class="listing-image">
                            <img src="../../../<?php echo htmlspecialchars($listing['image_path']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                        </div>
                        <div class="listing-details">
                            <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
                            <p><strong>Seller:</strong> <?php echo htmlspecialchars($listing['seller_name']); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['category_name']); ?></p>
                            <p><strong>Starting Price:</strong> ₹<?php echo number_format($listing['start_price'], 2); ?></p>
                            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                            <p><strong>End Time:</strong> <?php echo date('M d, Y H:i', strtotime($listing['end_time'])); ?></p>
                            
                            <div class="actions">
                                <a href="request.php?action=approve&id=<?php echo $listing['id']; ?>" class="btn approve">Approve</a>
                                <a href="request.php?action=reject&id=<?php echo $listing['id']; ?>" class="btn reject">Reject</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No pending listing requests at this time.</p>
        <?php endif; ?>
    </main>
    
    
</body>
</html>