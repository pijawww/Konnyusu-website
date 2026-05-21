<?php
// data/products.php

require_once __DIR__ . '/../config/database.php';

/**
 * Get all products from database with real sold count from completed orders
 */
function getProducts(): array {
    global $pdo;
    $stmt = $pdo->query("
        SELECT m.menu_id AS id, m.name, m.description, m.price, m.category, m.image, m.stock, m.is_new, m.is_best,
            COALESCE(SUM(oi.quantity), 0) AS sold
        FROM menu m
        LEFT JOIN order_item oi ON m.menu_id = oi.menu_id
        LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'completed'
        GROUP BY m.menu_id
        ORDER BY m.menu_id
    ");
    return $stmt->fetchAll();
}

/**
 * Get single product by id with real sold count
 */
function findProduct(int $id): ?array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.menu_id AS id, m.name, m.description, m.price, m.category, m.image, m.stock, m.is_new, m.is_best,
            COALESCE(SUM(oi.quantity), 0) AS sold
        FROM menu m
        LEFT JOIN order_item oi ON m.menu_id = oi.menu_id
        LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'completed'
        WHERE m.menu_id = ?
        GROUP BY m.menu_id
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

/**
 * Helper: format rupiah
 */
function formatRupiah(int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Initialize products array for backward compatibility
$products = getProducts();
?>
