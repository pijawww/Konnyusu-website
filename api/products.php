<?php
// api/products.php - API untuk produk
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../data/products.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Get single product
        $product = findProduct((int)$_GET['id']);
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        }
    } else {
        // Get all products
        $products = getProducts();
        echo json_encode(['success' => true, 'data' => $products]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
