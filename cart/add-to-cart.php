<?php
session_start();
require_once __DIR__ . '/../config/cart.php';
include __DIR__ . '/../data/products.php';

$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

$product = findProduct($id);

if (!$product) {
    header('Location: ../home/home.php');
    exit;
}

$quantity = (int) ($_POST['qty'] ?? $_POST['quantity'] ?? 1);
$iceLevel = $_POST['ice_level'] ?? 'Normal Ice';
$sugarLevel = $_POST['sugar_level'] ?? 'Normal';
$size = $_POST['size'] ?? 'Regular';

// Calculate price based on size
$price = $product['price'];
if ($size === 'Large') {
    // Assuming Large adds 5000 to base price
    $price = $product['price'] + 5000;
}

addToCart($id, $quantity, $price, $iceLevel, $sugarLevel, $size);

// Determine redirect target
$redirect = $_POST['redirect'] ?? 'cart';
if ($redirect === 'checkout') {
    header('Location: ../checkout/checkout.php');
} else {
    header('Location: cart.php');
}
exit;
?>