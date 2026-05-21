<?php
// history/mark-notifications-read.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/order.php';

requireLogin();

$currentUser = getCurrentUser();
if ($currentUser) {
    $result = markAllNotificationsAsRead($currentUser['user_id']);
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
}
?>