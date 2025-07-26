<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../public/auth/login.php');
    exit();
}

include '../../database/db-conn.php';

$user_id = $_SESSION['user_id'];

// Fetch user's bid history
$query = "SELECT b.*, p.title, p.image_path, p.end_time, p.status, u.name AS seller_name
          FROM bids b
          JOIN products p ON b.product_id = p.id
          JOIN users u ON p.seller_id = u.id
          WHERE b.user_id = ?
          ORDER BY b.bid_time DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$bids = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bid History | NepBay</title>
    <link rel="stylesheet" href="../../../assets/main.css">
</head>
<body>
    <?php include '../../../includes/header.php'; ?>
    
    <main class="container">
        <h2>Your Bid History</h2>
        
        <?php if (mysqli_num_rows($bids) > 0): ?>
            <div class="bid-history">
                <?php while ($bid = mysqli_fetch_assoc($bids)): ?>
                    <div class="bid-item">
                        <div class="product-image">
                            <img src="../../../<?php echo htmlspecialchars($bid['image_path']); ?>" alt="<?php echo htmlspecialchars($bid['title']); ?>">
                        </div>
                        <div class="bid-details">
                            <h3><a href="../../../public/view-au.php?id=<?php echo $bid['product_id']; ?>"><?php echo htmlspecialchars($bid['title']); ?></a></h3>
                            <p>Seller: <?php echo htmlspecialchars($bid['seller_name']); ?></p>
                            <p>Your Bid: ₹<?php echo number_format($bid['amount'], 2); ?></p>
                            <p>Bid Time: <?php echo date('M d, Y H:i', strtotime($bid['bid_time'])); ?></p>
                            <p>Status: <span class="status <?php echo $bid['status']; ?>"><?php echo ucfirst($bid['status']); ?></span></p>
                            <p>Ends: <?php echo date('M d, Y H:i', strtotime($bid['end_time'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>You haven't placed any bids yet. <a href="../../../index.php">Browse auctions</a> to get started!</p>
        <?php endif; ?>
    </main>
    
    <?php include '../../../includes/footer.php'; ?>
</body>
</html>