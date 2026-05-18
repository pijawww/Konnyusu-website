<?php
// api/auth.php - API untuk autentikasi
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS (CORS preflight)
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';
    
    if ($action === 'login') {
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        
        if (login($email, $password)) {
            $user = getCurrentUser();
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user_id' => $user['user_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Email atau password salah']);
        }
    } elseif ($action === 'register') {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        
        if (register($name, $email, $password)) {
            echo json_encode(['success' => true, 'message' => 'Registrasi berhasil']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        }
    } elseif ($action === 'logout') {
        logout();
        echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
} elseif ($method === 'GET') {
    if (isLoggedIn()) {
        $user = getCurrentUser();
        echo json_encode([
            'success' => true,
            'data' => [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Belum login']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
