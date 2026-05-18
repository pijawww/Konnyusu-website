<?php
// cart/remove-from-cart.php
session_start();
require_once __DIR__ . '/../config/cart.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    removeFromCart($id);
}

header('Location: cart.php');
exit;
