<?php
// api/cart.php - API untuk keranjang
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cart.php';
require_once __DIR__ . '/../config/auth.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($method === 'GET') {
    $items = getCartItems();
    $total = getCartTotal();
    $count = getCartCount();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'total' => $total,
            'count' => $count
        ]
    ]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $menuId = (int) ($input['menu_id'] ?? 0);
    $quantity = (int) ($input['quantity'] ?? 1);
    $price = (int) ($input['price'] ?? 0);
    $iceLevel = $input['ice_level'] ?? null;
    $sugarLevel = $input['sugar_level'] ?? null;
    $size = $input['size'] ?? null;
    
    if (addToCart($menuId, $quantity, $price, $iceLevel, $sugarLevel, $size)) {
        echo json_encode(['success' => true, 'message' => 'Item ditambahkan ke keranjang']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan item']);
    }
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $itemId = (int) ($input['item_id'] ?? 0);
    $quantity = (int) ($input['quantity'] ?? 1);
    
    if (updateCartItem($itemId, $quantity)) {
        echo json_encode(['success' => true, 'message' => 'Keranjang diperbarui']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui keranjang']);
    }
} elseif ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $itemId = (int) ($input['item_id'] ?? $_GET['item_id'] ?? 0);
    
    if (removeFromCart($itemId)) {
        echo json_encode(['success' => true, 'message' => 'Item dihapus dari keranjang']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus item']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
