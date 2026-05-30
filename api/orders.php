<?php
// api/orders.php - API untuk pesanan
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/order.php';
require_once __DIR__ . '/../config/auth.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Harus login terlebih dahulu']);
    exit;
}

if ($method === 'GET') {
    if (isset($_GET['order_id'])) {
        $orderId = (int)$_GET['order_id'];
        $order = getOrder($orderId);
        $items = getOrderItems($orderId);
        
        if ($order) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'items' => $items
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        }
    } else {
        $orders = getUserOrders($_SESSION['user_id']);
        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $orderType = $input['order_type'] ?? 'dine_in';
    $notes = $input['notes'] ?? '';
    $paymentMethod = $input['payment_method'] ?? 'qris';
    
    require_once __DIR__ . '/../config/cart.php';
    $cartItems = getCartItems();
    
    if (empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
        exit;
    }
    
    $orderItems = [];
    foreach ($cartItems as $item) {
        $orderItems[] = [
            'id' => $item['menu_id'] ?? $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => $item['image'],
            'ice_level' => $item['ice_level'] ?? null,
            'sugar_level' => $item['sugar_level'] ?? null,
            'size' => $item['size'] ?? null
        ];
    }
    
    $orderId = createOrder($_SESSION['user_id'], $orderItems, $orderType, $notes, $paymentMethod);

    if ($orderId) {
        clearCart();
        echo json_encode([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'data' => ['order_id' => $orderId]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal membuat pesanan']);
    }
} elseif ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_payment_proof') {
    // Upload payment proof
    $orderId = (int)($_POST['order_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    // Verify order belongs to user
    global $pdo;
    $stmt = $pdo->prepare("SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        exit;
    }

    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File tidak valid']);
        exit;
    }

    $file = $_FILES['payment_proof'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format file harus JPG atau PNG']);
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2MB']);
        exit;
    }

    $uploadDir = __DIR__ . '/../assets/uploads/payment_proofs/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'proof_' . $orderId . '_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("UPDATE payment SET payment_proof = ?, payment_date = NOW() WHERE order_id = ?");
        $stmt->execute([$newFileName, $orderId]);

        echo json_encode([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload',
            'data' => ['proof_url' => 'assets/uploads/payment_proofs/' . $newFileName]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
