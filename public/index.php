<?php
session_start();
// Include database connection
include __DIR__ . '/../config/db-conn.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | NepBay</title>
    <link rel="stylesheet" href="/Nepbay/assets/main.css">
    <link rel="stylesheet" href="/Nepbay/assets/client.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</h1>
            <div class="actions">
                <a href="/Nepbay/client/listings/create.php" class="btn btn-blue">Create New Listing</a>
                <a href="/Nepbay/auth/logout.php" class="btn btn-red">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h2>Your Listings</h2>

        <?php
        // Prepare and execute query
        $query = "SELECT * FROM listings WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $listings = mysqli_stmt_get_result($stmt);

        // Display listings
        if (mysqli_num_rows($listings) > 0): ?>
            <?php while($listing = mysqli_fetch_assoc($listings)): ?>
                <div class="listing-card">
                    <h3><?= htmlspecialchars($listing['title']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have no listings yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
