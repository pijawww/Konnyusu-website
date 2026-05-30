<?php
// history/repeat-order.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';
require_once __DIR__ . '/../config/order.php';
require_once __DIR__ . '/../data/products.php';

requireLogin();

if (isAdmin()) {
    header('Location: ../admin/dashboard/dashboard.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('Location: history.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$orderItems = getOrderItems($orderId);

if (empty($orderItems)) {
    header('Location: history.php');
    exit;
}

// Add all items to cart
foreach ($orderItems as $item) {
    $product = findProduct($item['menu_id']);
    if ($product) {
        addToCart(
            $item['menu_id'],
            $item['quantity'],
            $item['price'],
            $item['ice_level'] ?? null,
            $item['sugar_level'] ?? null,
            $item['size'] ?? null
        );
    }
}

header('Location: ../cart/cart.php');
exit;
?>
