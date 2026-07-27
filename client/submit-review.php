<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $rating     = intval($_POST['rating']);
    $comment    = mysqli_real_escape_string($conn, $_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $rating = 5;
    }

    $check_query = "SELECT id FROM product_reviews WHERE product_id = $product_id AND user_id = $user_id LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        header("Location: product-details.php?id=$product_id&error=already_reviewed#reviews-section");
        exit();
    }

    $sql = "INSERT INTO product_reviews (product_id, user_id, rating, comment, status, created_at)
            VALUES ($product_id, $user_id, $rating, '$comment', 'Approved', NOW())";

    if (mysqli_query($conn, $sql)) {
        header("Location: product-details.php?id=$product_id&msg=review_success#reviews-section");
        exit();
    } else {
        die("Error submitting review: " . mysqli_error($conn));
    }
} else {
    header("Location: index.php");
    exit();
}
