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
    $stmt = $pdo->prepare("SELECT user_id, name, username, email, phone, address, role, birthdate, gender, bio, notifications_enabled FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Update user notification setting
 */
function updateNotificationSetting(int $userId, bool $enabled): bool {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET notifications_enabled = ? WHERE user_id = ?");
        return $stmt->execute([$enabled ? 1 : 0, $userId]);
    } catch (Exception $e) {
        error_log("Update notification setting failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user notification setting
 */
function isNotificationsEnabled(int $userId): bool {
    global $pdo;
    try {
        // First check if column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'notifications_enabled'");
        if ($stmt->rowCount() === 0) {
            // Column doesn't exist, default to enabled
            return true;
        }
        $stmt = $pdo->prepare("SELECT notifications_enabled FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetchColumn();
        // If NULL or column exists with any value, return true as default
        return $result !== '0';
    } catch (Exception $e) {
        return true; // Default to enabled if any error
    }
}

/**
 * Auto-add notifications_enabled column if not exists
 */
function initNotificationColumn(): void {
    global $pdo;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'notifications_enabled'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN notifications_enabled TINYINT(1) DEFAULT 1 AFTER role");
        }
    } catch (Exception $e) {
        // Ignore errors if column already exists
    }
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

/**
 * Auto-add user profile columns if not exists
 */
function initUserProfileColumns(): void {
    global $pdo;
    try {
        $columns = ['birthdate', 'gender', 'bio'];
        foreach ($columns as $col) {
            $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE '$col'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE users ADD COLUMN $col VARCHAR(255) DEFAULT NULL");
            }
        }
    } catch (Exception $e) {
        // Ignore
    }
}

/**
 * Update user profile
 */
function updateUserProfile(int $userId, array $data): bool {
    global $pdo;
    try {
        // Auto-create columns if needed
        initUserProfileColumns();

        $fields = [];
        $values = [];

        $allowedFields = ['name', 'phone', 'birthdate', 'gender', 'bio', 'username', 'address'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        // Refresh session
        $stmt = $pdo->prepare("SELECT user_id, name, username, email, phone, address, role FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch();
        if ($updatedUser) {
            $_SESSION['user_id'] = $updatedUser['user_id'];
            $_SESSION['role'] = $updatedUser['role'];
        }

        return true;
    } catch (Exception $e) {
        error_log("Update profile failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Change user password
 */
function changePassword(int $userId, string $currentPassword, string $newPassword): array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => 'User tidak ditemukan'];
        }

        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Kata sandi lama salah'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => 'Kata sandi baru minimal 6 karakter'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $userId]);

        return ['success' => true];
    } catch (Exception $e) {
        error_log("Change password failed: " . $e->getMessage());
        return ['success' => false, 'error' => 'Terjadi kesalahan. Silakan coba lagi.'];
    }
}

initUserProfileColumns();
initNotificationColumn();
?>
