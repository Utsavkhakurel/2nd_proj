<?php
if($_SERVER['REQUEST_METHOD'] !='POST'){
    header('Location: login.php');
    exit();
}

$user_email = $_POST['email'];
$user_password = $_POST['password'];

include '../../database/db-conn.php';

$query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
$mysql_stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($mysql_stmt, 's', $user_email);
mysqli_stmt_execute($mysql_stmt);
$mysqli_result = mysqli_stmt_get_result($mysql_stmt);
$user_data = mysqli_fetch_assoc($mysqli_result);

if($user_data && password_verify($user_password, $user_data['password'])){
    session_start();
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['user_name'] = $user_data['name'];
    $_SESSION['user_role'] = $user_data['role'];
    header('Location: ../../client/client-dash.php');
    exit();
} else {
    header('Location: login.php?error=Email or password is incorrect');
    exit();
}
?>