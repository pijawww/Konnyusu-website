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
$iceLevel = $_POST['ice_level'] ?? 'normal';
$sugarLevel = $_POST['sugar_level'] ?? 'normal';

addToCart($id, $quantity, $product['price'], $iceLevel, $sugarLevel, 'Regular');

header('Location: cart.php');
exit;
?>