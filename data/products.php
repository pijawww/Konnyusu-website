<?php
// data/products.php

require_once __DIR__ . '/../config/database.php';

/**
 * Get all products from database
 */
function getProducts(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT menu_id AS id, name, description, price, category, image, stock, is_new, is_best, sold FROM menu ORDER BY menu_id");
    return $stmt->fetchAll();
}

/**
 * Get single product by id
 */
function findProduct(int $id): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT menu_id AS id, name, description, price, category, image, stock, is_new, is_best, sold FROM menu WHERE menu_id = ?");
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
