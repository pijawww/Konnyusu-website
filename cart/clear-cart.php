<?php
// cart/clear-cart.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

if (isAdmin()) {
    header('Location: ../admin/dashboard/dashboard.php');
    exit;
}

clearCart();

header('Location: cart.php');
exit;
