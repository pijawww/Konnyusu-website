<?php
// cart/remove-from-cart.php
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

$id = 0;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
}
// Handle GET request
elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if ($id > 0) {
    removeFromCart($id);
}

header('Location: cart.php');
exit;
