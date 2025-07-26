<?php
session_start();
include '../database/db-conn.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$auction_id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Fetch auction details
$query = "SELECT p.*, u.name AS seller_name, c.name AS category_name 
          FROM products p 
          JOIN users u ON p.seller_id = u.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $auction_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$auction = mysqli_fetch_assoc($result);

if (!$auction) {
    header('Location: index.php');
    exit();
}

// Fetch bids for this auction
$query = "SELECT b.*, u.name AS bidder_name 
          FROM bids b 
          JOIN users u ON b.user_id = u.id 
          WHERE b.product_id = ? 
          ORDER BY b.amount DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $auction_id);
mysqli_stmt_execute($stmt);
$bids = mysqli_stmt_get_result($stmt);

// Handle bid submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_bid'])) {
    if (!$user_id) {
        header('Location: ../auth/login.php');
        exit();
    }
    
    $bid_amount = (float)$_POST['bid_amount'];
    
    // Validate bid
    if ($bid_amount <= $auction['current_price']) {
        $error = "Bid must be higher than current price";
    } else {
        // Update product current price
        $update_query = "UPDATE products SET current_price = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'di', $bid_amount, $auction_id);
        mysqli_stmt_execute($update_stmt);
        
        // Create new bid
        $insert_query = "INSERT INTO bids (product_id, user_id, amount) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'iid', $auction_id, $user_id, $bid_amount);
        mysqli_stmt_execute($insert_stmt);
        
        // Refresh auction data
        $stmt = mysqli_prepare($conn, "SELECT current_price FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $auction_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $auction = array_merge($auction, mysqli_fetch_assoc($result));
        
        // Refresh bids
        $stmt = mysqli_prepare($conn, "SELECT b.*, u.name AS bidder_name 
                                      FROM bids b 
                                      JOIN users u ON b.user_id = u.id 
                                      WHERE b.product_id = ? 
                                      ORDER BY b.amount DESC");
        mysqli_stmt_bind_param($stmt, 'i', $auction_id);
        mysqli_stmt_execute($stmt);
        $bids = mysqli_stmt_get_result($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($auction['title']); ?> | NepBay</title>
    <link rel="stylesheet" href="../assets/main.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="auction-details">
            <div class="auction-images">
                <img src="../<?php echo htmlspecialchars($auction['image_path']); ?>" alt="<?php echo htmlspecialchars($auction['title']); ?>">
            </div>
            
            <div class="auction-info">
                <h1><?php echo htmlspecialchars($auction['title']); ?></h1>
                <p class="category">Category: <?php echo htmlspecialchars($auction['category_name']); ?></p>
                <p class="seller">Seller: <?php echo htmlspecialchars($auction['seller_name']); ?></p>
                
                <div class="price-section">
                    <div class="current-price">
                        <span>Current Price:</span>
                        <span class="price">₹<?php echo number_format($auction['current_price'], 2); ?></span>
                    </div>
                    
                    <?php if ($auction['status'] == 'active'): ?>
                        <div class="time-left">
                            <span>Time Left:</span>
                            <span class="time" id="countdown"><?php 
                                $end_time = strtotime($auction['end_time']);
                                $now = time();
                                $diff = $end_time - $now;
                                
                                if ($diff > 0) {
                                    $days = floor($diff / (60 * 60 * 24));
                                    $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
                                    $minutes = floor(($diff % (60 * 60)) / 60);
                                    echo $days . "d " . $hours . "h " . $minutes . "m";
                                } else {
                                    echo "Auction ended";
                                }
                            ?></span>
                        </div>
                    <?php else: ?>
                        <div class="status-badge <?php echo $auction['status']; ?>">
                            <?php echo ucfirst($auction['status']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($auction['description'])); ?></p>
                </div>
                
                <?php if ($auction['status'] == 'active'): ?>
                    <div class="bid-section">
                        <?php if ($error): ?>
                            <div class="alert error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($user_id && $user_id != $auction['seller_id']): ?>
                            <form method="POST" action="">
                                <label for="bid_amount">Enter your bid (₹)</label>
                                <input type="number" id="bid_amount" name="bid_amount" 
                                       min="<?php echo $auction['current_price'] + 1; ?>" 
                                       step="0.01" 
                                       value="<?php echo $auction['current_price'] + 1; ?>" 
                                       required>
                                <button type="submit" name="place_bid" class="btn">Place Bid</button>
                            </form>
                        <?php elseif (!$user_id): ?>
                            <p><a href="../auth/login.php">Log in</a> to place a bid</p>
                        <?php elseif ($user_id == $auction['seller_id']): ?>
                            <p>You cannot bid on your own auction</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bid-history">
            <h2>Bid History</h2>
            <?php if (mysqli_num_rows($bids) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bidder</th>
                            <th>Amount</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($bid = mysqli_fetch_assoc($bids)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bid['bidder_name']); ?></td>
                                <td>₹<?php echo number_format($bid['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($bid['bid_time'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bids yet. Be the first to bid!</p>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Countdown timer
        <?php if ($auction['status'] == 'active' && $diff > 0): ?>
            const endTime = <?php echo $end_time; ?> * 1000; // Convert to milliseconds
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = endTime - now;
                
                if (distance <= 0) {
                    document.getElementById("countdown").textContent = "Auction ended";
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                
                document.getElementById("countdown").textContent = 
                    days + "d " + hours + "h " + minutes + "m";
            }
            
            // Update every minute
            updateCountdown();
            setInterval(updateCountdown, 60000);
        <?php endif; ?>
    </script>
</body>
</html>