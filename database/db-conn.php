<?php
$conn = mysqli_connect("localhost", "root", "", "nepbay", "3306");

if($conn == false){
    echo "Connection Failed";
    exit();
}
?>