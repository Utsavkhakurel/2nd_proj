<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../../../public/auth/login.php");
    exit();
}

include "../../database/db-conn.php";

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $start_price = (float)$_POST['start_price'];
    $end_time = $_POST['end_time'];
    $category_id = (int)$_POST['category_id'];
    
    // Handle file upload
    $image_path = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $upload_dir = '../../../assets/images/products/';
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target_path)){
            $image_path = 'assets/images/products/' . $file_name;
        } else {
            $error = "Failed to upload image";
        }
    } else {
        $error = "Image is required";
    }

    if(empty($error)){
        $sql = "INSERT INTO products (seller_id, title, description, start_price, current_price, end_time, image_path, category_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $current_price = $start_price; // Initial current price
        mysqli_stmt_bind_param($stmt, 'issdssi', $_SESSION['user_id'], $title, $description, $start_price, $current_price, $end_time, $image_path, $category_id);
        
        if(mysqli_stmt_execute($stmt)){
            header("Location: ../manage.php?success=Listing created successfully");
            exit();
        } else {
            $error = "Failed to create listing";
        }
    }
}

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Listing | NepBay</title>
    <link rel="stylesheet" href="../../../assets/main.css">
    <link rel="stylesheet" href="../../../assets/client.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>NepBay</h1>
            <nav>
                <a href="../client-dash.php">Dashboard</a>
                <a href="create.php">Create Listing</a>
                <a href="../../public/auth/logout.php">Logout</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Create New Listing</h2>
        <?php if($error): ?>
            <div class="alert" style="color: var(--primary-red);"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" class="listing-form">
            <div class="form-group">
                <label>Product Title</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Starting Price (₹)</label>
                    <input type="number" name="start_price" min="1" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>End Time</label>
                    <input type="datetime-local" name="end_time" required>
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <?php while($category = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn btn-red">Create Listing</button>
        </form>
    </div>
</body>
</html>