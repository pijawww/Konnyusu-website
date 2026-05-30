<?php
// cart/update-cart.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';

if (!isLoggedIn() || isAdmin()) {
    http_response_code(403);
    exit;
}

// Handle POST request (update quantity, ice_level, sugar_level)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $quantity = isset($_POST['quantity']) ? max(1, min(99, (int)$_POST['quantity'])) : null;
    $iceLevel = isset($_POST['ice_level']) ? $_POST['ice_level'] : null;
    $sugarLevel = isset($_POST['sugar_level']) ? $_POST['sugar_level'] : null;

    $cartItems = getCartItems();
    foreach ($cartItems as $index => $item) {
        $itemId = $item['cart_item_id'] ?? $index;
        if ($itemId == $id) {
            // Update quantity
            if ($quantity !== null) {
                updateCartItem($itemId, $quantity);
            }
            // Update ice level
            if ($iceLevel !== null) {
                updateCartItemOption($itemId, 'ice_level', $iceLevel);
            }
            // Update sugar level
            if ($sugarLevel !== null) {
                updateCartItemOption($itemId, 'sugar_level', $sugarLevel);
            }
            break;
        }
    }
    exit;
}

// Handle GET request (increase/decrease action)
if (isset($_GET['id'], $_GET['action'])) {
    $id     = (int)$_GET['id'];
    $action = $_GET['action'];

    $cartItems = getCartItems();
    foreach ($cartItems as $index => $item) {
        $itemId = $item['cart_item_id'] ?? $index;
        if ($itemId == $id) {
            $newQty = $item['quantity'];
            if ($action === 'increase') {
                $newQty++;
            } elseif ($action === 'decrease') {
                $newQty--;
            }

            if ($newQty <= 0) {
                removeFromCart($itemId);
            } else {
                updateCartItem($itemId, $newQty);
            }
            break;
        }
    }
}

header('Location: cart.php');
exit;
