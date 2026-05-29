<?php
// config/order.php
require_once __DIR__ . '/database.php';

/**
 * Create new order from cart
 */
function createOrder(int $userId, array $cartItems, string $orderType = 'dine_in', string $notes = '', string $paymentMethod = 'cash', array $recipientData = []): ?int {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Calculate delivery fee based on method
        $deliveryFees = [
            'priority' => 8000,
            'standard' => 5000,
            'pickup' => 0
        ];
        $deliveryFee = $deliveryFees[$orderType] ?? 5000;
        
        // Apply free delivery if subtotal >= 50000 and not pickup
        if ($total >= 50000 && $orderType !== 'pickup' && $orderType !== 'takeaway') {
            $deliveryFee = 0;
        }
        
        $tax = (int) round($total * 0.01);
        $grandTotal = $total + $deliveryFee + $tax;
        
        // Insert order with recipient data
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_type, total, notes, recipient_name, recipient_phone, recipient_address, recipient_city, recipient_postal, delivery_fee, tax, viewed_by_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([
            $userId, 
            $orderType, 
            $grandTotal, 
            $notes,
            $recipientData['name'] ?? null,
            $recipientData['phone'] ?? null,
            $recipientData['address'] ?? null,
            $recipientData['city'] ?? null,
            $recipientData['postal'] ?? null,
            $deliveryFee,
            $tax
        ]);
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
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get order by id
 */
function getOrder(int $orderId): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, u.name AS user_name, u.email AS user_email, p.payment_method, p.payment_status FROM orders o LEFT JOIN users u ON o.user_id = u.user_id LEFT JOIN payment p ON o.order_id = p.order_id WHERE o.order_id = ?");
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
function updateOrderStatus(int $orderId, string $status, string $cancellationNote = ''): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, cancellation_note = ?, viewed_by_user = 0 WHERE order_id = ?");
        $stmt->execute([$status, $cancellationNote, $orderId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get cancellation note for an order
 */
function getCancellationNote(int $orderId): string {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT cancellation_note FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() ?: '';
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Get unviewed order count for user
 */
function getUserUnviewedCount(int $userId): int {
    global $pdo;
    try {
        // Count only active orders (processing/shipped) that are unread
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND viewed_by_user = 0 AND order_status IN ('processing', 'shipped')");
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get unviewed notification count (only unread notifications)
 */
function getUnviewedNotificationCount(int $userId): int {
    global $pdo;
    try {
        // Count all order status changes that are unread (processing, shipped, completed, cancelled)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND viewed_by_user = 0 AND order_status IN ('processing', 'shipped', 'completed', 'cancelled')");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get user notifications from orders
 */
function getUserNotifications(int $userId, int $limit = 10): array {
    global $pdo;
    try {
        // Get ALL order changes that need to be notified (processing, shipped, completed, cancelled)
        // Use hardcoded LIMIT to avoid PDO parameter issues
        $sql = "SELECT order_id AS id, order_status, viewed_by_user AS is_read, order_date AS created_at, cancellation_note
                FROM orders WHERE user_id = ? AND order_status IN ('processing', 'shipped', 'completed', 'cancelled')
                ORDER BY order_date DESC LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();

        $notifications = [];
        foreach ($orders as $order) {
            $title = '';
            $message = '';
            $actionText = '';
            $iconClass = '';
            $statusLabel = '';

            switch ($order['order_status']) {
                case 'processing':
                    $title = 'Pesanan Sedang Diproses';
                    $statusLabel = 'DIPROSES';
                    $actionText = '🔄 Admin telah mengklik tombol "PROSES"';
                    $message = "Pesanan #{$order['id']} sekarang sedang diproses oleh tim kami. Mohon tunggu beberapa saat ya!";
                    $iconClass = 'processing';
                    break;
                case 'shipped':
                    $title = 'Pesanan Sedang Dikirim';
                    $statusLabel = 'DIKIRIM';
                    $actionText = '📦 Admin telah mengklik tombol "KIRIM"';
                    $message = "Pesanan #{$order['id']} sedang dalam perjalanan. Ditunggu aja, sebentar lagi sampai!";
                    $iconClass = 'shipped';
                    break;
                case 'completed':
                    $title = 'Pesanan Selesai';
                    $statusLabel = 'SELESAI';
                    $actionText = '✅ Pesanan telah selesai dan diterima';
                    $message = "Yeay! Pesanan #{$order['id']} telah selesai. Terima kasih sudah memesan di Konnyusu!";
                    $iconClass = 'completed';
                    break;
                case 'cancelled':
                    $title = 'Pesanan Dibatalkan';
                    $statusLabel = 'DIBATALKAN';
                    $actionText = !empty($order['cancellation_note'])
                        ? '❌ Admin membatalkan. Alasan: ' . $order['cancellation_note']
                        : '❌ Admin membatalkan pesanan ini';
                    $message = $order['cancellation_note']
                        ? "Mohon maaf, pesanan #{$order['id']} telah dibatalkan. Alasan: " . $order['cancellation_note']
                        : "Mohon maaf, pesanan #{$order['id']} telah dibatalkan oleh admin.";
                    $iconClass = 'cancelled';
                    break;
            }

            if ($title) {
                $notifications[] = [
                    'id' => $order['id'],
                    'title' => $title,
                    'message' => $message,
                    'order_status' => $order['order_status'],
                    'order_id' => $order['id'],
                    'is_read' => $order['is_read'],
                    'created_at' => $order['created_at'],
                    'action_text' => $actionText,
                    'status_label' => $statusLabel,
                    'icon_class' => $iconClass,
                    'cancellation_note' => $order['cancellation_note'] ?? ''
                ];
            }
        }
        return $notifications;
    } catch (Exception $e) {
        error_log('getUserNotifications error: ' . $e->getMessage());
        return [];
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
 * Mark notification as read (calls markOrderAsViewedByUser)
 */
function markNotificationAsRead(int $orderId, int $userId): bool {
    return markOrderAsViewedByUser($orderId, $userId);
}

/**
 * Mark all notifications as read for user
 */
function markAllNotificationsAsRead(int $userId): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET viewed_by_user = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
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

/**
 * Auto-add cancellation_note column if not exists
 */
function initCancellationNoteColumn(): void {
    global $pdo;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'cancellation_note'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN cancellation_note TEXT AFTER order_status");
        }
    } catch (Exception $e) {
        // Ignore
    }
}

initUserNotificationColumn();
initCancellationNoteColumn();

/**
 * Get real sold count per product from completed orders
 * More accurate than the denormalized menu.sold column
 */
function getRealSoldCount(?int $menuId = null): int {
    global $pdo;
    if ($menuId !== null) {
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(oi.quantity), 0)
            FROM order_item oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.menu_id = ? AND o.order_status = 'completed'
        ");
        $stmt->execute([$menuId]);
        return (int) $stmt->fetchColumn();
    }
    // Total sold across all products
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(oi.quantity), 0)
        FROM order_item oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_status = 'completed'
    ");
    return (int) $stmt->fetchColumn();
}

/**
 * Get all products with real sold count from completed orders
 */
function getProductsWithRealSold(): array {
    global $pdo;
    $stmt = $pdo->query("
        SELECT m.menu_id AS id, m.name, m.description, m.price, m.category, m.image, m.stock, m.is_new, m.is_best,
            COALESCE(SUM(oi.quantity), 0) AS sold,
            COALESCE(SUM(oi.quantity), 0) AS real_sold
        FROM menu m
        LEFT JOIN order_item oi ON m.menu_id = oi.menu_id
        LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'completed'
        GROUP BY m.menu_id
        ORDER BY m.menu_id DESC
    ");
    return $stmt->fetchAll();
}
?>
