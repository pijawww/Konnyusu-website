<?php
// config/cart.php - Helper for cart database operations
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

/**
 * Get or create active cart for current user
 */
function getOrCreateCart(): int {
    global $pdo;
    
    if (!isLoggedIn()) {
        // For guests, use session-based cart (fallback)
        return 0;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Check if active cart exists
    $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch();
    
    if ($cart) {
        return $cart['cart_id'];
    }
    
    // Create new cart
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, status) VALUES (?, 'active')");
    $stmt->execute([$userId]);
    return $pdo->lastInsertId();
}

/**
 * Add item to cart
 */
function addToCart(int $menuId, int $quantity, int $price, ?string $iceLevel = null, ?string $sugarLevel = null, ?string $size = null): bool {
    global $pdo;
    
    $cartId = getOrCreateCart();
    $subtotal = $price * $quantity;
    
    try {
        if ($cartId > 0) {
            // Database cart (logged in)
            // Check if item already exists
            $stmt = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_item WHERE cart_id = ? AND menu_id = ? AND ice_level <=> ? AND sugar_level <=> ? AND size <=> ? LIMIT 1");
            $stmt->execute([$cartId, $menuId, $iceLevel, $sugarLevel, $size]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing item
                $newQty = $existing['quantity'] + $quantity;
                $newSubtotal = $price * $newQty;
                $stmt = $pdo->prepare("UPDATE cart_item SET quantity = ?, subtotal = ? WHERE cart_item_id = ?");
                $stmt->execute([$newQty, $newSubtotal, $existing['cart_item_id']]);
            } else {
                // Add new item
                $stmt = $pdo->prepare("INSERT INTO cart_item (cart_id, menu_id, quantity, price, subtotal, ice_level, sugar_level, size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$cartId, $menuId, $quantity, $price, $subtotal, $iceLevel, $sugarLevel, $size]);
            }
        } else {
            // Session cart (guest)
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            
            $found = false;
            foreach ($_SESSION['cart'] as $key => &$item) {
                if ($item['id'] == $menuId && 
                    ($item['ice_level'] ?? null) == $iceLevel && 
                    ($item['sugar_level'] ?? null) == $sugarLevel && 
                    ($item['size'] ?? null) == $size) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $_SESSION['cart'][] = [
                    'cart_item_id' => uniqid('sess_', true),
                    'id' => $menuId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'ice_level' => $iceLevel,
                    'sugar_level' => $sugarLevel,
                    'size' => $size
                ];
            }
        }
        return true;
    } catch (Exception $e) {
        error_log("Add to cart failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get cart items
 */
function getCartItems(): array {
    global $pdo;
    
    $cartId = getOrCreateCart();
    
    if ($cartId > 0) {
        // Database cart
        $stmt = $pdo->prepare("SELECT ci.*, m.name, m.image FROM cart_item ci LEFT JOIN menu m ON ci.menu_id = m.menu_id WHERE ci.cart_id = ?");
        $stmt->execute([$cartId]);
        return $stmt->fetchAll();
    } else {
        // Session cart with product details
        $items = $_SESSION['cart'] ?? [];
        $fullItems = [];
        foreach ($items as $key => $item) {
            $product = findProduct($item['id']);
            if ($product) {
                $fullItems[] = array_merge($item, [
                    'name' => $product['name'],
                    'image' => $product['image'],
                    'menu_id' => $item['id'],
                    'cart_item_id' => $item['cart_item_id'] ?? $key
                ]);
            }
        }
        return $fullItems;
    }
}

/**
 * Update cart item quantity
 */
function updateCartItem(int $itemId, int $quantity): bool {
    global $pdo;

    $cartId = getOrCreateCart();
    if ($cartId > 0) {
        // Database cart
        $stmt = $pdo->prepare("SELECT price FROM cart_item WHERE cart_item_id = ? AND cart_id = ? LIMIT 1");
        $stmt->execute([$itemId, $cartId]);
        $item = $stmt->fetch();

        if ($item) {
            $subtotal = $item['price'] * $quantity;
            $stmt = $pdo->prepare("UPDATE cart_item SET quantity = ?, subtotal = ? WHERE cart_item_id = ?");
            return $stmt->execute([$quantity, $subtotal, $itemId]);
        }
        return false;
    } else {
        // Session cart - find by cart_item_id
        foreach ($_SESSION['cart'] as $key => $item) {
            if (isset($item['cart_item_id']) && $item['cart_item_id'] == $itemId) {
                $_SESSION['cart'][$key]['quantity'] = $quantity;
                return true;
            }
        }
        return false;
    }
}

/**
 * Update cart item option (ice_level, sugar_level, size)
 */
function updateCartItemOption(int $itemId, string $option, string $value): bool {
    global $pdo;

    $cartId = getOrCreateCart();
    $allowedOptions = ['ice_level', 'sugar_level', 'size'];

    if (!in_array($option, $allowedOptions)) {
        return false;
    }

    if ($cartId > 0) {
        // Database cart
        $stmt = $pdo->prepare("UPDATE cart_item SET $option = ? WHERE cart_item_id = ? AND cart_id = ?");
        return $stmt->execute([$value, $itemId, $cartId]);
    } else {
        // Session cart
        foreach ($_SESSION['cart'] as $key => $item) {
            if (isset($item['cart_item_id']) && $item['cart_item_id'] == $itemId) {
                $_SESSION['cart'][$key][$option] = $value;
                return true;
            }
        }
        return false;
    }
}

/**
 * Remove item from cart
 */
function removeFromCart($itemId): bool {
    global $pdo;
    
    $cartId = getOrCreateCart();
    if ($cartId > 0) {
        // Database cart
        $stmt = $pdo->prepare("DELETE FROM cart_item WHERE cart_item_id = ? AND cart_id = ?");
        return $stmt->execute([$itemId, $cartId]);
    } else {
        // Session cart - find by cart_item_id
        foreach ($_SESSION['cart'] as $key => $item) {
            if (isset($item['cart_item_id']) && $item['cart_item_id'] == $itemId) {
                array_splice($_SESSION['cart'], $key, 1);
                return true;
            }
        }
        // Fallback for numeric index
        if (is_numeric($itemId) && isset($_SESSION['cart'][$itemId])) {
            array_splice($_SESSION['cart'], $itemId, 1);
            return true;
        }
        return false;
    }
}

/**
 * Clear cart
 */
function clearCart(): bool {
    global $pdo;
    
    $cartId = getOrCreateCart();
    if ($cartId > 0) {
        // Database cart
        $stmt = $pdo->prepare("DELETE FROM cart_item WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        $stmt = $pdo->prepare("UPDATE cart SET status = 'checkout' WHERE cart_id = ?");
        return $stmt->execute([$cartId]);
    } else {
        // Session cart
        $_SESSION['cart'] = [];
        return true;
    }
}

/**
 * Get cart total
 */
function getCartTotal(): int {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
    }
    return $total;
}

/**
 * Get cart count
 */
function getCartCount(): int {
    $items = getCartItems();
    $count = 0;
    foreach ($items as $item) {
        $count += $item['quantity'] ?? 0;
    }
    return $count;
}
?>
