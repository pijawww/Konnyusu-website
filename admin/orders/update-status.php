<?php
// admin/orders/update-status.php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/order.php';

requireAdmin();

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $cancellationNote = $_POST['cancellation_note'] ?? '';

    // Validasi status
    $validStatuses = ['pending', 'processing', 'sent', 'completed', 'cancelled'];
    if (in_array($status, $validStatuses)) {
        updateOrderStatus($orderId, $status, $cancellationNote);
    }
}

// Redirect back
header('Location: orders.php');
exit;
?>
