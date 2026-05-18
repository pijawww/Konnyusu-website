<?php
// config/order.php
require_once __DIR__ . '/database.php';

/**
 * Create new order from cart
 */
function createOrder(int $userId, array $cartItems, string $orderType = 'dine_in', string $notes = '', string $paymentMethod = 'cash'): ?int {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Add delivery fee if needed
        $deliveryFee = $total >= 50000 ? 0 : 5000;
        $tax = (int) round($total * 0.01);
        $grandTotal = $total + $deliveryFee + $tax;
        
        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_type, total, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $orderType, $grandTotal, $notes]);
        $orderId = $pdo->lastInsertId();
        
        // Insert order items
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_item (order_id, menu_id, quantity, price, ice_level, sugar_level, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $item['ice_level'] ?? null,
                $item['sugar_level'] ?? null,
                $item['size'] ?? null
            ]);
            
            // Update sold count
            $stmt = $pdo->prepare("UPDATE menu SET sold = sold + ? WHERE menu_id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
        }
        
        // Insert payment
        $stmt = $pdo->prepare("INSERT INTO payment (order_id, payment_method, amount, payment_status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$orderId, $paymentMethod, $grandTotal]);
        
        $pdo->commit();
        return $orderId;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Get user orders
 */
function getUserOrders(int $userId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT order_id, order_date, order_status, total, order_type FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get order by id
 */
function getOrder(int $orderId): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, u.name AS user_name, u.email AS user_email FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetch() ?: null;
}

/**
 * Get order items
 */
function getOrderItems(int $orderId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT oi.*, m.name, m.image FROM order_item oi LEFT JOIN menu m ON oi.menu_id = m.menu_id WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

/**
 * Get all orders (admin)
 */
function getAllOrders(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC");
    return $stmt->fetchAll();
}

/**
 * Update order status
 */
function updateOrderStatus(int $orderId, string $status): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->execute([$status, $orderId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get unviewed order count for user
 */
function getUserUnviewedCount(int $userId): int {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND viewed_by_user = 0");
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Mark order as viewed by user
 */
function markOrderAsViewedByUser(int $orderId, int $userId): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET viewed_by_user = 1 WHERE order_id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Auto-add viewed_by_user column if not exists
 */
function initUserNotificationColumn(): void {
    global $pdo;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'viewed_by_user'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN viewed_by_user TINYINT(1) DEFAULT 0 AFTER viewed_by_admin");
        }
    } catch (Exception $e) {
        // Ignore
    }
}

initUserNotificationColumn();
?>
