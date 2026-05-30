<?php
// cart/add-to-cart.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';
include __DIR__ . '/../data/products.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Return JSON for AJAX, redirect for normal request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu', 'require_login' => true]);
        exit;
    }
    header('Location: ../auth/login.php');
    exit;
}

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
$isBuyNow = isset($_POST['buy_now']) && $_POST['buy_now'] === '1';
$redirect = $_POST['redirect'] ?? 'cart';

// Calculate price based on size
$price = $product['price'];
if ($size === 'Large') {
    $price = $product['price'] + 5000;
}

// If buy now, clear any previous buy_now_cart and set flag
if ($isBuyNow) {
    unset($_SESSION['buy_now_cart']);
    $_SESSION['buy_now_cart'] = [
        'menu_id' => $id,
        'quantity' => $quantity,
        'price' => $price,
        'ice_level' => $iceLevel,
        'sugar_level' => $sugarLevel,
        'size' => $size,
        'name' => $product['name'],
        'image' => $product['image']
    ];
}

$result = addToCart($id, $quantity, $price, $iceLevel, $sugarLevel, $size);

// Handle AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Berhasil ditambahkan ke keranjang',
            'cart_count' => getCartCount(),
            'is_buy_now' => $isBuyNow
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ke keranjang']);
    }
    exit;
}

// Determine redirect target for normal requests
if ($isBuyNow) {
    header('Location: ../checkout/checkout.php');
} elseif ($redirect === 'checkout') {
    header('Location: ../checkout/checkout.php');
} elseif ($redirect === 'home') {
    header('Location: ../home/home.php');
} else {
    header('Location: cart.php');
}
exit;
?>