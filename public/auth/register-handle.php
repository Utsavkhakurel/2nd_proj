<?php
if($_SERVER['REQUEST_METHOD'] !='POST'){
    header('Location: register.php');
    exit();
}

$user_name = $_POST['name'];
$user_email = $_POST['email'];
$user_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

include '../../database/db-conn.php';

$query = "SELECT id FROM users WHERE email = ?";
$mysql_stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($mysql_stmt, 's', $user_email);
mysqli_stmt_execute($mysql_stmt);
$mysqli_result = mysqli_stmt_get_result($mysql_stmt);

if(mysqli_num_rows($mysqli_result) > 0){
    header('Location: register.php?error=Email already registered');
    exit();
}

$query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
$mysql_stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($mysql_stmt, 'sss', $user_name, $user_email, $user_password);

if(mysqli_stmt_execute($mysql_stmt)){
    $new_user_id = mysqli_insert_id($conn);
    session_start();
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['user_name'] = $user_name;
    $_SESSION['user_role'] = 'user';
    header('Location: ../../client/client-dash.php');
    exit();
} else {
    header('Location: register.php?error=Registration failed');
    exit();
}
?>
?>