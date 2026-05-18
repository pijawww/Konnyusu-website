<?php
// cart/update-cart.php
session_start();
require_once __DIR__ . '/../config/cart.php';

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
