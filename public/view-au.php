<?php
session_start();
if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

include "../database/db-conn.php";

$auction_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'] ?? null;

// Get auction details
$sql = "SELECT p.*, u.name AS seller_name, c.name AS category_name 
        FROM products p 
        JOIN users u ON p.seller_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $auction_id);
mysqli_stmt_execute($stmt);
$auction = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($auction) != 1){
    header("Location: index.php");
    exit();
}

$auction = mysqli_fetch_assoc($auction);

// Get bids
$sql = "SELECT b.*, u.name AS bidder_name 
        FROM bids b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.product_id = ? 
        ORDER BY b.amount DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $auction_id);
mysqli_stmt_execute($stmt);
$bids = mysqli_stmt_get_result($stmt);

// Handle bid placement
$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_bid'])){
    if(!$user_id){
        header("Location: ../auth/login.php");
        exit();
    }
    
    $bid_amount = (float)$_POST['bid_amount'];
    
    if($bid_amount <= $auction['current_price']){
        $error = "Bid must be higher than current price";
    } elseif($user_id == $auction['seller_id']){
        $error = "You cannot bid on your own auction";
    } else {
        // Update product price
        $sql = "UPDATE products SET current_price = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'di', $bid_amount, $auction_id);
        mysqli_stmt_execute($stmt);
        
        // Record bid
        $sql = "INSERT INTO bids (product_id, user_id, amount) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iid', $auction_id, $user_id, $bid_amount);
        mysqli_stmt_execute($stmt);
        
        // Refresh auction data
        $sql = "SELECT current_price FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $auction_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $auction = array_merge($auction, mysqli_fetch_assoc($result));
        
        header("Location: view-au.php?id=$auction_id&success=Bid placed successfully");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($auction['title']) ?> | NepBay</title>
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/client.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>NepBay</h1>
            <nav>
                <a href="../index.php">Home</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="../client/client-dash.php">Dashboard</a>
                    <a href="../auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="../auth/login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="auction-container">
            <div class="auction-images">
                <img src="../<?= $auction['image_path'] ?>" alt="<?= htmlspecialchars($auction['title']) ?>">
            </div>
            
            <div class="auction-details">
                <h1><?= htmlspecialchars($auction['title']) ?></h1>
                <p class="category">Category: <?= htmlspecialchars($auction['category_name']) ?></p>
                <p class="seller">Seller: <?= htmlspecialchars($auction['seller_name']) ?></p>
                
                <div class="price-section">
                    <div class="current-price">
                        <span>Current Price:</span>
                        <span class="price">₹<?= number_format($auction['current_price'], 2) ?></span>
                    </div>
                    
                    <?php if($auction['status'] == 'active'): ?>
                        <div class="time-left">
                            <span>Time Left:</span>
                            <span class="time">
                                <?php
                                $end = strtotime($auction['end_time']);
                                $now = time();
                                $diff = $end - $now;
                                
                                if($diff > 0){
                                    $days = floor($diff / (60 * 60 * 24));
                                    $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
                                    $minutes = floor(($diff % (60 * 60)) / 60);
                                    echo "$days days $hours hours $minutes minutes";
                                } else {
                                    echo "Auction ended";
                                }
                                ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="status-badge <?= $auction['status'] ?>">
                            <?= ucfirst($auction['status']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($auction['description'])) ?></p>
                </div>
                
                <?php if($auction['status'] == 'active'): ?>
                    <div class="bid-section">
                        <?php if($error): ?>
                            <div class="alert" style="color: var(--primary-red);"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($_GET['success'])): ?>
                            <div class="alert" style="color: var(--primary-blue);"><?= htmlspecialchars($_GET['success']) ?></div>
                        <?php endif; ?>
                        
                        <?php if($user_id && $user_id != $auction['seller_id']): ?>
                            <form method="POST" action="">
                                <label for="bid_amount">Enter your bid (₹)</label>
                                <input type="number" id="bid_amount" name="bid_amount" 
                                       min="<?= $auction['current_price'] + 1 ?>" 
                                       step="0.01" 
                                       value="<?= $auction['current_price'] + 1 ?>" 
                                       required>
                                <button type="submit" name="place_bid" class="btn btn-red">Place Bid</button>
                            </form>
                        <?php elseif(!$user_id): ?>
                            <p><a href="../auth/login.php">Log in</a> to place a bid</p>
                        <?php elseif($user_id == $auction['seller_id']): ?>
                            <p>You cannot bid on your own auction</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bid-history">
            <h2>Bid History</h2>
            <?php if(mysqli_num_rows($bids) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bidder</th>
                            <th>Amount</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($bid = mysqli_fetch_assoc($bids)): ?>
                            <tr>
                                <td><?= htmlspecialchars($bid['bidder_name']) ?></td>
                                <td>₹<?= number_format($bid['amount'], 2) ?></td>
                                <td><?= date('M d, Y H:i', strtotime($bid['bid_time'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bids yet. Be the first to bid!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>