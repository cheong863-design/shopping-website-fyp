<?php
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = isset($_POST['coupon_code']) ? mysqli_real_escape_string($conn, strtoupper(trim($_POST['coupon_code']))) : '';
    $today = date('Y-m-d');

    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Coupon code cannot be empty.']);
        exit;
    }

    if (empty($user_id)) {
        echo json_encode(['success' => false, 'message' => 'Please log in to use a coupon.']);
        exit;
    }

    $sql = "SELECT * FROM coupons WHERE code = '$code' AND (user_id IS NULL OR user_id = $user_id OR user_id = 0)";
    $res = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        $coupon = mysqli_fetch_assoc($res);

        if (isset($coupon['is_used']) && $coupon['is_used'] == 1) {
            echo json_encode(['success' => false, 'message' => 'This coupon has already been used.']);
            exit;
        }

        if (!empty($coupon['start_date']) && $coupon['start_date'] != '0000-00-00' && $today < $coupon['start_date']) {
            echo json_encode(['success' => false, 'message' => 'This coupon is not active yet.']);
            exit;
        }
        if (!empty($coupon['end_date']) && $coupon['end_date'] != '0000-00-00' && $today > $coupon['end_date']) {
            echo json_encode(['success' => false, 'message' => 'This coupon has expired.']);
            exit;
        }

        $coupon_id = (int)$coupon['id'];
        $user_used = 0;
        $usage_res = mysqli_query($conn, "SELECT used_count FROM coupon_usage WHERE coupon_id = $coupon_id AND user_id = $user_id LIMIT 1");
        if ($usage_res && $row = mysqli_fetch_assoc($usage_res)) {
            $user_used = (int)$row['used_count'];
        }
        if (!empty($coupon['usage_limit']) && $user_used >= (int)$coupon['usage_limit']) {
            echo json_encode(['success' => false, 'message' => 'You have reached your usage limit for this coupon.']);
            exit;
        }

        $type = !empty($coupon['discount_type']) ? $coupon['discount_type'] : 'percentage';
        $value = !empty($coupon['discount_value']) ? $coupon['discount_value'] : (!empty($coupon['discount_percent']) ? $coupon['discount_percent'] : 0);

        if ($value <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid coupon value configuration.']);
            exit;
        }

        $_SESSION['applied_coupon'] = $code;

        echo json_encode([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'type' => $type,
            'value' => $value
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid coupon code.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
