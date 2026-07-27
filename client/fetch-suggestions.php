<?php
include 'includes/db.php';
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';

if (strlen($q) > 0) {
    echo '<ul class="suggestion-list">';

    echo '<li style="color: #ff8002; font-weight: 500;">
            <i class="fas fa-store"></i> Search "' . htmlspecialchars($q) . '" Shops
          </li>';

    $sql = "SELECT DISTINCT name FROM products WHERE name LIKE '%$q%' LIMIT 6";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $bold_name = str_ireplace($q, "<strong>$q</strong>", $row['name']);
        echo '<li onclick="selectSuggestion(\'' . addslashes($row['name']) . '\')">' . $bold_name . '</li>';
    }
    echo '</ul>';
}
?>
