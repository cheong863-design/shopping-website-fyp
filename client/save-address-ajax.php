<?php
include 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['receiver_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $addr = mysqli_real_escape_string($conn, $_POST['address_line']);

    $sql = "INSERT INTO user_addresses (user_id, receiver_name, phone, address_line, is_default)
            VALUES ('$user_id', '$name', '$phone', '$addr', 1)";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    }
}
?>
