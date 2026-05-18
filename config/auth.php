<?php
// config/auth.php
require_once __DIR__ . '/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged in user
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT user_id, name, username, email, phone, address, role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Login user
 */
function login(string $email, string $password): bool {
    global $pdo;
    $stmt = $pdo->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

/**
 * Register new user
 */
function register(string $name, string $email, string $password, string $phone = null, string $address = null): bool {
    global $pdo;
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $address]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Logout user
 */
function logout(): void {
    session_unset();
    session_destroy();
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/../auth/login.php');
        exit;
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/../home/home.php');
        exit;
    }
}
?>
