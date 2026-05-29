<?php
// clean-orders.php - Bersihkan semua data pesanan
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

// Require admin
requireAdmin();

echo "<h2>🧹 Bersihkan Data Pesanan</h2>";
echo "<p>Menghapus semua data yang berhubungan dengan pesanan...</p>";

$results = [];

// 1. Delete order items
$stmt = $pdo->query("SELECT COUNT(*) FROM order_item");
$before = $stmt->fetchColumn();
$pdo->exec("TRUNCATE TABLE order_item");
$results[] = "✅ order_item: $before rows dihapus";

// 2. Delete payments
$stmt = $pdo->query("SELECT COUNT(*) FROM payment");
$before = $stmt->fetchColumn();
$pdo->exec("TRUNCATE TABLE payment");
$results[] = "✅ payment: $before rows dihapus";

// 3. Delete orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$before = $stmt->fetchColumn();
$pdo->exec("TRUNCATE TABLE orders");
$results[] = "✅ orders: $before rows dihapus";

// 4. Delete cart items
$stmt = $pdo->query("SELECT COUNT(*) FROM cart_item");
$before = $stmt->fetchColumn();
$pdo->exec("TRUNCATE TABLE cart_item");
$results[] = "✅ cart_item: $before rows dihapus";

// 5. Delete carts
$stmt = $pdo->query("SELECT COUNT(*) FROM cart");
$before = $stmt->fetchColumn();
$pdo->exec("TRUNCATE TABLE cart");
$results[] = "✅ cart: $before rows dihapus";

// 6. Delete sessions terkait cart (optional)
unset($_SESSION['cart']);
unset($_SESSION['buy_now_cart']);

echo "<h3>Hasil:</h3>";
echo "<ul>";
foreach ($results as $r) {
    echo "<li>$r</li>";
}
echo "</ul>";

echo "<h3>Sisa Data:</h3>";

// Check users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
echo "<p><strong>users:</strong> " . $stmt->fetchColumn() . " rows</p>";

// Check products/menu
$stmt = $pdo->query("SELECT COUNT(*) FROM menu");
echo "<p><strong>menu (produk):</strong> " . $stmt->fetchColumn() . " rows</p>";

// Verify orders empty
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
echo "<p><strong>orders:</strong> " . $stmt->fetchColumn() . " rows</p>";

// Verify cart empty
$stmt = $pdo->query("SELECT COUNT(*) FROM cart");
echo "<p><strong>cart:</strong> " . $stmt->fetchColumn() . " rows</p>";

echo "<p style='margin-top:20px;'><a href='../orders/orders.php' style='background:var(--primary);color:white;padding:10px 20px;text-decoration:none;border-radius:8px;'>Kembali ke Admin Orders</a></p>";
?>
