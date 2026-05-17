<?php
session_start();
include __DIR__ . '/../data/products.php';

$id = (int) $_POST['id'];

$product = findProduct($id, $products);

if (!$product) {
    header('Location: ../home/home.php');
    exit;
}

$size = $_POST['size'];

$basePrice = $product['price'];

if ($size === 'Large') {
    $basePrice += 5000;
}

$size = $_POST['size'];

$basePrice = $product['price'];

if ($size === 'Large') {
    $basePrice += 5000;
}

$item = [
    'id' => $product['id'],
    'name' => $product['name'],
    'price' => $basePrice,
    'image' => $product['image'],
    'quantity' => (int) $_POST['quantity'],
    'ice_level' => $_POST['ice_level'],
    'sugar_level' => $_POST['sugar_level'],
    'size' => $size,
];

$_SESSION['cart'][] = $item;

header('Location: cart.php');
exit;
?>