<?php
$host = "sql300.infinityfree.com"; 
$user = "if0_42322855";        
$pass = "06591507851aA"; 
$db_name = "if0_42322855_adidas_store"; 

$conn = mysqli_connect($host, $user, $pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
