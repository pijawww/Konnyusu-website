<?php
// admin/orders/mark-viewed.php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin.php';

requireAdmin();

if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
    markOrderAsViewed($orderId);
}

header('Location: orders.php');
exit;
?>
