<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])){
    $upload_dir = '../../assets/images/products/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $file = $_FILES['image'];
    
    // Validate file
    if(!in_array($file['type'], $allowed_types)){
        echo json_encode(['error' => 'Only JPG, PNG, and GIF images are allowed']);
        exit();
    }
    
    if($file['size'] > $max_size){
        echo json_encode(['error' => 'File size exceeds 2MB limit']);
        exit();
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $target_path = $upload_dir . $filename;
    
    if(move_uploaded_file($file['tmp_name'], $target_path)){
        echo json_encode([
            'success' => true,
            'path' => 'assets/images/products/' . $filename
        ]);
    } else {
        echo json_encode(['error' => 'Failed to upload file']);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
}
?>