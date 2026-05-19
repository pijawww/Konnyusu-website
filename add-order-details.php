<?php
// Script untuk menambahkan kolom informasi penerima ke tabel orders
require_once __DIR__ . '/config/database.php';

try {
    echo "Menambahkan kolom ke tabel orders...\n";
    
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
            echo "✓ Kolom ditambahkan: $column\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "○ Kolom sudah ada: $column\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "\n✓ Semua kolom berhasil ditambahkan!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>