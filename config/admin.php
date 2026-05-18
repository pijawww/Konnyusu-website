<?php
// config/admin.php - Admin helper functions
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

/**
 * Auto-add viewed_by_admin column if not exists
 */
function initNotificationColumn(): void {
    global $pdo;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'viewed_by_admin'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN viewed_by_admin TINYINT(1) DEFAULT 0 AFTER order_status");
        }
    } catch (Exception $e) {
        // Ignore errors if column already exists
    }
}

initNotificationColumn();

/**
 * Get all menu items
 */
function getAllMenu(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM menu ORDER BY menu_id DESC");
    return $stmt->fetchAll();
}

/**
 * Get single menu item
 */
function getMenu(int $menuId): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE menu_id = ?");
    $stmt->execute([$menuId]);
    return $stmt->fetch() ?: null;
}

/**
 * Alias for getMenu() for compatibility
 */
function getMenuById(int $menuId): ?array {
    return getMenu($menuId);
}

/**
 * Add new menu item
 */
function addMenu(string $name, string $description, int $price, string $category, ?string $image = null, int $stock = 0, bool $isNew = false, bool $isBest = false): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO menu (name, description, price, category, image, stock, is_new, is_best) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $description, $price, $category, $image, $stock, $isNew, $isBest]);
    } catch (Exception $e) {
        error_log("Add menu failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Update menu item
 */
function updateMenu(int $menuId, string $name, string $description, int $price, string $category, ?string $image = null, int $stock = 0, bool $isNew = false, bool $isBest = false): bool {
    global $pdo;
    try {
        if ($image) {
            $stmt = $pdo->prepare("UPDATE menu SET name=?, description=?, price=?, category=?, image=?, stock=?, is_new=?, is_best=? WHERE menu_id=?");
            return $stmt->execute([$name, $description, $price, $category, $image, $stock, $isNew, $isBest, $menuId]);
        } else {
            $stmt = $pdo->prepare("UPDATE menu SET name=?, description=?, price=?, category=?, stock=?, is_new=?, is_best=? WHERE menu_id=?");
            return $stmt->execute([$name, $description, $price, $category, $stock, $isNew, $isBest, $menuId]);
        }
    } catch (Exception $e) {
        error_log("Update menu failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete menu item
 */
function deleteMenu(int $menuId): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM menu WHERE menu_id = ?");
        return $stmt->execute([$menuId]);
    } catch (Exception $e) {
        error_log("Delete menu failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get complete sales stats for dashboard
 */
function getCompleteStats(): array {
    global $pdo;
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $totalOrders = $stmt->fetch()['count'];
    
    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $pendingOrders = $stmt->fetch()['count'];
    
    // Total revenue (only completed or pending)
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE order_status IN ('pending', 'processing', 'completed', 'shipped')");
    $totalRevenue = $stmt->fetch()['total'];
    
    // Total customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $totalCustomers = $stmt->fetch()['count'];
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM menu");
    $totalProducts = $stmt->fetch()['count'];
    
    // Total products sold
    $stmt = $pdo->query("SELECT COALESCE(SUM(quantity), 0) as total FROM order_item");
    $totalSold = $stmt->fetch()['total'];
    
    return [
        'total_orders' => $totalOrders,
        'pending_orders' => $pendingOrders,
        'total_revenue' => $totalRevenue,
        'total_customers' => $totalCustomers,
        'total_products' => $totalProducts,
        'total_sold' => $totalSold
    ];
}

/**
 * Get recent orders
 */
function getRecentOrders(int $limit = 10): array {
    global $pdo;
    $limit = max(1, $limit);
    $sql = "SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC LIMIT $limit";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get unviewed orders count (with fallback)
 */
function getUnviewedOrdersCount(): int {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE viewed_by_admin = 0");
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get unviewed orders (with fallback)
 */
function getUnviewedOrders(): array {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.viewed_by_admin = 0 ORDER BY o.order_date DESC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Mark order as viewed (with fallback)
 */
function markOrderAsViewed(int $orderId): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE orders SET viewed_by_admin = 1 WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return true;
    } catch (Exception $e) {
        error_log("Mark order viewed failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get top selling products
 */
function getTopSellingProducts(int $limit = 5): array {
    global $pdo;
    
    $sql = "SELECT m.*, COALESCE(SUM(oi.quantity), 0) as total_sold 
            FROM menu m 
            LEFT JOIN order_item oi ON m.menu_id = oi.menu_id 
            GROUP BY m.menu_id 
            ORDER BY total_sold DESC 
            LIMIT $limit";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get weekly revenue data (for charts)
 */
function getWeeklyRevenue(): array {
    global $pdo;
    
    $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    $revenues = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE DATE(order_date) = ? AND order_status IN ('pending', 'processing', 'completed', 'shipped')");
        $stmt->execute([$date]);
        $revenues[] = $stmt->fetch()['total'];
    }
    
    $maxRevenue = max($revenues) ?: 1;
    $data = [];
    
    foreach ($days as $i => $day) {
        $percent = $maxRevenue > 0 ? round(($revenues[$i] / $maxRevenue) * 100) : 0;
        $data[] = [
            'day' => $day,
            'percent' => max(10, $percent) . '%', // Minimum 10% for visibility
            'is_max' => $revenues[$i] === $maxRevenue && $maxRevenue > 0
        ];
    }
    
    return $data;
}
?>
