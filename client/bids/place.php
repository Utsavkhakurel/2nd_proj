<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../public/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['product_id'])) {
    header('Location: ../../../index.php');
    exit();
}

include '../../database/db-conn.php';

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$bid_amount = (float)$_POST['bid_amount'];

// Get current product price
$query = "SELECT current_price, seller_id FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// Validate bid
if ($bid_amount <= $product['current_price']) {
    header("Location: ../../../public/view-au.php?id=$product_id&error=Bid must be higher than current price");
    exit();
}

if ($user_id == $product['seller_id']) {
    header("Location: ../../../public/view-au.php?id=$product_id&error=You cannot bid on your own auction");
    exit();
}

// Update product current price
$update_query = "UPDATE products SET current_price = ? WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, 'di', $bid_amount, $product_id);
mysqli_stmt_execute($update_stmt);

// Create new bid
$insert_query = "INSERT INTO bids (product_id, user_id, amount) VALUES (?, ?, ?)";
$insert_stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($insert_stmt, 'iid', $product_id, $user_id, $bid_amount);
mysqli_stmt_execute($insert_stmt);

header("Location: ../../../public/view-au.php?id=$product_id&success=Bid placed successfully");
exit();
?>