<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$cart = $_SESSION['cart'];

// ==========================================
// ==========================================
mysqli_begin_transaction($conn);

try {
    $subtotal = 0;
    $items_data = [];

    foreach ($cart as $id => $qty) {
        $p_id = intval($id);
        $p_qty = intval($qty);

        $res = mysqli_query($conn, "SELECT name, price, stock FROM products WHERE id = $p_id FOR UPDATE");
        $product = mysqli_fetch_assoc($res);

        if (!$product) {
            throw new Exception("ARTIFACT_MISSING: Item ID $p_id not found in archive.");
        }

        if ($product['stock'] < $p_qty) {
            throw new Exception("INSUFFICIENT_STOCK: Item '{$product['name']}' is unavailable in the requested quantity.");
        }

        $subtotal += ($product['price'] * $p_qty);
        $items_data[] = [
            'id' => $p_id,
            'qty' => $p_qty,
            'price' => (float)$product['price']
        ];
    }

    $shipping_fee_from_post = isset($_POST['shipping_fee']) ? floatval($_POST['shipping_fee']) : 0;
    $calculated_shipping = ($subtotal >= 500) ? 0 : 15.00;

    $final_shipping = max($shipping_fee_from_post, $calculated_shipping);

    $discount_value = 0;
    $applied_coupon = isset($_SESSION['applied_coupon']) ? mysqli_real_escape_string($conn, $_SESSION['applied_coupon']) : '';

    if (!empty($applied_coupon)) {
        $c_res = mysqli_query($conn, "SELECT * FROM coupons WHERE code = '$applied_coupon'");
        $c_row = mysqli_fetch_assoc($c_res);

        if ($c_row) {
            $coupon_id = intval($c_row['id']);

            $usage_res = mysqli_query($conn, "SELECT used_count FROM coupon_usage WHERE coupon_id = $coupon_id AND user_id = $user_id LIMIT 1");
            $u_row = mysqli_fetch_assoc($usage_res);
            $used_count = $u_row ? intval($u_row['used_count']) : 0;

            if ($c_row['usage_limit'] === null || $used_count < intval($c_row['usage_limit'])) {
                if ($c_row['discount_type'] == 'percentage') {
                    $discount_value = $subtotal * (floatval($c_row['discount_value']) / 100);
                } else {
                    $discount_value = floatval($c_row['discount_value']);
                }
                $discount_value = min($discount_value, $subtotal);

                mysqli_query($conn, "INSERT INTO coupon_usage (coupon_id, user_id, used_count) VALUES ($coupon_id, $user_id, 1) ON DUPLICATE KEY UPDATE used_count = used_count + 1");
                mysqli_query($conn, "UPDATE coupons SET used_count = used_count + 1 WHERE id = $coupon_id");
            }
        }
        unset($_SESSION['applied_coupon']);
    }

    $discounted_subtotal = $subtotal - $discount_value;
    $tax_rate = 0.06; // 6% SST
    $total_amount = ($discounted_subtotal + $final_shipping) * (1 + $tax_rate);

    $sql_order = "INSERT INTO orders (user_id, total_price, shipping_fee, tax_amount, status, created_at)
                  VALUES ('$user_id', '$total_amount', '$final_shipping', " . ($total_amount - $discounted_subtotal - $final_shipping) . ", 'Paid', NOW())";

    if (!mysqli_query($conn, $sql_order)) {
        throw new Exception("ORDER_MASTER_FAILURE: " . mysqli_error($conn));
    }

    $order_id = mysqli_insert_id($conn);

    foreach ($items_data as $item) {
        $p_id = $item['id'];
        $p_qty = $item['qty'];
        $p_price = $item['price'];

        $update_stock = "UPDATE products SET stock = stock - $p_qty WHERE id = $p_id AND stock >= $p_qty";
        mysqli_query($conn, $update_stock);

        if (mysqli_affected_rows($conn) <= 0) {
            throw new Exception("STOCK_SYNC_ERROR: Item $p_id was acquired by another entity during authorization.");
        }

        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price)
                             VALUES ($order_id, $p_id, $p_qty, $p_price)");
    }

    // ==========================================
    // ==========================================
    mysqli_commit($conn);

    unset($_SESSION['cart']);
    header("Location: order-success.php?id=" . $order_id);
    exit();

} catch (Exception $e) {
    // ==========================================
    // ==========================================
    mysqli_rollback($conn);

    $_SESSION['checkout_error'] = $e->getMessage();
    header("Location: cart.php?error=authorization_failed");
    exit();
}
?>
