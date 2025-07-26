<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../public/auth/login.php');
    exit();
}

include '../../database/db-conn.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_price = $_POST['start_price'];
    $end_time = $_POST['end_time'];
    $category_id = $_POST['category_id'];
    $seller_id = $_SESSION['user_id'];

    // Handle file upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../../../assets/images/';
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = 'assets/images/' . $file_name;
        } else {
            $error = "Failed to upload image";
        }
    } else {
        $error = "Image upload error";
    }

    if (empty($error)) {
        $query = "INSERT INTO products (seller_id, title, description, start_price, current_price, end_time, image_path, category_id, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = mysqli_prepare($conn, $query);
        $current_price = $start_price; // Set initial current price
        
        mysqli_stmt_bind_param(
            $stmt, 
            'issddssi', 
            $seller_id, 
            $title, 
            $description, 
            $start_price, 
            $current_price, 
            $end_time, 
            $image_path, 
            $category_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Listing created successfully! Waiting for admin approval.";
        } else {
            $error = "Failed to create listing: " . mysqli_error($conn);
        }
    }
}

// Fetch categories
$categories = [];
$cat_query = "SELECT id, name FROM categories";
$cat_result = mysqli_query($conn, $cat_query);
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing | NepBay</title>
    <link rel="stylesheet" href="../../../assets/main.css">
</head>
<body>
    <?php include '../../../includes/header.php'; ?>
    
    <main class="container">
        <h2>Create New Listing</h2>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data" class="listing-form">
            <div class="form-group">
                <label for="title">Product Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_price">Starting Price (₹)</label>
                    <input type="number" id="start_price" name="start_price" min="1" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="end_time">Auction End Time</label>
                    <input type="datetime-local" id="end_time" name="end_time" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Only JPG, PNG images (max 2MB)</small>
            </div>
            
            <button type="submit" class="btn">Create Listing</button>
        </form>
    </main>
    
    
</body>
</html>