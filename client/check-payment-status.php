<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$addr_res = mysqli_query($conn, "SELECT id FROM user_addresses WHERE user_id = '$user_id' LIMIT 1");
if (mysqli_num_rows($addr_res) == 0) {
    header("Location: cart.php?error=no_address");
    exit();
}

$pay_res = mysqli_query($conn, "SELECT id FROM user_payments WHERE user_id = '$user_id' LIMIT 1");
if (mysqli_num_rows($pay_res) > 0) {
    header("Location: process-order.php");
} else {
    header("Location: cart.php?error=no_payment");
}
exit();
?>
