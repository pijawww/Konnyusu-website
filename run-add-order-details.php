<?php
require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html><html><head><title>Update Database</title></head><body style='font-family:sans-serif;padding:2rem;'>";
echo "<h2>Menambahkan kolom ke tabel orders...</h2>";

$columns = [
    "recipient_name VARCHAR(100) AFTER notes",
    "recipient_phone VARCHAR(20) AFTER recipient_name",
    "recipient_address TEXT AFTER recipient_phone",
    "recipient_city VARCHAR(100) AFTER recipient_address",
    "recipient_postal VARCHAR(10) AFTER recipient_city",
    "delivery_fee INT DEFAULT 0 AFTER recipient_postal",
    "tax INT DEFAULT 0 AFTER delivery_fee"
];

foreach ($columns as $column) {
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN $column");
        echo "<p style='color:green;'>✓ Kolom ditambahkan: $column</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p style='color:orange;'>○ Kolom sudah ada: $column</p>";
        } else {
            echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<hr>";
echo "<p><strong>Selesai!</strong> <a href='admin/orders/orders.php'>Klik di sini untuk ke halaman admin pesanan</a></p>";
echo "</body></html>";
?>