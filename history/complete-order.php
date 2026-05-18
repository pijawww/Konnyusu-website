<?php
// history/complete-order.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/order.php';

requireLogin();

if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
    $currentUser = getCurrentUser();
    
    // Verify order belongs to user
    $order = getOrder($orderId);
    if ($order && $order['user_id'] == $currentUser['user_id']) {
        // Only allow if status is shipped or processing
        if (in_array($order['order_status'], ['shipped', 'processing'])) {
            updateOrderStatus($orderId, 'completed');
        }
    }
}

header('Location: history.php');
exit;
?>
